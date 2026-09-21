<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\PipelineStageField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademyFunnelAndObjectionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name' => 'الفرع الرئيسي', 'is_active' => true]
        );

        $this->admin = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => Group::SUPER_ADMIN_CODE],
            ['name' => 'مدير النظام', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(Permission::all());
        $this->admin->groups()->attach($superAdminGroup);
    }

    public function test_dashboard_renders_academy_funnel_and_objection_sections(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('قمع مبيعات الأكاديمية (Academy Conversion Funnel)');
        $response->assertSee('العملاء المحتملون');
        $response->assertSee('تم التواصل');
        $response->assertSee('حجز تجربة');
        $response->assertSee('حضور التجربة');
        $response->assertSee('الاشتراك الجديد');
        $response->assertSee('تحليل الاعتراضات وأسباب الفقد');
    }

    public function test_objections_summary_calculates_and_displays_real_reasons(): void
    {
        $category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'position' => 1]
        );

        $stage = PipelineStage::query()->create([
            'pipeline_stage_category_id' => $category->id,
            'code' => 'stage_lost_test_' . uniqid(),
            'name_ar' => 'مفقود للاختبار',
            'position' => 1,
            'is_active' => true,
        ]);

        $status = LeadStatus::query()->create([
            'pipeline_stage_id' => $stage->id,
            'code' => 'status_lost_test_' . uniqid(),
            'name_ar' => 'مفقود',
            'position' => 1,
        ]);

        $lead1 = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $status->id,
            'name' => 'عميل معترض 1',
        ]);

        $lead2 = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $status->id,
            'name' => 'عميل معترض 2',
        ]);

        // Insert distinct objection reasons
        DB::table('lead_stage_field_values')->insert([
            [
                'lead_id' => $lead1->id,
                'pipeline_stage_id' => $stage->id,
                'field_key' => 'reason_for_not_subscribing',
                'field_type' => 'select',
                'value' => 'السعر مرتفع جداً',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'lead_id' => $lead2->id,
                'pipeline_stage_id' => $stage->id,
                'field_key' => 'non_renewal_reason',
                'field_type' => 'select',
                'value' => 'المواعيد غير مناسبة نهائياً',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('السعر مرتفع جداً');
        $response->assertSee('المواعيد غير مناسبة نهائياً');
        $response->assertSee('2 اعتراض');
    }
}
