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

        if (Schema::hasTable('appointment_attendance_records')) {
            return;
        }

        Schema::create('appointment_attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('outcome', 20);
            $table->date('appointment_date')->nullable();
            $table->string('appointment_time', 32)->nullable();
            $table->text('activity')->nullable();
            $table->string('branch_name_ar')->nullable();
            $table->string('branch_name_en')->nullable();
            $table->text('coach')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['lead_id', 'created_at', 'id'], 'appointment_attendance_lead_created_idx');
            $table->index(['lead_id', 'appointment_date', 'appointment_time'], 'appointment_attendance_schedule_idx');
        });
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::dropIfExists('appointment_attendance_records');
    }
};
