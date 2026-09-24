<?php

declare(strict_types=1);

use App\Support\CrmDatabaseGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CrmDatabaseGuard::ensureConnected();

        if (! Schema::hasTable('referral_settings')) {
            Schema::create('referral_settings', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_enabled')->default(true);
                $table->foreignId('target_pipeline_stage_id')
                    ->nullable()
                    ->constrained('pipeline_stages')
                    ->nullOnDelete();
                $table->foreignId('target_lead_status_id')
                    ->nullable()
                    ->constrained('lead_statuses')
                    ->nullOnDelete();
                $table->boolean('allow_notes')->default(true);
                $table->json('trigger_stage_field_ids')->nullable();
                $table->json('trigger_values')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('referral_fields')) {
            Schema::create('referral_fields', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('pipeline_stage_field_id')
                    ->constrained('pipeline_stage_fields')
                    ->cascadeOnDelete();
                $table->boolean('is_required')->default(false);
                $table->unsignedInteger('position')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('pipeline_stage_field_id');
                $table->index(['is_active', 'position'], 'rf_active_pos_idx');
            });
        }

        // Initialize default settings record if empty
        if (DB::table('referral_settings')->count() === 0) {
            DB::table('referral_settings')->insert([
                'is_enabled' => true,
                'target_pipeline_stage_id' => null,
                'target_lead_status_id' => null,
                'allow_notes' => true,
                'trigger_stage_field_ids' => null,
                'trigger_values' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Prepopulate default referral fields from existing activity fields if found
        if (DB::table('referral_fields')->count() === 0) {
            $activityFieldId = DB::table('pipeline_stage_fields')
                ->whereIn('key', ['requested_activity', 'activity'])
                ->where('is_active', true)
                ->orderBy('id')
                ->value('id');

            if ($activityFieldId !== null) {
                DB::table('referral_fields')->insert([
                    'pipeline_stage_field_id' => $activityFieldId,
                    'is_required' => false,
                    'position' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::dropIfExists('referral_fields');
        Schema::dropIfExists('referral_settings');
    }
};
