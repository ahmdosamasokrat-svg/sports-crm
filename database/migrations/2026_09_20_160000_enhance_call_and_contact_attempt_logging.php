<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_followups', function (Blueprint $table): void {
            $table->unsignedInteger('call_attempt_number')->default(1)->after('communication_type');
            $table->string('call_status', 40)->nullable()->after('call_attempt_number');
            $table->string('outcome_category', 50)->nullable()->after('call_status');
            $table->unsignedInteger('call_duration_seconds')->nullable()->after('outcome_category');
            $table->string('call_recording_url', 500)->nullable()->after('call_duration_seconds');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->unsignedInteger('call_attempts_count')->default(0)->after('phone');
            $table->string('last_call_status', 40)->nullable()->after('call_attempts_count');
            $table->string('last_outcome_category', 50)->nullable()->after('last_call_status');
            $table->timestamp('first_contacted_at')->nullable()->after('last_outcome_category');
            $table->timestamp('last_contacted_at')->nullable()->after('first_contacted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_followups', function (Blueprint $table): void {
            $table->dropColumn([
                'call_attempt_number',
                'call_status',
                'outcome_category',
                'call_duration_seconds',
                'call_recording_url',
            ]);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn([
                'call_attempts_count',
                'last_call_status',
                'last_outcome_category',
                'first_contacted_at',
                'last_contacted_at',
            ]);
        });
    }
};
