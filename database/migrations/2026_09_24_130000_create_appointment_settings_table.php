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

        if (! Schema::hasTable('appointment_settings')) {
            Schema::create('appointment_settings', function (Blueprint $table): void {
                $table->id();
                $table->boolean('is_enabled')->default(true);
                $table->json('stage_ids')->nullable();
                $table->string('date_field_key', 100)->default('trial_date');
                $table->string('time_field_key', 100)->nullable()->default('trial_time');
                $table->string('coach_field_key', 100)->nullable()->default('coach');
                $table->string('status_field_key', 100)->nullable()->default('trial_status');
                $table->boolean('allow_quick_actions')->default(true);
                $table->timestamps();
            });

            // Seed default record with stage 17 (تجربة محجوزة) mapped if stages exist
            $defaultStageIds = DB::table('pipeline_stages')
                ->where('id', 17)
                ->orWhere('name_ar', 'like', '%تجربة محجوزة%')
                ->pluck('id')
                ->all();

            DB::table('appointment_settings')->insert([
                'id' => 1,
                'is_enabled' => true,
                'stage_ids' => json_encode($defaultStageIds ?: [17]),
                'date_field_key' => 'trial_date',
                'time_field_key' => 'trial_time',
                'coach_field_key' => 'coach',
                'status_field_key' => 'trial_status',
                'allow_quick_actions' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::dropIfExists('appointment_settings');
    }
};
