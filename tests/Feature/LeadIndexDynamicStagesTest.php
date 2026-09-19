<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadIndexDynamicStagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PipelineStage $startStage;
    private PipelineStage $interestStage;
    private PipelineStage $customStage;
    private LeadStatus $newStatus;
    private LeadStatus $noAnswerStatus;
    private LeadStatus $interestedStatus;
    private LeadStatus $customStatus;

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
            'username' => 'leads_admin',
            'name' => 'Leads Admin',
            'is_active' => true,
        ]);
        $this->admin->groups()->attach($superAdminGroup);

        // 3. Create active stages & statuses
        $this->startStage = PipelineStage::query()->firstOrCreate(
            ['code' => 'start'],
            [
                'name_ar' => 'البداية',
                'position' => 1,
                'color' => '#3478f6',
                'icon' => 'bi-person-plus',
                'is_primary' => true,
                'is_active' => true,
            ]
        );
        $this->newStatus = LeadStatus::query()->firstOrCreate(
            ['code' => 'new'],
            [
                'pipeline_stage_id' => $this->startStage->id,
                'name_ar' => 'جديد',
                'position' => 1,
                'color' => '#3478f6',
                'is_terminal' => false,
            ]
        );
        $this->newStatus->update([
            'pipeline_stage_id' => $this->startStage->id,
            'name_ar' => 'جديد',
            'color' => '#3478f6',
        ]);
        $this->noAnswerStatus = LeadStatus::query()->firstOrCreate(
            ['code' => 'no_answer'],
            [
                'pipeline_stage_id' => $this->startStage->id,
                'name_ar' => 'لم يرد',
                'position' => 2,
                'color' => '#e59b16',
                'is_terminal' => false,
            ]
        );

        $this->interestStage = PipelineStage::query()->firstOrCreate(
            ['code' => 'interest'],
            [
                'name_ar' => 'الاهتمام',
                'position' => 2,
                'color' => '#10b981',
                'is_primary' => true,
                'is_active' => true,
            ]
        );
        $this->interestedStatus = $this->interestStage->statuses()->first()
            ?? LeadStatus::query()->firstOrCreate(
                ['code' => 'interested'],
                [
                    'pipeline_stage_id' => $this->interestStage->id,
                    'name_ar' => 'مهتم',
                    'position' => 3,
                    'color' => '#10b981',
                    'is_terminal' => false,
                ]
            );

        $this->customStage = PipelineStage::query()->firstOrCreate(
            ['code' => 'stage_inspection'],
            [
                'name_ar' => 'المعاينة الفنية',
                'position' => 3,
                'color' => '#8b5cf6',
                'icon' => 'bi-tools',
                'is_primary' => false,
                'is_active' => true,
            ]
        );
        $this->customStatus = $this->customStage->statuses()->first()
            ?? LeadStatus::query()->firstOrCreate(
                ['code' => 'stage_inspection'],
                [
                    'pipeline_stage_id' => $this->customStage->id,
                    'name_ar' => 'المعاينة الفنية',
                    'position' => 4,
                    'color' => '#8b5cf6',
                    'is_terminal' => false,
                ]
            );
    }

    // 1. Leads Index stages come from active PipelineStages
    public function test_1_leads_index_stages_come_from_active_pipeline_stages(): void
    {
        app()->setLocale('ar');
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $pipelineStages = $response->viewData('pipelineStages');
        $this->assertTrue($pipelineStages->contains('id', $this->startStage->id));
        $this->assertTrue($pipelineStages->contains('id', $this->interestStage->id));
        $this->assertTrue($pipelineStages->contains('id', $this->customStage->id));
    }

    // 2. custom stage appears automatically in pipeline stages
    public function test_2_custom_stage_appears_automatically(): void
    {
        $newCustom = PipelineStage::query()->create([
            'code' => 'stage_vip_leads_' . uniqid(),
            'name_ar' => 'عملاء VIP في القائمة',
            'position' => 4,
            'color' => '#f59e0b',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $pipelineStages = $response->viewData('pipelineStages');
        $this->assertTrue($pipelineStages->contains('id', $newCustom->id));
    }

    // 3. inactive stage does not appear
    public function test_3_inactive_stage_does_not_appear(): void
    {
        $inactiveStage = PipelineStage::query()->create([
            'code' => 'stage_archived_leads',
            'name_ar' => 'مرحلة معطلة في القائمة',
            'position' => 15,
            'color' => '#6b7280',
            'is_primary' => false,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertDontSee('مرحلة معطلة في القائمة');
        $pipelineStages = $response->viewData('pipelineStages');
        $this->assertFalse($pipelineStages->contains('id', $inactiveStage->id));
    }

    // 4. stats grid is not rendered
    public function test_4_stats_grid_is_not_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertDontSee('stats-grid');
        $response->assertDontSee('stat-card');
    }

    // 5. zero-count active stage appears in pipelineStages data
    public function test_5_zero_count_active_stage_appears(): void
    {
        // customStage has 0 leads
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $pipelineStages = $response->viewData('pipelineStages');
        $custom = $pipelineStages->firstWhere('id', $this->customStage->id);
        $this->assertNotNull($custom);
        $this->assertEquals(0, $custom->leads_count ?? ($custom->scoped_leads_count ?? 0));
    }

    // 6. stage count includes all statuses under stage
    public function test_6_stage_count_includes_all_statuses_under_stage(): void
    {
        // startStage has newStatus and noAnswerStatus
        Lead::query()->create([
            'lead_status_id' => $this->newStatus->id,
            'name' => 'Lead New 1',
            'phone' => '0501110001',
            'source' => 'web',
        ]);
        Lead::query()->create([
            'lead_status_id' => $this->newStatus->id,
            'name' => 'Lead New 2',
            'phone' => '0501110002',
            'source' => 'web',
        ]);
        Lead::query()->create([
            'lead_status_id' => $this->noAnswerStatus->id,
            'name' => 'Lead No Answer 1',
            'phone' => '0501110003',
            'source' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();

        $pipelineStages = $response->viewData('pipelineStages');
        $startData = $pipelineStages->firstWhere('id', $this->startStage->id);
        $this->assertEquals(3, $startData->leads_count);
    }

    // 7. stage ordering follows PipelineStage.position
    public function test_7_stage_ordering_follows_pipeline_stage_position(): void
    {
        $this->customStage->update(['position' => 0]);
        $this->startStage->update(['position' => 5]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();

        $pipelineStages = $response->viewData('pipelineStages');
        $this->assertEquals('المعاينة الفنية', $pipelineStages->first()->name_ar);
    }

    // 8. localized stage name resolves properly
    public function test_8_localized_stage_name_renders(): void
    {
        app()->setLocale('ar');
        $this->assertEquals('البداية', $this->startStage->localizedName());
        $this->assertEquals('الاهتمام', $this->interestStage->localizedName());

        app()->setLocale('en');
        $this->assertEquals('Start', $this->startStage->localizedName());
        $this->assertEquals('Interest', $this->interestStage->localizedName());
    }

    // 9. color is sourced from PipelineStage
    public function test_9_color_is_sourced_from_pipeline_stage(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $pipelineStages = $response->viewData('pipelineStages');
        $custom = $pipelineStages->firstWhere('id', $this->customStage->id);
        $this->assertEquals('#8b5cf6', $custom->color);
    }

    // 10. stats grid and cards are not rendered
    public function test_10_stats_grid_and_cards_are_not_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertDontSee('stats-grid');
        $response->assertDontSee('stat-card');
    }

    // 11. stage filter returns correct leads
    public function test_11_stage_filter_returns_correct_leads(): void
    {
        $leadStart = Lead::query()->create([
            'lead_status_id' => $this->newStatus->id,
            'name' => 'Lead In Start',
            'phone' => '0501111111',
            'source' => 'web',
        ]);
        $leadCustom = Lead::query()->create([
            'lead_status_id' => $this->customStatus->id,
            'name' => 'Lead In Custom',
            'phone' => '0502222222',
            'source' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads', [
            'stage' => $this->customStage->id,
        ]));

        $response->assertOk();
        $response->assertSee('Lead In Custom');
        $response->assertDontSee('Lead In Start');
    }

    // 12. existing filters combine with stage filter
    public function test_12_existing_filters_combine_with_stage_filter(): void
    {
        $leadTarget = Lead::query()->create([
            'lead_status_id' => $this->customStatus->id,
            'name' => 'Target Search Match',
            'phone' => '0503333331',
            'source' => 'facebook',
        ]);
        $leadOtherName = Lead::query()->create([
            'lead_status_id' => $this->customStatus->id,
            'name' => 'Different Person',
            'phone' => '0503333332',
            'source' => 'facebook',
        ]);
        $leadOtherStage = Lead::query()->create([
            'lead_status_id' => $this->newStatus->id,
            'name' => 'Target Search Match In Different Stage',
            'phone' => '0503333333',
            'source' => 'facebook',
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads', [
            'stage' => $this->customStage->id,
            'q' => 'Target Search Match',
            'source' => 'facebook',
        ]));

        $response->assertOk();
        $response->assertSee('Target Search Match');
        $response->assertDontSee('Different Person');
        $response->assertDontSee('Target Search Match In Different Stage');
    }

    // 13. branch/user scope preserved
    public function test_13_branch_and_user_lead_scope_preserved(): void
    {
        $restrictedGroup = Group::query()->create([
            'name' => 'مندوب مبيعات',
            'code' => 'agent-lead-scope',
            'is_system' => false,
        ]);
        $restrictedGroup->permissions()->sync(Permission::whereIn('code', [
            CrmPermission::DASHBOARD_VIEW->value,
            CrmPermission::LEADS_VIEW->value,
        ])->pluck('id'));

        $agentUser = User::factory()->create(['is_active' => true]);
        $agentUser->groups()->attach($restrictedGroup);

        $myLead = Lead::query()->create([
            'lead_status_id' => $this->customStatus->id,
            'name' => 'My Assigned Lead',
            'phone' => '0504444441',
            'assigned_user_id' => $agentUser->id,
            'source' => 'web',
        ]);

        $otherLead = Lead::query()->create([
            'lead_status_id' => $this->customStatus->id,
            'name' => 'Others Assigned Lead',
            'phone' => '0504444442',
            'assigned_user_id' => $this->admin->id,
            'source' => 'web',
        ]);

        $response = $this->actingAs($agentUser)->get(route('v2.leads', [
            'stage' => $this->customStage->id,
        ]));

        $response->assertOk();
        $response->assertSee('My Assigned Lead');
        $response->assertDontSee('Others Assigned Lead');
    }

    // 14. "كل الحالات" remains functional
    public function test_14_all_stages_button_clears_stage_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads', [
            'stage' => $this->customStage->id,
        ]));
        $response->assertOk();
        $response->assertSee(route('v2.leads'));
    }

    // 15. legacy status={code} filter remains compatible
    public function test_15_legacy_status_filter_remains_compatible(): void
    {
        $leadNew = Lead::query()->create([
            'lead_status_id' => $this->newStatus->id,
            'name' => 'Lead Legacy Code New',
            'phone' => '0505555551',
            'source' => 'web',
        ]);
        $leadNoAnswer = Lead::query()->create([
            'lead_status_id' => $this->noAnswerStatus->id,
            'name' => 'Lead Legacy Code No Answer',
            'phone' => '0505555552',
            'source' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads', [
            'status' => 'new',
        ]));

        $response->assertOk();
        $response->assertSee('Lead Legacy Code New');
        $response->assertDontSee('Lead Legacy Code No Answer');
    }

    // 16. no old hardcoded workflow cards remain
    public function test_16_no_old_hardcoded_workflow_cards_remain(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertDontSee('hero-pipeline');
        $response->assertDontSee('stats-grid');
    }

    // 17. existing Lead tests remain green
    public function test_17_existing_lead_index_features_remain_intact(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertViewHas('leads');
        $response->assertViewHas('pipelineStages');
        $response->assertViewHas('totalLeads');
        $response->assertViewHas('employees');
        $response->assertViewHas('sources');
    }

    // 18. hero pipeline and stats grid are not rendered
    public function test_18_hero_pipeline_is_not_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.leads'));
        $response->assertOk();
        $response->assertDontSee('hero-pipeline');
        $response->assertDontSee('heroPipelineSection');
        $response->assertDontSee('stats-grid');
        $response->assertDontSee('stat-card');
    }
}
