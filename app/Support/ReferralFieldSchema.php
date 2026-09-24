<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\PipelineStageField;
use App\Models\ReferralField;
use App\Models\ReferralSetting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReferralFieldSchema
{
    public static function isEnabled(): bool
    {
        return ReferralSetting::current()->is_enabled;
    }

    public static function allowNotes(): bool
    {
        return ReferralSetting::current()->allow_notes;
    }

    private static ?Collection $memoizedActiveFields = null;

    public static function flushCache(): void
    {
        self::$memoizedActiveFields = null;
        ReferralSetting::flushCache();
    }

    /**
     * @return Collection<int, PipelineStageField>
     */
    public static function getActiveFields(): Collection
    {
        if (self::$memoizedActiveFields !== null) {
            return self::$memoizedActiveFields;
        }

        $referralFields = ReferralField::query()
            ->with(['stageField.stage'])
            ->active()
            ->ordered()
            ->get();

        $result = new Collection();
        foreach ($referralFields as $rf) {
            $sf = $rf->stageField;
            if ($sf && $sf->is_active) {
                $cloned = clone $sf;
                $cloned->is_required = $rf->is_required;
                $cloned->position = $rf->position;
                $result->push($cloned);
            }
        }

        self::$memoizedActiveFields = $result;

        return $result;
    }

    /**
     * Validate the referral request data.
     *
     * @throws ValidationException
     */
    public static function validate(Request $request): array
    {
        $settings = ReferralSetting::current();
        $fields = self::getActiveFields();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ];

        if ($settings->allow_notes) {
            $rules['notes'] = ['nullable', 'string', 'max:1000'];
        }

        // Support backward compatibility if activity is passed directly
        if ($request->has('activity')) {
            $rules['activity'] = ['nullable', 'string', 'max:100'];
        }

        $inputData = [
            'name' => trim((string) $request->input('name')),
            'phone' => trim((string) $request->input('phone')),
        ];

        if ($settings->allow_notes) {
            $inputData['notes'] = $request->input('notes') ? trim((string) $request->input('notes')) : null;
        }

        if ($request->has('activity')) {
            $inputData['activity'] = $request->input('activity') ? trim((string) $request->input('activity')) : null;
        }

        $dynamicInputs = [];
        $rawReferralFields = $request->input('referral_fields', []);
        if (! is_array($rawReferralFields)) {
            $rawReferralFields = [];
        }

        foreach ($fields as $field) {
            $key = $field->key;
            $rawVal = $rawReferralFields[$key] ?? $request->input($key);

            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            switch ($field->type) {
                case 'number':
                case 'currency':
                    $fieldRules[] = 'numeric';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:190';
                    break;
                case 'tel':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:50';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'boolean':
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'select':
                case 'radio':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
                case 'multiselect':
                    $fieldRules = [$field->is_required ? 'required' : 'nullable', 'array'];
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:1000';
                    break;
            }

            $rules["field_{$key}"] = $fieldRules;
            $inputData["field_{$key}"] = $rawVal;
            $dynamicInputs[$key] = $rawVal;
        }

        $validator = Validator::make($inputData, $rules, [
            'name.required' => 'اسم العميل أو اللاعب المُحال مطلوب.',
            'phone.required' => 'رقم هاتف العميل المُحال مطلوب.',
            '*.required' => 'هذا الحقل مطلوب.',
        ]);

        $customAttributes = [
            'name' => 'اسم العميل المُحال',
            'phone' => 'رقم الهاتف',
            'notes' => 'الملاحظات',
        ];
        foreach ($fields as $field) {
            $customAttributes["field_{$field->key}"] = $field->localizedLabel();
        }
        $validator->setAttributeNames($customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return [
            'name' => $inputData['name'],
            'phone' => $inputData['phone'],
            'notes' => $inputData['notes'] ?? null,
            'activity' => $inputData['activity'] ?? ($dynamicInputs['activity'] ?? ($dynamicInputs['requested_activity'] ?? null)),
            'dynamic_fields' => $dynamicInputs,
        ];
    }

    /**
     * Persist the new referral prospect lead and its dynamic values.
     */
    public static function persist(Lead $referrer, array $validated, User $actor): Lead
    {
        $settings = ReferralSetting::current();

        // 1. Resolve Status from Target Stage
        $initialStatusId = null;
        if ($settings->target_pipeline_stage_id) {
            $initialStatusId = LeadStatus::query()
                ->where('pipeline_stage_id', $settings->target_pipeline_stage_id)
                ->orderBy('position')
                ->orderBy('id')
                ->value('id');
        }
        if (! $initialStatusId) {
            $initialStatusId = LeadStatus::query()
                ->whereHas('stage', fn ($q) => $q->where('is_active', true))
                ->orderBy('position')
                ->orderBy('id')
                ->value('id');
        }

        // 2. Identify canonical fields vs custom stage fields
        $fields = self::getActiveFields()->keyBy('key');
        $dynamicInputs = $validated['dynamic_fields'] ?? [];

        $leadData = [
            'name' => trim($validated['name']),
            'phone' => trim($validated['phone']),
            'activity' => $validated['activity'] ?? $referrer->activity,
            'source' => 'referral',
            'branch_id' => $referrer->branch_id ?? $actor->branch_id,
            'lead_status_id' => $initialStatusId,
            'assigned_user_id' => $actor->id,
            'assigned_employee' => $actor->name,
            'created_by' => $actor->name,
            'created_by_user_id' => $actor->id,
            'referred_by_lead_id' => $referrer->id,
            'notes' => ! empty($validated['notes'])
                ? "إحالة من المشترك: {$referrer->name} (#ID: {$referrer->id})\n" . trim($validated['notes'])
                : "إحالة من المشترك: {$referrer->name} (#ID: {$referrer->id})",
        ];

        // Map canonical stage fields to leads table columns
        $customValuesToPersist = [];
        foreach ($dynamicInputs as $key => $val) {
            $field = $fields->get($key);
            if (! $field) {
                continue;
            }

            if ($field->binding_type === 'canonical' && ! empty($field->binding_target)) {
                $targetCol = $field->binding_target;
                if (! in_array($targetCol, ['id', 'created_at', 'updated_at', 'lead_status_id'], true)) {
                    $leadData[$targetCol] = $val;
                }
            } else {
                $customValuesToPersist[$field->id] = [
                    'field' => $field,
                    'value' => $val,
                ];
            }
        }

        /** @var Lead $newLead */
        $newLead = Lead::query()->create($leadData);

        // 3. Persist custom stage fields into lead_stage_field_values
        if (! empty($customValuesToPersist)) {
            $targetStageId = $newLead->status?->pipeline_stage_id;
            foreach ($customValuesToPersist as $fieldId => $item) {
                $val = $item['value'];
                if ($val === null || $val === '') {
                    continue;
                }

                $field = $item['field'];
                $stageId = $targetStageId ?? $field->pipeline_stage_id;

                LeadStageFieldValue::query()->create([
                    'lead_id' => $newLead->id,
                    'pipeline_stage_id' => $stageId,
                    'pipeline_stage_field_id' => $fieldId,
                    'field_key' => $field->key,
                    'value' => is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string) $val,
                    'actor_id' => $actor->id,
                ]);
            }
        }

        ActivityLogger::log(
            action: 'referral.created',
            module: 'leads',
            description: "تم تسجيل إحالة جديدة ({$newLead->name}) مرتبطة بالمشترك ({$referrer->name})",
            properties: [
                'referrer_lead_id' => $referrer->id,
                'referral_lead_id' => $newLead->id,
            ],
            actor: $actor,
        );

        return $newLead;
    }
}
