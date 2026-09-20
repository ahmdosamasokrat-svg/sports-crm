<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FollowupCustomerField;
use App\Support\CrmDatabaseGuard;
use App\Support\FollowupCustomerFieldSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportTimeCustomerFieldsSeeder extends Seeder
{
    public function run(): void
    {
        CrmDatabaseGuard::ensureConnected();

        DB::transaction(function (): void {
            $fields = [
                [
                    'key' => 'lead_temperature',
                    'label_ar' => 'حرارة العميل (Temperature)',
                    'label_en' => 'Lead Temperature',
                    'type' => 'select',
                    'placeholder_ar' => 'اختر تصنيف الحرارة',
                    'placeholder_en' => 'Select temperature',
                    'help_text_ar' => 'تصنيف رغبة وجاهزية العميل (بارد / متوسط / حار)',
                    'help_text_en' => 'Lead intent level: Cold, Warm, or Hot',
                    'is_required' => false,
                    'options' => [
                        [
                            'value' => 'cold',
                            'label_ar' => 'بارد (Cold)',
                            'label_en' => 'Cold',
                        ],
                        [
                            'value' => 'warm',
                            'label_ar' => 'متوسط (Warm)',
                            'label_en' => 'Warm',
                        ],
                        [
                            'value' => 'hot',
                            'label_ar' => 'حار (Hot)',
                            'label_en' => 'Hot',
                        ],
                    ],
                    'position' => 1,
                ],
                [
                    'key' => 'fitness_goal',
                    'label_ar' => 'الهدف الرياضي (Fitness Goal)',
                    'label_en' => 'Fitness Goal',
                    'type' => 'select',
                    'placeholder_ar' => 'اختر الهدف الرياضي',
                    'placeholder_en' => 'Select fitness goal',
                    'help_text_ar' => 'الهدف الأساسي للعميل من الاشتراك',
                    'help_text_en' => 'Primary goal of the client',
                    'options' => [
                        ['value' => 'weight_loss', 'label_ar' => 'خسارة وزن (Weight Loss)', 'label_en' => 'Weight Loss'],
                        ['value' => 'muscle_gain', 'label_ar' => 'بناء عضلات (Muscle Gain)', 'label_en' => 'Muscle Gain'],
                        ['value' => 'general_fitness', 'label_ar' => 'لياقة عامة وصحة (General Fitness)', 'label_en' => 'General Fitness'],
                        ['value' => 'rehab', 'label_ar' => 'تأهيل ولياقة بدنية (Rehab & Recovery)', 'label_en' => 'Rehab & Recovery'],
                    ],
                    'position' => 2,
                ],
            ];

            foreach ($fields as $fieldData) {
                FollowupCustomerField::query()->updateOrCreate(
                    ['key' => $fieldData['key']],
                    [
                        'label_ar' => $fieldData['label_ar'],
                        'label_en' => $fieldData['label_en'],
                        'type' => $fieldData['type'],
                        'placeholder_ar' => $fieldData['placeholder_ar'] ?? null,
                        'placeholder_en' => $fieldData['placeholder_en'] ?? null,
                        'help_text_ar' => $fieldData['help_text_ar'] ?? null,
                        'help_text_en' => $fieldData['help_text_en'] ?? null,
                        'is_required' => $fieldData['is_required'] ?? false,
                        'options' => $fieldData['options'] ?? null,
                        'is_system' => false,
                        'is_active' => true,
                        'position' => $fieldData['position'],
                    ]
                );
            }
        });

        FollowupCustomerFieldSchema::flushCache();
    }
}
