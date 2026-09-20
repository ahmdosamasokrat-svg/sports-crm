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
use Tests\TestCase;

class ReferralEngineTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private PipelineStageCategory $category;
    private PipelineStage $stage;
    private LeadStatus $status;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_can_create_referral_linked_to_subscriber(): void
    {
        $subscriber = Lead::query()->create([
            'name' => 'ياسر رضوان',
            'phone' => '0501112233',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
            'activity' => 'جمباز',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('v2.leads.referrals.store', $subscriber), [
            'name' => 'سامح عزت (صديق ياسر)',
            'phone' => '0509988776',
            'activity' => 'جمباز',
            'notes' => 'يرغب في الانضمام لنفس المجموعة مع ياسر',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('referral.name', 'سامح عزت (صديق ياسر)');
        $response->assertJsonPath('referrer.total_referrals', 1);

        // Verify newly created prospect in DB
        $newLeadId = $response->json('referral.id');
        $referredLead = Lead::query()->findOrFail($newLeadId);
        $this->assertEquals('referral', $referredLead->source);
        $this->assertEquals($subscriber->id, $referredLead->referred_by_lead_id);
        $this->assertEquals($subscriber->id, $referredLead->referredBy->id);
        $this->assertTrue($subscriber->referrals->contains('id', $referredLead->id));
    }

    public function test_lead_profile_displays_referral_actions_and_network(): void
    {
        $subscriber = Lead::query()->create([
            'name' => 'أحمد طارق',
            'phone' => '0554433221',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
        ]);

        $referredFriend = Lead::query()->create([
            'name' => 'مصطفى كمال',
            'phone' => '0559900112',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
            'source' => 'referral',
            'referred_by_lead_id' => $subscriber->id,
        ]);

        // Viewing subscriber profile should show the referral button and list of referrals
        $response = $this->actingAs($this->user)->get(route('v2.leads.show', $subscriber));
        $response->assertOk();
        $response->assertSee('إحالة صديق');
        $response->assertSee('إحالات قام بها هذا المشترك (1)');
        $response->assertSee('مصطفى كمال');

        // Viewing referred friend profile should show who referred him
        $responseFriend = $this->actingAs($this->user)->get(route('v2.leads.show', $referredFriend));
        $responseFriend->assertOk();
        $responseFriend->assertSee('مُحال من المشترك:');
        $responseFriend->assertSee('أحمد طارق');
    }
}
