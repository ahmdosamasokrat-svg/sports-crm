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

class DynamicStageQuestionDailyTasksFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Branch $branch;
    private PipelineStage $stage;
    private LeadStatus $status;

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

        $this->stage = PipelineStage::query()->create([
            'pipeline_stage_category_id' => $category->id,
            'code' => 'stage_custom_' . uniqid(),
            'name_ar' => 'مرحلة التأهيل الرياضي',
            'name_en' => 'Sports Qualification',
            'position' => 1,
            'is_active' => true,
        ]);

        $this->status = LeadStatus::query()->create([
            'pipeline_stage_id' => $this->stage->id,
            'code' => 'status_custom_' . uniqid(),
            'name_ar' => 'قيد التأهيل',
            'name_en' => 'In Qualification',
            'position' => 1,
        ]);
    }

    public function test_can_configure_question_as_daily_tasks_filter_via_gui_controller(): void
    {
        $response = $this->actingAs($this->admin)->post(route('v2.settings.stages.fields.store', $this->stage), [
            'label_ar' => 'المستوى الرياضي للاعب',
            'type' => 'select',
            'binding_type' => 'custom',
            'show_in_daily_tasks' => '1',
            'daily_tasks_filter_values' => 'متقدم, محترف',
            'options_raw' => "مبتدئ\nمتوسط\nمتقدم\nمحترف",
        ]);

        $response->assertRedirect(route('v2.settings.stages.fields.index', $this->stage));

        $field = PipelineStageField::query()
            ->where('pipeline_stage_id', $this->stage->id)
            ->where('label_ar', 'المستوى الرياضي للاعب')
            ->firstOrFail();

        $this->assertTrue($field->show_in_daily_tasks);
        $this->assertEquals(['متقدم', 'محترف'], $field->daily_tasks_filter_values);
    }

    public function test_configured_question_filter_appears_and_filters_leads_in_daily_tasks(): void
    {
        // 1. Create a question flagged for daily tasks
        $field = PipelineStageField::query()->create([
            'pipeline_stage_id' => $this->stage->id,
            'key' => 'player_level',
            'label_ar' => 'المستوى الرياضي للاعب',
            'type' => 'select',
            'binding_type' => 'custom',
            'show_in_daily_tasks' => true,
            'daily_tasks_filter_values' => ['متقدم', 'محترف'],
            'is_active' => true,
            'position' => 1,
        ]);

        // 2. Create matching lead
        $matchingLead = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status->id,
            'name' => 'لاعب متقدم محترف',
        ]);

        DB::table('lead_stage_field_values')->insert([
            'lead_id' => $matchingLead->id,
            'pipeline_stage_id' => $this->stage->id,
            'pipeline_stage_field_id' => $field->id,
            'field_key' => 'player_level',
            'field_type' => 'select',
            'value' => 'متقدم',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create non-matching lead (different value)
        $nonMatchingLead = Lead::query()->create([
            'branch_id' => $this->branch->id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $this->status->id,
            'name' => 'لاعب مبتدئ غير مطابق',
        ]);

        DB::table('lead_stage_field_values')->insert([
            'lead_id' => $nonMatchingLead->id,
            'pipeline_stage_id' => $this->stage->id,
            'pipeline_stage_field_id' => $field->id,
            'field_key' => 'player_level',
            'field_type' => 'select',
            'value' => 'مبتدئ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. View daily tasks
        $response = $this->actingAs($this->admin)->get(route('v2.tasks.daily'));
        $response->assertOk();
        $response->assertSee('المستوى الرياضي للاعب');
        $response->assertSee('متقدم/محترف');

        // 5. Filter by this custom question scope
        $scopeResponse = $this->actingAs($this->admin)->get(route('v2.tasks.daily', ['scope' => 'q_' . $field->id]));
        $scopeResponse->assertOk();
        $scopeResponse->assertSee('لاعب متقدم محترف');
        $scopeResponse->assertDontSee('لاعب مبتدئ غير مطابق');
    }
}
