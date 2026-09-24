<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSetting;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\PipelineStageField;
use App\Services\ActivityLogger;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentSettingController extends Controller
{
    public function index(): View
    {
        CrmDatabaseGuard::ensureConnected();

        $setting = AppointmentSetting::current();
        $categories = PipelineStageCategory::query()
            ->with(['stages.activeFields'])
            ->orderBy('position')
            ->get();

        $allStageFields = PipelineStageField::query()
            ->with('stage')
            ->where('is_active', true)
            ->get();

        return view('settings.appointments.index', [
            'setting' => $setting,
            'categories' => $categories,
            'allStageFields' => $allStageFields,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'stage_ids' => ['nullable', 'array'],
            'stage_ids.*' => ['integer', 'exists:pipeline_stages,id'],
            'date_field_key' => ['required', 'string', 'max:100'],
            'time_field_key' => ['nullable', 'string', 'max:100'],
            'coach_field_key' => ['nullable', 'string', 'max:100'],
            'status_field_key' => ['nullable', 'string', 'max:100'],
            'allow_quick_actions' => ['nullable', 'boolean'],
        ]);

        $setting = AppointmentSetting::current();
        $setting->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'stage_ids' => $validated['stage_ids'] ?? [],
            'date_field_key' => $validated['date_field_key'],
            'time_field_key' => $validated['time_field_key'] ?? null,
            'coach_field_key' => $validated['coach_field_key'] ?? null,
            'status_field_key' => $validated['status_field_key'] ?? null,
            'allow_quick_actions' => $request->boolean('allow_quick_actions', true),
        ]);

        AppointmentSetting::flushCache();

        ActivityLogger::log(
            action: 'settings.appointments.updated',
            module: 'settings',
            description: app()->getLocale() === 'en'
                ? 'Updated appointment and booking settings'
                : 'تم تحديث إعدادات ومراحل نظام المواعيد والتجارب',
            properties: [
                'is_enabled' => $setting->is_enabled,
                'stage_ids' => $setting->stage_ids,
                'date_field_key' => $setting->date_field_key,
            ],
            actor: $request->user(),
        );

        return to_route('v2.settings.appointments.index')
            ->with('success', __('crm.appointment_settings_saved') ?? 'تم حفظ إعدادات نظام المواعيد والتجارب بنجاح.');
    }
}
