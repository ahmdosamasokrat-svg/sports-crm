<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index(Request $request): View
    {
        CrmDatabaseGuard::ensureConnected();

        $sources = LeadSource::query()->ordered()->get();
        $editSource = $request->filled('edit')
            ? $sources->firstWhere('id', $request->integer('edit'))
            : null;

        return view('settings.lead-sources.index', [
            'sources' => $sources,
            'editSource' => $editSource,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $position = (int) (LeadSource::query()->max('position') ?? 0) + 1;

        LeadSource::query()->create([
            'name_ar' => trim($validated['name_ar']),
            'name_en' => ! empty($validated['name_en']) ? trim($validated['name_en']) : null,
            'icon' => ! empty($validated['icon']) ? trim($validated['icon']) : 'bi-funnel',
            'color' => ! empty($validated['color']) ? trim($validated['color']) : '#3b82f6',
            'is_active' => true,
            'position' => $position,
        ]);

        return redirect()
            ->route('v2.settings.lead-sources.index')
            ->with('status', 'تمت إضافة مصدر العميل بنجاح');
    }

    public function update(Request $request, LeadSource $source): RedirectResponse
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $source->update([
            'name_ar' => trim($validated['name_ar']),
            'name_en' => ! empty($validated['name_en']) ? trim($validated['name_en']) : null,
            'icon' => ! empty($validated['icon']) ? trim($validated['icon']) : 'bi-funnel',
            'color' => ! empty($validated['color']) ? trim($validated['color']) : '#3b82f6',
        ]);

        return redirect()
            ->route('v2.settings.lead-sources.index')
            ->with('status', 'تم تحديث مصدر العميل بنجاح');
    }

    public function toggle(LeadSource $source): RedirectResponse
    {
        $source->update(['is_active' => ! $source->is_active]);

        return redirect()
            ->route('v2.settings.lead-sources.index')
            ->with('status', $source->is_active ? 'تم تفعيل المصدر' : 'تم تعطيل المصدر');
    }

    public function destroy(LeadSource $source): RedirectResponse
    {
        $source->delete();

        return redirect()
            ->route('v2.settings.lead-sources.index')
            ->with('status', 'تم حذف مصدر العميل بنجاح');
    }

    public function move(Request $request, LeadSource $source): RedirectResponse
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';
        $sources = LeadSource::query()->ordered()->get();
        $index = $sources->search(fn (LeadSource $item): bool => $item->is($source));

        if ($index !== false) {
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if (isset($sources[$targetIndex])) {
                $target = $sources[$targetIndex];
                $posA = $source->position;
                $posB = $target->position;

                $source->update(['position' => $posB]);
                $target->update(['position' => $posA]);
            }
        }

        return redirect()->route('v2.settings.lead-sources.index');
    }
}
