<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('pipeline_stage_fields')
            ->whereIn('key', ['requested_activity', 'activity'])
            ->update([
                'binding_type' => 'canonical',
                'binding_target' => 'activity',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pipeline_stage_fields')
            ->whereIn('key', ['requested_activity', 'activity'])
            ->update([
                'binding_type' => 'custom',
                'binding_target' => null,
            ]);
    }
};
