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

        if (! Schema::hasTable('lead_sources')) {
            Schema::create('lead_sources', function (Blueprint $table): void {
                $table->id();
                $table->string('name_ar', 150);
                $table->string('name_en', 150)->nullable();
                $table->string('icon', 100)->default('bi-funnel');
                $table->string('color', 50)->default('#3b82f6');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('position')->default(1);
                $table->softDeletes();
                $table->timestamps();

                $table->index(['is_active', 'position'], 'lead_sources_active_pos_idx');
            });
        }

        // Seed SportTime Default Lead Sources
        $defaultSources = [
            ['name_ar' => 'Meta / فيسبوك', 'name_en' => 'Meta / Facebook', 'icon' => 'bi-facebook', 'color' => '#1877f2', 'position' => 1],
            ['name_ar' => 'انستغرام', 'name_en' => 'Instagram', 'icon' => 'bi-instagram', 'color' => '#e1306c', 'position' => 2],
            ['name_ar' => 'واتساب', 'name_en' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'color' => '#25d366', 'position' => 3],
            ['name_ar' => 'ترشيح / إحالة (Referral)', 'name_en' => 'Referral', 'icon' => 'bi-people', 'color' => '#8b5cf6', 'position' => 4],
            ['name_ar' => 'زيارة مباشرة (Walk-in)', 'name_en' => 'Walk-in', 'icon' => 'bi-door-open', 'color' => '#f59e0b', 'position' => 5],
            ['name_ar' => 'استيراد إكسيل (Excel / Import)', 'name_en' => 'Excel Import', 'icon' => 'bi-file-earmark-excel', 'color' => '#10b981', 'position' => 6],
            ['name_ar' => 'إعلانات جوجل (Google Ads)', 'name_en' => 'Google Ads', 'icon' => 'bi-google', 'color' => '#ea4335', 'position' => 7],
        ];

        $now = now();
        foreach ($defaultSources as $source) {
            DB::table('lead_sources')->updateOrInsert(
                ['name_ar' => $source['name_ar']],
                [
                    'name_en' => $source['name_en'],
                    'icon' => $source['icon'],
                    'color' => $source['color'],
                    'is_active' => true,
                    'position' => $source['position'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();
        Schema::dropIfExists('lead_sources');
    }
};
