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

class RetentionAndRenewalsPipelineTest extends TestCase
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

    public function test_retention_and_renewals_category_has_all_four_stages_and_questions(): void
    {
        $category = PipelineStageCategory::query()
            ->where('name_en', 'Retention & Renewals')
            ->orWhere('name_ar', 'like', '%التجديد%')
            ->firstOrFail();

        $stages = PipelineStage::query()
            ->where('pipeline_stage_category_id', $category->id)
            ->orderBy('position')
            ->get();

        $this->assertCount(4, $stages);

        $stage1 = $stages->firstWhere('position', 1);
        $stage2 = $stages->firstWhere('position', 2);
        $stage3 = $stages->firstWhere('position', 3);
        $stage4 = $stages->firstWhere('position', 4);

        $this->assertEquals('مستحق التجديد (قرب الانتهاء)', $stage1->name_ar);
        $this->assertEquals('متابعة وتفاوض التجديد', $stage2->name_ar);
        $this->assertEquals('تم التجديد بنجاح', $stage3->name_ar);
        $this->assertEquals('لم يجدد / متوقف (منقطع)', $stage4->name_ar);

        // Verify stage 1 fields
        $stage1Fields = PipelineStageField::query()->where('pipeline_stage_id', $stage1->id)->pluck('key')->all();
        $this->assertContains('expiry_bucket', $stage1Fields);
        $this->assertContains('current_end_date', $stage1Fields);

        // Verify stage 2 fields
        $stage2Fields = PipelineStageField::query()->where('pipeline_stage_id', $stage2->id)->pluck('key')->all();
        $this->assertContains('parent_decision_status', $stage2Fields);

        // Verify stage 3 fields
        $stage3Fields = PipelineStageField::query()->where('pipeline_stage_id', $stage3->id)->pluck('key')->all();
        $this->assertContains('renewed_package', $stage3Fields);
        $this->assertContains('renewal_amount_collected', $stage3Fields);

        // Verify stage 4 mandatory loss reasons (Spec 49)
        $stage4Fields = PipelineStageField::query()->where('pipeline_stage_id', $stage4->id)->pluck('key')->all();
        $this->assertContains('non_renewal_reason', $stage4Fields);
        $this->assertContains('can_winback_later', $stage4Fields);
    }

    public function test_can_view_renewals_stages_in_settings_grouped_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.settings.stages.index'));

        $response->assertOk();
        $response->assertSee('التجديدات والمحافظة');
        $response->assertSee('مستحق التجديد (قرب الانتهاء)');
        $response->assertSee('متابعة وتفاوض التجديد');
        $response->assertSee('تم التجديد بنجاح');
        $response->assertSee('لم يجدد / متوقف (منقطع)');
    }
}
