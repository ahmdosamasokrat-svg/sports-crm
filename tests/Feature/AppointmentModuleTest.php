<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AppointmentSetting;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PipelineStage $stage17;

    private PipelineStage $stage18;

    private LeadStatus $status17;

    protected function setUp(): void
    {
        parent::setUp();
        AppointmentSetting::flushCache();

        $branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch']
        );

        $this->user = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(Permission::pluck('id'));
        $this->user->groups()->attach($superAdminGroup);

        $category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'color' => '#dc2626', 'position' => 1, 'is_active' => true]
        );

        $this->stage17 = PipelineStage::query()->firstOrCreate(
            ['id' => 17],
            [
                'pipeline_stage_category_id' => $category->id,
                'code' => 'stage_trial_booked_test',
                'name' => 'Trial Booked',
                'name_ar' => 'تجربة محجوزة',
                'position' => 4,
            ]
        );

        $this->stage18 = PipelineStage::query()->firstOrCreate(
            ['id' => 18],
            [
                'pipeline_stage_category_id' => $category->id,
                'code' => 'stage_post_trial_test',
                'name' => 'Post-Trial Follow-up',
                'name_ar' => 'متابعة ما بعد التجربة',
                'position' => 5,
            ]
        );

        $this->status17 = LeadStatus::query()->firstOrCreate(
            ['pipeline_stage_id' => $this->stage17->id, 'name_ar' => 'تم الحجز'],
            ['code' => 'trial_booked', 'position' => 1, 'color' => '#8b5cf6']
        );

        AppointmentSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'is_enabled' => true,
                'stage_ids' => [$this->stage17->id],
                'date_field_key' => 'trial_date',
                'time_field_key' => 'trial_time',
                'coach_field_key' => 'coach',
                'status_field_key' => 'trial_status',
                'allow_quick_actions' => true,
            ]
        );
        AppointmentSetting::flushCache();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        AppointmentSetting::flushCache();
        parent::tearDown();
    }

    public function test_can_view_appointments_screen_when_enabled(): void
    {
        $response = $this->actingAs($this->user)->get(route('v2.appointments.index'));

        $response->assertOk();
        $response->assertViewIs('appointments.index');
        $response->assertSee(__('crm.appointments_management'));
        $response->assertSee(__('crm.today_appointments'));
    }

    public function test_appointments_screen_is_forbidden_when_deactivated(): void
    {
        AppointmentSetting::current()->update(['is_enabled' => false]);
        AppointmentSetting::flushCache();

        $response = $this->actingAs($this->user)->get(route('v2.appointments.index'));

        $response->assertStatus(403);
    }

    public function test_can_view_and_update_appointments_settings(): void
    {
        $response = $this->actingAs($this->user)->get(route('v2.settings.appointments.index'));
        $response->assertOk();
        $response->assertSee(__('crm.appointments_settings'));

        $postResponse = $this->actingAs($this->user)->post(route('v2.settings.appointments.update'), [
            'is_enabled' => 1,
            'stage_ids' => [$this->stage17->id, $this->stage18->id],
            'date_field_key' => 'trial_date',
            'time_field_key' => 'trial_time',
            'coach_field_key' => 'coach',
            'status_field_key' => 'trial_status',
            'allow_quick_actions' => 1,
        ]);

        $postResponse->assertRedirect(route('v2.settings.appointments.index'));
        $setting = AppointmentSetting::current();
        $this->assertTrue($setting->is_enabled);
        $this->assertEquals([$this->stage17->id, $this->stage18->id], $setting->stage_ids);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings.appointments.updated',
            'module' => 'settings',
        ]);
    }

    public function test_displays_appointments_in_today_and_upcoming_tabs(): void
    {
        $now = Carbon::parse('2026-09-24 10:00:00');
        Carbon::setTestNow($now);

        // Lead 1: Today's appointment
        $leadToday = Lead::query()->create([
            'name' => 'بطل موعد اليوم',
            'phone' => '0551112222',
            'lead_status_id' => $this->status17->id,
            'assigned_user_id' => $this->user->id,
            'activity' => 'جمباز',
        ]);
        LeadStageFieldValue::query()->create([
            'lead_id' => $leadToday->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_date',
            'field_type' => 'date',
            'value' => '2026-09-24',
        ]);
        LeadStageFieldValue::query()->create([
            'lead_id' => $leadToday->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_time',
            'field_type' => 'time',
            'value' => '16:00',
        ]);
        LeadStageFieldValue::query()->create([
            'lead_id' => $leadToday->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'coach',
            'field_type' => 'text',
            'value' => 'كابتن محمود',
        ]);

        // Lead 2: Upcoming appointment (in 2 days)
        $leadUpcoming = Lead::query()->create([
            'name' => 'بطل موعد قادم',
            'phone' => '0553334444',
            'lead_status_id' => $this->status17->id,
            'assigned_user_id' => $this->user->id,
            'activity' => 'سباحة',
        ]);
        LeadStageFieldValue::query()->create([
            'lead_id' => $leadUpcoming->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_date',
            'field_type' => 'date',
            'value' => '2026-09-26',
        ]);

        // Check Today tab
        $responseToday = $this->actingAs($this->user)->get(route('v2.appointments.index', ['tab' => 'today']));
        $responseToday->assertOk();
        $responseToday->assertSee('بطل موعد اليوم');
        $responseToday->assertSee('كابتن محمود');
        $responseToday->assertDontSee('بطل موعد قادم');

        // Check Upcoming tab
        $responseUpcoming = $this->actingAs($this->user)->get(route('v2.appointments.index', ['tab' => 'upcoming']));
        $responseUpcoming->assertOk();
        $responseUpcoming->assertSee('بطل موعد قادم');
        $responseUpcoming->assertDontSee('بطل موعد اليوم');
    }

    public function test_quick_action_mark_attended(): void
    {
        $branch = Branch::query()->findOrFail($this->user->branch_id);
        $branch->update(['name_en' => 'Attendance Snapshot Branch']);

        $lead = Lead::query()->create([
            'name' => 'اللاعب الحاضر',
            'phone' => '0557778888',
            'lead_status_id' => $this->status17->id,
            'branch_id' => $branch->id,
            'assigned_user_id' => $this->user->id,
            'activity' => 'Football',
        ]);
        foreach ([
            ['trial_date', 'date', '2026-09-24'],
            ['trial_time', 'time', '15:30'],
            ['coach', 'text', 'Coach Snapshot'],
        ] as [$key, $type, $value]) {
            LeadStageFieldValue::query()->create([
                'lead_id' => $lead->id,
                'pipeline_stage_id' => $this->stage17->id,
                'field_key' => $key,
                'field_type' => $type,
                'value' => $value,
            ]);
        }

        $payload = ['notes' => 'حضر في الموعد وأتم التدريب بنجاح'];
        $response = $this->actingAs($this->user)->post(route('v2.appointments.attended', $lead), $payload);
        $response->assertRedirect();

        $this->actingAs($this->user)->post(route('v2.appointments.attended', $lead), $payload)->assertRedirect();

        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'trial_status',
            'value' => 'حضر',
        ]);
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'attended',
            'value' => 'نعم',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'appointment.attended',
            'subject_id' => $lead->id,
        ]);
        $this->assertDatabaseHas('appointment_attendance_records', [
            'lead_id' => $lead->id,
            'branch_id' => $branch->id,
            'outcome' => 'attended',
            'appointment_date' => '2026-09-24',
            'appointment_time' => '15:30',
            'activity' => 'Football',
            'branch_name_en' => 'Attendance Snapshot Branch',
            'coach' => 'Coach Snapshot',
            'notes' => $payload['notes'],
            'recorded_by_user_id' => $this->user->id,
        ]);
        $this->assertDatabaseCount('appointment_attendance_records', 1);

        $appointments = $this->actingAs($this->user)->get(route('v2.appointments.index', ['tab' => 'all']));
        $appointments->assertOk()->assertDontSee(route('v2.appointments.no_show', $lead), false);

        $profile = $this->actingAs($this->user)->get(route('v2.leads.show', $lead));
        $profile->assertOk()
            ->assertSee(__('crm.attendance_history'))
            ->assertSee('Football')
            ->assertSee('Coach Snapshot')
            ->assertSee($payload['notes']);

        $englishProfile = $this->withSession(['locale' => 'en'])
            ->actingAs($this->user)
            ->get(route('v2.leads.show', $lead));
        $englishProfile->assertOk()
            ->assertSee('Attendance History')
            ->assertSee('Activity')
            ->assertSee('Recorded at');
    }

    public function test_quick_action_mark_no_show(): void
    {
        $lead = Lead::query()->create([
            'name' => 'اللاعب الغائب',
            'phone' => '0559990000',
            'lead_status_id' => $this->status17->id,
            'assigned_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('v2.appointments.no_show', $lead), [
            'reason' => 'تعذر التواصل والهاتف مغلق',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'trial_status',
            'value' => 'لم يحضر',
        ]);
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'attended',
            'value' => 'لا',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'appointment.no_show',
            'subject_id' => $lead->id,
        ]);
        $this->assertDatabaseHas('appointment_attendance_records', [
            'lead_id' => $lead->id,
            'outcome' => 'no_show',
            'notes' => 'تعذر التواصل والهاتف مغلق',
            'recorded_by_user_id' => $this->user->id,
        ]);
    }

    public function test_quick_action_reschedule_clears_current_attendance_but_keeps_history(): void
    {
        $lead = Lead::query()->create([
            'name' => 'اللاعب المؤجل',
            'phone' => '0552223333',
            'lead_status_id' => $this->status17->id,
            'assigned_user_id' => $this->user->id,
        ]);
        foreach ([
            ['trial_date', 'date', '2026-09-24'],
            ['trial_time', 'time', '15:30'],
        ] as [$key, $type, $value]) {
            LeadStageFieldValue::query()->create([
                'lead_id' => $lead->id,
                'pipeline_stage_id' => $this->stage17->id,
                'field_key' => $key,
                'field_type' => $type,
                'value' => $value,
            ]);
        }

        app(AppointmentService::class)->markAttended($lead, $this->user, 'Original appointment attended');

        $response = $this->actingAs($this->user)->post(route('v2.appointments.reschedule', $lead), [
            'date' => '2026-09-30',
            'time' => '17:30',
            'notes' => 'طلب ولي الأمر التأجيل بسبب الامتحانات',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'trial_date',
            'value' => '2026-09-30',
        ]);
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'trial_time',
            'value' => '17:30',
        ]);
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'field_key' => 'trial_status',
            'value' => 'أعيدت الجدولة',
        ]);
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'attended',
            'value' => '',
        ]);
        $this->assertDatabaseHas('appointment_attendance_records', [
            'lead_id' => $lead->id,
            'outcome' => 'attended',
            'appointment_date' => '2026-09-24',
        ]);

        $meta = app(AppointmentService::class)->resolveDetails($lead->fresh(), AppointmentSetting::current());
        $this->assertFalse($meta['is_attended']);

        $appointments = $this->actingAs($this->user)->get(route('v2.appointments.index', ['tab' => 'all']));
        $appointments->assertOk()->assertSee(route('v2.appointments.no_show', $lead), false);
    }

    public function test_reschedule_without_time_does_not_reuse_old_appointment_time(): void
    {
        $lead = Lead::query()->create([
            'name' => 'اللاعب بدون وقت جديد',
            'lead_status_id' => $this->status17->id,
            'assigned_user_id' => $this->user->id,
        ]);
        LeadStageFieldValue::query()->create([
            'lead_id' => $lead->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_time',
            'field_type' => 'time',
            'value' => '15:30',
        ]);

        $response = $this->actingAs($this->user)->post(route('v2.appointments.reschedule', $lead), [
            'date' => '2026-09-30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $lead->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_time',
            'value' => '',
        ]);
        $meta = app(AppointmentService::class)->resolveDetails($lead->fresh(), AppointmentSetting::current());
        $this->assertSame('09:00', $meta['time']);
    }
}
