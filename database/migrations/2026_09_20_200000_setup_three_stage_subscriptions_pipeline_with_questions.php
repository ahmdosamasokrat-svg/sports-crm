<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cat = DB::table('pipeline_stage_categories')
            ->where('id', 4)
            ->orWhere('name_en', 'Subscriptions')
            ->orWhere('name_ar', 'الاشتراكات')
            ->first();

        if (! $cat) {
            return;
        }

        // 1. Ensure Stage 1: "مشترك جديد" (Contract & Payment) exists as position 1
        $stage1 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $cat->id)
            ->where('name_ar', 'مشترك جديد')
            ->first();

        if (! $stage1) {
            $stage1Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $cat->id,
                'code' => 'stage_new_sub_' . Str::lower(Str::random(6)),
                'name_ar' => 'مشترك جديد',
                'description_ar' => 'تسجيل بيانات الاشتراك المالي والباقة والمجموعة والمدرب',
                'position' => 1,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#3b82f6',
                'icon' => 'bi-person-check',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage1Id,
                'code' => 'status_new_sub_' . Str::lower(Str::random(6)),
                'name_ar' => 'مشترك جديد',
                'position' => 1,
                'color' => '#3b82f6',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage1Id = $stage1->id;
            DB::table('pipeline_stages')->where('id', $stage1Id)->update([
                'position' => 1,
                'description_ar' => 'تسجيل بيانات الاشتراك المالي والباقة والمجموعة والمدرب',
                'color' => '#3b82f6',
                'icon' => 'bi-person-check',
            ]);
        }

        // 2. Create or update Stage 2: "فترة التهيئة والاستبيان" (Onboarding & Survey) as position 2
        $stage2 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $cat->id)
            ->where('name_ar', 'فترة التهيئة والاستبيان')
            ->first();

        if (! $stage2) {
            $stage2Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $cat->id,
                'code' => 'stage_onboarding_' . Str::lower(Str::random(6)),
                'name_ar' => 'فترة التهيئة والاستبيان',
                'description_ar' => 'متابعة أول 14 يوم تدريب، التأكد من رضا اللاعب والأسرة، وتسجيل استبيان التجربة',
                'position' => 2,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#8b5cf6',
                'icon' => 'bi-chat-heart',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage2Id,
                'code' => 'status_onboarding_' . Str::lower(Str::random(6)),
                'name_ar' => 'قيد التهيئة والمتابعة',
                'position' => 1,
                'color' => '#8b5cf6',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage2Id = $stage2->id;
            DB::table('pipeline_stages')->where('id', $stage2Id)->update([
                'position' => 2,
                'color' => '#8b5cf6',
                'icon' => 'bi-chat-heart',
            ]);
        }

        // 3. Ensure Stage 3: "مشترك مفعل" (Active Training) is position 3
        $stage3 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $cat->id)
            ->where('name_ar', 'مشترك مفعل')
            ->first();

        if ($stage3) {
            $stage3Id = $stage3->id;
            DB::table('pipeline_stages')->where('id', $stage3Id)->update([
                'position' => 3,
                'description_ar' => 'لاعب منتظم بالتدريب الأكاديمي النشط بعد إتمام فترة التهيئة بنجاح',
                'color' => '#10b981',
                'icon' => 'bi-star-fill',
            ]);
        }

        // 4. Update category target stage to point to stage 1 (مشترك جديد)
        DB::table('pipeline_stage_categories')
            ->where('id', $cat->id)
            ->update([
                'target_stage_id' => $stage1Id,
                'updated_at' => now(),
            ]);

        // 5. Populate Stage 1 Questions (Contract & Finance - 6 essential fields)
        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage1Id)->delete();

        $stage1Questions = [
            [
                'key' => 'package_plan',
                'label_ar' => 'الباقة وخطة الاشتراك',
                'label_en' => 'Package Plan',
                'type' => 'text',
                'is_required' => true,
                'position' => 1,
            ],
            [
                'key' => 'base_price',
                'label_ar' => 'السعر الأساسي',
                'label_en' => 'Base Price',
                'type' => 'currency',
                'is_required' => true,
                'position' => 2,
            ],
            [
                'key' => 'discount',
                'label_ar' => 'الخصم المطبق',
                'label_en' => 'Discount',
                'type' => 'text',
                'is_required' => false,
                'placeholder_ar' => 'مبلغ ثابت مثل 200 أو نسبة مثل 15%',
                'position' => 3,
            ],
            [
                'key' => 'final_price',
                'label_ar' => 'السعر النهائي',
                'label_en' => 'Final Price',
                'type' => 'currency',
                'is_required' => true,
                'help_text_ar' => 'يُحسب تلقائيًا: السعر الأساسي - الخصم',
                'position' => 4,
            ],
            [
                'key' => 'collected_amount',
                'label_ar' => 'المبلغ المحصل',
                'label_en' => 'Collected Amount',
                'type' => 'currency',
                'is_required' => true,
                'position' => 5,
            ],
            [
                'key' => 'remaining_amount',
                'label_ar' => 'المبلغ المتبقي',
                'label_en' => 'Remaining Amount',
                'type' => 'currency',
                'is_required' => false,
                'help_text_ar' => 'يُحسب تلقائيًا: السعر النهائي - المحصل',
                'position' => 6,
            ],
            [
                'key' => 'payment_method',
                'label_ar' => 'طريقة الدفع',
                'label_en' => 'Payment Method',
                'type' => 'select',
                'options' => json_encode([
                    ['value' => 'كاش', 'label_ar' => 'كاش (نقدي)', 'label_en' => 'Cash'],
                    ['value' => 'بطاقة / شبكة', 'label_ar' => 'بطاقة / شبكة (POS)', 'label_en' => 'Card / POS'],
                    ['value' => 'تحويل بنكي', 'label_ar' => 'تحويل بنكي / محفظة', 'label_en' => 'Bank Transfer'],
                    ['value' => 'أقساط', 'label_ar' => 'أقساط مجدولة', 'label_en' => 'Installments'],
                ], JSON_UNESCAPED_UNICODE),
                'is_required' => true,
                'position' => 7,
            ],
            [
                'key' => 'start_date',
                'label_ar' => 'تاريخ بداية الاشتراك',
                'label_en' => 'Start Date',
                'type' => 'date',
                'is_required' => true,
                'position' => 8,
            ],
            [
                'key' => 'end_date',
                'label_ar' => 'تاريخ نهاية الاشتراك',
                'label_en' => 'End Date',
                'type' => 'date',
                'is_required' => true,
                'position' => 9,
            ],
            [
                'key' => 'group_class',
                'label_ar' => 'المجموعة والفصل التدريبي',
                'label_en' => 'Training Group / Class',
                'type' => 'text',
                'placeholder_ar' => 'مثال: مجموعة أبطال A - كابتن أحمد',
                'is_required' => true,
                'position' => 10,
            ],
        ];

        foreach ($stage1Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage1Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => $q['label_en'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'placeholder_ar' => $q['placeholder_ar'] ?? null,
                'help_text_ar' => $q['help_text_ar'] ?? null,
                'is_required' => $q['is_required'],
                'binding_type' => 'custom',
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'is_active' => true,
                'position' => $q['position'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6. Populate Stage 2 Questions (Onboarding & Survey - 5 focused experience fields)
        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage2Id)->delete();

        $stage2Questions = [
            [
                'key' => 'is_player_happy',
                'label_ar' => 'هل اللاعب سعيد بالتدريب والأكاديمية؟',
                'label_en' => 'Is Player Happy with Training?',
                'type' => 'select',
                'options' => json_encode([
                    ['value' => 'سعيد ومتحمس جدًا', 'label_ar' => 'سعيد ومتحمس جدًا (5/5)', 'label_en' => 'Very Happy'],
                    ['value' => 'راضي إلى حد ما', 'label_ar' => 'راضي إلى حد ما (3/5)', 'label_en' => 'Satisfied'],
                    ['value' => 'يواجه صعوبة أو غير متأقلم', 'label_ar' => 'يواجه صعوبة أو غير متأقلم (يحتاج تدخل)', 'label_en' => 'Needs Attention'],
                ], JSON_UNESCAPED_UNICODE),
                'is_required' => true,
                'position' => 1,
            ],
            [
                'key' => 'rating_coach_performance',
                'label_ar' => 'تقييم أداء وتفاعل المدرب في الحصص الأولى',
                'label_en' => 'Coach Performance Rating',
                'type' => 'select',
                'options' => json_encode([
                    ['value' => 'ممتاز', 'label_ar' => '⭐⭐⭐⭐⭐ ممتاز', 'label_en' => 'Excellent'],
                    ['value' => 'جيد جدًا', 'label_ar' => '⭐⭐⭐⭐ جيد جدًا', 'label_en' => 'Very Good'],
                    ['value' => 'مقبول', 'label_ar' => '⭐⭐⭐ مقبول', 'label_en' => 'Average'],
                    ['value' => 'يحتاج تحسين', 'label_ar' => '⭐ يحتاج متابعة وتغيير', 'label_en' => 'Needs Improvement'],
                ], JSON_UNESCAPED_UNICODE),
                'is_required' => true,
                'position' => 2,
            ],
            [
                'key' => 'rating_facility_cleanliness',
                'label_ar' => 'تقييم التنظيم والمرافق والنظافة',
                'label_en' => 'Facility & Cleanliness Rating',
                'type' => 'select',
                'options' => json_encode([
                    ['value' => 'ممتاز', 'label_ar' => '⭐⭐⭐⭐⭐ ممتاز ونظيف جدًا', 'label_en' => 'Excellent'],
                    ['value' => 'جيد', 'label_ar' => '⭐⭐⭐⭐ جيد', 'label_en' => 'Good'],
                    ['value' => 'ملاحظات نظافة أو زحام', 'label_ar' => '⚠️ توجد ملاحظات نظافة أو زحام', 'label_en' => 'Has Issues'],
                ], JSON_UNESCAPED_UNICODE),
                'is_required' => false,
                'position' => 3,
            ],
            [
                'key' => 'onboarding_notes_feedback',
                'label_ar' => 'أي ملاحظات أو طلبات خاصة من الأسرة؟',
                'label_en' => 'Family Feedback & Notes',
                'type' => 'textarea',
                'placeholder_ar' => 'ملاحظات بخصوص الزي الرياضي، المواعيد، أو طلبات خاصة باللاعب...',
                'is_required' => false,
                'position' => 4,
            ],
            [
                'key' => 'nps_referral_readiness',
                'label_ar' => 'احتمالية ترشيح الأكاديمية لمعارفكم (NPS)',
                'label_en' => 'Likelihood to Recommend (NPS)',
                'type' => 'select',
                'options' => json_encode([
                    ['value' => '10/10', 'label_ar' => '10/10 (متحمس ومستعد لإحالة أصدقائه)', 'label_en' => '10/10 Promoter'],
                    ['value' => '8-9/10', 'label_ar' => '8-9/10 (راضي جدًا)', 'label_en' => '8-9/10 Satisfied'],
                    ['value' => 'أقل من 7', 'label_ar' => 'أقل من 7 (غير راضي حاليًا)', 'label_en' => 'Detractor'],
                ], JSON_UNESCAPED_UNICODE),
                'is_required' => true,
                'position' => 5,
            ],
        ];

        foreach ($stage2Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage2Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => $q['label_en'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'placeholder_ar' => $q['placeholder_ar'] ?? null,
                'help_text_ar' => $q['help_text_ar'] ?? null,
                'is_required' => $q['is_required'],
                'binding_type' => 'custom',
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'is_active' => true,
                'position' => $q['position'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
