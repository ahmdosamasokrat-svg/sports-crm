<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadProfileSetting;
use App\Models\LeadStatus;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();
        LeadProfileSetting::flushCache();

        $branch = Branch::query()->firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch']
        );

        $this->admin = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $superAdminGroup = Group::query()->firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superAdminGroup->permissions()->sync(Permission::pluck('id'));
        $this->admin->groups()->attach($superAdminGroup);

        $this->regularUser = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $staffGroup = Group::query()->firstOrCreate(
            ['code' => 'staff'],
            ['name' => 'Staff', 'is_system' => false]
        );
        $staffPermissions = Permission::query()
            ->whereIn('code', ['leads.view'])
            ->pluck('id');
        $staffGroup->permissions()->sync($staffPermissions);
        $this->regularUser->groups()->attach($staffGroup);

        $category = PipelineStageCategory::query()->firstOrCreate(
            ['name_ar' => 'المبيعات'],
            ['name_en' => 'Sales', 'color' => '#dc2626', 'position' => 1, 'is_active' => true]
        );

        $stage = PipelineStage::query()->firstOrCreate(
            ['id' => 17],
            [
                'pipeline_stage_category_id' => $category->id,
                'code' => 'stage_trial_booked_test',
                'name' => 'Trial Booked',
                'name_ar' => 'تجربة محجوزة',
                'position' => 4,
            ]
        );

        $status = LeadStatus::query()->firstOrCreate(
            ['pipeline_stage_id' => $stage->id, 'name_ar' => 'تم الحجز'],
            ['code' => 'trial_booked', 'position' => 1, 'color' => '#8b5cf6']
        );

        $this->lead = Lead::query()->create([
            'name' => 'العميل التجريبي للتبويبات',
            'phone' => '0559998877',
            'branch_id' => $branch->id,
            'lead_status_id' => $status->id,
            'assigned_user_id' => $this->admin->id,
            'activity' => 'جمباز',
            'notes' => 'ملاحظات العميل التجريبية',
        ]);
    }

    protected function tearDown(): void
    {
        LeadProfileSetting::flushCache();
        parent::tearDown();
    }

    public function test_can_view_lead_profile_settings_screen_when_authorized(): void
    {
        $response = $this->actingAs($this->admin)->get(route('v2.settings.lead_profile.index'));

        $response->assertOk();
        $response->assertViewIs('settings.lead-profile.index');
        $response->assertSee(__('crm.lead_profile_settings'));
        $response->assertSee(__('crm.lead_profile_layout_mode'));
        $response->assertSee(__('crm.default_active_tab'));
        $response->assertSee(__('crm.lead_profile_tabs_config'));
    }

    public function test_unauthorized_users_cannot_access_lead_profile_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('v2.settings.lead_profile.index'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->regularUser)->post(route('v2.settings.lead_profile.update'), [
            'layout_mode' => 'full_width',
            'default_tab' => 'timeline',
            'tabs' => [],
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_can_update_lead_profile_settings_and_tabs_config(): void
    {
        $tabs = [
            [
                'key' => 'appointments',
                'position' => 1,
                'is_enabled' => 1,
                'label_ar' => 'الحجوزات والحضور المخصص',
                'label_en' => 'Custom Bookings',
            ],
            [
                'key' => 'timeline',
                'position' => 2,
                'is_enabled' => 1,
                'label_ar' => 'سجل النشاط',
                'label_en' => 'Activity Log',
            ],
            [
                'key' => 'notes',
                'position' => 3,
                'is_enabled' => 0, // Disabled
                'label_ar' => 'الملاحظات',
                'label_en' => 'Notes',
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('v2.settings.lead_profile.update'), [
            'layout_mode' => 'full_width',
            'default_tab' => 'appointments',
            'tabs' => $tabs,
        ]);

        $response->assertRedirect(route('v2.settings.lead_profile.index'));
        $response->assertSessionHas('success');

        $setting = LeadProfileSetting::current();
        $this->assertSame('full_width', $setting->layout_mode);
        $this->assertSame('appointments', $setting->default_tab);

        $activeTabs = $setting->getActiveTabs();
        $this->assertSame('appointments', $activeTabs[0]['key']);
        $this->assertSame('timeline', $activeTabs[1]['key']);

        $activeKeys = array_column($activeTabs, 'key');
        $this->assertNotContains('notes', $activeKeys);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings.lead_profile.updated',
            'module' => 'settings',
        ]);
    }

    public function test_lead_profile_renders_configured_tabs_and_hides_disabled(): void
    {
        // Configure: appointments first, notes disabled
        $setting = LeadProfileSetting::current();
        $setting->update([
            'layout_mode' => 'hybrid',
            'default_tab' => 'timeline',
            'tabs_config' => [
                [
                    'key' => 'timeline',
                    'position' => 1,
                    'is_enabled' => true,
                    'label_ar' => 'سجل النشاط',
                    'label_en' => 'Timeline',
                    'icon' => 'bi-clock-history',
                ],
                [
                    'key' => 'appointments',
                    'position' => 2,
                    'is_enabled' => true,
                    'label_ar' => 'المواعيد والحضور',
                    'label_en' => 'Appointments',
                    'icon' => 'bi-calendar2-check',
                ],
                [
                    'key' => 'notes',
                    'position' => 3,
                    'is_enabled' => false, // Disabled
                    'label_ar' => 'الملاحظات',
                    'label_en' => 'Notes',
                    'icon' => 'bi-chat-left-text',
                ],
            ],
        ]);
        LeadProfileSetting::flushCache();

        $response = $this->actingAs($this->admin)->get(route('v2.leads.show', $this->lead));

        $response->assertOk();
        $response->assertSee('tab-btn-timeline');
        $response->assertSee('tab-btn-appointments');
        $response->assertDontSee('tab-btn-notes');
        $response->assertSee('<aside class="profile-persistent-sidebar">', false);
    }

    public function test_full_width_mode_omits_persistent_sidebar(): void
    {
        $setting = LeadProfileSetting::current();
        $setting->update(['layout_mode' => 'full_width']);
        LeadProfileSetting::flushCache();

        $response = $this->actingAs($this->admin)->get(route('v2.leads.show', $this->lead));

        $response->assertOk();
        $response->assertDontSee('<aside class="profile-persistent-sidebar">', false);
        $response->assertSee('profile-fullwidth-grid');
    }
}
