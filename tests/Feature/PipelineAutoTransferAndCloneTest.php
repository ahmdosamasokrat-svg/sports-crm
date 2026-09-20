<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use App\Security\CrmPermission;
use App\Services\LeadTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineAutoTransferAndCloneTest extends TestCase
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

    public function test_can_configure_auto_transfer_trigger_via_settings_controller(): void
    {
        $mainCat = PipelineStageCategory::query()->create([
            'name_ar' => 'مسار المبيعات الرئيسي',
            'position' => 1,
            'is_active' => true,
        ]);

        $subscribedStage = PipelineStage::query()->create([
            'code' => 'stage_subscribed_' . uniqid(),
            'name_ar' => 'مشترك (تم البيع)',
            'pipeline_stage_category_id' => $mainCat->id,
            'position' => 1,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $subscribedStatus = $subscribedStage->statuses()->first();

        $response = $this->actingAs($this->admin)->post(route('v2.settings.stage_categories.store'), [
            'name_ar' => 'مسار الاشتراكات',
            'name_en' => 'Subscriptions',
            'color' => '#10b981',
            'icon' => 'bi-repeat',
            'auto_transfer_enabled' => '1',
            'auto_transfer_action' => 'clone',
            'trigger_stage_id' => $subscribedStage->id,
            'trigger_status_id' => $subscribedStatus->id,
        ]);

        $response->assertRedirect(route('v2.settings.stage_categories.index'));

        $this->assertDatabaseHas('pipeline_stage_categories', [
            'name_ar' => 'مسار الاشتراكات',
            'auto_transfer_enabled' => true,
            'auto_transfer_action' => 'clone',
            'trigger_stage_id' => $subscribedStage->id,
            'trigger_status_id' => $subscribedStatus->id,
        ]);
    }

    public function test_lead_transition_to_trigger_stage_clones_lead_to_target_pipeline(): void
    {
        // 1. Sales pipeline with Initial & Subscribed stages
        $salesCat = PipelineStageCategory::query()->create([
            'name_ar' => 'مسار المبيعات',
            'position' => 1,
            'is_active' => true,
        ]);

        $initialStage = PipelineStage::query()->create([
            'code' => 'stage_init_' . uniqid(),
            'name_ar' => 'تواصل أولي',
            'pipeline_stage_category_id' => $salesCat->id,
            'position' => 1,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $initialStatus = $initialStage->statuses()->first();

        $subscribedStage = PipelineStage::query()->create([
            'code' => 'stage_sub_' . uniqid(),
            'name_ar' => 'مشترك جديد',
            'pipeline_stage_category_id' => $salesCat->id,
            'position' => 2,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $subscribedStatus = $subscribedStage->statuses()->first();

        // 2. Subscriptions pipeline with Onboarding & Active stages
        $subsCat = PipelineStageCategory::query()->create([
            'name_ar' => 'الاشتراكات',
            'position' => 2,
            'is_active' => true,
            'auto_transfer_enabled' => true,
            'auto_transfer_action' => 'clone',
            'trigger_stage_id' => $subscribedStage->id,
        ]);

        $onboardingStage = PipelineStage::query()->create([
            'code' => 'stage_onboard_' . uniqid(),
            'name_ar' => 'تهيئة المشترك',
            'pipeline_stage_category_id' => $subsCat->id,
            'position' => 1,
            'is_primary' => false,
            'is_active' => true,
        ]);
        $onboardingStatus = $onboardingStage->statuses()->first();

        $subsCat->update([
            'target_stage_id' => $onboardingStage->id,
            'target_status_id' => $onboardingStatus->id,
        ]);

        // 3. Create lead in initial status
        $lead = Lead::query()->create([
            'name' => 'Ahmed Mohamed',
            'phone' => '0501234567',
            'company_name' => 'Gym Pro',
            'source' => 'web',
            'lead_status_id' => $initialStatus->id,
        ]);

        // Store an answer in stage 1
        LeadStageFieldValue::query()->create([
            'lead_id' => $lead->id,
            'pipeline_stage_id' => $initialStage->id,
            'field_key' => 'gym_branch',
            'value' => 'Cairo Branch',
        ]);

        // 4. Transition lead to Subscribed stage
        /** @var LeadTransitionService $service */
        $service = app(LeadTransitionService::class);
        $result = $service->transition($lead, $subscribedStatus, $this->admin, [
            'outcome' => 'تم التعاقد والاشتراك بنجاح',
        ]);

        // Assert original lead remains in subscribed status
        $this->assertEquals($subscribedStatus->id, $result['lead']->lead_status_id);

        // Assert cloned lead was automatically created in Subscriptions pipeline onboarding stage
        $clonedLead = Lead::query()->where('parent_lead_id', $lead->id)->first();
        $this->assertNotNull($clonedLead);
        $this->assertEquals($onboardingStatus->id, $clonedLead->lead_status_id);
        $this->assertEquals('Ahmed Mohamed', $clonedLead->name);
        $this->assertEquals('Gym Pro', $clonedLead->company_name);

        // Assert historical answers were copied to cloned lead
        $this->assertDatabaseHas('lead_stage_field_values', [
            'lead_id' => $clonedLead->id,
            'field_key' => 'gym_branch',
            'value' => 'Cairo Branch',
        ]);

        // Assert status history was written for the clone
        $this->assertDatabaseHas('lead_status_histories', [
            'lead_id' => $clonedLead->id,
            'to_status_id' => $onboardingStatus->id,
        ]);

        // Assert parent lead details view links to cloned lead and vice-versa
        $responseParent = $this->actingAs($this->admin)->get(route('v2.leads.show', $lead));
        $responseParent->assertOk();
        $responseParent->assertSee('تم نسخه لمسار آخر:');
        $responseParent->assertSee('#' . $clonedLead->id);

        $responseClone = $this->actingAs($this->admin)->get(route('v2.leads.show', $clonedLead));
        $responseClone->assertOk();
        $responseClone->assertSee('متفرع من العميل الأصلي:');
        $responseClone->assertSee('#' . $lead->id);
    }
}
