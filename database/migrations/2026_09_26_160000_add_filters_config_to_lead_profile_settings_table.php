<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_profile_settings') && ! Schema::hasColumn('lead_profile_settings', 'filters_config')) {
            Schema::table('lead_profile_settings', function (Blueprint $table): void {
                $table->json('filters_config')->nullable()->after('tabs_config');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lead_profile_settings') && Schema::hasColumn('lead_profile_settings', 'filters_config')) {
            Schema::table('lead_profile_settings', function (Blueprint $table): void {
                $table->dropColumn('filters_config');
            });
        }
    }
};
