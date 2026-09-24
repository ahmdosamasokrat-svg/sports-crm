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

        if (Schema::hasTable('lead_profile_settings')) {
            return;
        }

        Schema::create('lead_profile_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('layout_mode', 50)->default('hybrid');
            $table->string('default_tab', 50)->default('timeline');
            $table->json('tabs_config')->nullable();
            $table->timestamps();
        });

        $defaultTabs = [
            [
                'key' => 'timeline',
                'is_enabled' => true,
                'position' => 1,
                'label_ar' => 'سجل النشاط والمتابعات',
                'label_en' => 'Activity Timeline',
                'icon' => 'bi-clock-history',
            ],
            [
                'key' => 'client_data',
                'is_enabled' => true,
                'position' => 2,
                'label_ar' => 'بيانات العميل',
                'label_en' => 'Customer Info',
                'icon' => 'bi-person-vcard',
            ],
            [
                'key' => 'stage_data',
                'is_enabled' => true,
                'position' => 3,
                'label_ar' => 'بيانات المرحلة والأسئلة',
                'label_en' => 'Stage Questions',
                'icon' => 'bi-ui-checks-grid',
            ],
            [
                'key' => 'appointments',
                'is_enabled' => true,
                'position' => 4,
                'label_ar' => 'المواعيد والحضور',
                'label_en' => 'Appointments & Attendance',
                'icon' => 'bi-calendar2-check',
            ],
            [
                'key' => 'voip_calls',
                'is_enabled' => true,
                'position' => 5,
                'label_ar' => 'سجل المكالمات VoIP',
                'label_en' => 'VoIP Calls',
                'icon' => 'bi-telephone-inbound',
            ],
            [
                'key' => 'documents',
                'is_enabled' => true,
                'position' => 6,
                'label_ar' => 'المستندات وعروض الأسعار',
                'label_en' => 'Documents & Quotes',
                'icon' => 'bi-folder2-open',
            ],
            [
                'key' => 'referrals',
                'is_enabled' => true,
                'position' => 7,
                'label_ar' => 'الإحالات والأسرة',
                'label_en' => 'Referrals & Family',
                'icon' => 'bi-people',
            ],
            [
                'key' => 'notes',
                'is_enabled' => true,
                'position' => 8,
                'label_ar' => 'الملاحظات',
                'label_en' => 'Notes',
                'icon' => 'bi-chat-left-text',
            ],
        ];

        DB::table('lead_profile_settings')->insert([
            'id' => 1,
            'layout_mode' => 'hybrid',
            'default_tab' => 'timeline',
            'tabs_config' => json_encode($defaultTabs, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        CrmDatabaseGuard::ensureConnected();

        Schema::dropIfExists('lead_profile_settings');
    }
};
