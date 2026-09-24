<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\PipelineStageField;
use App\Models\ReferralField;
use App\Models\ReferralSetting;
use App\Models\User;
use App\Support\ReferralFieldSchema;
use Tests\TestCase;

class ReferralSettingsTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private PipelineStageCategory $category;
    private PipelineStage $stage;
    private LeadStatus $status;
    private PipelineStageField $activityField;
    private PipelineStageField $customQuestionField;

    protected function setUp(): void
    {
        parent::setUp();

        ReferralFieldSchema::flushCache();

        $this->branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch']
        );

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(Permission::pluck('id'));
        $this->user->groups()->attach($superAdminGroup);

        $this->category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'color' => '#dc2626', 'position' => 1, 'is_active' => true]
        );

        $this->stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'referral_test_stage'],
            [
                'pipeline_stage_category_id' => $this->category->id,
                'name_ar' => 'عميل جديد',
                'name_en' => 'New Lead',
                'position' => 1,
                'is_active' => true,
            ]
        );

        $this->status = LeadStatus::query()->firstOrCreate(
            ['code' => 'referral_test_status'],
            [
                'pipeline_stage_id' => $this->stage->id,
                'name_ar' => 'جديد',
                'name_en' => 'New',
                'position' => 1,
            ]
        );

        // Canonical stage field: activity
        $this->activityField = PipelineStageField::query()->firstOrCreate(
            [
                'pipeline_stage_id' => $this->stage->id,
                'key' => 'activity',
            ],
            [
                'label_ar' => 'النشاط التجاري / الرياضي',
                'label_en' => 'Activity',
                'type' => 'select',
                'binding_type' => 'canonical',
                'binding_target' => 'activity',
                'options' => json_encode([
                    ['value' => 'جمباز', 'label_ar' => 'جمباز', 'label_en' => 'Gymnastics'],
                    ['value' => 'سباحة', 'label_ar' => 'سباحة', 'label_en' => 'Swimming'],
                ]),
                'is_required' => false,
                'is_active' => true,
                'position' => 1,
            ]
        );

        // Custom stage question field: referral notes / survey
        $this->customQuestionField = PipelineStageField::query()->firstOrCreate(
            [
                'pipeline_stage_id' => $this->stage->id,
                'key' => 'referral_how_met',
            ],
            [
                'label_ar' => 'كيف تعرف المشترك بالصديق؟',
                'label_en' => 'How did they meet?',
                'type' => 'text',
                'binding_type' => 'custom',
                'binding_target' => null,
                'is_required' => false,
                'is_active' => true,
                'position' => 2,
            ]
        );
    }

    protected function tearDown(): void
    {
        ReferralSetting::query()->updateOrCreate(
            ['id' => 1],
            ['is_enabled' => true, 'allow_notes' => true, 'target_pipeline_stage_id' => null]
        );
        ReferralField::query()->delete();
        ReferralFieldSchema::flushCache();

        parent::tearDown();
    }

    public function test_can_view_referral_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('v2.settings.referrals.index'));
        $response->assertOk();
        $response->assertSee(__('crm.referrals_settings'));
        $response->assertSee(__('crm.referral_system_status'));
    }

    public function test_can_turn_referrals_off_and_blocks_creation(): void
    {
        $subscriber = Lead::query()->create([
            'name' => 'طارق كمال',
            'phone' => '0501119988',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
        ]);

        // Toggle referrals OFF
        $response = $this->actingAs($this->user)->post(route('v2.settings.referrals.update'), [
            'is_enabled' => 0,
            'allow_notes' => 1,
        ]);
        $response->assertRedirect(route('v2.settings.referrals.index'));

        ReferralFieldSchema::flushCache();
        $this->assertFalse(ReferralSetting::current()->is_enabled);

        // Visiting lead profile should hide the referral button
        $showResponse = $this->actingAs($this->user)->get(route('v2.leads.show', $subscriber));
        $showResponse->assertOk();
        $showResponse->assertDontSee('onclick="openReferralModal()"', false);

        // Submitting referral when disabled returns 403 Forbidden
        $storeResponse = $this->actingAs($this->user)->postJson(route('v2.leads.referrals.store', $subscriber), [
            'name' => 'ماجد سامي',
            'phone' => '0502223344',
        ]);
        $storeResponse->assertStatus(403);
        $storeResponse->assertJsonPath('success', false);
    }

    public function test_can_configure_dynamic_fields_based_on_stage_questions(): void
    {
        $subscriber = Lead::query()->create([
            'name' => 'هاني فوزي',
            'phone' => '0507778899',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
        ]);

        // Configure activity (canonical) and customQuestionField as referral fields,
        // making customQuestionField required!
        $updateResponse = $this->actingAs($this->user)->post(route('v2.settings.referrals.update'), [
            'is_enabled' => 1,
            'allow_notes' => 1,
            'selected_fields' => [$this->activityField->id, $this->customQuestionField->id],
            'required_fields' => [$this->customQuestionField->id],
            'field_positions' => [
                $this->activityField->id => 1,
                $this->customQuestionField->id => 2,
            ],
        ]);
        $updateResponse->assertRedirect(route('v2.settings.referrals.index'));

        ReferralFieldSchema::flushCache();
        $this->assertTrue(ReferralSetting::current()->is_enabled);
        $activeFields = ReferralFieldSchema::getActiveFields();
        $this->assertCount(2, $activeFields);

        // 1. Validation fails if required dynamic stage question is missing
        $failResponse = $this->actingAs($this->user)->postJson(route('v2.leads.referrals.store', $subscriber), [
            'name' => 'خالد يوسف',
            'phone' => '0509988112',
            'referral_fields' => [
                'activity' => 'سباحة',
                // referral_how_met is missing!
            ],
        ]);
        $failResponse->assertStatus(422);

        // 2. Succeeds when required dynamic stage question is provided
        $successResponse = $this->actingAs($this->user)->postJson(route('v2.leads.referrals.store', $subscriber), [
            'name' => 'خالد يوسف',
            'phone' => '0509988112',
            'referral_fields' => [
                'activity' => 'سباحة',
                'referral_how_met' => 'زملاء في المدرسة',
            ],
        ]);
        $successResponse->assertOk();
        $successResponse->assertJsonPath('success', true);

        // Verify newly created prospect lead has canonical activity and custom value in lead_stage_field_values
        $newLeadId = $successResponse->json('referral.id');
        $newLead = Lead::query()->findOrFail($newLeadId);
        $this->assertEquals('سباحة', $newLead->activity);
        $this->assertEquals($subscriber->id, $newLead->referred_by_lead_id);

        $customVal = LeadStageFieldValue::query()
            ->where('lead_id', $newLead->id)
            ->where('field_key', 'referral_how_met')
            ->value('value');
        $this->assertEquals('زملاء في المدرسة', $customVal);
    }

    public function test_can_set_target_pipeline_stage_and_referral_lands_in_it(): void
    {
        $subscriber = Lead::query()->create([
            'name' => 'باسم عادل',
            'phone' => '0503332211',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
        ]);

        // Create a custom target stage inside category
        $uid = uniqid();
        $targetStage = PipelineStage::query()->create([
            'pipeline_stage_category_id' => $this->category->id,
            'code' => 'stage_vip_' . $uid,
            'name_ar' => 'إحالة مميزة VIP',
            'position' => 20,
            'is_active' => true,
        ]);
        $targetStatus = LeadStatus::query()->create([
            'pipeline_stage_id' => $targetStage->id,
            'code' => 'status_vip_' . $uid,
            'name_ar' => 'جديد VIP',
            'position' => 1,
        ]);

        // Save target pipeline stage in settings
        $response = $this->actingAs($this->user)->post(route('v2.settings.referrals.update'), [
            'is_enabled' => 1,
            'allow_notes' => 1,
            'target_pipeline_stage_id' => $targetStage->id,
        ]);
        $response->assertRedirect(route('v2.settings.referrals.index'));

        ReferralFieldSchema::flushCache();
        $this->assertEquals($targetStage->id, ReferralSetting::current()->target_pipeline_stage_id);

        // Create referral prospect
        $storeResponse = $this->actingAs($this->user)->postJson(route('v2.leads.referrals.store', $subscriber), [
            'name' => 'سمير وجدي',
            'phone' => '0508889900',
        ]);
        $storeResponse->assertOk();

        $newLeadId = $storeResponse->json('referral.id');
        $newLead = Lead::query()->findOrFail($newLeadId);
        $this->assertEquals($targetStatus->id, $newLead->lead_status_id);
        $this->assertEquals($targetStage->id, $newLead->status->pipeline_stage_id);
        $this->assertEquals('إحالة مميزة VIP', $storeResponse->json('referral.stage'));
    }
}
