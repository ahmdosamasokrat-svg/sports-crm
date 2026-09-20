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
        $subscriptionsCategory = DB::table('pipeline_stage_categories')
            ->where('id', 4)
            ->orWhere('name_en', 'Subscriptions')
            ->orWhere('name_ar', 'الاشتراكات')
            ->first();

        if (! $subscriptionsCategory) {
            return;
        }

        // 1. Shift existing stages in Subscriptions to make position 1 available
        DB::table('pipeline_stages')
            ->where('pipeline_stage_category_id', $subscriptionsCategory->id)
            ->whereNull('deleted_at')
            ->increment('position');

        // 2. Create the new onboarding stage "مشترك جديد" as stage #1 in Subscriptions
        $stageId = DB::table('pipeline_stages')->insertGetId([
            'pipeline_stage_category_id' => $subscriptionsCategory->id,
            'code' => 'stage_new_subscriber_' . Str::lower(Str::random(6)),
            'name_ar' => 'مشترك جديد',
            'description_ar' => 'فترة استقبال وتهيئة المشترك الجديد، رسالة الترحيب، وتحديد المجموعة والمدرب',
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

        // 3. Create initial lead status for this stage
        $statusId = DB::table('lead_statuses')->insertGetId([
            'pipeline_stage_id' => $stageId,
            'code' => 'status_new_sub_' . Str::lower(Str::random(6)),
            'name_ar' => 'مشترك جديد (قيد التهيئة)',
            'position' => 1,
            'color' => '#3b82f6',
            'is_terminal' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Update the Subscriptions Category trigger to point to this new stage as the target
        DB::table('pipeline_stage_categories')
            ->where('id', $subscriptionsCategory->id)
            ->update([
                'target_stage_id' => $stageId,
                'target_status_id' => $statusId,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert target_stage_id to 21 (مشترك مفعل) if needed
        DB::table('pipeline_stage_categories')
            ->where('id', 4)
            ->update([
                'target_stage_id' => 21,
                'target_status_id' => null,
            ]);

        $stage = DB::table('pipeline_stages')
            ->where('name_ar', 'مشترك جديد')
            ->where('pipeline_stage_category_id', 4)
            ->first();

        if ($stage) {
            DB::table('lead_statuses')->where('pipeline_stage_id', $stage->id)->delete();
            DB::table('pipeline_stages')->where('id', $stage->id)->delete();
        }
    }
};
