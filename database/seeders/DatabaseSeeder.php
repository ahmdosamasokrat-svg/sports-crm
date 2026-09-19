<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CrmV2PipelineSeeder::class,
            CrmAccessControlSeeder::class,
        ]);

        $seedDemoData = filter_var(env('CRM_SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN);

        if (! app()->isProduction() && app()->environment(['local', 'testing']) && $seedDemoData) {
            $this->call([
                CalendarEventSeeder::class,
            ]);
        }
    }
}
