<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CalendarEvent;
use App\Models\Campaign;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\PipelineStage;
use App\Models\TechnicalSupportDevice;
use App\Models\TechnicalSupportTicket;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EndpointsHealthScanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Permission::query()->count() < count(CrmPermission::cases())) {
            $perms = array_map(static fn ($p) => [
                'code' => $p->value,
                'module' => $p->module(),
                'name_ar' => $p->label(),
            ], CrmPermission::cases());
            Permission::query()->upsert($perms, ['code'], ['module', 'name_ar']);
        }

        $superGroup = Group::firstOrCreate(
            ['code' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        $superGroup->permissions()->sync(Permission::pluck('id'));

        $this->seed(\Database\Seeders\CrmV2PipelineSeeder::class);

        $branch = Branch::firstOrCreate(
            ['code' => 'main'],
            ['name_ar' => 'الفرع الرئيسي', 'name_en' => 'Main Branch', 'is_active' => true]
        );

        $this->admin = User::factory()->create([
            'is_active' => true,
            'branch_id' => $branch->id,
            'voip_extension' => '101',
        ]);
        $this->admin->groups()->sync([$superGroup->id]);
    }

    public function test_all_registered_get_web_endpoints_do_not_produce_500_errors(): void
    {
        // 1. Seed or resolve test fixtures for route parameter substitution
        $stage = PipelineStage::query()->first();
        $status = \App\Models\LeadStatus::query()->first();
        $lead = Lead::query()->create([
            'branch_id' => $this->admin->branch_id,
            'assigned_user_id' => $this->admin->id,
            'lead_status_id' => $status?->id,
            'name' => 'Test Lead',
            'phone' => '0123456789',
            'source' => 'web',
            'created_by' => 'Test',
        ]);
        $campaign = Campaign::firstOrCreate(
            ['name' => 'Test Campaign'],
            ['starts_at' => now(), 'ends_at' => now()->addDays(7), 'created_by_user_id' => $this->admin->id]
        );
        $ticket = TechnicalSupportTicket::query()->first();
        $device = TechnicalSupportDevice::query()->first();
        $event = CalendarEvent::query()->first();

        $routes = Route::getRoutes();
        $serverErrors = [];
        $testedRoutesCount = 0;

        foreach ($routes as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = $route->uri();

            // Skip ignition/debugbar routes
            if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum')) {
                continue;
            }

            // Substitute known parameter placeholders with valid IDs
            $testUri = $uri;
            if (str_contains($testUri, '{lead}')) $testUri = str_replace('{lead}', (string) ($lead?->id ?? 1), $testUri);
            if (str_contains($testUri, '{user}')) $testUri = str_replace('{user}', (string) $this->admin->id, $testUri);
            if (str_contains($testUri, '{campaign}')) $testUri = str_replace('{campaign}', (string) ($campaign?->id ?? 1), $testUri);
            if (str_contains($testUri, '{stage}')) $testUri = str_replace('{stage}', (string) ($stage?->id ?? 1), $testUri);
            if (str_contains($testUri, '{ticket}')) $testUri = str_replace('{ticket}', (string) ($ticket?->id ?? 1), $testUri);
            if (str_contains($testUri, '{card}')) $testUri = str_replace('{card}', (string) ($device?->id ?? 1), $testUri);
            if (str_contains($testUri, '{event}')) $testUri = str_replace('{event}', (string) ($event?->id ?? 1), $testUri);
            if (str_contains($testUri, '{scope}')) $testUri = str_replace('{scope}', 'today', $testUri);
            if (str_contains($testUri, '{locale}')) $testUri = str_replace('{locale}', 'ar', $testUri);
            if (str_contains($testUri, '{group}')) $testUri = str_replace('{group}', '1', $testUri);
            if (str_contains($testUri, '{branch}')) $testUri = str_replace('{branch}', (string) $this->admin->branch_id, $testUri);
            if (str_contains($testUri, '{source}')) $testUri = str_replace('{source}', '1', $testUri);
            if (str_contains($testUri, '{field}')) $testUri = str_replace('{field}', '1', $testUri);

            // Skip if any unresolved curly parameter remains
            if (preg_match('/\{[a-zA-Z0-9_]+\}/', $testUri)) {
                continue;
            }

            $testedRoutesCount++;

            try {
                $response = $this->actingAs($this->admin)->get('/' . ltrim($testUri, '/'));
                $status = $response->getStatusCode();

                // Status 500 represents unhandled server errors / crashes (502 from mock/external upstream PBX is handled)
                if ($status === 500) {
                    $serverErrors[] = [
                        'uri' => $uri,
                        'test_uri' => $testUri,
                        'status' => $status,
                    ];
                }
            } catch (\Throwable $e) {
                $serverErrors[] = [
                    'uri' => $uri,
                    'test_uri' => $testUri,
                    'status' => 500,
                    'exception' => $e->getMessage(),
                ];
            }
        }

        $this->assertGreaterThan(30, $testedRoutesCount, 'Expected at least 30 GET routes to be scanned.');
        $this->assertEmpty($serverErrors, 'Found HTTP 500 errors on the following routes: ' . json_encode($serverErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
