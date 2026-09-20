<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatus;
use App\Models\LeadStatusHistory;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\StageFieldSchema;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadTransitionService
{
    /**
     * Perform a centralized Lead status transition inside an atomic database transaction.
     *
     * @param  Lead  $lead
     * @param  LeadStatus  $toStatus
     * @param  User  $actor
     * @param  array<string, mixed>  $context
     * @return array{
     *     lead: Lead,
     *     history: ?LeadStatusHistory,
     *     followup: ?LeadFollowup,
     *     stage_field_values: \Illuminate\Database\Eloquent\Collection,
     *     changed: bool
     * }
     *
     * @throws ValidationException
     */
    public function transition(
        Lead $lead,
        LeadStatus $toStatus,
        User $actor,
        array $context = []
    ): array {
        return DB::transaction(function () use ($lead, $toStatus, $actor, $context): array {
            /** @var Lead $lockedLead */
            $lockedLead = Lead::query()
                ->lockForUpdate()
                ->findOrFail($lead->getKey());

            if (! $lockedLead->isAccessibleTo($actor)) {
                throw ValidationException::withMessages([
                    'lead_id' => 'ليس لديك صلاحية للوصول إلى هذا العميل أو تعديل مرحلته.',
                ]);
            }

            $fromStatus = $lockedLead->status ?? LeadStatus::query()->find($lockedLead->lead_status_id);
            $fromStatusId = $fromStatus?->id ?? (int) $lockedLead->lead_status_id;
            $toStatusId = (int) $toStatus->id;
            $statusChanged = $fromStatusId !== $toStatusId;

            // 1. Destination stage resolution
            $toStage = $toStatus->stage ?? PipelineStage::query()->find($toStatus->pipeline_stage_id);

            if ($toStage === null || ! $actor->canAccessPipelineStage($toStage)) {
                throw ValidationException::withMessages([
                    'lead_status_id' => 'ليس لديك صلاحية نقل العميل إلى هذه المرحلة.',
                ]);
            }

            // 2. Validate stage field schema and extract normalized stage values
            $normalizedStageValues = [];
            // If this transition is a funnel handoff stage targeting another pipeline stage,
            // use the destination validation stage instead of toStage for validating stage fields
            $validationStage = $toStage;
            if (! empty($context['effective_stage_id'])) {
                $customEffectiveStage = PipelineStage::query()->find($context['effective_stage_id']);
                if ($customEffectiveStage !== null) {
                    $validationStage = $customEffectiveStage;
                }
            } elseif ($toStage !== null) {
                $autoTrigger = \App\Models\PipelineStageCategory::query()
                    ->where('is_active', true)
                    ->where('auto_transfer_enabled', true)
                    ->where('trigger_stage_id', $toStage->id)
                    ->where(function ($q) use ($toStatus): void {
                        $q->whereNull('trigger_status_id')
                            ->orWhere('trigger_status_id', $toStatus->id);
                    })
                    ->with(['targetStage', 'activeStages'])
                    ->first();

                if ($autoTrigger) {
                    $targetStg = $autoTrigger->targetStage ?? $autoTrigger->activeStages->first();
                    if ($targetStg) {
                        $validationStage = $targetStg;
                    }
                }
            }

            if ($validationStage !== null) {
                $rawStageInputs = isset($context['stage_fields']) && is_array($context['stage_fields'])
                    ? $context['stage_fields']
                    : [];

                // Backward compatibility mapping for canonical rules if field exists on stage
                $stageFields = StageFieldSchema::getFieldsForStage($validationStage, true);
                $stageFieldKeys = $stageFields->pluck('key')->all();

                if (in_array('callback_at', $stageFieldKeys, true) && ! isset($rawStageInputs['callback_at']) && ! empty($context['next_follow_up_at'])) {
                    $rawStageInputs['callback_at'] = $context['next_follow_up_at'] instanceof Carbon
                        ? $context['next_follow_up_at']->toDateTimeString()
                        : (string) $context['next_follow_up_at'];
                }

                if (in_array('reason', $stageFieldKeys, true) && ! isset($rawStageInputs['reason'])) {
                    $disReason = (string) ($context['disinterest_reason'] ?? $context['outcome'] ?? '');
                    if ($disReason !== '') {
                        $reasonField = $stageFields->firstWhere('key', 'reason');
                        $opts = $reasonField ? $reasonField->normalizedOptions() : [];
                        $optVals = array_column($opts, 'value');
                        $optLabelsAr = array_column($opts, 'label_ar');
                        $optLabelsEn = array_column($opts, 'label_en');
                        $allAllowed = array_merge($optVals, $optLabelsAr, $optLabelsEn);

                        if (in_array($disReason, $allAllowed, true) || empty($optVals)) {
                            $rawStageInputs['reason'] = $disReason;
                        } elseif (in_array('other', $optVals, true)) {
                            $rawStageInputs['reason'] = 'other';
                        } else {
                            $rawStageInputs['reason'] = $optVals[0] ?? $disReason;
                        }
                    }
                }

                try {
                    $normalizedStageValues = StageFieldSchema::validateAndExtract($validationStage, $rawStageInputs, $actor);
                } catch (ValidationException $e) {
                    $errors = $e->errors();
                    if (isset($errors['callback_at']) && ! isset($errors['next_follow_up_at'])) {
                        $errors['next_follow_up_at'] = $errors['callback_at'];
                    }
                    if (isset($errors['reason']) && ! isset($errors['disinterest_reason'])) {
                        $errors['disinterest_reason'] = $errors['reason'];
                    }
                    throw ValidationException::withMessages($errors);
                }
            }

            // 3. Business Rule Validation
            $this->validateTransitionRules($lockedLead, $fromStatus, $toStatus, $context);

            // 4. Resolve next_follow_up_at based on business rules and stage fields
            $nextFollowUpAt = $this->resolveNextFollowUpAt($toStatus, $context, $lockedLead, $normalizedStageValues);

            // 5. Prepare Lead attributes for update (including dynamic canonical stage fields)
            $splitValues = $toStage !== null ? StageFieldSchema::splitValues($toStage, $normalizedStageValues) : ['canonical' => [], 'custom' => []];
            $canonicalUpdates = $splitValues['canonical'];

            $leadUpdateData = [
                'lead_status_id' => $toStatusId,
                'next_follow_up_at' => $nextFollowUpAt,
            ];

            // Apply validated canonical stage fields (Type A)
            foreach ($canonicalUpdates as $cAttr => $cVal) {
                if (! in_array($cAttr, ['id', 'created_at', 'updated_at', 'lead_status_id'], true)) {
                    $leadUpdateData[$cAttr] = $cVal;
                }
            }

            if (array_key_exists('assigned_user_id', $context)) {
                $leadUpdateData['assigned_user_id'] = $context['assigned_user_id'];
            }
            if (array_key_exists('assigned_employee', $context)) {
                $leadUpdateData['assigned_employee'] = $context['assigned_employee'];
            }
            if (array_key_exists('disinterest_reason', $context)) {
                $leadUpdateData['disinterest_reason'] = $context['disinterest_reason'];
            } elseif (! empty($normalizedStageValues['reason'])) {
                $leadUpdateData['disinterest_reason'] = (string) $normalizedStageValues['reason'];
            }
            if (array_key_exists('notes', $context) && $context['notes'] !== null) {
                $leadUpdateData['notes'] = $context['notes'];
            }

            // Additional lead attributes if passed in context (e.g. direct lead edit)
            if (! empty($context['lead_attributes']) && is_array($context['lead_attributes'])) {
                foreach ($context['lead_attributes'] as $attrKey => $attrVal) {
                    if (! in_array($attrKey, ['id', 'created_at', 'updated_at'], true)) {
                        $leadUpdateData[$attrKey] = $attrVal;
                    }
                }
                // Ensure lead_status_id and next_follow_up_at resolved take precedence
                $leadUpdateData['lead_status_id'] = $toStatusId;
                $leadUpdateData['next_follow_up_at'] = $nextFollowUpAt;
            }

            // Track canonical field changes for audit and followups
            $canonicalFieldChanges = [];
            foreach ($canonicalUpdates as $cAttr => $cVal) {
                $oldVal = $lockedLead->getAttribute($cAttr);
                $oldStr = $oldVal === null ? '' : trim((string) $oldVal);
                $newStr = $cVal === null ? '' : trim((string) $cVal);
                if ($oldStr !== $newStr) {
                    $cfg = \App\Models\PipelineStageField::CANONICAL_FIELDS[$cAttr] ?? null;
                    $label = $cfg ? (app()->getLocale() === 'en' ? $cfg['label_en'] : $cfg['label_ar']) : $cAttr;
                    $canonicalFieldChanges[] = [
                        'field' => $cAttr,
                        'label' => $label,
                        'old' => $oldStr !== '' ? $oldStr : '—',
                        'new' => $newStr !== '' ? $newStr : '—',
                    ];
                }
            }

            $lockedLead->update($leadUpdateData);

            // Handle campaign association if provided
            if (isset($context['campaign']) && $context['campaign'] instanceof Campaign) {
                $lockedLead->campaigns()->sync([$context['campaign']->id]);
            } elseif (! empty($context['campaign_id'])) {
                $lockedLead->campaigns()->sync([(int) $context['campaign_id']]);
            }

            // 6. Create LeadFollowup record if follow-up context is provided
            $followupRecord = null;
            $hasFollowupContext = ! empty($context['record_followup'])
                || ! empty($context['communication_type'])
                || ! empty($context['outcome']);

            if ($hasFollowupContext) {
                $communicationType = (string) ($context['communication_type'] ?? 'other');
                $outcome = isset($context['outcome']) ? (string) $context['outcome'] : null;
                $employeeName = ! empty($context['employee_name'])
                    ? (string) $context['employee_name']
                    : (trim((string) $actor->name) ?: 'System');

                $followupData = [
                    'lead_id' => $lockedLead->id,
                    'from_status_id' => $fromStatusId,
                    'to_status_id' => $toStatusId,
                    'user_id' => $actor->id ?? null,
                    'employee_name' => $employeeName,
                    'communication_type' => $communicationType,
                    'outcome' => $outcome,
                    'next_follow_up_at' => $nextFollowUpAt,
                    'followed_up_at' => ! empty($context['followed_up_at'])
                        ? ($context['followed_up_at'] instanceof Carbon ? $context['followed_up_at'] : Carbon::parse($context['followed_up_at']))
                        : now(),
                ];

                $providedChanges = (isset($context['field_changes']) && is_array($context['field_changes']))
                    ? $context['field_changes']
                    : [];
                $mergedChanges = array_merge($providedChanges, $canonicalFieldChanges);
                if (! empty($mergedChanges)) {
                    $followupData['field_changes'] = $mergedChanges;
                }

                $followupRecord = LeadFollowup::query()->create($followupData);
            }

            // 7. Create LeadStatusHistory atomically if status changed or force_history requested
            $historyRecord = null;
            if ($statusChanged || ! empty($context['force_history'])) {
                $actorName = trim((string) $actor->name) ?: 'System';
                $defaultNote = $statusChanged
                    ? 'تغيير الحالة إلى ' . $toStatus->localizedName()
                    : 'تحديث حالة العميل';

                $historyNote = $context['history_note'] ?? $defaultNote;

                $historyRecord = LeadStatusHistory::query()->create([
                    'lead_id' => $lockedLead->id,
                    'from_status_id' => $fromStatusId,
                    'to_status_id' => $toStatusId,
                    'changed_by' => $actorName,
                    'changed_by_user_id' => $actor->id ?? null,
                    'note' => $historyNote,
                    'changed_at' => now(),
                ]);
            }

            // 8. Persist LeadStageFieldValue records atomically within the same transaction
            $savedStageValues = collect();
            $isFunnelForward = ! empty($context['effective_stage_id']) && (int) $context['effective_stage_id'] !== (int) $toStage?->id;
            if ($toStage !== null && ! empty($normalizedStageValues) && ! $isFunnelForward) {
                $savedStageValues = StageFieldSchema::persistValues(
                    $lockedLead,
                    $toStage,
                    $normalizedStageValues,
                    $historyRecord,
                    $actor
                );
            }

            if ($statusChanged) {
                $fromLabel = (app()->getLocale() === 'en' && !empty($fromStatus?->name_en)) ? $fromStatus->name_en : ($fromStatus?->name_ar ?? '—');
                $toLabel = (app()->getLocale() === 'en' && !empty($toStatus->name_en)) ? $toStatus->name_en : $toStatus->name_ar;
                $desc = app()->getLocale() === 'en'
                    ? "Changed stage/status of lead {$lockedLead->name} from [{$fromLabel}] to [{$toLabel}]"
                    : "قام بتغيير مرحلة/حالة العميل {$lockedLead->name} من [{$fromLabel}] إلى [{$toLabel}]";

                ActivityLogger::log(
                    action: 'lead.stage_transition',
                    module: 'leads',
                    description: $desc,
                    subject: $lockedLead,
                    properties: [
                        'from_status_id' => $fromStatusId,
                        'from_status' => $fromLabel,
                        'to_status_id' => $toStatusId,
                        'to_status' => $toLabel,
                        'to_stage' => $toStage?->localizedName(),
                        'context' => $context,
                    ],
                    actor: $actor,
                );
            }

            // 9. Automated Pipeline Transfer / Clone Triggers
            $clonedLead = null;
            if ($statusChanged && empty($context['suppress_pipeline_triggers'])) {
                $clonedLead = $this->handlePipelineTriggers(
                    $lockedLead,
                    $toStage,
                    $toStatus,
                    $actor,
                    $context
                );
            }
            return [
                'lead' => $lockedLead->fresh(['status.stage', 'assignedUser', 'stageValues']),
                'history' => $historyRecord,
                'followup' => $followupRecord,
                'stage_field_values' => $savedStageValues,
                'changed' => $statusChanged,
            ];
        });
    }
    /**
     * Check and execute automated pipeline transfer or clone triggers.
     */
    protected function handlePipelineTriggers(
        Lead $lead,
        ?PipelineStage $currentStage,
        LeadStatus $currentStatus,
        User $actor,
        array $context
    ): ?Lead {
        if ($currentStage === null) {
            return null;
        }
        $triggerRules = \App\Models\PipelineStageCategory::query()
            ->where('is_active', true)
            ->where('auto_transfer_enabled', true)
            ->where('trigger_stage_id', $currentStage->id)
            ->where(function ($q) use ($currentStatus): void {
                $q->whereNull('trigger_status_id')
                    ->orWhere('trigger_status_id', 0)
                    ->orWhere('trigger_status_id', $currentStatus->id);
            })
            ->with(['targetStage.statuses', 'targetStatus', 'activeStages.statuses', 'stages.statuses'])
            ->get();
        foreach ($triggerRules as $rule) {
            // Resolve target stage and status inside the destination pipeline
            $targetStage = $rule->targetStage;
            if ($targetStage === null) {
                // Default to the first active stage in this category
                $targetStage = $rule->activeStages()->first() ?? $rule->stages()->first();
            }
            if ($targetStage === null) {
                continue;
            }

            $targetStatus = $rule->targetStatus;
            if ($targetStatus === null) {
                $targetStatus = $targetStage->statuses()->orderBy('position')->first();
            }
            if ($targetStatus === null) {
                continue;
            }

            if ($rule->auto_transfer_action === 'move') {
                // Move the same lead directly into the new pipeline
                $this->transition($lead, $targetStatus, $actor, [
                    'history_note' => "نقل تلقائي إلى مسار [{$rule->localizedName()}] عند الوصول إلى مرحلة [{$currentStage->localizedName()}]",
                    'suppress_pipeline_triggers' => true,
                ]);
                return null;
            }

            // CLONE / FORK MODE:
            // Create cloned lead linked via parent_lead_id
            $cloneData = $lead->only([
                'name',
                'company_name',
                'phone',
                'email',
                'source',
                'assigned_employee',
                'assigned_user_id',
                'branch_id',
                'created_by',
                'created_by_user_id',
                'temperature',
                'notes',
            ]);
            $cloneData['lead_status_id'] = $targetStatus->id;
            $cloneData['parent_lead_id'] = $lead->id;
            $cloneData['notes'] = ($cloneData['notes'] ? $cloneData['notes'] . "\n\n" : '')
                . "تم استنساخ هذا العميل تلقائيًا في مسار [{$rule->localizedName()}] عند وصول العميل الأصلي إلى مرحلة [{$currentStage->localizedName()}].";

            $newClone = Lead::query()->create($cloneData);

            // Copy active campaigns
            if ($lead->campaigns->isNotEmpty()) {
                $newClone->campaigns()->sync($lead->campaigns->pluck('id')->all());
            }

            // Duplicate existing stage field answers so cloned lead carries historical context
            $existingValues = \App\Models\LeadStageFieldValue::query()
                ->where('lead_id', $lead->id)
                ->get();

            foreach ($existingValues as $val) {
                \App\Models\LeadStageFieldValue::query()->create([
                    'lead_id' => $newClone->id,
                    'pipeline_stage_id' => $val->pipeline_stage_id,
                    'pipeline_stage_field_id' => $val->pipeline_stage_field_id,
                    'field_key' => $val->field_key,
                    'value' => $val->value,
                    'created_by_user_id' => $actor->id,
                ]);
            }

            // If context contains stage_fields meant for the destination stage, persist them directly on the clone
            $submittedStageFields = isset($context['stage_fields']) && is_array($context['stage_fields'])
                ? $context['stage_fields']
                : [];

            if (! empty($submittedStageFields)) {
                // Check if targetStage has fields matching these submitted keys
                $targetFields = \App\Support\StageFieldSchema::getFieldsForStage($targetStage, false)->pluck('key')->all();
                $targetMatchedFields = array_filter(
                    $submittedStageFields,
                    static fn ($k): bool => in_array($k, $targetFields, true),
                    ARRAY_FILTER_USE_KEY
                );

                if (! empty($targetMatchedFields)) {
                    \App\Support\StageFieldSchema::persistValues(
                        $newClone,
                        $targetStage,
                        $targetMatchedFields,
                        null,
                        $actor
                    );
                }
            }
            // Create initial status history for cloned record
            \App\Models\LeadStatusHistory::query()->create([
                'lead_id' => $newClone->id,
                'from_status_id' => null,
                'to_status_id' => $targetStatus->id,
                'changed_by' => trim((string) $actor->name) ?: 'System Automation',
                'changed_by_user_id' => $actor->id ?? null,
                'note' => "إنشاء عميل مستنسخ في مسار [{$rule->localizedName()}] متفرع من العميل #{$lead->id} عند وصوله لمرحلة [{$currentStage->localizedName()}]",
                'changed_at' => now(),
            ]);

            // Activity log for both parent and clone
            ActivityLogger::log(
                action: 'lead.stage_transition',
                module: 'leads',
                description: "تم ترحيل واستنساخ العميل {$lead->name} إلى مسار [{$rule->localizedName()}] برقم جديد #{$newClone->id}",
                subject: $newClone,
                properties: [
                    'parent_lead_id' => $lead->id,
                    'cloned_lead_id' => $newClone->id,
                    'pipeline_category_id' => $rule->id,
                    'pipeline_category_name' => $rule->localizedName(),
                    'trigger_stage' => $currentStage->localizedName(),
                    'target_stage' => $targetStage->localizedName(),
                ],
                actor: $actor,
            );

            return $newClone;
        }

        return null;
    }

    /**
     * Centralized transition validation rules.
     *
     * @throws ValidationException
     */
    protected function validateTransitionRules(
        Lead $lead,
        ?LeadStatus $fromStatus,
        LeadStatus $toStatus,
        array $context
    ): void {
        // Enforce not_interested reason requirement if legacy field is checked
        if ($toStatus->code === 'not_interested') {
            $reason = trim((string) ($context['disinterest_reason'] ?? $context['stage_fields']['reason'] ?? ''));
            if ($reason === '') {
                // If there's a reason question configured on stage, StageFieldSchema handles it.
                // If not, enforce legacy disinterest_reason rule
                $stage = $toStatus->stage;
                $hasReasonField = $stage && StageFieldSchema::getFieldsForStage($stage, true)->where('key', 'reason')->isNotEmpty();
                if (! $hasReasonField) {
                    throw ValidationException::withMessages([
                        'disinterest_reason' => [__('crm.reason_required') ?: 'يجب تسجيل السبب عند اختيار غير مهتم.'],
                    ]);
                }
            }
        }
    }

    /**
     * Resolve the appropriate next_follow_up_at datetime.
     */
    protected function resolveNextFollowUpAt(
        LeadStatus $toStatus,
        array $context,
        Lead $lead,
        array $normalizedStageValues = []
    ): ?Carbon {
        // 1. If explicit callback_at provided in stage fields, use it
        if (! empty($normalizedStageValues['callback_at'])) {
            try {
                return Carbon::parse($normalizedStageValues['callback_at']);
            } catch (\Throwable) {
                // continue to fallback
            }
        }

        // 2. If explicit next_follow_up_at provided in context
        if (array_key_exists('next_follow_up_at', $context)) {
            $raw = $context['next_follow_up_at'];
            if ($raw === null || $raw === '') {
                return null;
            }
            if ($raw instanceof Carbon) {
                return $raw;
            }
            try {
                return Carbon::parse((string) $raw);
            } catch (\Throwable) {
                return null;
            }
        }

        // 3. Status-specific semantic rules
        if ($toStatus->code === 'not_interested') {
            return null;
        }

        // 4. Default: preserve existing next_follow_up_at
        return $lead->next_follow_up_at ? Carbon::parse($lead->next_follow_up_at) : null;
    }
}
