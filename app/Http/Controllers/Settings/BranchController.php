<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        CrmDatabaseGuard::ensureConnected();

        $branches = Branch::query()
            ->withCount(['users', 'leads'])
            ->orderBy('id')
            ->get();

        $editBranch = $request->filled('edit')
            ? $branches->firstWhere('id', $request->integer('edit'))
            : null;

        return view('settings.branches.index', [
            'branches' => $branches,
            'editBranch' => $editBranch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('branches', 'code')->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Branch::query()->create([
            'name_ar' => trim($validated['name_ar']),
            'name_en' => ! empty($validated['name_en']) ? trim($validated['name_en']) : null,
            'code' => strtolower(trim($validated['code'])),
            'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
            'address' => ! empty($validated['address']) ? trim($validated['address']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('v2.settings.branches.index')
            ->with('status', __('crm.branch_created_successfully') ?: 'تمت إضافة الفرع بنجاح');
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('branches', 'code')->ignore($branch->id)->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update([
            'name_ar' => trim($validated['name_ar']),
            'name_en' => ! empty($validated['name_en']) ? trim($validated['name_en']) : null,
            'code' => strtolower(trim($validated['code'])),
            'phone' => ! empty($validated['phone']) ? trim($validated['phone']) : null,
            'address' => ! empty($validated['address']) ? trim($validated['address']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? $branch->is_active),
        ]);

        return redirect()
            ->route('v2.settings.branches.index')
            ->with('status', __('crm.branch_updated_successfully') ?: 'تم تحديث بيانات الفرع بنجاح');
    }

    public function toggle(Branch $branch): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $branch->update([
            'is_active' => ! $branch->is_active,
        ]);

        $message = $branch->is_active
            ? (__('crm.branch_activated_successfully') ?: 'تم تفعيل الفرع بنجاح')
            : (__('crm.branch_deactivated_successfully') ?: 'تم تعطيل الفرع بنجاح');

        return redirect()
            ->route('v2.settings.branches.index')
            ->with('status', $message);
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        if ($branch->code === 'main') {
            return redirect()
                ->route('v2.settings.branches.index')
                ->with('error', __('crm.cannot_delete_main_branch') ?: 'لا يمكن حذف الفرع الرئيسي الافتراضي');
        }

        if ($branch->users()->exists() || $branch->leads()->exists()) {
            return redirect()
                ->route('v2.settings.branches.index')
                ->with('error', __('crm.cannot_delete_branch_with_relations') ?: 'لا يمكن حذف الفرع لوجود مستخدمين أو عملاء مرتبطين به');
        }

        $branch->delete();

        return redirect()
            ->route('v2.settings.branches.index')
            ->with('status', __('crm.branch_deleted_successfully') ?: 'تم حذف الفرع بنجاح');
    }
}
