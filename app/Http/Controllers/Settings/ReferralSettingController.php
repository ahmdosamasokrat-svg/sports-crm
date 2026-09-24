<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\PipelineStageCategory;
use App\Models\PipelineStageField;
use App\Models\ReferralField;
use App\Models\ReferralSetting;
use App\Services\ActivityLogger;
use App\Support\CrmDatabaseGuard;
use App\Support\ReferralFieldSchema;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralSettingController extends Controller
{
    public function index(Request $request): View
    {
        CrmDatabaseGuard::ensureConnected();

        $settings = ReferralSetting::current();
        $settings->loadMissing('targetStage.category');
        $configuredFields = ReferralField::query()
            ->with('stageField')
            ->ordered()
            ->get()
            ->keyBy('pipeline_stage_field_id');

        $categories = PipelineStageCategory::query()
            ->with(['stages.activeFields'])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $stages = PipelineStage::query()
            ->with(['statuses' => fn ($q) => $q->orderBy('position')->orderBy('id')])
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $allStageFields = PipelineStageField::query()
            ->with('stage')
            ->where('is_active', true)
            ->get();

        $categoriesWithStages = $categories->mapWithKeys(function ($cat) {
            return [
                (string) $cat->id => $cat->stages->where('is_active', true)->map(fn ($s) => [
                    'id' => (int) $s->id,
                    'name' => $s->localizedName(),
                    'fields_count' => $s->activeFields->count(),
                ])->values()->all(),
            ];
        })->all();

        return view('settings.referrals.index', [
            'settings' => $settings,
            'configuredFields' => $configuredFields,
            'categories' => $categories,
            'stages' => $stages,
            'categoriesWithStages' => $categoriesWithStages,
            'allStageFields' => $allStageFields,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'target_pipeline_stage_id' => ['nullable', 'integer', 'exists:pipeline_stages,id'],
            'allow_notes' => ['nullable', 'boolean'],
            'selected_fields' => ['nullable', 'array'],
            'selected_fields.*' => ['integer', 'exists:pipeline_stage_fields,id'],
            'required_fields' => ['nullable', 'array'],
            'required_fields.*' => ['integer'],
            'field_positions' => ['nullable', 'array'],
            'field_positions.*' => ['integer', 'min:0'],
            'trigger_stage_field_ids' => ['nullable', 'array'],
            'trigger_stage_field_ids.*' => ['integer', 'exists:pipeline_stage_fields,id'],
        ]);

        $isEnabled = $request->boolean('is_enabled');
        $allowNotes = $request->boolean('allow_notes');
        $targetStageId = ! empty($validated['target_pipeline_stage_id']) ? (int) $validated['target_pipeline_stage_id'] : null;
        $triggerFieldIds = $validated['trigger_stage_field_ids'] ?? [];

        $selectedFieldIds = array_map('intval', $validated['selected_fields'] ?? []);
        $requiredFieldIds = array_map('intval', $validated['required_fields'] ?? []);
        $fieldPositions = $validated['field_positions'] ?? [];

        DB::transaction(function () use (
            $isEnabled,
            $allowNotes,
            $targetStageId,
            $triggerFieldIds,
            $selectedFieldIds,
            $requiredFieldIds,
            $fieldPositions,
            $request
        ): void {
            $setting = ReferralSetting::query()->first() ?? new ReferralSetting();
            $setting->fill([
                'is_enabled' => $isEnabled,
                'allow_notes' => $allowNotes,
                'target_pipeline_stage_id' => $targetStageId,
                'trigger_stage_field_ids' => ! empty($triggerFieldIds) ? array_values($triggerFieldIds) : null,
            ]);
            $setting->save();

            // Deactivate or delete unselected fields
            ReferralField::query()
                ->whereNotIn('pipeline_stage_field_id', $selectedFieldIds)
                ->delete();

            // Insert or update selected fields
            $pos = 1;
            foreach ($selectedFieldIds as $fieldId) {
                $isRequired = in_array($fieldId, $requiredFieldIds, true);
                $position = isset($fieldPositions[$fieldId]) ? (int) $fieldPositions[$fieldId] : $pos++;

                ReferralField::query()->updateOrCreate(
                    ['pipeline_stage_field_id' => $fieldId],
                    [
                        'is_required' => $isRequired,
                        'position' => $position,
                        'is_active' => true,
                    ]
                );
            }

            ReferralFieldSchema::flushCache();

            ActivityLogger::log(
                action: 'settings.referrals.updated',
                module: 'settings',
                description: 'تم تحديث إعدادات وحقول الإحالات الديناميكية',
                properties: [
                    'is_enabled' => $isEnabled,
                    'selected_fields_count' => count($selectedFieldIds),
                ],
                actor: $request->user(),
            );
        });

        return redirect()
            ->route('v2.settings.referrals.index')
            ->with('success', __('crm.referral_settings_saved') ?: 'تم حفظ إعدادات الإحالات بنجاح.');
    }
}
