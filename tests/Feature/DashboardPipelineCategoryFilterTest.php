<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPipelineCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PipelineStageCategory $salesCategory;
    private PipelineStageCategory $supportCategory;
    private PipelineStage $stage1;
    private PipelineStage $stage2;
    private PipelineStage $stage3;

    protected function setUp(): void
    {
        parent::setUp();

        if (Permission::query()->count() < count(CrmPermission::cases())) {
            $perms = array_map(static fn ($p) => [
                'code' => $p->value,
                'module' => $p->module(),
                'name_ar' => $p->label(),
            ], CrmPermission::cases());
            Permission::query()->upsert($perms, ['code'], ['module', 'name_ar']);
        }

        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            [
                'name' => 'مدير النظام',
                'description' => 'Super Admin',
                'is_system' => true,
            ]
        );
        $superAdminGroup->permissions()->sync(Permission::pluck('id'));

        $this->admin = User::factory()->create([
            'username' => 'dash_cat_admin',
            'name' => 'Dashboard Category Admin',
            'is_active' => true,
        ]);
        $this->admin->groups()->attach($superAdminGroup);

        $this->salesCategory = PipelineStageCategory::query()->create([
            'name_ar' => 'مسار المبيعات الرئيسي',
            'name_en' => 'Main Sales Pipeline',
            'is_active' => true,
            'position' => 1,
        ]);

        $this->supportCategory = PipelineStageCategory::query()->create([
            'name_ar' => 'مسار الدعم الفني',
            'name_en' => 'Support Pipeline',
            'is_active' => true,
            'position' => 2,
        ]);

        $this->stage1 = PipelineStage::query()->create([
            'code' => 'dash_test_stage_1',
            'name_ar' => 'مرحلة مبيعات 1',
            'position' => 1,
            'color' => '#3478f6',
            'is_active' => true,
            'pipeline_stage_category_id' => $this->salesCategory->id,
        ]);

        $this->stage2 = PipelineStage::query()->create([
            'code' => 'dash_test_stage_2',
            'name_ar' => 'مرحلة دعم 1',
            'position' => 2,
            'color' => '#10b981',
            'is_active' => true,
            'pipeline_stage_category_id' => $this->supportCategory->id,
        ]);

        $this->stage3 = PipelineStage::query()->create([
            'code' => 'dash_test_stage_3',
            'name_ar' => 'مرحلة عامة بدون فئة',
            'position' => 3,
            'color' => '#f59e0b',
            'is_active' => true,
            'pipeline_stage_category_id' => null,
        ]);
    }

    public function test_dashboard_provides_categories_and_active_pipeline_stages_with_category_id(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        $categories = $response->viewData('categories');
        $this->assertNotNull($categories);
        $this->assertTrue($categories->contains('id', $this->salesCategory->id));
        $this->assertTrue($categories->contains('id', $this->supportCategory->id));

        $this->assertEquals('all', $response->viewData('selectedCategoryId'));
        $this->assertTrue($response->viewData('hasUncategorizedStages'));

        $activeStages = $response->viewData('activePipelineStages');
        $this->assertNotNull($activeStages);

        $stage1Item = $activeStages->firstWhere('id', $this->stage1->id);
        $this->assertNotNull($stage1Item);
        $this->assertEquals((string) $this->salesCategory->id, $stage1Item['category_id']);

        $stage2Item = $activeStages->firstWhere('id', $this->stage2->id);
        $this->assertNotNull($stage2Item);
        $this->assertEquals((string) $this->supportCategory->id, $stage2Item['category_id']);

        $stage3Item = $activeStages->firstWhere('id', $this->stage3->id);
        $this->assertNotNull($stage3Item);
        $this->assertEquals('uncategorized', $stage3Item['category_id']);
    }

    public function test_dashboard_respects_category_id_query_parameter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard', [
            'category_id' => (string) $this->salesCategory->id,
        ]));
        $response->assertOk();

        $this->assertEquals((string) $this->salesCategory->id, $response->viewData('selectedCategoryId'));

        $responseUncat = $this->actingAs($this->admin)->get(route('dashboard', [
            'category_id' => 'uncategorized',
        ]));
        $responseUncat->assertOk();
        $this->assertEquals('uncategorized', $responseUncat->viewData('selectedCategoryId'));
    }

    public function test_dashboard_renders_category_dropdown_and_data_attributes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('id="dashPipelineCategoryFilter"', false);
        $response->assertSee($this->salesCategory->localizedName());
        $response->assertSee($this->supportCategory->localizedName());
        $response->assertSee('data-category-id="' . $this->salesCategory->id . '"', false);
        $response->assertSee('data-category-id="' . $this->supportCategory->id . '"', false);
        $response->assertSee('data-category-id="uncategorized"', false);
        $response->assertSee('id="dashPipelineStripEmpty"', false);
    }
}
