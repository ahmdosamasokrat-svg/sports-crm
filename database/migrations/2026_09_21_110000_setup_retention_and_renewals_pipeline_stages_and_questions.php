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
        // 1. Locate or create Category 5: "التجديدات والمحافظة"
        $cat = DB::table('pipeline_stage_categories')
            ->where('id', 5)
            ->orWhere('name_en', 'Retention & Renewals')
            ->orWhere('name_ar', 'like', '%التجديد%')
            ->first();

        if (! $cat) {
            $catId = DB::table('pipeline_stage_categories')->insertGetId([
                'name_ar' => 'التجديدات والمحافظة',
                'name_en' => 'Retention & Renewals',
                'description_ar' => 'متابعة انتهاء الاشتراكات، عروض التجديد، والمحافظة على المشتركين',
                'color' => '#f59e0b',
                'icon' => 'bi-arrow-repeat',
                'position' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $catId = $cat->id;
            DB::table('pipeline_stage_categories')->where('id', $catId)->update([
                'name_ar' => 'التجديدات والمحافظة',
                'name_en' => 'Retention & Renewals',
                'description_ar' => 'متابعة انتهاء الاشتراكات، عروض التجديد، والمحافظة على المشتركين',
                'color' => '#f59e0b',
                'icon' => 'bi-arrow-repeat',
                'position' => 3,
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        // =========================================================================
        // STAGE 1: مستحق التجديد (قرب الانتهاء) - Position 1
        // =========================================================================
        $stage1 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $catId)
            ->where('name_ar', 'مستحق التجديد (قرب الانتهاء)')
            ->first();

        if (! $stage1) {
            $stage1Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $catId,
                'code' => 'stage_renewal_due_' . Str::lower(Str::random(6)),
                'name_ar' => 'مستحق التجديد (قرب الانتهاء)',
                'description_ar' => 'الاشتراكات التي تنتهي خلال 30، 14، 7، 3 أيام أو اليوم وبحاجة لتذكير وتواصل مبكر',
                'position' => 1,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#f59e0b',
                'icon' => 'bi-hourglass-split',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage1Id,
                'code' => 'status_renewal_due_' . Str::lower(Str::random(6)),
                'name_ar' => 'مستحق التجديد',
                'position' => 1,
                'color' => '#f59e0b',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage1Id = $stage1->id;
            DB::table('pipeline_stages')->where('id', $stage1Id)->update([
                'position' => 1,
                'description_ar' => 'الاشتراكات التي تنتهي خلال 30، 14، 7، 3 أيام أو اليوم وبحاجة لتذكير وتواصل مبكر',
                'color' => '#f59e0b',
                'icon' => 'bi-hourglass-split',
                'is_active' => true,
            ]);
        }

        // Questions for Stage 1:
        $stage1Questions = [
            [
                'key' => 'expiry_bucket',
                'label_ar' => 'فترة قرب الانتهاء',
                'type' => 'select',
                'is_required' => true,
                'options' => json_encode([
                    ['value' => '30_days', 'label_ar' => 'خلال 30 يوم', 'label_en' => 'Within 30 Days'],
                    ['value' => '14_days', 'label_ar' => 'خلال 14 يوم', 'label_en' => 'Within 14 Days'],
                    ['value' => '7_days', 'label_ar' => 'خلال 7 أيام', 'label_en' => 'Within 7 Days'],
                    ['value' => '3_days', 'label_ar' => 'خلال 3 أيام', 'label_en' => 'Within 3 Days'],
                    ['value' => 'today', 'label_ar' => 'ينتهي اليوم', 'label_en' => 'Expires Today'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'key' => 'current_package',
                'label_ar' => 'الباقة الحالية المنتهية',
                'type' => 'text',
                'is_required' => false,
                'options' => null,
            ],
            [
                'key' => 'current_end_date',
                'label_ar' => 'تاريخ انتهاء الاشتراك الحالي',
                'type' => 'date',
                'is_required' => true,
                'options' => null,
            ],
            [
                'key' => 'renewal_reminder_sent',
                'label_ar' => 'هل تم إرسال رسالة التذكير بالتجديد؟',
                'type' => 'select',
                'is_required' => false,
                'options' => json_encode([
                    ['value' => 'نعم عبر واتساب', 'label_ar' => 'نعم عبر واتساب', 'label_en' => 'Yes via WhatsApp'],
                    ['value' => 'نعم عبر SMS', 'label_ar' => 'نعم عبر SMS', 'label_en' => 'Yes via SMS'],
                    ['value' => 'لا لم ترسل بعد', 'label_ar' => 'لا لم ترسل بعد', 'label_en' => 'Not yet sent'],
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];

        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage1Id)->delete();
        $pos = 1;
        foreach ($stage1Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage1Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => Str::title(str_replace('_', ' ', $q['key'])),
                'type' => $q['type'],
                'binding_type' => 'custom',
                'binding_target' => null,
                'is_required' => $q['is_required'],
                'options' => $q['options'],
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'show_in_daily_tasks' => ($q['key'] === 'expiry_bucket'),
                'daily_tasks_filter_values' => ($q['key'] === 'expiry_bucket') ? json_encode(['today', '3_days', '7_days'], JSON_UNESCAPED_UNICODE) : null,
                'is_active' => true,
                'position' => $pos++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // =========================================================================
        // STAGE 2: متابعة وتفاوض التجديد - Position 2
        // =========================================================================
        $stage2 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $catId)
            ->where('name_ar', 'متابعة وتفاوض التجديد')
            ->first();

        if (! $stage2) {
            $stage2Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $catId,
                'code' => 'stage_renewal_nego_' . Str::lower(Str::random(6)),
                'name_ar' => 'متابعة وتفاوض التجديد',
                'description_ar' => 'التواصل مع ولي الأمر وعرض باقات التجديد والمزايا والخصومات الخاصة',
                'position' => 2,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#6366f1',
                'icon' => 'bi-telephone-outbound',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage2Id,
                'code' => 'status_renewal_nego_' . Str::lower(Str::random(6)),
                'name_ar' => 'متابعة التجديد',
                'position' => 1,
                'color' => '#6366f1',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage2Id = $stage2->id;
            DB::table('pipeline_stages')->where('id', $stage2Id)->update([
                'position' => 2,
                'description_ar' => 'التواصل مع ولي الأمر وعرض باقات التجديد والمزايا والخصومات الخاصة',
                'color' => '#6366f1',
                'icon' => 'bi-telephone-outbound',
                'is_active' => true,
            ]);
        }

        // Questions for Stage 2:
        $stage2Questions = [
            [
                'key' => 'parent_decision_status',
                'label_ar' => 'موقف ولي الأمر من التجديد',
                'type' => 'select',
                'is_required' => true,
                'options' => json_encode([
                    ['value' => 'موافق على التجديد', 'label_ar' => 'موافق على التجديد', 'label_en' => 'Agreed to Renew'],
                    ['value' => 'يفكر ويقارن', 'label_ar' => 'يفكر ويقارن', 'label_en' => 'Thinking / Comparing'],
                    ['value' => 'ينتظر الراتب', 'label_ar' => 'ينتظر الراتب', 'label_en' => 'Waiting for Salary'],
                    ['value' => 'طلب خصم إضافي', 'label_ar' => 'طلب خصم إضافي', 'label_en' => 'Requested Extra Discount'],
                    ['value' => 'متردد / غير راضٍ', 'label_ar' => 'متردد / غير راضٍ', 'label_en' => 'Hesitant / Dissatisfied'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'key' => 'offered_renewal_package',
                'label_ar' => 'باقة التجديد المعروضة',
                'type' => 'text',
                'is_required' => false,
                'options' => null,
            ],
            [
                'key' => 'offered_discount',
                'label_ar' => 'الخصم الخاص المعروض للتجديد (مبلغ أو %)',
                'type' => 'text',
                'is_required' => false,
                'options' => null,
            ],
            [
                'key' => 'renewal_followup_date',
                'label_ar' => 'موعد المتابعة القادمة لحسم التجديد',
                'type' => 'datetime',
                'is_required' => false,
                'options' => null,
            ],
            [
                'key' => 'renewal_negotiation_notes',
                'label_ar' => 'ملاحظات تفاوض التجديد والاعتراضات',
                'type' => 'textarea',
                'is_required' => false,
                'options' => null,
            ],
        ];

        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage2Id)->delete();
        $pos = 1;
        foreach ($stage2Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage2Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => Str::title(str_replace('_', ' ', $q['key'])),
                'type' => $q['type'],
                'binding_type' => 'custom',
                'binding_target' => null,
                'is_required' => $q['is_required'],
                'options' => $q['options'],
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'show_in_daily_tasks' => false,
                'daily_tasks_filter_values' => null,
                'is_active' => true,
                'position' => $pos++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // =========================================================================
        // STAGE 3: تم التجديد بنجاح - Position 3
        // =========================================================================
        $stage3 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $catId)
            ->where('name_ar', 'تم التجديد بنجاح')
            ->first();

        if (! $stage3) {
            $stage3Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $catId,
                'code' => 'stage_renewed_won_' . Str::lower(Str::random(6)),
                'name_ar' => 'تم التجديد بنجاح',
                'description_ar' => 'اعتماد تجديد الاشتراك لفترة جديدة وتسجيل الدفع وتواريخ البداية والنهاية',
                'position' => 3,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#10b981',
                'icon' => 'bi-check-circle-fill',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage3Id,
                'code' => 'status_renewed_won_' . Str::lower(Str::random(6)),
                'name_ar' => 'تم التجديد',
                'position' => 1,
                'color' => '#10b981',
                'is_terminal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage3Id = $stage3->id;
            DB::table('pipeline_stages')->where('id', $stage3Id)->update([
                'position' => 3,
                'description_ar' => 'اعتماد تجديد الاشتراك لفترة جديدة وتسجيل الدفع وتواريخ البداية والنهاية',
                'color' => '#10b981',
                'icon' => 'bi-check-circle-fill',
                'is_active' => true,
            ]);
        }

        // Questions for Stage 3:
        $stage3Questions = [
            [
                'key' => 'renewed_package',
                'label_ar' => 'باقة التجديد المعتمدة',
                'type' => 'text',
                'is_required' => true,
                'options' => null,
            ],
            [
                'key' => 'new_start_date',
                'label_ar' => 'تاريخ بداية التجديد الجديد',
                'type' => 'date',
                'is_required' => true,
                'options' => null,
            ],
            [
                'key' => 'new_end_date',
                'label_ar' => 'تاريخ نهاية التجديد الجديد',
                'type' => 'date',
                'is_required' => true,
                'options' => null,
            ],
            [
                'key' => 'renewal_amount_collected',
                'label_ar' => 'المبلغ المحصل للتجديد',
                'type' => 'number',
                'is_required' => true,
                'options' => null,
            ],
            [
                'key' => 'renewal_payment_method',
                'label_ar' => 'طريقة دفع التجديد',
                'type' => 'select',
                'is_required' => true,
                'options' => json_encode([
                    ['value' => 'كاش', 'label_ar' => 'كاش (نقدي)', 'label_en' => 'Cash'],
                    ['value' => 'شبكة / بطاقة بنكية', 'label_ar' => 'شبكة / بطاقة بنكية', 'label_en' => 'Card / POS'],
                    ['value' => 'تحويل بنكي', 'label_ar' => 'تحويل بنكي', 'label_en' => 'Bank Transfer'],
                    ['value' => 'أونلاين / رابط دفع', 'label_ar' => 'أونلاين / رابط دفع', 'label_en' => 'Online Link'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'key' => 'loyalty_referral_request',
                'label_ar' => 'طلب ترشيح أو إحالة صديق (Loyalty Referral)',
                'type' => 'select',
                'is_required' => false,
                'options' => json_encode([
                    ['value' => 'تم طلب إحالة ووعد بتقديم أصدقاء', 'label_ar' => 'تم طلب إحالة ووعد بتقديم أصدقاء', 'label_en' => 'Referral Requested & Promised'],
                    ['value' => 'قدم إحالة الآن بالفعل', 'label_ar' => 'قدم إحالة الآن بالفعل', 'label_en' => 'Provided Referral Now'],
                    ['value' => 'يؤجل طلب الإحالة لاحقاً', 'label_ar' => 'يؤجل طلب الإحالة لاحقاً', 'label_en' => 'Postpone Referral Request'],
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];

        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage3Id)->delete();
        $pos = 1;
        foreach ($stage3Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage3Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => Str::title(str_replace('_', ' ', $q['key'])),
                'type' => $q['type'],
                'binding_type' => 'custom',
                'binding_target' => null,
                'is_required' => $q['is_required'],
                'options' => $q['options'],
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'show_in_daily_tasks' => false,
                'daily_tasks_filter_values' => null,
                'is_active' => true,
                'position' => $pos++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // =========================================================================
        // STAGE 4: لم يجدد / متوقف (منقطع) - Position 4
        // =========================================================================
        $stage4 = DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $catId)
            ->where('name_ar', 'لم يجدد / متوقف (منقطع)')
            ->first();

        if (! $stage4) {
            $stage4Id = DB::table('pipeline_stages')->insertGetId([
                'pipeline_stage_category_id' => $catId,
                'code' => 'stage_renewal_lost_' . Str::lower(Str::random(6)),
                'name_ar' => 'لم يجدد / متوقف (منقطع)',
                'description_ar' => 'المشتركون المنتهية عضوياتهم الذين قرروا عدم التجديد أو توقفوا، مع إلزامية توثيق السبب',
                'position' => 4,
                'is_primary' => false,
                'is_system' => false,
                'is_default' => false,
                'color' => '#dc2626',
                'icon' => 'bi-person-x',
                'is_active' => true,
                'has_followups' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('lead_statuses')->insertGetId([
                'pipeline_stage_id' => $stage4Id,
                'code' => 'status_renewal_lost_' . Str::lower(Str::random(6)),
                'name_ar' => 'لم يجدد',
                'position' => 1,
                'color' => '#dc2626',
                'is_terminal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $stage4Id = $stage4->id;
            DB::table('pipeline_stages')->where('id', $stage4Id)->update([
                'position' => 4,
                'description_ar' => 'المشتركون المنتهية عضوياتهم الذين قرروا عدم التجديد أو توقفوا، مع إلزامية توثيق السبب',
                'color' => '#dc2626',
                'icon' => 'bi-person-x',
                'is_active' => true,
            ]);
        }

        // Questions for Stage 4 (Mandatory loss reasons according to Spec 49):
        $stage4Questions = [
            [
                'key' => 'non_renewal_reason',
                'label_ar' => 'سبب عدم التجديد (إلزامي وفق المواصفات)',
                'type' => 'select',
                'is_required' => true,
                'options' => json_encode([
                    ['value' => 'السعر مرتفع', 'label_ar' => 'السعر مرتفع', 'label_en' => 'High Price'],
                    ['value' => 'توقف اللاعب عن النشاط الرياضي', 'label_ar' => 'توقف اللاعب عن النشاط الرياضي', 'label_en' => 'Stopped Sport'],
                    ['value' => 'انتقل لنشاط رياضي آخر خارج الأكاديمية', 'label_ar' => 'انتقل لنشاط رياضي آخر خارج الأكاديمية', 'label_en' => 'Switched Sport Elsewhere'],
                    ['value' => 'المواعيد غير مناسبة', 'label_ar' => 'المواعيد غير مناسبة', 'label_en' => 'Unsuitable Schedule'],
                    ['value' => 'مشكلة مع المدرب', 'label_ar' => 'مشكلة مع المدرب', 'label_en' => 'Coach Issue'],
                    ['value' => 'الموقع بعيد / تغيير السكن', 'label_ar' => 'الموقع بعيد / تغيير السكن', 'label_en' => 'Location / Relocated'],
                    ['value' => 'عدم الرضا عن مستوى الخدمة أو النظافة', 'label_ar' => 'عدم الرضا عن مستوى الخدمة أو النظافة', 'label_en' => 'Service / Cleanliness Dissatisfaction'],
                    ['value' => 'انتقل إلى أكاديمية منافسة', 'label_ar' => 'انتقل إلى أكاديمية منافسة', 'label_en' => 'Joined Competitor'],
                    ['value' => 'ظروف دراسية أو امتحانات', 'label_ar' => 'ظروف دراسية أو امتحانات', 'label_en' => 'Academic / Exam Reasons'],
                    ['value' => 'ظروف مالية', 'label_ar' => 'ظروف مالية', 'label_en' => 'Financial Reasons'],
                    ['value' => 'إصابة أو عارض صحي للاعب', 'label_ar' => 'إصابة أو عارض صحي للاعب', 'label_en' => 'Player Injury / Health'],
                    ['value' => 'سفر أو ظروف شخصية مؤقتة', 'label_ar' => 'سفر أو ظروف شخصية مؤقتة', 'label_en' => 'Travel / Temporary'],
                    ['value' => 'سبب آخر', 'label_ar' => 'سبب آخر', 'label_en' => 'Other Reason'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'key' => 'coach_feedback_summary',
                'label_ar' => 'ملخص تقييم المدرب للاعب قبل التوقف',
                'type' => 'textarea',
                'is_required' => false,
                'options' => null,
            ],
            [
                'key' => 'can_winback_later',
                'label_ar' => 'هل توجد فرصة لاستعادة المشترك مستقبلاً؟',
                'type' => 'select',
                'is_required' => false,
                'options' => json_encode([
                    ['value' => 'نعم بعد انتهاء الامتحانات', 'label_ar' => 'نعم بعد انتهاء الامتحانات', 'label_en' => 'Yes After Exams'],
                    ['value' => 'نعم مع بداية الموسم الصيفي', 'label_ar' => 'نعم مع بداية الموسم الصيفي', 'label_en' => 'Yes In Summer Season'],
                    ['value' => 'نعم بعد تعافي اللاعب من الإصابة', 'label_ar' => 'نعم بعد تعافي اللاعب من الإصابة', 'label_en' => 'Yes After Recovery'],
                    ['value' => 'لا توجد رغبة نهائياً', 'label_ar' => 'لا توجد رغبة نهائياً', 'label_en' => 'No - Definite Drop'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            [
                'key' => 'winback_date',
                'label_ar' => 'موعد إعادة التواصل للاستعادة (Win-back Date)',
                'type' => 'date',
                'is_required' => false,
                'options' => null,
            ],
        ];

        DB::table('pipeline_stage_fields')->where('pipeline_stage_id', $stage4Id)->delete();
        $pos = 1;
        foreach ($stage4Questions as $q) {
            DB::table('pipeline_stage_fields')->insert([
                'pipeline_stage_id' => $stage4Id,
                'key' => $q['key'],
                'label_ar' => $q['label_ar'],
                'label_en' => Str::title(str_replace('_', ' ', $q['key'])),
                'type' => $q['type'],
                'binding_type' => 'custom',
                'binding_target' => null,
                'is_required' => $q['is_required'],
                'options' => $q['options'],
                'show_on_transition' => true,
                'show_on_stage_view' => true,
                'show_in_history' => true,
                'show_in_daily_tasks' => ($q['key'] === 'can_winback_later'),
                'daily_tasks_filter_values' => ($q['key'] === 'can_winback_later') ? json_encode(['نعم بعد انتهاء الامتحانات', 'نعم مع بداية الموسم الصيفي', 'نعم بعد تعافي اللاعب من الإصابة'], JSON_UNESCAPED_UNICODE) : null,
                'is_active' => true,
                'position' => $pos++,
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
        $cat = DB::table('pipeline_stage_categories')
            ->where('name_en', 'Retention & Renewals')
            ->orWhere('name_ar', 'like', '%التجديد%')
            ->first();

        if ($cat) {
            $stageIds = DB::table('pipeline_stages')
                ->where('pipeline_stage_category_id', $cat->id)
                ->pluck('id');

            DB::table('pipeline_stage_fields')->whereIn('pipeline_stage_id', $stageIds)->delete();
            DB::table('lead_statuses')->whereIn('pipeline_stage_id', $stageIds)->delete();
            DB::table('pipeline_stages')->whereIn('id', $stageIds)->delete();
        }
    }
};
