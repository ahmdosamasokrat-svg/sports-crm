<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadFollowupPipelineFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (CrmPermission::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission->value],
                ['module' => $permission->module(), 'name_ar' => $permission->label()],
            );
        }

        $group = Group::query()->updateOrCreate(
            ['code' => 'super-admin'],
            [
                'name' => 'مدير النظام',
                'description' => 'Super Admin',
                'is_system' => true,
            ],
        );
        $group->permissions()->sync(Permission::query()->pluck('id'));

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->groups()->attach($group);
    }

    public function test_followup_page_renders_pipeline_filter_and_categorized_stages(): void
    {
        $salesCat = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            [
                'name_en' => 'Sales & Outreach',
                'color' => '#3478f6',
                'position' => 1,
                'is_active' => true,
            ]
        );

        $closingCat = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'الإغلاق والتنفيذ'],
            [
                'name_en' => 'Closing & Execution',
                'color' => '#16a34a',
                'position' => 2,
                'is_active' => true,
            ]
        );

        $stage1 = PipelineStage::query()->create([
            'code' => 'stage_sales_' . uniqid(),
            'name_ar' => 'مرحلة مبيعات',
            'pipeline_stage_category_id' => $salesCat->id,
            'position' => 1,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $status1 = $stage1->statuses()->first();

        $stage2 = PipelineStage::query()->create([
            'code' => 'stage_closing_' . uniqid(),
            'name_ar' => 'مرحلة إغلاق',
            'pipeline_stage_category_id' => $closingCat->id,
            'position' => 2,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $status2 = $stage2->statuses()->first();

        $lead = Lead::query()->create([
            'name' => 'Lead Pipeline Test',
            'phone' => '0501234567',
            'source' => 'web',
            'lead_status_id' => $status1->id,
        ]);

        $response = $this->actingAs($this->admin)->withSession(['locale' => 'ar'])->get(route('v2.leads.followups.index', $lead));

        $response->assertOk();
        $response->assertSee('id="pipeline_filter"', false);
        $response->assertSee('المبيعات والتواصل');
        $response->assertSee('الإغلاق والتنفيذ');
        $response->assertSee('data-category-id="' . $salesCat->id . '"', false);
        $response->assertSee('data-category-id="' . $closingCat->id . '"', false);
    }
}
