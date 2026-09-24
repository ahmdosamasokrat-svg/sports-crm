<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FollowupCustomerField;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\NotificationDelivery;
use App\Models\NotificationOccurrence;
use App\Models\NotificationPreference;
use App\Models\NotificationRule;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\PipelineStageField;
use App\Models\User;
use App\Security\CrmPermission;
use App\Services\BirthdayService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\ReminderPlanner;
use App\Support\BirthdayModuleGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirthdayModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PipelineStage $stage;
    private LeadStatus $status;

    protected function setUp(): void
    {
        parent::setUp();
        BirthdayModuleGuard::flushCache();

        $branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch']
        );

        $this->admin = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $superAdminGroup = \App\Models\Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(\App\Models\Permission::pluck('id'));
        $this->admin->groups()->attach($superAdminGroup);

        $category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'color' => '#dc2626', 'position' => 1, 'is_active' => true]
        );

        $this->stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'new_leads'],
            [
                'pipeline_stage_category_id' => $category->id,
                'name' => 'New Leads',
                'name_ar' => 'عملاء جدد',
                'position' => 1,
            ]
        );

        $this->status = LeadStatus::query()->firstOrCreate(
            ['pipeline_stage_id' => $this->stage->id, 'name_ar' => 'عميل جديد'],
            ['code' => 'new', 'position' => 1, 'color' => '#3b82f6']
        );

        // Ensure active core birth_date field exists
        FollowupCustomerField::query()->updateOrCreate(
            ['key' => 'birth_date'],
            [
                'label_ar' => 'تاريخ الميلاد',
                'label_en' => 'Date of Birth',
                'type' => 'date',
                'is_active' => true,
                'is_system' => true,
                'position' => 1,
            ]
        );

        // Ensure default birthday notification rule exists
        $rule = NotificationRule::query()->firstOrCreate(
            ['event_key' => NotificationRule::EVENT_BIRTHDAY_REMINDER],
            [
                'name_ar' => 'تذكير بعيد ميلاد اللاعب',
                'name_en' => 'Athlete Birthday Reminder',
                'enabled' => true,
                'trigger_offset_minutes' => 0,
                'priority' => 'important',
            ]
        );
        $rule->channels()->firstOrCreate(['channel' => 'database']);
        $rule->channels()->firstOrCreate(['channel' => 'push']);
        $rule->recipients()->firstOrCreate(['recipient_type' => 'assigned_user']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        BirthdayModuleGuard::flushCache();
        parent::tearDown();
    }

    public function test_birthday_screen_is_accessible_when_core_birthday_field_is_active(): void
    {
        $this->assertTrue(BirthdayModuleGuard::isEnabled());

        $response = $this->actingAs($this->admin)->get(route('v2.birthdays.index'));

        $response->assertOk();
        $response->assertViewIs('birthdays.index');
        $response->assertSee(__('crm.athletes_birthdays'));
        $response->assertSee(__('crm.birthdays_today'));
    }

    public function test_birthday_screen_forbidden_when_birthday_and_age_fields_are_disabled(): void
    {
        // Deactivate all birthday and age fields in core and stages
        FollowupCustomerField::query()->whereIn('key', ['birth_date', 'birthday', 'age'])->update(['is_active' => false]);
        PipelineStageField::query()->where(function ($q): void {
            $q->whereIn('key', ['birth_date', 'birthday', 'age'])
                ->orWhere('binding_target', 'birth_date');
        })->update(['is_active' => false]);

        BirthdayModuleGuard::flushCache();
        $this->assertFalse(BirthdayModuleGuard::isEnabled());

        $response = $this->actingAs($this->admin)->get(route('v2.birthdays.index'));

        $response->assertStatus(403);
    }

    public function test_birthday_screen_accessible_when_only_stage_has_birthday_field(): void
    {
        // Deactivate core
        FollowupCustomerField::query()->whereIn('key', ['birth_date', 'birthday', 'age'])->update(['is_active' => false]);

        // Activate in stage
        PipelineStageField::query()->create([
            'pipeline_stage_id' => $this->stage->id,
            'key' => 'player_birthday',
            'label_ar' => 'تاريخ ميلاد اللاعب',
            'type' => 'date',
            'binding_type' => 'canonical',
            'binding_target' => 'birth_date',
            'is_active' => true,
            'position' => 1,
        ]);

        BirthdayModuleGuard::flushCache();
        $this->assertTrue(BirthdayModuleGuard::isEnabled());

        $response = $this->actingAs($this->admin)->get(route('v2.birthdays.index'));
        $response->assertOk();
    }

    public function test_birthday_screen_displays_athletes_and_dynamic_age_calculation(): void
    {
        $fixedNow = Carbon::parse('2026-09-24 10:00:00');
        Carbon::setTestNow($fixedNow);

        // Lead 1: Birthday today (Born 2014-09-24, turns 12 today)
        $leadToday = Lead::query()->create([
            'name' => 'البطل أحمد اليوم',
            'phone' => '0551111111',
            'birth_date' => '2014-09-24',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
            'activity' => 'جمباز',
        ]);

        // Lead 2: Birthday in 3 days (Born 2016-09-27, turns 10)
        $leadUpcoming = Lead::query()->create([
            'name' => 'اللاعبة مريم قادمة',
            'phone' => '0552222222',
            'birth_date' => '2016-09-27',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
            'activity' => 'سباحة',
        ]);

        // 1. Check Today tab
        $responseToday = $this->actingAs($this->admin)->get(route('v2.birthdays.index', ['tab' => 'today']));
        $responseToday->assertOk();
        $responseToday->assertSee('البطل أحمد اليوم');
        $responseToday->assertSee('12');
        $responseToday->assertDontSee('اللاعبة مريم قادمة');

        // 2. Check Week tab (both should be in week)
        $responseWeek = $this->actingAs($this->admin)->get(route('v2.birthdays.index', ['tab' => 'week']));
        $responseWeek->assertOk();
        $responseWeek->assertSee('البطل أحمد اليوم');
        $responseWeek->assertSee('اللاعبة مريم قادمة');

        // 3. Check Search
        $responseSearch = $this->actingAs($this->admin)->get(route('v2.birthdays.index', ['tab' => 'week', 'search' => 'مريم']));
        $responseSearch->assertOk();
        $responseSearch->assertSee('اللاعبة مريم قادمة');
        $responseSearch->assertDontSee('البطل أحمد اليوم');
    }

    public function test_birthday_notification_reminders_planned_without_messages(): void
    {
        $fixedNow = Carbon::parse('2026-09-24 09:00:00');
        Carbon::setTestNow($fixedNow);

        config(['crm_notifications.enabled' => true]);
        config(['crm_notifications.channels.database' => true]);
        config(['crm_notifications.channels.push' => true]);

        NotificationPreference::query()->updateOrCreate(
            ['user_id' => $this->admin->id],
            ['in_app_enabled' => true, 'push_enabled' => true]
        );

        $lead = Lead::query()->create([
            'name' => 'بطل عيد الميلاد',
            'phone' => '0559999999',
            'birth_date' => '2015-09-24',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
        ]);

        $planner = app(ReminderPlanner::class);
        $dispatcher = app(NotificationDispatcher::class);

        // First run: plans 1 occurrence
        $planned = $planner->planScheduled($fixedNow);
        $this->assertSame(1, $planned);

        // Deduplication: running again plans 0
        $plannedAgain = $planner->planScheduled($fixedNow);
        $this->assertSame(0, $plannedAgain);

        // Check occurrence properties
        $occurrence = NotificationOccurrence::query()
            ->where('event_key', NotificationRule::EVENT_BIRTHDAY_REMINDER)
            ->where('source_id', $lead->id)
            ->firstOrFail();

        $this->assertSame('lead_birthday', $occurrence->source_kind);
        $this->assertSame($this->admin->id, $occurrence->recipient_user_id);

        // Dispatch occurrences
        $dispatched = $dispatcher->dispatchDueOccurrences($fixedNow);
        $this->assertSame(1, $dispatched);

        // Ensure deliveries created only for internal channels (no sms/whatsapp)
        $deliveries = NotificationDelivery::query()
            ->where('notification_occurrence_id', $occurrence->id)
            ->get();

        $channels = $deliveries->pluck('channel')->all();
        $this->assertContains('database', $channels);
        $this->assertNotContains('sms', $channels);
        $this->assertNotContains('whatsapp', $channels);
    }
    public function test_notification_drawer_due_followups_includes_birthday_counts_and_items(): void
    {
        $fixedNow = Carbon::parse('2026-09-24 10:00:00');
        Carbon::setTestNow($fixedNow);

        Lead::query()->create([
            'name' => 'بطل الدرج اليوم',
            'phone' => '0553333333',
            'birth_date' => '2015-09-24',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
            'activity' => 'كرة قدم',
        ]);

        Lead::query()->create([
            'name' => 'بطل الشهر القادم قريباً',
            'phone' => '0554444444',
            'birth_date' => '2017-09-29',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
            'activity' => 'جمباز',
        ]);

        // Default call: should include birthdays_month and birthdays_today in meta
        $response = $this->actingAs($this->admin)->getJson(route('v2.notifications.due-followups'));
        $response->assertOk();
        $response->assertJsonPath('meta.birthdays_month', 2);
        $response->assertJsonPath('meta.birthdays_today', 1);

        // Filter = birthdays: should return birthday items
        $responseBday = $this->actingAs($this->admin)->getJson(route('v2.notifications.due-followups', ['filter' => 'birthdays']));
        $responseBday->assertOk();
        $responseBday->assertJsonPath('meta.total_for_filter', 2);
        $this->assertCount(2, $responseBday->json('items'));
        $names = collect($responseBday->json('items'))->pluck('name')->all();
        $this->assertTrue(collect($names)->contains(fn ($n) => str_contains((string) $n, 'بطل الدرج اليوم')));
        $this->assertTrue(collect($names)->contains(fn ($n) => str_contains((string) $n, 'بطل الشهر القادم قريباً')));
    }


    public function test_birthday_reminders_not_planned_when_module_is_disabled(): void
    {
        $fixedNow = Carbon::parse('2026-09-24 09:00:00');
        Carbon::setTestNow($fixedNow);

        FollowupCustomerField::query()->whereIn('key', ['birth_date', 'birthday', 'age'])->update(['is_active' => false]);
        PipelineStageField::query()->where(function ($q): void {
            $q->whereIn('key', ['birth_date', 'birthday', 'age'])
                ->orWhere('binding_target', 'birth_date');
        })->update(['is_active' => false]);

        BirthdayModuleGuard::flushCache();
        $this->assertFalse(BirthdayModuleGuard::isEnabled());

        Lead::query()->create([
            'name' => 'بطل غير مفعل',
            'phone' => '0558888888',
            'birth_date' => '2015-09-24',
            'lead_status_id' => $this->status->id,
            'assigned_user_id' => $this->admin->id,
        ]);

        $planner = app(ReminderPlanner::class);
        $planned = $planner->planScheduled($fixedNow);

        $this->assertSame(0, $planned);
    }
}
