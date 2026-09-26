<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\AppointmentSetting;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnforcedPermissionsVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $readOnlyUser;
    private User $stageAdminWithoutDelete;
    private User $stageAdminWithDelete;
    private PipelineStage $stageA;
    private PipelineStage $stageB;
    private Lead $leadA;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch', 'is_active' => true]
        );

        $this->stageA = PipelineStage::query()->create([
            'code' => 'stage_test_a',
            'name_ar' => 'مرحلة اختبار 1',
            'name_en' => 'Stage 1',
            'position' => 1,
            'color' => '#3b82f6',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->stageB = PipelineStage::query()->create([
            'code' => 'stage_test_b',
            'name_ar' => 'مرحلة اختبار 2',
            'name_en' => 'Stage 2',
            'position' => 2,
            'color' => '#10b981',
            'is_active' => true,
            'is_default' => false,
        ]);

        $statusA = LeadStatus::query()->create([
            'pipeline_stage_id' => $this->stageA->id,
            'code' => 'st_test_a',
            'name_ar' => 'حالة 1',
            'name_en' => 'Status 1',
            'position' => 1,
        ]);

        $this->leadA = Lead::query()->create([
            'name' => 'Appointment Test Lead',
            'phone' => '0501112233',
            'branch_id' => $branch->id,
            'lead_status_id' => $statusA->id,
            'source' => 'web',
            'created_by' => 'System',
        ]);

        AppointmentSetting::query()->updateOrCreate(
            ['id' => 1],
            ['is_enabled' => true, 'stage_ids' => [$this->stageA->id]]
        );
        AppointmentSetting::flushCache();

        // 1. Read-only user (has leads.view, but NOT leads.update)
        $this->readOnlyUser = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $readOnlyGroup = Group::query()->create(['name' => 'Read Only Lead Viewers', 'code' => 'read-only-leads']);
        $readOnlyGroup->permissions()->syncWithoutDetaching(
            \App\Models\Permission::query()->whereIn('code', [
                CrmPermission::LEADS_VIEW->value,
                CrmPermission::LEADS_SCOPE_ALL->value,
            ])->pluck('id')->all()
        );
        $this->readOnlyUser->groups()->attach($readOnlyGroup->id);

        // 2. Settings user WITHOUT pipeline_stages.delete
        $this->stageAdminWithoutDelete = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $stageGroupNoDelete = Group::query()->create(['name' => 'Settings Access No Delete', 'code' => 'settings-no-delete']);
        $stageGroupNoDelete->permissions()->syncWithoutDetaching(
            \App\Models\Permission::query()->whereIn('code', [
                CrmPermission::SETTINGS_ACCESS->value,
            ])->pluck('id')->all()
        );
        $this->stageAdminWithoutDelete->groups()->attach($stageGroupNoDelete->id);

        // 3. Settings user WITH pipeline_stages.delete
        $this->stageAdminWithDelete = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $stageGroupWithDelete = Group::query()->create(['name' => 'Settings Access With Delete', 'code' => 'settings-with-delete']);
        $stageGroupWithDelete->permissions()->syncWithoutDetaching(
            \App\Models\Permission::query()->whereIn('code', [
                CrmPermission::SETTINGS_ACCESS->value,
                CrmPermission::PIPELINE_STAGES_DELETE->value,
            ])->pluck('id')->all()
        );
        $this->stageAdminWithDelete->groups()->attach($stageGroupWithDelete->id);
    }

    protected function tearDown(): void
    {
        AppointmentSetting::flushCache();
        parent::tearDown();
    }

    public function test_user_without_leads_update_cannot_mutate_appointments(): void
    {
        // 1. Mark attended forbidden
        $this->actingAs($this->readOnlyUser)
            ->post(route('v2.appointments.attended', $this->leadA))
            ->assertForbidden();

        // 2. Mark no-show forbidden
        $this->actingAs($this->readOnlyUser)
            ->post(route('v2.appointments.no_show', $this->leadA))
            ->assertForbidden();

        // 3. Reschedule forbidden
        $this->actingAs($this->readOnlyUser)
            ->post(route('v2.appointments.reschedule', $this->leadA), [
                'date' => now()->addDays(2)->format('Y-m-d'),
            ])
            ->assertForbidden();

        // 4. Cancel forbidden
        $this->actingAs($this->readOnlyUser)
            ->post(route('v2.appointments.cancel', $this->leadA))
            ->assertForbidden();
    }

    public function test_user_without_pipeline_stages_delete_cannot_delete_stages(): void
    {
        // Deleting stage without pipeline_stages.delete permission is forbidden
        $this->actingAs($this->stageAdminWithoutDelete)
            ->delete(route('v2.settings.stages.destroy', $this->stageB))
            ->assertForbidden();

        $this->assertDatabaseHas('pipeline_stages', [
            'id' => $this->stageB->id,
            'deleted_at' => null,
        ]);

        // Deleting with permission succeeds
        $this->actingAs($this->stageAdminWithDelete)
            ->delete(route('v2.settings.stages.destroy', $this->stageB))
            ->assertRedirect(route('v2.settings.stages.index'));

        $this->assertSoftDeleted('pipeline_stages', [
            'id' => $this->stageB->id,
        ]);
    }
}
