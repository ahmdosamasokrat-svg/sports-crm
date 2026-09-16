#!/bin/bash
set -e

echo "=== Sokrat CRM V2 Container Starting ==="

# Initialize .env if missing
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure storage and bootstrap/cache permissions
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/private \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate APP_KEY if empty or default
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "Generating Application Key..."
    php artisan key:generate --force --no-interaction
fi

# Wait for MySQL database connection
if [ -n "$DB_HOST" ]; then
    echo "Waiting for MySQL database at ${DB_HOST}:${DB_PORT:-3306}..."
    max_tries=30
    count=0
    until php -r "
        try {
            new PDO(
                'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 3]
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " >/dev/null 2>&1; do
        count=$((count + 1))
        if [ $count -gt $max_tries ]; then
            echo "Error: Database connection timeout after ${max_tries} attempts."
            exit 1
        fi
        echo "Database is not ready yet (attempt $count/$max_tries) - retrying in 2s..."
        sleep 2
    done
    echo "Database is online and reachable!"

    echo "Running database migrations..."
    php artisan migrate --force --no-interaction

    echo "Running seeders (admin user & default pipeline)..."
    php artisan db:seed --force --no-interaction || true
fi

echo "Creating storage symlink..."
php artisan storage:link --no-interaction >/dev/null 2>&1 || true

echo "Optimizing route and view cache..."
php artisan config:clear --no-interaction || true
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction || true

echo "=== Sokrat CRM V2 is ready! Starting Apache ==="
exec "$@"
