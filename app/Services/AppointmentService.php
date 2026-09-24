<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppointmentAttendanceRecord;
use App\Models\AppointmentSetting;
use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Resolve appointment details for a lead.
     *
     * @return array{
     *     date: ?string,
     *     time: ?string,
     *     datetime: ?CarbonInterface,
     *     coach: ?string,
     *     status: ?string,
     *     notes: ?string,
     *     is_today: bool,
     *     is_upcoming: bool,
     *     is_no_show: bool,
     *     is_attended: bool
     * }
     */
    public function resolveDetails(Lead $lead, ?AppointmentSetting $setting = null): array
    {
        $setting ??= AppointmentSetting::current();
        $dateKey = $setting->date_field_key ?: 'trial_date';
        $timeKey = $setting->time_field_key ?: 'trial_time';
        $coachKey = $setting->coach_field_key ?: 'coach';
        $statusKey = $setting->status_field_key ?: 'trial_status';

        // Load stage values keyed by field_key
        $stageValues = LeadStageFieldValue::query()
            ->where('lead_id', $lead->id)
            ->whereIn('field_key', [$dateKey, $timeKey, $coachKey, $statusKey, 'attended', 'completed_trial', 'trial_notes'])
            ->pluck('value', 'field_key')
            ->all();

        // 1. Resolve Date
        $rawDate = $stageValues[$dateKey] ?? null;
        $appointmentDate = null;
        if (! empty($rawDate)) {
            try {
                $appointmentDate = Carbon::parse((string) $rawDate)->format('Y-m-d');
            } catch (\Throwable) {
                $appointmentDate = null;
            }
        }

        // Fallback to next_follow_up_at if no stage date
        if (! $appointmentDate && $lead->next_follow_up_at) {
            $appointmentDate = $lead->next_follow_up_at->format('Y-m-d');
        }

        // 2. Resolve Time
        $rawTime = $stageValues[$timeKey] ?? null;
        $appointmentTime = ! empty($rawTime) ? (string) $rawTime : ($lead->next_follow_up_at ? $lead->next_follow_up_at->format('H:i') : null);

        // 3. Resolve Datetime
        $datetime = null;
        if ($appointmentDate) {
            try {
                $timeString = $appointmentTime ?: '09:00';
                $datetime = Carbon::parse($appointmentDate.' '.$timeString);
            } catch (\Throwable) {
                $datetime = null;
            }
        }

        // 4. Resolve Coach, Status, Notes
        $coach = ! empty($stageValues[$coachKey]) ? (string) $stageValues[$coachKey] : null;
        $status = ! empty($stageValues[$statusKey]) ? (string) $stageValues[$statusKey] : null;
        $attendedVal = strtolower(trim((string) ($stageValues['attended'] ?? '')));
        $notes = ! empty($stageValues['trial_notes']) ? (string) $stageValues['trial_notes'] : $lead->notes;

        $now = now();
        $isAttended = ($status === 'حضر' || $attendedVal === 'نعم' || $attendedVal === 'yes');
        $isNoShow = ($status === 'لم يحضر' || $attendedVal === 'لا' || $attendedVal === 'no');
        $isToday = $appointmentDate === $now->format('Y-m-d');
        $isUpcoming = $datetime ? $datetime->isAfter($now) : false;

        return [
            'date' => $appointmentDate,
            'time' => $appointmentTime,
            'datetime' => $datetime,
            'coach' => $coach,
            'status' => $status ?: ($isAttended ? 'حضر' : ($isNoShow ? 'لم يحضر' : ($isToday ? 'موعد اليوم' : 'تم الحجز'))),
            'notes' => $notes,
            'is_today' => $isToday,
            'is_upcoming' => $isUpcoming,
            'is_no_show' => $isNoShow,
            'is_attended' => $isAttended,
        ];
    }

    /**
     * Base query for appointment leads mapped to configured stages and accessible to user.
     */
    public function baseQuery(User $user, ?AppointmentSetting $setting = null): Builder
    {
        $setting ??= AppointmentSetting::current();
        $stageIds = is_array($setting->stage_ids) ? array_map('intval', $setting->stage_ids) : [];

        $query = Lead::query()
            ->accessibleTo($user)
            ->with([
                'status.stage',
                'assignedUser',
                'branch',
                'guardian',
            ]);

        if (! empty($stageIds)) {
            $query->whereHas('status', function (Builder $sq) use ($stageIds): void {
                $sq->whereIn('pipeline_stage_id', $stageIds);
            });
        }

        return $query;
    }

    /**
     * Get appointments for a given tab scope.
     *
     * @param array{
     *     branch_id?: ?int,
     *     coach?: ?string,
     *     activity?: ?string,
     *     search?: ?string,
     *     date_from?: ?string,
     *     date_to?: ?string
     * } $filters
     */
    public function getAppointments(User $user, string $tab = 'today', array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $setting = AppointmentSetting::current();
        $query = $this->baseQuery($user, $setting);
        $dateKey = $setting->date_field_key ?: 'trial_date';
        $statusKey = $setting->status_field_key ?: 'trial_status';
        $now = now();
        $todayStr = $now->format('Y-m-d');

        // Apply filters
        if (! empty($filters['branch_id'])) {
            $query->where('leads.branch_id', (int) $filters['branch_id']);
        }

        if (! empty($filters['activity'])) {
            $query->where('leads.activity', (string) $filters['activity']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.trim((string) $filters['search']).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('leads.name', 'like', $term)
                    ->orWhere('leads.phone', 'like', $term);
            });
        }

        if (! empty($filters['coach'])) {
            $coach = trim((string) $filters['coach']);
            $query->whereExists(function ($sub) use ($coach, $setting): void {
                $sub->select(DB::raw(1))
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('lead_stage_field_values.field_key', $setting->coach_field_key ?: 'coach')
                    ->where('lead_stage_field_values.value', 'like', '%'.$coach.'%');
            });
        }

        // Scope to specific tabs
        switch ($tab) {
            case 'today':
                $query->where(function (Builder $q) use ($dateKey, $todayStr): void {
                    $q->whereDate('leads.next_follow_up_at', $todayStr)
                        ->orWhereExists(function ($sub) use ($dateKey, $todayStr): void {
                            $sub->select(DB::raw(1))
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('lead_stage_field_values.field_key', $dateKey)
                                ->whereDate('lead_stage_field_values.value', $todayStr);
                        });
                });
                break;

            case 'upcoming':
                $query->where(function (Builder $q) use ($dateKey, $todayStr): void {
                    $q->whereDate('leads.next_follow_up_at', '>', $todayStr)
                        ->orWhereExists(function ($sub) use ($dateKey, $todayStr): void {
                            $sub->select(DB::raw(1))
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('lead_stage_field_values.field_key', $dateKey)
                                ->whereDate('lead_stage_field_values.value', '>', $todayStr);
                        });
                });
                break;

            case 'no_shows':
                $query->where(function (Builder $q) use ($dateKey, $statusKey, $todayStr): void {
                    $q->whereExists(function ($sub) use ($statusKey): void {
                        $sub->select(DB::raw(1))
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('lead_stage_field_values.field_key', $statusKey)
                            ->where('lead_stage_field_values.value', 'لم يحضر');
                    })->orWhere(function (Builder $subQ) use ($dateKey, $todayStr): void {
                        // Past appointment date without attended status
                        $subQ->where(function (Builder $dateQ) use ($dateKey, $todayStr): void {
                            $dateQ->whereDate('leads.next_follow_up_at', '<', $todayStr)
                                ->orWhereExists(function ($sub) use ($dateKey, $todayStr): void {
                                    $sub->select(DB::raw(1))
                                        ->from('lead_stage_field_values')
                                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                        ->where('lead_stage_field_values.field_key', $dateKey)
                                        ->whereDate('lead_stage_field_values.value', '<', $todayStr);
                                });
                        })->whereNotExists(function ($sub): void {
                            $sub->select(DB::raw(1))
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('lead_stage_field_values.field_key', 'attended')
                                ->where('lead_stage_field_values.value', 'نعم');
                        });
                    });
                });
                break;

            case 'attended':
                $query->where(function (Builder $q) use ($statusKey): void {
                    $q->whereExists(function ($sub) use ($statusKey): void {
                        $sub->select(DB::raw(1))
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('lead_stage_field_values.field_key', $statusKey)
                            ->where('lead_stage_field_values.value', 'حضر');
                    })->orWhereExists(function ($sub): void {
                        $sub->select(DB::raw(1))
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('lead_stage_field_values.field_key', 'attended')
                            ->where('lead_stage_field_values.value', 'نعم');
                    });
                });
                break;

            case 'all':
            default:
                // No extra scope filtering
                break;
        }

        $paginator = $query->latest('id')->paginate($perPage)->withQueryString();

        // Attach resolved appointment details onto each lead item
        $paginator->getCollection()->transform(function (Lead $lead) use ($setting): Lead {
            $lead->setAttribute('appointment_meta', $this->resolveDetails($lead, $setting));

            return $lead;
        });

        return $paginator;
    }

    /**
     * Get aggregate counter metrics.
     *
     * @return array{today: int, upcoming: int, no_shows: int, attended: int, total: int}
     */
    public function getCounts(User $user, ?int $branchId = null): array
    {
        $setting = AppointmentSetting::current();
        $base = $this->baseQuery($user, $setting);
        if ($branchId !== null && $branchId > 0) {
            $base->where('leads.branch_id', $branchId);
        }

        $now = now();
        $todayStr = $now->format('Y-m-d');
        $dateKey = $setting->date_field_key ?: 'trial_date';
        $statusKey = $setting->status_field_key ?: 'trial_status';

        $todayCount = (clone $base)->where(function (Builder $q) use ($dateKey, $todayStr): void {
            $q->whereDate('leads.next_follow_up_at', $todayStr)
                ->orWhereExists(function ($sub) use ($dateKey, $todayStr): void {
                    $sub->select(DB::raw(1))
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('lead_stage_field_values.field_key', $dateKey)
                        ->whereDate('lead_stage_field_values.value', $todayStr);
                });
        })->count();

        $upcomingCount = (clone $base)->where(function (Builder $q) use ($dateKey, $todayStr): void {
            $q->whereDate('leads.next_follow_up_at', '>', $todayStr)
                ->orWhereExists(function ($sub) use ($dateKey, $todayStr): void {
                    $sub->select(DB::raw(1))
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('lead_stage_field_values.field_key', $dateKey)
                        ->whereDate('lead_stage_field_values.value', '>', $todayStr);
                });
        })->count();

        $noShowsCount = (clone $base)->where(function (Builder $q) use ($statusKey): void {
            $q->whereExists(function ($sub) use ($statusKey): void {
                $sub->select(DB::raw(1))
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('lead_stage_field_values.field_key', $statusKey)
                    ->where('lead_stage_field_values.value', 'لم يحضر');
            })->orWhereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('lead_stage_field_values.field_key', 'attended')
                    ->where('lead_stage_field_values.value', 'لا');
            });
        })->count();

        $attendedCount = (clone $base)->where(function (Builder $q) use ($statusKey): void {
            $q->whereExists(function ($sub) use ($statusKey): void {
                $sub->select(DB::raw(1))
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('lead_stage_field_values.field_key', $statusKey)
                    ->where('lead_stage_field_values.value', 'حضر');
            })->orWhereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('lead_stage_field_values.field_key', 'attended')
                    ->where('lead_stage_field_values.value', 'نعم');
            });
        })->count();

        $totalCount = (clone $base)->count();

        return [
            'today' => $todayCount,
            'upcoming' => $upcomingCount,
            'no_shows' => $noShowsCount,
            'attended' => $attendedCount,
            'total' => $totalCount,
        ];
    }

    /**
     * Mark an appointment as attended.
     */
    public function markAttended(Lead $lead, User $actor, ?string $notes = null): void
    {
        $this->recordAttendance($lead, $actor, 'attended', $notes);
    }

    /**
     * Mark an appointment as no-show.
     */
    public function markNoShow(Lead $lead, User $actor, ?string $reason = null): void
    {
        $this->recordAttendance($lead, $actor, 'no_show', $reason);
    }

    private function recordAttendance(Lead $lead, User $actor, string $outcome, ?string $notes): void
    {
        $setting = AppointmentSetting::current();
        $statusKey = $setting->status_field_key ?: 'trial_status';

        DB::transaction(function () use ($lead, $setting, $statusKey, $actor, $outcome, $notes): void {
            $lockedLead = Lead::query()
                ->with(['status', 'branch'])
                ->whereKey($lead->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $currentStageId = (int) ($lockedLead->status?->pipeline_stage_id ?? 17);
            $appointment = $this->resolveDetails($lockedLead, $setting);
            $branch = $lockedLead->branch;

            $existingRecord = AppointmentAttendanceRecord::query()
                ->where('lead_id', $lockedLead->id)
                ->where('appointment_date', $appointment['date'])
                ->where('appointment_time', $appointment['time'])
                ->where('activity', $lockedLead->activity)
                ->where('branch_id', $lockedLead->branch_id)
                ->where('coach', $appointment['coach'])
                ->orderByDesc('id')
                ->first();

            if ($existingRecord?->outcome === $outcome) {
                return;
            }

            $statusValue = $outcome === 'attended' ? 'حضر' : 'لم يحضر';
            $attendanceValue = $outcome === 'attended' ? 'نعم' : 'لا';

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => $statusKey,
                ],
                [
                    'field_type' => 'select',
                    'value' => $statusValue,
                    'created_by_user_id' => $actor->id,
                ]
            );

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => 'attended',
                ],
                [
                    'field_type' => 'select',
                    'value' => $attendanceValue,
                    'created_by_user_id' => $actor->id,
                ]
            );

            if ($notes) {
                $notesKey = $outcome === 'attended' ? 'reception_notes' : 'trial_notes';
                $notesValue = $outcome === 'attended'
                    ? $notes
                    : 'سبب عدم الحضور: '.$notes;

                LeadStageFieldValue::query()->updateOrCreate(
                    [
                        'lead_id' => $lockedLead->id,
                        'pipeline_stage_id' => $currentStageId,
                        'field_key' => $notesKey,
                    ],
                    [
                        'field_type' => 'textarea',
                        'value' => $notesValue,
                        'created_by_user_id' => $actor->id,
                    ]
                );
            }

            AppointmentAttendanceRecord::query()->create([
                'lead_id' => $lockedLead->id,
                'branch_id' => $lockedLead->branch_id,
                'outcome' => $outcome,
                'appointment_date' => $appointment['date'],
                'appointment_time' => $appointment['time'],
                'activity' => $lockedLead->activity,
                'branch_name_ar' => $branch?->name_ar,
                'branch_name_en' => $branch?->name_en,
                'coach' => $appointment['coach'],
                'notes' => $notes,
                'recorded_by_user_id' => $actor->id,
            ]);

            ActivityLogger::log(
                action: $outcome === 'attended' ? 'appointment.attended' : 'appointment.no_show',
                module: 'leads',
                description: $outcome === 'attended'
                    ? "تسجيل حضور العميل/اللاعب {$lockedLead->name} للموعد المجدول"
                    : "تسجيل عدم حضور العميل/اللاعب {$lockedLead->name} للموعد",
                subject: $lockedLead,
                properties: [
                    'stage_id' => $currentStageId,
                    'appointment_date' => $appointment['date'],
                    'appointment_time' => $appointment['time'],
                    'activity' => $lockedLead->activity,
                    'coach' => $appointment['coach'],
                    'branch_id' => $lockedLead->branch_id,
                    'notes' => $notes,
                ],
                actor: $actor,
            );
        });
    }

    /**
     * Reschedule an appointment with a new date and time.
     */
    public function reschedule(Lead $lead, string $date, ?string $time, User $actor, ?string $notes = null): void
    {
        $setting = AppointmentSetting::current();
        $dateKey = $setting->date_field_key ?: 'trial_date';
        $timeKey = $setting->time_field_key ?: 'trial_time';
        $statusKey = $setting->status_field_key ?: 'trial_status';

        DB::transaction(function () use ($lead, $date, $time, $dateKey, $timeKey, $statusKey, $actor, $notes): void {
            $lockedLead = Lead::query()
                ->with('status')
                ->whereKey($lead->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $currentStageId = (int) ($lockedLead->status?->pipeline_stage_id ?? 17);

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => $dateKey,
                ],
                [
                    'field_type' => 'date',
                    'value' => $date,
                    'created_by_user_id' => $actor->id,
                ]
            );

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => $timeKey,
                ],
                [
                    'field_type' => 'time',
                    'value' => $time ?: '',
                    'created_by_user_id' => $actor->id,
                ]
            );

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => $statusKey,
                ],
                [
                    'field_type' => 'select',
                    'value' => 'أعيدت الجدولة',
                    'created_by_user_id' => $actor->id,
                ]
            );

            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lockedLead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => 'attended',
                ],
                [
                    'field_type' => 'select',
                    'value' => '',
                    'created_by_user_id' => $actor->id,
                ]
            );

            $datetimeStr = $date.' '.($time ?: '09:00:00');
            try {
                $lockedLead->update(['next_follow_up_at' => Carbon::parse($datetimeStr)]);
            } catch (\Throwable) {
                // Ignore parse errors
            }

            ActivityLogger::log(
                action: 'appointment.rescheduled',
                module: 'leads',
                description: "إعادة جدولة موعد العميل/اللاعب {$lockedLead->name} إلى {$date} ".($time ?: ''),
                subject: $lockedLead,
                properties: [
                    'date' => $date,
                    'time' => $time,
                    'notes' => $notes,
                ],
                actor: $actor,
            );
        });
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Lead $lead, User $actor, ?string $reason = null): void
    {
        $setting = AppointmentSetting::current();
        $statusKey = $setting->status_field_key ?: 'trial_status';
        $currentStageId = (int) ($lead->status?->pipeline_stage_id ?? 17);

        DB::transaction(function () use ($lead, $statusKey, $currentStageId, $actor, $reason): void {
            LeadStageFieldValue::query()->updateOrCreate(
                [
                    'lead_id' => $lead->id,
                    'pipeline_stage_id' => $currentStageId,
                    'field_key' => $statusKey,
                ],
                [
                    'field_type' => 'select',
                    'value' => 'ألغي',
                    'created_by_user_id' => $actor->id,
                ]
            );

            if ($reason) {
                LeadStageFieldValue::query()->updateOrCreate(
                    [
                        'lead_id' => $lead->id,
                        'pipeline_stage_id' => $currentStageId,
                        'field_key' => 'trial_notes',
                    ],
                    [
                        'field_type' => 'textarea',
                        'value' => 'سبب الإلغاء: '.$reason,
                        'created_by_user_id' => $actor->id,
                    ]
                );
            }

            ActivityLogger::log(
                action: 'appointment.canceled',
                module: 'leads',
                description: "إلغاء موعد العميل/اللاعب {$lead->name}",
                subject: $lead,
                properties: [
                    'reason' => $reason,
                ],
                actor: $actor,
            );
        });
    }
}
