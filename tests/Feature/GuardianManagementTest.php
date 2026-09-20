<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Guardian;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LeadStatus $defaultStatus;

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

        $stage = PipelineStage::query()->create([
            'code' => 'stage_guardian_test_' . uniqid(),
            'name_ar' => 'مرحلة أولياء الأمور',
            'position' => 1,
            'is_primary' => true,
            'is_active' => true,
        ]);
        $this->defaultStatus = $stage->statuses()->first();
    }

    public function test_can_search_guardians_via_json_api(): void
    {
        Guardian::query()->create([
            'name' => 'محمد أحمد إبراهيم',
            'phone' => '0551122334',
            'relationship' => 'أب',
        ]);

        Guardian::query()->create([
            'name' => 'سارة علي حسن',
            'phone' => '0559988776',
            'relationship' => 'أم',
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.guardians.search', ['q' => 'محمد']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'guardians');
        $response->assertJsonPath('guardians.0.name', 'محمد أحمد إبراهيم');
    }

    public function test_can_create_guardian_and_link_inline_to_lead(): void
    {
        $lead = Lead::query()->create([
            'name' => 'يوسف محمد',
            'phone' => '0501112233',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
        ]);

        $this->assertNull($lead->guardian_id);

        $response = $this->actingAs($this->admin)->post(route('v2.guardians.store'), [
            'name' => 'محمد أحمد الوالد',
            'phone' => '0553334455',
            'relationship' => 'أب',
            'lead_id' => $lead->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('guardian.name', 'محمد أحمد الوالد');

        $this->assertDatabaseHas('guardians', [
            'name' => 'محمد أحمد الوالد',
            'phone' => '0553334455',
        ]);

        $this->assertNotNull($lead->fresh()->guardian_id);
        $this->assertEquals('محمد أحمد الوالد', $lead->fresh()->guardian->name);
    }

    public function test_linking_guardian_reveals_registered_siblings(): void
    {
        $guardian = Guardian::query()->create([
            'name' => 'خالد محمود',
            'phone' => '0558889999',
            'relationship' => 'أب',
        ]);

        // Child 1
        $child1 = Lead::query()->create([
            'name' => 'عمر خالد',
            'phone' => '0558889999',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
            'guardian_id' => $guardian->id,
        ]);

        // Child 2
        $child2 = Lead::query()->create([
            'name' => 'مريم خالد',
            'phone' => '0558889999',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
        ]);

        // Link child 2 to same guardian
        $response = $this->actingAs($this->admin)->post(route('v2.leads.guardian.link', $child2), [
            'guardian_id' => $guardian->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('siblings.0.name', 'عمر خالد');
        $this->assertEquals($guardian->id, $child2->fresh()->guardian_id);
    }

    public function test_can_unlink_guardian_from_lead(): void
    {
        $guardian = Guardian::query()->create([
            'name' => 'طارق كمال',
            'phone' => '0507776655',
        ]);

        $lead = Lead::query()->create([
            'name' => 'أدهم طارق',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
            'guardian_id' => $guardian->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('v2.leads.guardian.unlink', $lead));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertNull($lead->fresh()->guardian_id);
    }

    public function test_leads_index_filters_by_guardian_id(): void
    {
        $guardian = Guardian::query()->create([
            'name' => 'عبدالله سعيد',
            'phone' => '0501239876',
        ]);

        $child = Lead::query()->create([
            'name' => 'سعيد عبدالله',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
            'guardian_id' => $guardian->id,
        ]);

        $other = Lead::query()->create([
            'name' => 'ماجد حسام',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('v2.leads', ['guardian_id' => $guardian->id]));

        $response->assertOk();
        $response->assertSee('سعيد عبدالله');
        $response->assertDontSee('ماجد حسام');
    }
}
