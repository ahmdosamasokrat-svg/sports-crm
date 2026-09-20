<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use App\Security\CrmPermission;
use Tests\TestCase;

class CallAndContactAttemptLoggingTest extends TestCase
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
        $superAdminGroup = \App\Models\Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(\App\Models\Permission::pluck('id'));
        $this->user->groups()->attach($superAdminGroup);

        $this->category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات والتواصل'],
            ['name_en' => 'Sales', 'color' => '#dc2626', 'position' => 1, 'is_active' => true]
        );

        $this->stage = PipelineStage::query()->firstOrCreate(
            ['code' => 'contact_attempts_test'],
            [
                'pipeline_stage_category_id' => $this->category->id,
                'name_ar' => 'محاولة التواصل',
                'name_en' => 'Contact Attempt',
                'position' => 1,
                'is_active' => true,
            ]
        );

        $this->status = LeadStatus::query()->firstOrCreate(
            ['code' => 'contact_attempts_test_status'],
            [
                'pipeline_stage_id' => $this->stage->id,
                'name_ar' => 'محاولة أولى',
                'name_en' => 'First Attempt',
                'position' => 1,
            ]
        );
    }

    public function test_logging_call_increments_attempts_count_and_saves_call_status(): void
    {
        $lead = Lead::query()->create([
            'name' => 'حمزة أحمد',
            'phone' => '0551122334',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
            'call_attempts_count' => 0,
        ]);

        // First call: No Answer
        $response1 = $this->actingAs($this->user)->post(route('v2.leads.followups.store', $lead), [
            'lead_status_id' => $this->status->id,
            'communication_type' => 'call',
            'call_status' => 'no_answer',
            'outcome' => 'تم الاتصال ولم يرد العميل',
            'next_follow_up_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response1->assertRedirect();
        $lead->refresh();

        $this->assertEquals(1, $lead->call_attempts_count);
        $this->assertEquals('no_answer', $lead->last_call_status);
        $this->assertNotNull($lead->first_contacted_at);
        $this->assertNotNull($lead->last_contacted_at);

        $followup1 = LeadFollowup::query()->where('lead_id', $lead->id)->latest('id')->first();
        $this->assertNotNull($followup1);
        $this->assertEquals(1, $followup1->call_attempt_number);
        $this->assertEquals('no_answer', $followup1->call_status);

        // Second call: Connected with WhatsApp Details Sent
        $response2 = $this->actingAs($this->user)->post(route('v2.leads.followups.store', $lead), [
            'lead_status_id' => $this->status->id,
            'communication_type' => 'call',
            'call_status' => 'connected',
            'outcome_category' => 'whatsapp_details_sent',
            'outcome' => 'تم التواصل مع ولي الأمر وطلب إرسال الباقات على واتساب',
            'next_follow_up_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        ]);

        $response2->assertRedirect();
        $lead->refresh();

        $this->assertEquals(2, $lead->call_attempts_count);
        $this->assertEquals('connected', $lead->last_call_status);
        $this->assertEquals('whatsapp_details_sent', $lead->last_outcome_category);

        $followup2 = LeadFollowup::query()->where('lead_id', $lead->id)->latest('id')->first();
        $this->assertNotNull($followup2);
        $this->assertEquals(2, $followup2->call_attempt_number);
        $this->assertEquals('connected', $followup2->call_status);
        $this->assertEquals('whatsapp_details_sent', $followup2->outcome_category);
    }

    public function test_lead_profile_displays_call_attempts_and_timeline_badges(): void
    {
        $lead = Lead::query()->create([
            'name' => 'كريم مصطفى',
            'phone' => '0559988776',
            'branch_id' => $this->branch->id,
            'lead_status_id' => $this->status->id,
            'call_attempts_count' => 3,
            'last_call_status' => 'busy',
            'last_contacted_at' => now(),
        ]);

        LeadFollowup::query()->create([
            'lead_id' => $lead->id,
            'to_status_id' => $this->status->id,
            'user_id' => $this->user->id,
            'employee_name' => $this->user->name,
            'communication_type' => 'call',
            'call_attempt_number' => 3,
            'call_status' => 'busy',
            'outcome_category' => 'followup_later',
            'outcome' => 'الهاتف مشغول',
            'followed_up_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('v2.leads.show', $lead));
        $response->assertOk();
        $response->assertSee('محاولات الاتصال');
        $response->assertSee('3 محاولة');
        $response->assertSee('المحاولة #3');
        $response->assertSee('مشغول');
        $response->assertSee('طلب متابعة لاحقًا');
    }
}
