<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FollowupCustomerField;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\User;
use App\Security\CrmPermission;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAutomaticAgeCalculationTest extends TestCase
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
            'code' => 'stage_test_' . uniqid(),
            'name_ar' => 'مرحلة تجريبية',
            'position' => 1,
            'is_primary' => true,
            'is_active' => true,
        ]);
        $this->defaultStatus = $stage->statuses()->first();
    }

    public function test_lead_model_calculates_age_automatically_from_birth_date(): void
    {
        $tenYearsAgo = Carbon::now()->subYears(10)->subMonths(2)->format('Y-m-d');

        $lead = Lead::query()->create([
            'name' => 'Young Athlete',
            'phone' => '0558877665',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
            'birth_date' => $tenYearsAgo,
        ]);

        $this->assertEquals(10, $lead->age);
        $this->assertEquals($tenYearsAgo, $lead->birth_date->format('Y-m-d'));
    }

    public function test_lead_profile_displays_birth_date_and_auto_calculated_age(): void
    {
        $fifteenYearsAgo = Carbon::now()->subYears(15)->subDays(10)->format('Y-m-d');

        $lead = Lead::query()->create([
            'name' => 'Football Player Lead',
            'phone' => '0554433221',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
            'birth_date' => $fifteenYearsAgo,
        ]);

        $response = $this->actingAs($this->admin)->withSession(['locale' => 'ar'])->get(route('v2.leads.show', $lead));

        $response->assertOk();
        $response->assertSee('تاريخ الميلاد');
        $response->assertSee('العمر (محسوب تلقائيًا)');
        $response->assertSee('15');
        $response->assertSee($fifteenYearsAgo);
    }

    public function test_followup_and_lead_update_persists_birth_date_canonically(): void
    {
        $birthDate = '2016-05-20';
        $expectedAge = Carbon::parse($birthDate)->age;

        FollowupCustomerField::query()->firstOrCreate(
            ['key' => 'birth_date'],
            [
                'lead_attribute' => 'birth_date',
                'label_ar' => 'تاريخ الميلاد',
                'type' => 'date',
                'is_active' => true,
                'is_system' => true,
                'position' => 2,
            ]
        );

        $lead = Lead::query()->create([
            'name' => 'Gymnast Child',
            'phone' => '0509988112',
            'source' => 'web',
            'lead_status_id' => $this->defaultStatus->id,
        ]);

        $this->assertNull($lead->birth_date);
        $this->assertNull($lead->age);

        // Update via follow-up customer fields
        $response = $this->actingAs($this->admin)->post(route('v2.leads.followups.store', $lead), [
            'lead_status_id' => $this->defaultStatus->id,
            'communication_type' => 'call',
            'outcome' => 'تم تسجيل تاريخ ميلاد اللاعب',
            'next_follow_up_at' => now()->addDays(3)->format('Y-m-d H:i'),
            'customer_field_presence' => ['birth_date'],
            'customer_fields' => [
                'birth_date' => $birthDate,
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $freshLead = $lead->fresh();
        $this->assertNotNull($freshLead->birth_date);
        $this->assertEquals($birthDate, $freshLead->birth_date->format('Y-m-d'));
        $this->assertEquals($expectedAge, $freshLead->age);
    }
}
