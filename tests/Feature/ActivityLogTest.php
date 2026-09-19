<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\User;
use App\Security\CrmPermission;
use App\Services\ActivityLogger;
use App\Services\LeadTransitionService;
use App\Services\LeadTrashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;

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
            'username' => 'logs_admin',
            'name' => 'Logs Admin',
            'is_active' => true,
        ]);
        $this->admin->groups()->attach($superAdminGroup);

        $agentGroup = Group::query()->firstOrCreate(
            ['code' => 'agent-group'],
            [
                'name' => 'موظف مبيعات',
                'description' => 'Sales Agent',
                'is_system' => false,
            ]
        );
        $agentGroup->permissions()->sync(
            Permission::whereIn('code', [
                CrmPermission::DASHBOARD_VIEW->value,
                CrmPermission::LEADS_VIEW->value,
                CrmPermission::LEADS_CREATE->value,
                CrmPermission::LEADS_UPDATE->value,
            ])->pluck('id')
        );

        $this->agent = User::factory()->create([
            'username' => 'logs_agent',
            'name' => 'Logs Agent',
            'is_active' => true,
        ]);
        $this->agent->groups()->attach($agentGroup);
    }

    public function test_activity_logger_writes_to_database(): void
    {
        $log = ActivityLogger::log(
            action: 'custom.test_action',
            module: 'settings',
            description: 'اختبار تسجيل النشاط',
            subject: $this->admin,
            properties: ['foo' => 'bar'],
            actor: $this->admin,
        );

        $this->assertNotNull($log);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'custom.test_action',
            'module' => 'settings',
            'user_id' => $this->admin->id,
            'description' => 'اختبار تسجيل النشاط',
        ]);
    }

    public function test_creating_lead_triggers_activity_log(): void
    {
        $stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'stage_test_lead'],
            ['name_ar' => 'مرحلة الاختبار', 'position' => 1, 'is_active' => true]
        );
        $status = LeadStatus::query()->firstOrCreate(
            ['code' => 'status_test_lead'],
            ['pipeline_stage_id' => $stage->id, 'name_ar' => 'حالة الاختبار', 'position' => 1]
        );

        $this->actingAs($this->admin);

        $lead = Lead::query()->create([
            'lead_status_id' => $status->id,
            'name' => 'تجربة عميل جديد للتدقيق',
            'phone' => '0509999999',
            'source' => 'web',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'lead.created',
            'subject_id' => $lead->id,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_updating_lead_tracks_field_changes(): void
    {
        $stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'stage_test_update'],
            ['name_ar' => 'مرحلة التعديل', 'position' => 1, 'is_active' => true]
        );
        $status = LeadStatus::query()->firstOrCreate(
            ['code' => 'status_test_update'],
            ['pipeline_stage_id' => $stage->id, 'name_ar' => 'حالة التعديل', 'position' => 1]
        );

        $lead = Lead::query()->create([
            'lead_status_id' => $status->id,
            'name' => 'عميل قبل التعديل',
            'phone' => '0508888888',
            'source' => 'web',
        ]);

        $this->actingAs($this->admin);

        $lead->update([
            'name' => 'عميل بعد التعديل',
            'notes' => 'ملاحظات تدقيق جديدة',
        ]);

        $latestLog = ActivityLog::query()
            ->where('subject_id', $lead->id)
            ->where('action', 'lead.updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($latestLog);
        $this->assertEquals('عميل قبل التعديل', $latestLog->properties['changes']['name']['old'] ?? null);
        $this->assertEquals('عميل بعد التعديل', $latestLog->properties['changes']['name']['new'] ?? null);
    }

    public function test_stage_transition_creates_audit_log(): void
    {
        $stage1 = PipelineStage::query()->firstOrCreate(
            ['code' => 'stg_1'],
            ['name_ar' => 'المرحلة الأولى', 'position' => 1, 'is_active' => true]
        );
        $status1 = LeadStatus::query()->firstOrCreate(
            ['code' => 'stat_1'],
            ['pipeline_stage_id' => $stage1->id, 'name_ar' => 'حالة 1', 'position' => 1]
        );

        $stage2 = PipelineStage::query()->firstOrCreate(
            ['code' => 'stg_2'],
            ['name_ar' => 'المرحلة الثانية', 'position' => 2, 'is_active' => true]
        );
        $status2 = LeadStatus::query()->firstOrCreate(
            ['code' => 'stat_2'],
            ['pipeline_stage_id' => $stage2->id, 'name_ar' => 'حالة 2', 'position' => 2]
        );

        $lead = Lead::query()->create([
            'lead_status_id' => $status1->id,
            'name' => 'عميل الانتقال',
            'phone' => '0507777777',
            'source' => 'web',
        ]);

        $transitionService = app(LeadTransitionService::class);
        $transitionService->transition(
            lead: $lead,
            toStatus: $status2,
            actor: $this->admin,
            context: []
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'lead.stage_transition',
            'subject_id' => $lead->id,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_trashing_and_restoring_lead_creates_audit_logs(): void
    {
        $stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'stg_trash'],
            ['name_ar' => 'مرحلة الحذف', 'position' => 1, 'is_active' => true]
        );
        $status = LeadStatus::query()->firstOrCreate(
            ['code' => 'stat_trash'],
            ['pipeline_stage_id' => $stage->id, 'name_ar' => 'حالة الحذف', 'position' => 1]
        );

        $lead = Lead::query()->create([
            'lead_status_id' => $status->id,
            'name' => 'عميل السلة',
            'phone' => '0506666666',
            'source' => 'web',
        ]);

        $trashService = app(LeadTrashService::class);
        $trashService->trashLead($lead, $this->admin, 'اختبار التدقيق للحذف');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'lead.trashed',
            'subject_id' => $lead->id,
            'user_id' => $this->admin->id,
        ]);

        $trashService->restoreLead($lead, $this->admin);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'lead.restored',
            'subject_id' => $lead->id,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_audit_logs_screen_requires_permission(): void
    {
        // Agent without audit_logs.view gets 403 Forbidden
        $this->actingAs($this->agent)
            ->get(route('v2.settings.audit-logs.index'))
            ->assertForbidden();

        app()->setLocale('ar');
        // Admin with audit_logs.view gets 200 OK in Arabic
        $this->actingAs($this->admin)
            ->get(route('v2.settings.audit-logs.index'))
            ->assertOk()
            ->assertSee('سجل العمليات والنشاطات');

        app()->setLocale('en');
        // Admin with audit_logs.view gets 200 OK in English
        $this->actingAs($this->admin)
            ->get(route('v2.settings.audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit Logs');
    }

    public function test_audit_logs_screen_filters_by_user_and_query(): void
    {
        ActivityLogger::log(
            action: 'user.custom_event',
            module: 'leads',
            description: 'سجل نشاط لموظف معين 123',
            actor: $this->agent,
        );

        $this->actingAs($this->admin)
            ->get(route('v2.settings.audit-logs.index', ['q' => '123']))
            ->assertOk()
            ->assertSee('سجل نشاط لموظف معين 123');

        $this->actingAs($this->admin)
            ->get(route('v2.settings.audit-logs.index', ['user_id' => $this->agent->id]))
            ->assertOk()
            ->assertSee('سجل نشاط لموظف معين 123');
    }

    public function test_group_and_calendar_events_trigger_activity_logs(): void
    {
        $this->actingAs($this->admin);

        $group = Group::query()->create([
            'name' => 'مجموعة الدعم الفني التجريبية',
            'code' => 'test-support-group',
            'description' => 'Test Group',
            'is_system' => false,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'group.created',
            'subject_id' => $group->id,
            'user_id' => $this->admin->id,
        ]);

        $event = \App\Models\CalendarEvent::query()->create([
            'user_id' => $this->admin->id,
            'title' => 'اجتماع عمل تجريبي للتدقيق',
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'type' => 'meeting',
            'status' => 'scheduled',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'calendarevent.created',
            'subject_id' => $event->id,
            'user_id' => $this->admin->id,
        ]);
    }
}
