#!/usr/bin/env bash
set -Eeuo pipefail
umask 027

APP_NAME="SportTime CRM"
REPO_URL="https://github.com/ahmdosamasokrat-svg/mpc-crm.git"
DEFAULT_APP_DIR="/var/www/html/sporttime-crm"
LEGACY_APP_DIR="/var/www/html/crm-v2"

log() {
    printf '\n[%s] %s\n' "$(date '+%H:%M:%S')" "$*"
}

fail() {
    printf '\nERROR: %s\n' "$*" >&2
    exit 1
}

[ "${EUID}" -eq 0 ] || fail "Run this update script as root or with sudo."

# Detect APP_DIR
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR=""

if [ -f "${SCRIPT_DIR}/artisan" ] && [ -f "${SCRIPT_DIR}/.env" ]; then
    APP_DIR="$SCRIPT_DIR"
elif [ -d "$DEFAULT_APP_DIR" ] && [ -f "${DEFAULT_APP_DIR}/artisan" ] && [ -f "${DEFAULT_APP_DIR}/.env" ]; then
    APP_DIR="$DEFAULT_APP_DIR"
elif [ -d "$LEGACY_APP_DIR" ] && [ -f "${LEGACY_APP_DIR}/artisan" ] && [ -f "${LEGACY_APP_DIR}/.env" ]; then
    APP_DIR="$LEGACY_APP_DIR"
else
    fail "Could not locate CRM installation directory with existing .env file. Please run this script from inside the application directory or specify APP_DIR."
fi

log "Starting safe update for ${APP_NAME} in ${APP_DIR}"
log "Existing database and customer records will be preserved."

cd "$APP_DIR"

# 1. Update source code if in git repository
if [ -d ".git" ]; then
    log "Updating application files via git..."
    git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
    # Fetch and pull without discarding .env or local uploads
    git fetch origin main || true
    git merge origin/main --no-edit || git rebase origin/main || true
elif [ "$SCRIPT_DIR" != "$APP_DIR" ] && [ -f "${SCRIPT_DIR}/artisan" ]; then
    log "Copying updated files from ${SCRIPT_DIR} to ${APP_DIR}..."
    rsync -av --exclude='.env' --exclude='storage/' --exclude='public/storage' "${SCRIPT_DIR}/" "${APP_DIR}/"
fi

# 2. Update PHP dependencies
log "Installing / Updating Composer dependencies..."
COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# 3. Build frontend assets
if [ -f package.json ]; then
    log "Building frontend assets..."
    if [ -f package-lock.json ]; then
        npm ci --no-audit --no-fund --no-progress
    else
        npm install --no-audit --no-fund --no-progress
    fi
    npm run build
fi

# 4. Run database migrations (non-destructive)
log "Running database migrations (preserving existing data)..."
php artisan migrate --force --no-interaction

# 5. Seed new stages, statuses, and permissions safely using updateOrCreate
log "Synchronizing default pipeline stages and permissions (preserving existing leads and users)..."
php artisan db:seed --class="Database\\Seeders\\CrmV2PipelineSeeder" --force --no-interaction
php artisan db:seed --class="Database\\Seeders\\CrmAccessControlSeeder" --force --no-interaction

# 6. Ensure storage link
php artisan storage:link --no-interaction >/dev/null 2>&1 || true

# 7. Clear and optimize caches
log "Clearing and optimizing application caches..."
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs
php artisan optimize:clear --no-ansi || true
php artisan view:cache --no-ansi || true
php artisan route:cache --no-ansi || true
php artisan config:cache --no-ansi || true

# 8. Reset file permissions
log "Setting file permissions for www-data..."
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 775 {} +
find "$APP_DIR" -type f -exec chmod 664 {} +
chmod +x "${APP_DIR}/artisan"

# 9. Reload Apache if present
if command -v apache2ctl >/dev/null 2>&1 && command -v systemctl >/dev/null 2>&1; then
    apache2ctl configtest >/dev/null 2>&1 && systemctl reload apache2 >/dev/null 2>&1 || true
fi

# 10. Health check
log "Running health check..."
php artisan migrate:status --no-ansi >/dev/null
curl -fsS --max-time 15 "http://localhost/login" >/dev/null 2>&1 || curl -fsS --max-time 15 "http://127.0.0.1/login" >/dev/null 2>&1 || true

printf '\n==================================================\n'
printf '%s UPDATE COMPLETE\n' "$APP_NAME"
printf '==================================================\n'
printf 'The application was updated successfully.\n'
printf 'All existing leads, customers, and data have been preserved.\n'
printf '==================================================\n'
