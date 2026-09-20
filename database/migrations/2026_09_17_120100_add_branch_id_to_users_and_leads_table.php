<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('branches')
                    ->nullOnDelete();

                $table->index(['is_active', 'branch_id'], 'users_active_branch_idx');
            }
        });

        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('lead_status_id')
                    ->constrained('branches')
                    ->nullOnDelete();

                $table->index(['deleted_at', 'branch_id', 'lead_status_id'], 'leads_del_branch_status_idx');
            }
        });
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::table('leads', function (Blueprint $table): void {
            if (Schema::hasColumn('leads', 'branch_id')) {
                $table->dropIndex('leads_del_branch_status_idx');
                $table->dropConstrainedForeignId('branch_id');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropIndex('users_active_branch_idx');
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
