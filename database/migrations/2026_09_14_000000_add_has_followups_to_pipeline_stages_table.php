<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pipeline_stages', 'has_followups')) {
            Schema::table('pipeline_stages', function (Blueprint $table): void {
                $table->boolean('has_followups')->default(true)->after('is_active');
            });

            // Mark known non-followup stages as false by default
            DB::table('pipeline_stages')
                ->whereIn('code', ['new', 'not_interested', 'execution'])
                ->update(['has_followups' => false]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pipeline_stages', 'has_followups')) {
            Schema::table('pipeline_stages', function (Blueprint $table): void {
                $table->dropColumn('has_followups');
            });
        }
    }
};
