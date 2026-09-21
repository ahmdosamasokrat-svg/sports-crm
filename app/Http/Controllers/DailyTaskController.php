<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use App\Models\PipelineStageField;
use App\Models\User;
use App\Security\CrmPermission;
use App\Support\CrmDatabaseGuard;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DailyTaskController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        $this->assertCrmDatabase();
        $user = $request->user();

        abort_unless(
            $user !== null && $user->hasPermission(CrmPermission::TASKS_VIEW),
            403
        );

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $next7Days = $now->copy()->addDays(7)->endOfDay();

        // Extract and sanitize filter params
        $scope = trim((string) $request->query('scope', 'all'));
        $validScopes = [
            'all', 'overdue', 'today', 'upcoming', 'no_date', 'completed',
            'today_trials', 'attended_not_subscribed', 'trial_no_shows',
        ];

        // Load custom dynamic question filters configured via GUI (Option B)
        $customQuestionFilters = PipelineStageField::query()
            ->where('show_in_daily_tasks', true)
            ->where('is_active', true)
            ->with('stage')
            ->orderBy('pipeline_stage_id')
            ->orderBy('position')
            ->get();

        foreach ($customQuestionFilters as $cqf) {
            $validScopes[] = 'q_' . $cqf->id;
        }

        if (!in_array($scope, $validScopes, true)) {
            $scope = 'all';
        }

        $search = trim((string) $request->query('search', ''));
        $statusId = $request->filled('status_id') ? (int) $request->query('status_id') : null;
        $stageId = $request->filled('stage_id') ? (int) $request->query('stage_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->query('employee_id') : null;
        $sort = trim((string) $request->query('sort', 'followup_asc'));
        $viewMode = trim((string) $request->query('view', 'cards'));
        if (!in_array($viewMode, ['cards', 'timeline', 'table'], true)) {
            $viewMode = 'cards';
        }

        // Apply shared filters builder
        $applySharedFilters = static function (Builder $query) use ($search, $statusId, $stageId, $employeeId): void {
            if ($search !== '') {
                $query->where(static function (Builder $q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($statusId !== null && $statusId > 0) {
                $query->where('lead_status_id', $statusId);
            }

            if ($stageId !== null && $stageId > 0) {
                $query->whereHas('status', static fn (Builder $q): Builder => $q->where('pipeline_stage_id', $stageId));
            }

            if ($employeeId !== null && $employeeId > 0) {
                $query->where('assigned_user_id', $employeeId);
            }
        };

        // Base query for accessible leads
        $baseAccessibleQuery = Lead::query()->accessibleTo($user);
        $filteredCountBase = clone $baseAccessibleQuery;
        $applySharedFilters($filteredCountBase);

        // Calculate accurate KPI metrics for today respecting active filters
        $scopeCounts = (clone $filteredCountBase)
            ->selectRaw('
                count(case when next_follow_up_at is not null and next_follow_up_at < ? then 1 end) as overdue_count,
                count(case when next_follow_up_at is not null and next_follow_up_at between ? and ? then 1 end) as today_count,
                count(case when next_follow_up_at is not null and next_follow_up_at > ? then 1 end) as upcoming_count,
                count(case when next_follow_up_at is null then 1 end) as no_date_count
            ', [$todayStart, $todayStart, $todayEnd, $todayEnd])
            ->first();

        $overdueCount = (int) ($scopeCounts->overdue_count ?? 0);
        $todayCount = (int) ($scopeCounts->today_count ?? 0);
        $upcomingCount = (int) ($scopeCounts->upcoming_count ?? 0);
        $noDateCount = (int) ($scopeCounts->no_date_count ?? 0);

        // Academy Specialized Scope Counts
        $todayDateStr = $now->toDateString();
        $stage17Ids = PipelineStage::query()->where('id', 17)->orWhere('name_ar', 'تجربة محجوزة')->pluck('id')->all() ?: [17];
        $stage18Ids = PipelineStage::query()->where('id', 18)->orWhere('name_ar', 'متابعة ما بعد التجربة')->pluck('id')->all() ?: [18];

        // 1. Today's Trials (Stage 17: تجربة محجوزة with trial_date = today OR next_follow_up_at = today)
        $todayTrialsCount = (clone $filteredCountBase)
            ->whereHas('status', static fn (Builder $q): Builder => $q->whereIn('pipeline_stage_id', $stage17Ids))
            ->where(static function (Builder $q) use ($todayDateStr, $todayStart, $todayEnd): void {
                $q->whereBetween('next_follow_up_at', [$todayStart, $todayEnd])
                    ->orWhereExists(static function ($sub): void {
                        $sub->selectRaw('1')
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('field_key', 'trial_date')
                            ->whereDate('value', now()->toDateString());
                    });
            })
            ->count();

        // 2. Attended - Not Subscribed (Stage 18: متابعة ما بعد التجربة with attended = 'نعم' and subscribed != 'نعم')
        $attendedNotSubscribedCount = (clone $filteredCountBase)
            ->whereHas('status', static fn (Builder $q): Builder => $q->whereIn('pipeline_stage_id', $stage18Ids))
            ->whereExists(static function ($sub): void {
                $sub->selectRaw('1')
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('field_key', 'attended')
                    ->where('value', 'نعم');
            })
            ->whereNotExists(static function ($sub): void {
                $sub->selectRaw('1')
                    ->from('lead_stage_field_values')
                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                    ->where('field_key', 'subscribed')
                    ->where('value', 'نعم');
            })
            ->count();

        // 3. Trial No-Shows (Stage 17 with trial_status = 'لم يحضر' OR Stage 18 with attended = 'لا')
        $trialNoShowsCount = (clone $filteredCountBase)
            ->where(static function (Builder $q) use ($stage17Ids, $stage18Ids): void {
                $q->where(static function (Builder $sq17) use ($stage17Ids): void {
                    $sq17->whereHas('status', static fn (Builder $st): Builder => $st->whereIn('pipeline_stage_id', $stage17Ids))
                        ->whereExists(static function ($sub): void {
                            $sub->selectRaw('1')
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('field_key', 'trial_status')
                                ->where('value', 'لم يحضر');
                        });
                })->orWhere(static function (Builder $sq18) use ($stage18Ids): void {
                    $sq18->whereHas('status', static fn (Builder $st): Builder => $st->whereIn('pipeline_stage_id', $stage18Ids))
                        ->whereExists(static function ($sub): void {
                            $sub->selectRaw('1')
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('field_key', 'attended')
                                ->where('value', 'لا');
                        });
                });
            })
            ->count();

        // Calculate counts for custom dynamic question filters
        $customQuestionCounts = [];
        foreach ($customQuestionFilters as $cqf) {
            $cqfQuery = (clone $filteredCountBase)
                ->whereHas('status', static fn (Builder $q): Builder => $q->where('pipeline_stage_id', $cqf->pipeline_stage_id));

            $filterVals = $cqf->daily_tasks_filter_values;
            if (!empty($filterVals) && is_array($filterVals)) {
                $cqfQuery->whereExists(static function ($sub) use ($cqf, $filterVals): void {
                    $sub->selectRaw('1')
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('pipeline_stage_field_id', $cqf->id)
                        ->whereIn('value', $filterVals);
                });
            } else {
                $cqfQuery->whereExists(static function ($sub) use ($cqf): void {
                    $sub->selectRaw('1')
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('pipeline_stage_field_id', $cqf->id)
                        ->whereNotNull('value')
                        ->where('value', '!=', '');
                });
            }

            $customQuestionCounts[$cqf->id] = $cqfQuery->count();
        }

        $completedTodayQuery = LeadFollowup::query()
            ->whereBetween('followed_up_at', [$todayStart, $todayEnd])
            ->whereHas('lead', static fn (Builder $q): Builder => $q->accessibleTo($user));

        if ($search !== '') {
            $completedTodayQuery->whereHas('lead', static function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($statusId !== null && $statusId > 0) {
            $completedTodayQuery->where(static function (Builder $q) use ($statusId): void {
                $q->where('to_status_id', $statusId)
                    ->orWhereHas('lead', static fn (Builder $lq): Builder => $lq->where('lead_status_id', $statusId));
            });
        }

        if ($employeeId !== null && $employeeId > 0) {
            $completedTodayQuery->where(static function (Builder $q) use ($employeeId): void {
                $q->where('user_id', $employeeId)
                    ->orWhereHas('lead', static fn (Builder $lq): Builder => $lq->where('assigned_user_id', $employeeId));
            });
        }

        $completedTodayCount = $completedTodayQuery->count();

        $totalDueToday = $overdueCount + $todayCount;
        $progressTotal = $completedTodayCount + $totalDueToday;
        $completionRate = $progressTotal > 0
            ? (int) round(($completedTodayCount / $progressTotal) * 100)
            : 0;

        // Query leads with all necessary relationships
        $leadsBase = Lead::query()
            ->accessibleTo($user)
            ->with([
                'status.stage.category',
                'assignedUser:id,name',
                'creator:id,name',
                'latestFollowup' => static fn ($q) => $q->with(['user:id,name', 'fromStatus', 'toStatus']),
            ]);

        $applySharedFilters($leadsBase);

        // Sorting logic
        $applySorting = static function (Builder $query) use ($sort): void {
            match ($sort) {
                'followup_desc' => $query->orderByDesc('next_follow_up_at')->orderByDesc('id'),
                'name_asc' => $query->orderBy('name')->orderByDesc('id'),
                'created_desc' => $query->orderByDesc('created_at')->orderByDesc('id'),
                default => $query->orderByRaw('next_follow_up_at IS NULL')
                    ->orderBy('next_follow_up_at')
                    ->orderBy('id'),
            };
        };

        // Separate collections for 'all' scope vs paginated query for specific scopes
        $overdueTasks = collect();
        $todayTasks = collect();
        $upcomingTasks = collect();
        $noDateTasks = collect();
        $paginatedTasks = null;
        $completedTodayFollowups = null;

        if ($scope === 'all') {
            $overdueQuery = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<', $todayStart);
            $applySorting($overdueQuery);
            $overdueTasks = $overdueQuery->limit(50)->get();

            $todayQuery = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->whereBetween('next_follow_up_at', [$todayStart, $todayEnd]);
            $applySorting($todayQuery);
            $todayTasks = $todayQuery->limit(50)->get();

            $upcomingQuery = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '>', $todayEnd);
            $applySorting($upcomingQuery);
            $upcomingTasks = $upcomingQuery->limit(25)->get();

            $noDateQuery = (clone $leadsBase)
                ->whereNull('next_follow_up_at')
                ->orderByDesc('created_at');
            $noDateTasks = $noDateQuery->limit(25)->get();
        } elseif ($scope === 'overdue') {
            $query = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<', $todayStart);
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'today') {
            $query = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->whereBetween('next_follow_up_at', [$todayStart, $todayEnd]);
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'upcoming') {
            $query = (clone $leadsBase)
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '>', $todayEnd);
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'no_date') {
            $query = (clone $leadsBase)
                ->whereNull('next_follow_up_at')
                ->orderByDesc('created_at');
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'completed') {
            $completedQuery = LeadFollowup::query()
                ->whereBetween('followed_up_at', [$todayStart, $todayEnd])
                ->whereHas('lead', static fn (Builder $q): Builder => $q->accessibleTo($user))
                ->with([
                    'lead.status.stage.category',
                    'lead.assignedUser:id,name',
                    'user:id,name',
                    'fromStatus',
                    'toStatus',
                ])
                ->orderByDesc('followed_up_at')
                ->orderByDesc('id');

            if ($search !== '') {
                $completedQuery->whereHas('lead', static function (Builder $q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            if ($employeeId !== null && $employeeId > 0) {
                $completedQuery->where('user_id', $employeeId);
            }

            $completedTodayFollowups = $completedQuery->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'today_trials') {
            $stage17Ids = PipelineStage::query()->where('id', 17)->orWhere('name_ar', 'تجربة محجوزة')->pluck('id')->all() ?: [17];
            $query = (clone $leadsBase)
                ->whereHas('status', static fn (Builder $q): Builder => $q->whereIn('pipeline_stage_id', $stage17Ids))
                ->where(static function (Builder $q) use ($todayStart, $todayEnd): void {
                    $q->whereBetween('next_follow_up_at', [$todayStart, $todayEnd])
                        ->orWhereExists(static function ($sub): void {
                            $sub->selectRaw('1')
                                ->from('lead_stage_field_values')
                                ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                ->where('field_key', 'trial_date')
                                ->whereDate('value', now()->toDateString());
                        });
                });
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'attended_not_subscribed') {
            $stage18Ids = PipelineStage::query()->where('id', 18)->orWhere('name_ar', 'متابعة ما بعد التجربة')->pluck('id')->all() ?: [18];
            $query = (clone $leadsBase)
                ->whereHas('status', static fn (Builder $q): Builder => $q->whereIn('pipeline_stage_id', $stage18Ids))
                ->whereExists(static function ($sub): void {
                    $sub->selectRaw('1')
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('field_key', 'attended')
                        ->where('value', 'نعم');
                })
                ->whereNotExists(static function ($sub): void {
                    $sub->selectRaw('1')
                        ->from('lead_stage_field_values')
                        ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                        ->where('field_key', 'subscribed')
                        ->where('value', 'نعم');
                });
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif ($scope === 'trial_no_shows') {
            $stage17Ids = PipelineStage::query()->where('id', 17)->orWhere('name_ar', 'تجربة محجوزة')->pluck('id')->all() ?: [17];
            $stage18Ids = PipelineStage::query()->where('id', 18)->orWhere('name_ar', 'متابعة ما بعد التجربة')->pluck('id')->all() ?: [18];
            $query = (clone $leadsBase)
                ->where(static function (Builder $q) use ($stage17Ids, $stage18Ids): void {
                    $q->where(static function (Builder $sq17) use ($stage17Ids): void {
                        $sq17->whereHas('status', static fn (Builder $st): Builder => $st->whereIn('pipeline_stage_id', $stage17Ids))
                            ->whereExists(static function ($sub): void {
                                $sub->selectRaw('1')
                                    ->from('lead_stage_field_values')
                                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                    ->where('field_key', 'trial_status')
                                    ->where('value', 'لم يحضر');
                            });
                    })->orWhere(static function (Builder $sq18) use ($stage18Ids): void {
                        $sq18->whereHas('status', static fn (Builder $st): Builder => $st->whereIn('pipeline_stage_id', $stage18Ids))
                            ->whereExists(static function ($sub): void {
                                $sub->selectRaw('1')
                                    ->from('lead_stage_field_values')
                                    ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                                    ->where('field_key', 'attended')
                                    ->where('value', 'لا');
                            });
                    });
                });
            $applySorting($query);
            $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
        } elseif (str_starts_with($scope, 'q_')) {
            $cqfId = (int) substr($scope, 2);
            $cqf = $customQuestionFilters->firstWhere('id', $cqfId);

            if ($cqf) {
                $query = (clone $leadsBase)
                    ->whereHas('status', static fn (Builder $q): Builder => $q->where('pipeline_stage_id', $cqf->pipeline_stage_id));

                $filterVals = $cqf->daily_tasks_filter_values;
                if (!empty($filterVals) && is_array($filterVals)) {
                    $query->whereExists(static function ($sub) use ($cqf, $filterVals): void {
                        $sub->selectRaw('1')
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('pipeline_stage_field_id', $cqf->id)
                            ->whereIn('value', $filterVals);
                    });
                } else {
                    $query->whereExists(static function ($sub) use ($cqf): void {
                        $sub->selectRaw('1')
                            ->from('lead_stage_field_values')
                            ->whereColumn('lead_stage_field_values.lead_id', 'leads.id')
                            ->where('pipeline_stage_field_id', $cqf->id)
                            ->whereNotNull('value')
                            ->where('value', '!=', '');
                    });
                }

                $applySorting($query);
                $paginatedTasks = $query->paginate(self::PER_PAGE)->withQueryString();
            }
        }

        // Data for filters and modals
        $statuses = LeadStatus::query()
            ->visibleTo($user)
            ->with('stage')
            ->orderBy('position')
            ->get();

        $stages = PipelineStage::query()
            ->visibleTo($user)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $assignableUsers = collect();
        if ($user->isSuperAdmin() || $user->hasPermission(CrmPermission::LEADS_SCOPE_ALL) || $user->hasPermission(CrmPermission::LEADS_SCOPE_GROUP)) {
            $assignableUsers = User::query()
                ->where('is_active', true)
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        } else {
            $assignableUsers = collect([$user]);
        }

        $communicationTypes = [
            'call' => __('crm.communication_call'),
            'whatsapp' => __('crm.communication_whatsapp'),
            'email' => __('crm.communication_email'),
            'meeting' => __('crm.communication_meeting'),
            'other' => __('crm.communication_other'),
        ];

        return view('tasks.daily', [
            'scope' => $scope,
            'search' => $search,
            'statusId' => $statusId,
            'stageId' => $stageId,
            'employeeId' => $employeeId,
            'sort' => $sort,
            'viewMode' => $viewMode,
            'overdueCount' => $overdueCount,
            'todayCount' => $todayCount,
            'upcomingCount' => $upcomingCount,
            'noDateCount' => $noDateCount,
            'completedTodayCount' => $completedTodayCount,
            'todayTrialsCount' => $todayTrialsCount,
            'attendedNotSubscribedCount' => $attendedNotSubscribedCount,
            'trialNoShowsCount' => $trialNoShowsCount,
            'customQuestionFilters' => $customQuestionFilters,
            'customQuestionCounts' => $customQuestionCounts,
            'totalDueToday' => $totalDueToday,
            'completionRate' => $completionRate,
            'overdueTasks' => $overdueTasks,
            'todayTasks' => $todayTasks,
            'upcomingTasks' => $upcomingTasks,
            'noDateTasks' => $noDateTasks,
            'paginatedTasks' => $paginatedTasks,
            'completedTodayFollowups' => $completedTodayFollowups,
            'statuses' => $statuses,
            'stages' => $stages,
            'assignableUsers' => $assignableUsers,
            'communicationTypes' => $communicationTypes,
            'todayDateFormatted' => app()->getLocale() === 'ar'
                ? $now->locale('ar')->translatedFormat('l، d F Y')
                : $now->locale('en')->isoFormat('dddd, MMMM D, YYYY'),
        ]);
    }

    public function reschedule(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $this->assertCrmDatabase();
        $user = $request->user();

        abort_unless(
            $user !== null && $lead->isAccessibleTo($user) && ($user->hasPermission(CrmPermission::LEADS_UPDATE) || $user->hasPermission(CrmPermission::TASKS_VIEW)),
            403
        );

        $validated = $request->validate([
            'next_follow_up_at' => ['required', 'date'],
            'reschedule_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $nextDate = Carbon::parse($validated['next_follow_up_at']);
        $lead->update([
            'next_follow_up_at' => $nextDate,
        ]);

        if (!empty($validated['reschedule_reason'])) {
            LeadFollowup::create([
                'lead_id' => $lead->id,
                'from_status_id' => $lead->lead_status_id,
                'to_status_id' => $lead->lead_status_id,
                'employee_name' => $user->name ?? 'System',
                'user_id' => $user->id,
                'communication_type' => 'other',
                'outcome' => __('crm.reschedule_task') . ': ' . $validated['reschedule_reason'],
                'next_follow_up_at' => $nextDate,
                'followed_up_at' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('crm.task_rescheduled_success'),
                'lead_id' => $lead->id,
                'next_follow_up_at' => $nextDate->format('Y-m-d H:i'),
            ]);
        }

        return back()->with('status', __('crm.task_rescheduled_success'));
    }

    public function quickFollowup(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $this->assertCrmDatabase();
        $user = $request->user();

        abort_unless(
            $user !== null && $lead->isAccessibleTo($user) && ($user->hasPermission(CrmPermission::LEADS_FOLLOWUPS_CREATE) || $user->hasPermission(CrmPermission::TASKS_VIEW)),
            403
        );

        $validated = $request->validate([
            'communication_type' => ['required', 'string', 'in:call,whatsapp,email,meeting,other'],
            'outcome' => ['required', 'string', 'max:3000'],
            'lead_status_id' => ['nullable', 'integer', Rule::exists('lead_statuses', 'id')],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        $fromStatusId = $lead->lead_status_id;
        $toStatusId = !empty($validated['lead_status_id']) ? (int) $validated['lead_status_id'] : $fromStatusId;
        $nextFollowUpAt = !empty($validated['next_follow_up_at']) ? Carbon::parse($validated['next_follow_up_at']) : null;
        $toStatus = LeadStatus::query()->findOrFail($toStatusId);

        // Check if destination stage has applicable required questions
        if ($toStatusId !== $fromStatusId && $toStatus->pipeline_stage_id) {
            $destStage = $toStatus->stage;
            if ($destStage !== null) {
                $rawStageInputs = \App\Support\StageFieldSchema::extractStageInputs($request);
                $activeFields = \App\Support\StageFieldSchema::getFieldsForStage($destStage, true);
                $hasRequiredUnanswered = false;
                $unansweredKeys = [];
                foreach ($activeFields as $f) {
                    $isApp = \App\Support\StageFieldSchema::evaluateFieldApplicability($f, $rawStageInputs, $activeFields);
                    $submittedVal = $rawStageInputs[$f->key] ?? null;
                    if ($f->is_required && $isApp) {
                        if ($submittedVal === null || $submittedVal === '' || $submittedVal === []) {
                            $hasRequiredUnanswered = true;
                            $unansweredKeys[$f->key] = __('crm.field_required') ?: 'هذا الحقل مطلوب';
                        }
                    }
                }
                if ($hasRequiredUnanswered) {
                    $redirectUrl = route('v2.leads.followups.index', [
                        'lead' => $lead->id,
                        'target_status_id' => $toStatusId,
                    ]);

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'requires_stage_questions' => true,
                            'redirect_url' => $redirectUrl,
                            'message' => __('crm.stage_questions_required_notice') ?: 'المرحلة المختارة تحتوي على أسئلة ومحددات إجبارية. يرجى استكمالها من شاشة المتابعة.',
                        ], 422);
                    }

                    return redirect($redirectUrl)
                        ->with('warning', __('crm.stage_questions_required_notice') ?: 'المرحلة المختارة تحتوي على أسئلة ومحددات إجبارية. يرجى استكمالها من شاشة المتابعة.')
                        ->withErrors($unansweredKeys);
                }
            }
        }

        $transitionService = app(\App\Services\LeadTransitionService::class);
        $transitionService->transition(
            $lead,
            $toStatus,
            $user,
            [
                'record_followup' => true,
                'communication_type' => $validated['communication_type'],
                'outcome' => $validated['outcome'],
                'next_follow_up_at' => $nextFollowUpAt,
                'stage_fields' => \App\Support\StageFieldSchema::extractStageInputs($request),
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('crm.task_followup_saved_success'),
                'lead_id' => $lead->id,
                'status_id' => $toStatusId,
            ]);
        }

        return back()->with('status', __('crm.task_followup_saved_success'));
    }

    private function assertCrmDatabase(): void
    {
        CrmDatabaseGuard::ensureConnected();
    }
}
