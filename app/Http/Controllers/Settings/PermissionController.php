<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePermissionMatrixRequest;
use App\Models\Group;
use App\Models\Permission;
use App\Security\CrmPermission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index(): View
    {
        foreach (CrmPermission::cases() as $permCase) {
            Permission::query()->firstOrCreate(
                ['code' => $permCase->value],
                [
                    'module' => $permCase->module(),
                    'name_ar' => $permCase->label(),
                ],
            );
        }

        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('code')
            ->get();

        $sidebarOrder = [
            'dashboard' => 1,
            'leads' => 2,
            'tasks' => 3,
            'campaigns' => 4,
            'quotations' => 5,
            'reports' => 6,
            'calendar' => 7,
            'voip' => 8,
            'technical_support' => 9,
            'settings' => 10,
            'users' => 11,
            'groups' => 12,
            'notifications' => 13,
        ];

        $permissionsByModule = $permissions
            ->groupBy('module')
            ->sortBy(
                static fn ($items, string $module): int => $sidebarOrder[$module] ?? 99,
            );

        return view('settings.permissions.index', [
            'groups' => Group::query()
                ->with('permissions:id,code')
                ->orderByDesc('is_system')
                ->orderBy('name')
                ->get(),
            'permissionsByModule' => $permissionsByModule,
            'moduleLabels' => CrmPermission::moduleLabels(),
        ]);
    }

    public function update(
        UpdatePermissionMatrixRequest $request,
    ): RedirectResponse {
        $submitted = $request->validated('permissions', []);
        $permissionIds = Permission::query()->pluck('id', 'code');

        DB::transaction(function () use (
            $submitted,
            $permissionIds,
        ): void {
            Group::query()
                ->where('code', '<>', Group::SUPER_ADMIN_CODE)
                ->each(function (Group $group) use (
                    $submitted,
                    $permissionIds,
                ): void {
                    $codes = $submitted[(string) $group->id]
                        ?? $submitted[$group->id]
                        ?? [];

                    $ids = collect($codes)
                        ->unique()
                        ->map(
                            static fn (string $code): int => (int) $permissionIds->get($code),
                        )
                        ->filter()
                        ->values()
                        ->all();

                    $group->permissions()->sync($ids);
                });
        });

        \App\Services\ActivityLogger::log(
            action: 'settings.permissions_updated',
            module: 'settings',
            description: app()->getLocale() === 'en'
                ? 'Updated system permission matrix'
                : 'قام بتعديل وتحديث مصفوفة الصلاحيات للنظام',
            actor: $request->user(),
        );

        return back()->with('success', 'تم حفظ مصفوفة الصلاحيات.');
    }
}
