<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pipeline_stage_fields')) {
            Schema::table('pipeline_stage_fields', function (Blueprint $table): void {
                if (!Schema::hasColumn('pipeline_stage_fields', 'show_in_daily_tasks')) {
                    $table->boolean('show_in_daily_tasks')->default(false)->after('show_in_history');
                }
                if (!Schema::hasColumn('pipeline_stage_fields', 'daily_tasks_filter_values')) {
                    $table->json('daily_tasks_filter_values')->nullable()->after('show_in_daily_tasks');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pipeline_stage_fields')) {
            Schema::table('pipeline_stage_fields', function (Blueprint $table): void {
                $columnsToDrop = [];
                if (Schema::hasColumn('pipeline_stage_fields', 'daily_tasks_filter_values')) {
                    $columnsToDrop[] = 'daily_tasks_filter_values';
                }
                if (Schema::hasColumn('pipeline_stage_fields', 'show_in_daily_tasks')) {
                    $columnsToDrop[] = 'show_in_daily_tasks';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
