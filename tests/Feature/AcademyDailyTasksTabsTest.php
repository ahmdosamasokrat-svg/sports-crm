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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AcademyDailyTasksTabsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Branch $branch;
    private PipelineStage $stage17;
    private PipelineStage $stage18;
    private LeadStatus $status17;
    private LeadStatus $status18;

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

        $category = PipelineStageCategory::firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'position' => 1]
        );

        $this->stage17 = PipelineStage::query()->find(17) ?? PipelineStage::query()->create([
            'id' => 17,
            'pipeline_stage_category_id' => $category->id,
            'code' => 'stage_17_trial_' . uniqid(),
            'name_ar' => 'تجربة محجوزة',
            'name_en' => 'Booked Trial',
            'position' => 5,
            'is_active' => true,
        ]);

        $this->stage18 = PipelineStage::query()->find(18) ?? PipelineStage::query()->create([
            'id' => 18,
            'pipeline_stage_category_id' => $category->id,
            'code' => 'stage_18_post_trial_' . uniqid(),
            'name_ar' => 'متابعة ما بعد التجربة',
            'name_en' => 'Post Trial Followup',
            'position' => 6,
            'is_active' => true,
        ]);

        $this->status17 = LeadStatus::query()->where('pipeline_stage_id', $this->stage17->id)->first()
            ?? LeadStatus::query()->create([
                'pipeline_stage_id' => $this->stage17->id,
                'code' => 'status_16_trial_' . uniqid(),
                'name_ar' => 'تجربة محجوزة',
                'name_en' => 'Booked Trial',
                'position' => 1,
            ]);

        $this->status18 = LeadStatus::query()->where('pipeline_stage_id', $this->stage18->id)->first()
            ?? LeadStatus::query()->create([
                'pipeline_stage_id' => $this->stage18->id,
                'code' => 'status_17_post_trial_' . uniqid(),
                'name_ar' => 'متابعة ما بعد التجربة',
                'name_en' => 'Post Trial Followup',
                'position' => 1,
            ]);
    }

    public function test_can_view_academy_filter_tabs_on_daily_tasks(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.tasks.daily'));

        $response->assertOk();
        $response->assertSee('تجارب اليوم');
        $response->assertSee('حضروا ولم يشتركوا');
        $response->assertSee('لم يحضروا التجربة');
    }

    public function test_today_trials_scope_filters_correctly(): void
    {
        $leadTodayTrial = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status17->id,
            'name' => 'لاعب تجربة اليوم',
            'next_follow_up_at' => now(),
        ]);

        $leadOtherStage = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status18->id,
            'name' => 'لاعب مرحلة أخرى',
            'next_follow_up_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.tasks.daily', ['scope' => 'today_trials']));

        $response->assertOk();
        $response->assertSee('لاعب تجربة اليوم');
        $response->assertDontSee('لاعب مرحلة أخرى');
    }

    public function test_attended_not_subscribed_scope_filters_correctly(): void
    {
        $leadAttended = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status18->id,
            'name' => 'لاعب حضر ولم يشترك',
        ]);

        // Insert stage field value attended = نعم
        DB::table('lead_stage_field_values')->insert([
            'lead_id' => $leadAttended->id,
            'pipeline_stage_id' => $this->stage18->id,
            'field_key' => 'attended',
            'field_type' => 'select',
            'value' => 'نعم',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leadSubscribed = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status18->id,
            'name' => 'لاعب اشترك بالفعل',
        ]);

        DB::table('lead_stage_field_values')->insert([
            [
                'lead_id' => $leadSubscribed->id,
                'pipeline_stage_id' => $this->stage18->id,
                'field_key' => 'attended',
                'field_type' => 'select',
                'value' => 'نعم',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'lead_id' => $leadSubscribed->id,
                'pipeline_stage_id' => $this->stage18->id,
                'field_key' => 'subscribed',
                'field_type' => 'select',
                'value' => 'نعم',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.tasks.daily', ['scope' => 'attended_not_subscribed']));

        $response->assertOk();
        $response->assertSee('لاعب حضر ولم يشترك');
        $response->assertDontSee('لاعب اشترك بالفعل');
    }

    public function test_trial_no_shows_scope_filters_correctly(): void
    {
        $leadNoShow = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status17->id,
            'name' => 'لاعب غائب عن التجربة',
        ]);

        DB::table('lead_stage_field_values')->insert([
            'lead_id' => $leadNoShow->id,
            'pipeline_stage_id' => $this->stage17->id,
            'field_key' => 'trial_status',
            'field_type' => 'select',
            'value' => 'لم يحضر',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.tasks.daily', ['scope' => 'trial_no_shows']));

        $response->assertOk();
        $response->assertSee('لاعب غائب عن التجربة');
    }
}
