<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Branch;
use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BranchAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;
    private Branch $inactiveBranch;
    private User $admin;
    private User $managerAllBranches;
    private User $agentBranchA;
    private User $agentBranchB;
    private Lead $leadBranchA;
    private Lead $leadBranchB;
    private LeadStatus $statusNew;
    private LeadStatus $statusInterested;
    private PipelineStage $stage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);

        foreach (CrmPermission::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission->value],
                [
                    'module' => $permission->module(),
                    'name_ar' => $permission->label(),
                ]
            );
        }

        // 1. Create Branches
        $this->branchA = Branch::query()->create([
            'name_ar' => 'فرع المعادي',
            'name_en' => 'Maadi Branch',
            'code' => 'maadi',
            'phone' => '01011111111',
            'address' => 'شارع النصر، المعادي',
            'is_active' => true,
        ]);

        $this->branchB = Branch::query()->create([
            'name_ar' => 'فرع التجمع',
            'name_en' => 'Tagamoa Branch',
            'code' => 'tagamoa',
            'phone' => '01022222222',
            'address' => 'شارع التسعين، التجمع الخامس',
            'is_active' => true,
        ]);

        $this->inactiveBranch = Branch::query()->create([
            'name_ar' => 'فرع الإسكندرية (مغلق)',
            'name_en' => 'Alexandria Branch (Closed)',
            'code' => 'alex',
            'is_active' => false,
        ]);

        // 2. Groups
        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => Group::SUPER_ADMIN_CODE],
            ['name' => 'مدير النظام', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(Permission::all());

        $allBranchGroup = Group::query()->create([
            'name' => 'مدراء العمليات (كافة الفروع)',
            'code' => 'operations-managers',
            'is_system' => false,
        ]);
        $allBranchGroup->permissions()->sync(Permission::whereIn('code', [
            CrmPermission::DASHBOARD_VIEW->value,
            CrmPermission::SETTINGS_ACCESS->value,
            CrmPermission::BRANCHES_VIEW->value,
            CrmPermission::BRANCHES_MANAGE->value,
            CrmPermission::BRANCHES_SCOPE_ALL->value,
            CrmPermission::LEADS_VIEW->value,
            CrmPermission::LEADS_SCOPE_ALL->value,
            CrmPermission::LEADS_CREATE->value,
            CrmPermission::LEADS_UPDATE->value,
            CrmPermission::LEADS_DELETE->value,
            CrmPermission::LEADS_FOLLOWUPS_VIEW->value,
            CrmPermission::LEADS_FOLLOWUPS_CREATE->value,
            CrmPermission::TASKS_VIEW->value,
            CrmPermission::CALENDAR_VIEW->value,
            CrmPermission::CALENDAR_MANAGE->value,
        ])->pluck('id'));

        $staffGroup = Group::query()->create([
            'name' => 'موظفو الفرع',
            'code' => 'branch-staff',
            'is_system' => false,
        ]);
        $staffGroup->permissions()->sync(Permission::whereIn('code', [
            CrmPermission::DASHBOARD_VIEW->value,
            CrmPermission::BRANCHES_VIEW->value,
            CrmPermission::BRANCHES_SCOPE_ASSIGNED->value,
            CrmPermission::LEADS_VIEW->value,
            CrmPermission::LEADS_SCOPE_GROUP->value,
            CrmPermission::LEADS_CREATE->value,
            CrmPermission::LEADS_UPDATE->value,
            CrmPermission::LEADS_DELETE->value,
            CrmPermission::LEADS_FOLLOWUPS_VIEW->value,
            CrmPermission::LEADS_FOLLOWUPS_CREATE->value,
            CrmPermission::TASKS_VIEW->value,
            CrmPermission::CALENDAR_VIEW->value,
            CrmPermission::CALENDAR_MANAGE->value,
        ])->pluck('id'));

        // 3. Users
        $this->admin = User::factory()->create([
            'name' => 'سوبر أدمن',
            'username' => 'admin_super',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->admin->groups()->attach($superAdminGroup);

        $this->managerAllBranches = User::factory()->create([
            'name' => 'مدير عام الفروع',
            'username' => 'manager_all',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->managerAllBranches->groups()->attach($allBranchGroup);

        $this->agentBranchA = User::factory()->create([
            'name' => 'موظف فرع المعادي',
            'username' => 'staff_maadi',
            'branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);
        $this->agentBranchA->groups()->attach($staffGroup);

        $this->agentBranchB = User::factory()->create([
            'name' => 'موظف فرع التجمع',
            'username' => 'staff_tagamoa',
            'branch_id' => $this->branchB->id,
            'is_active' => true,
        ]);
        $this->agentBranchB->groups()->attach($staffGroup);

        // 4. Stages & Statuses
        $this->stage = PipelineStage::query()->create([
            'code' => 'sales_funnel',
            'name_ar' => 'مسار المبيعات',
            'name_en' => 'Sales Funnel',
            'position' => 1,
            'color' => '#3b82f6',
            'is_primary' => true,
            'is_active' => true,
            'has_followups' => true,
        ]);

        $this->statusNew = $this->stage->statuses()->first();
        $this->statusNew->update(['code' => 'new', 'name_ar' => 'جديد']);

        $this->statusInterested = LeadStatus::query()->create([
            'pipeline_stage_id' => $this->stage->id,
            'code' => 'interested',
            'name_ar' => 'مهتم',
            'position' => 2,
            'color' => '#10b981',
        ]);

        // 5. Leads in separate branches
        $this->leadBranchA = Lead::query()->create([
            'branch_id' => $this->branchA->id,
            'lead_status_id' => $this->statusNew->id,
            'name' => 'كابتن أحمد - عميل المعادي',
            'phone' => '01010000001',
            'email' => 'ahmed.maadi@example.com',
            'source' => 'Walk-in',
            'assigned_user_id' => $this->agentBranchA->id,
            'created_by_user_id' => $this->agentBranchA->id,
            'next_follow_up_at' => now()->addDay(),
        ]);

        $this->leadBranchB = Lead::query()->create([
            'branch_id' => $this->branchB->id,
            'lead_status_id' => $this->statusNew->id,
            'name' => 'كابتن محمود - عميل التجمع',
            'phone' => '01020000002',
            'email' => 'mahmoud.tagamoa@example.com',
            'source' => 'Instagram',
            'assigned_user_id' => $this->agentBranchB->id,
            'created_by_user_id' => $this->agentBranchB->id,
            'next_follow_up_at' => now()->addDay(),
        ]);
    }

    public function test_branch_crud_requires_manage_permissions(): void
    {
        // 1. Staff without branches.manage cannot access settings branches
        $response = $this->actingAs($this->agentBranchA)->get(route('v2.settings.branches.index'));
        $response->assertForbidden();

        // 2. Manager with branches.manage can view and create
        $response = $this->actingAs($this->managerAllBranches)->get(route('v2.settings.branches.index'));
        $response->assertOk();
        $response->assertSee('فرع المعادي');
        $response->assertSee('فرع التجمع');

        // 3. Create a new branch
        $createResponse = $this->actingAs($this->managerAllBranches)->post(route('v2.settings.branches.store'), [
            'name_ar' => 'فرع الشيخ زايد',
            'name_en' => 'Zayed Branch',
            'code' => 'zayed',
            'phone' => '01033333333',
            'address' => 'مول أركان، الشيخ زايد',
            'is_active' => '1',
        ]);
        $createResponse->assertRedirect(route('v2.settings.branches.index'));

        $this->assertDatabaseHas('branches', [
            'code' => 'zayed',
            'name_ar' => 'فرع الشيخ زايد',
            'is_active' => true,
        ]);
    }

    public function test_branch_deletion_safeguards(): void
    {
        // 1. Cannot delete branch with attached leads or users
        $response = $this->actingAs($this->managerAllBranches)
            ->delete(route('v2.settings.branches.destroy', $this->branchA));

        $response->assertRedirect(route('v2.settings.branches.index'));
        $this->assertNull($this->branchA->fresh()->deleted_at);

        // 2. Empty branch can be safely soft-deleted
        $emptyBranch = Branch::query()->create([
            'name_ar' => 'فرع تجريبي فارغ',
            'code' => 'empty-branch',
            'is_active' => true,
        ]);

        $deleteResponse = $this->actingAs($this->managerAllBranches)
            ->delete(route('v2.settings.branches.destroy', $emptyBranch));

        $deleteResponse->assertRedirect(route('v2.settings.branches.index'));
        $this->assertNotNull($emptyBranch->fresh()->deleted_at);
    }

    public function test_creating_lead_as_staff_automatically_assigns_user_branch(): void
    {
        $response = $this->actingAs($this->agentBranchA)->post(route('v2.leads.store'), [
            'first_name' => 'طارق',
            'last_name' => 'سعيد',
            'phone' => '01099999999',
            'source' => 'Walk-in',
            'lead_status_id' => $this->statusNew->id,
            'assigned_user_id' => $this->agentBranchA->id,
        ]);

        $response->assertRedirect();
        $newLead = Lead::query()->where('phone', '01099999999')->firstOrFail();
        $this->assertEquals($this->branchA->id, $newLead->branch_id);
    }

    public function test_submitted_branch_tampering_by_ordinary_staff_is_rejected(): void
    {
        // Ordinary staff from branch A tries to force branch B id
        $response = $this->actingAs($this->agentBranchA)->post(route('v2.leads.store'), [
            'first_name' => 'هاكر',
            'last_name' => 'الفروع',
            'phone' => '01088888888',
            'source' => 'Walk-in',
            'branch_id' => $this->branchB->id,
            'lead_status_id' => $this->statusNew->id,
            'assigned_user_id' => $this->agentBranchA->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('leads', ['phone' => '01088888888']);
    }

    public function test_branch_a_staff_can_view_edit_transition_and_followup_branch_a_lead(): void
    {
        // 1. List
        $listResponse = $this->actingAs($this->agentBranchA)->get(route('v2.leads'));
        $listResponse->assertOk();
        $listResponse->assertSee('كابتن أحمد - عميل المعادي');
        $listResponse->assertDontSee('كابتن محمود - عميل التجمع');

        // 2. Show
        $showResponse = $this->actingAs($this->agentBranchA)->get(route('v2.leads.show', $this->leadBranchA));
        $showResponse->assertOk();
        $showResponse->assertSee('كابتن أحمد - عميل المعادي');
        $showResponse->assertSee('فرع المعادي');

        // 3. Edit & Update
        $updateResponse = $this->actingAs($this->agentBranchA)->patch(route('v2.leads.update', $this->leadBranchA), [
            'first_name' => 'كابتن أحمد',
            'last_name' => 'المعدل',
            'phone' => '01010000001',
            'source' => 'Walk-in',
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals('كابتن أحمد المعدل', $this->leadBranchA->fresh()->name);

        // 4. Follow-up
        $followupResponse = $this->actingAs($this->agentBranchA)->post(route('v2.leads.followups.store', $this->leadBranchA), [
            'communication_type' => 'call',
            'outcome' => 'تم الاتصال بالعميل وتأكيد موعد التمرين التجريبي',
            'lead_status_id' => $this->statusInterested->id,
            'next_follow_up_at' => now()->addDays(2)->format('Y-m-d H:i'),
        ]);
        $followupResponse->assertRedirect();
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $this->leadBranchA->id,
            'communication_type' => 'call',
        ]);
    }

    public function test_branch_a_staff_is_strictly_forbidden_from_branch_b_lead(): void
    {
        // 1. Direct Show URL -> 403 Forbidden
        $showResponse = $this->actingAs($this->agentBranchA)->get(route('v2.leads.show', $this->leadBranchB));
        $showResponse->assertStatus(403);

        // 2. Direct Edit URL -> 403 Forbidden
        $editResponse = $this->actingAs($this->agentBranchA)->get(route('v2.leads.edit', $this->leadBranchB));
        $editResponse->assertStatus(403);

        // 3. Direct Update -> 403 Forbidden
        $updateResponse = $this->actingAs($this->agentBranchA)->patch(route('v2.leads.update', $this->leadBranchB), [
            'first_name' => 'محاولة اختراق',
            'phone' => '01020000002',
            'source' => 'Instagram',
        ]);
        $updateResponse->assertStatus(403);

        // 4. Direct Followup -> 403 Forbidden
        $followupResponse = $this->actingAs($this->agentBranchA)->post(route('v2.leads.followups.store', $this->leadBranchB), [
            'communication_type' => 'call',
            'outcome' => 'محاولة تسجيل متابعة لفرع آخر',
            'lead_status_id' => $this->statusNew->id,
        ]);
        $followupResponse->assertStatus(403);

        // 5. Daily Task Reschedule -> 403 Forbidden
        $rescheduleResponse = $this->actingAs($this->agentBranchA)->post(route('v2.tasks.reschedule', $this->leadBranchB), [
            'next_follow_up_at' => now()->addDays(3)->format('Y-m-d H:i'),
        ]);
        $rescheduleResponse->assertStatus(403);
    }

    public function test_calendar_events_strictly_inherit_lead_branch_isolation(): void
    {
        $calEventA = CalendarEvent::query()->create([
            'user_id' => $this->agentBranchA->id,
            'lead_id' => $this->leadBranchA->id,
            'title' => 'جلسة قياس وزن - المعادي',
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(3),
            'type' => 'meeting',
            'status' => 'scheduled',
        ]);

        $calEventB = CalendarEvent::query()->create([
            'user_id' => $this->agentBranchB->id,
            'lead_id' => $this->leadBranchB->id,
            'title' => 'حصة تجريبية فتنس - التجمع',
            'start_time' => now()->addHours(4),
            'end_time' => now()->addHours(5),
            'type' => 'meeting',
            'status' => 'scheduled',
        ]);

        // Branch A staff sees Event A in calendar feed, but Event B is absent
        $calendarFeed = $this->actingAs($this->agentBranchA)->get(route('v2.calendar.events', [
            'start' => now()->startOfDay()->toDateTimeString(),
            'end' => now()->endOfDay()->toDateTimeString(),
        ]));

        $calendarFeed->assertOk();
        $feedData = $calendarFeed->json();
        $items = $feedData['data'] ?? $feedData;
        $eventIds = array_column($items, 'id');

        $this->assertContains($calEventA->id, $eventIds);
        $this->assertNotContains($calEventB->id, $eventIds);

        // Direct show endpoint for Event B is forbidden for Branch A staff
        $eventShow = $this->actingAs($this->agentBranchA)->get(route('v2.calendar.show', $calEventB));
        $eventShow->assertStatus(403);
    }

    public function test_manager_with_all_branch_scope_can_view_and_filter_all_branches(): void
    {
        // 1. Manager sees both leads in index
        $indexResponse = $this->actingAs($this->managerAllBranches)->get(route('v2.leads'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('كابتن أحمد - عميل المعادي');
        $indexResponse->assertSee('كابتن محمود - عميل التجمع');

        // 2. Filter by Branch A only
        $filterAResponse = $this->actingAs($this->managerAllBranches)->get(route('v2.leads', ['branch' => $this->branchA->id]));
        $filterAResponse->assertOk();
        $filterAResponse->assertSee('كابتن أحمد - عميل المعادي');
        $filterAResponse->assertDontSee('كابتن محمود - عميل التجمع');

        // 3. Filter by Branch B only
        $filterBResponse = $this->actingAs($this->managerAllBranches)->get(route('v2.leads', ['branch' => $this->branchB->id]));
        $filterBResponse->assertOk();
        $filterBResponse->assertDontSee('كابتن أحمد - عميل المعادي');
        $filterBResponse->assertSee('كابتن محمود - عميل التجمع');

        // 4. Manager can reassign branch
        $reassignResponse = $this->actingAs($this->managerAllBranches)->patch(route('v2.leads.update', $this->leadBranchA), [
            'first_name' => 'كابتن أحمد',
            'phone' => '01010000001',
            'source' => 'Walk-in',
            'branch_id' => $this->branchB->id,
        ]);
        $reassignResponse->assertRedirect();
        $this->assertEquals($this->branchB->id, $this->leadBranchA->fresh()->branch_id);
    }

    public function test_inactive_branch_cannot_be_assigned_to_new_records(): void
    {
        $response = $this->actingAs($this->managerAllBranches)->post(route('v2.leads.store'), [
            'first_name' => 'عميل',
            'last_name' => 'جديد',
            'phone' => '01077777777',
            'source' => 'Walk-in',
            'branch_id' => $this->inactiveBranch->id,
            'lead_status_id' => $this->statusNew->id,
        ]);

        $response->assertSessionHasErrors('branch_id');
        $this->assertDatabaseMissing('leads', ['phone' => '01077777777']);
    }

    public function test_legacy_backfill_migration_is_idempotent_and_does_not_overwrite_existing(): void
    {
        // 1. Create a lead with explicit Branch B
        $existingLead = Lead::query()->create([
            'branch_id' => $this->branchB->id,
            'lead_status_id' => $this->statusNew->id,
            'name' => 'عميل مسبق في التجمع',
            'phone' => '01055555555',
            'source' => 'Walk-in',
        ]);

        // 2. Create an orphaned lead with null branch
        $orphanedLeadId = DB::table('leads')->insertGetId([
            'branch_id' => null,
            'lead_status_id' => $this->statusNew->id,
            'name' => 'عميل قديم بدون فرع',
            'phone' => '01066666666',
            'source' => 'Legacy',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Re-run backfill logic
        $migration = require database_path('migrations/2026_09_17_120200_backfill_default_branch_for_users_and_leads.php');
        $migration->up();

        // 4. Assert existing lead still has Branch B (was not overwritten)
        $this->assertEquals($this->branchB->id, $existingLead->fresh()->branch_id);

        // 5. Assert orphaned lead now has a valid branch
        $orphanedLead = Lead::query()->findOrFail($orphanedLeadId);
        $this->assertNotNull($orphanedLead->branch_id);
    }
}
