<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function verifyDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }

    public function up(): void
    {
        $this->verifyDatabase();

        if (! Schema::hasColumn('pipeline_stage_categories', 'auto_transfer_enabled')) {
            Schema::table('pipeline_stage_categories', function (Blueprint $table): void {
                $table->boolean('auto_transfer_enabled')->default(false)->after('is_active');
                $table->string('auto_transfer_action', 20)->default('clone')->after('auto_transfer_enabled'); // 'clone' or 'move'
                $table->foreignId('trigger_stage_id')
                    ->nullable()
                    ->after('auto_transfer_action')
                    ->constrained('pipeline_stages')
                    ->nullOnDelete();
                $table->foreignId('trigger_status_id')
                    ->nullable()
                    ->after('trigger_stage_id')
                    ->constrained('lead_statuses')
                    ->nullOnDelete();
                $table->foreignId('target_stage_id')
                    ->nullable()
                    ->after('trigger_status_id')
                    ->constrained('pipeline_stages')
                    ->nullOnDelete();
                $table->foreignId('target_status_id')
                    ->nullable()
                    ->after('target_stage_id')
                    ->constrained('lead_statuses')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('leads', 'parent_lead_id')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->foreignId('parent_lead_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('leads')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->verifyDatabase();

        if (Schema::hasColumn('leads', 'parent_lead_id')) {
            Schema::table('leads', function (Blueprint $table): void {
                $table->dropForeign(['parent_lead_id']);
                $table->dropColumn('parent_lead_id');
            });
        }

        if (Schema::hasColumn('pipeline_stage_categories', 'auto_transfer_enabled')) {
            Schema::table('pipeline_stage_categories', function (Blueprint $table): void {
                $table->dropForeign(['trigger_stage_id']);
                $table->dropForeign(['trigger_status_id']);
                $table->dropForeign(['target_stage_id']);
                $table->dropForeign(['target_status_id']);
                $table->dropColumn([
                    'auto_transfer_enabled',
                    'auto_transfer_action',
                    'trigger_stage_id',
                    'trigger_status_id',
                    'target_stage_id',
                    'target_status_id',
                ]);
            });
        }
    }
};
