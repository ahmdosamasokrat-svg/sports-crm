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

        if (Schema::hasColumn('referral_settings', 'target_lead_status_id')) {
            Schema::table('referral_settings', function (Blueprint $table): void {
                $table->dropForeign(['target_lead_status_id']);
                $table->dropColumn('target_lead_status_id');
            });
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        if (! Schema::hasColumn('referral_settings', 'target_lead_status_id')) {
            Schema::table('referral_settings', function (Blueprint $table): void {
                $table->foreignId('target_lead_status_id')
                    ->nullable()
                    ->after('target_pipeline_stage_id')
                    ->constrained('lead_statuses')
                    ->nullOnDelete();
            });
        }
    }
};
