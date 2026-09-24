<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadStageFieldValue;
use App\Models\User;
use App\Support\BirthdayModuleGuard;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BirthdayService
{
    /**
     * Resolve the birth date for a lead from core data or stage field values.
     */
    public function resolveBirthDate(Lead $lead): ?CarbonInterface
    {
        if ($lead->birth_date !== null) {
            return Carbon::parse($lead->birth_date);
        }

        // Check if there is a custom stage field value for birthday
        try {
            $stageVal = LeadStageFieldValue::query()
                ->where('lead_id', $lead->id)
                ->where(function ($q): void {
                    $q->whereIn('field_key', ['birth_date', 'birthday', 'date_of_birth', 'player_birthday'])
                        ->orWhereHas('field', function ($fq): void {
                            $fq->whereIn('key', ['birth_date', 'birthday', 'date_of_birth'])
                                ->orWhere('binding_target', 'birth_date');
                        });
                })
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->latest('id')
                ->value('value');

            if ($stageVal) {
                return Carbon::parse($stageVal);
            }
        } catch (\Throwable) {
            // Ignore parsing failures
        }

        return null;
    }

    /**
     * Calculate the next birthday occurrence datetime.
     */
    public static function getNextBirthday(CarbonInterface $birthDate, ?CarbonInterface $from = null): CarbonInterface
    {
        $from = ($from ?? now())->copy()->startOfDay();
        $year = $from->year;

        $month = (int) $birthDate->month;
        $day = (int) $birthDate->day;

        // Handle leap year Feb 29 on non-leap years
        if ($month === 2 && $day === 29 && ! checkdate(2, 29, $year)) {
            $day = 28;
        }

        $candidate = Carbon::create($year, $month, $day, 0, 0, 0, $from->timezone);
        if ($candidate->isBefore($from)) {
            $nextYear = $year + 1;
            $nextDay = (int) $birthDate->day;
            if ($month === 2 && $nextDay === 29 && ! checkdate(2, 29, $nextYear)) {
                $nextDay = 28;
            }
            $candidate = Carbon::create($nextYear, $month, $nextDay, 0, 0, 0, $from->timezone);
        }

        return $candidate;
    }

    /**
     * Calculate turning age on the next birthday.
     */
    public static function getAgeOnNextBirthday(CarbonInterface $birthDate, ?CarbonInterface $from = null): int
    {
        $nextBirthday = self::getNextBirthday($birthDate, $from);

        return (int) $birthDate->diffInYears($nextBirthday);
    }

    /**
     * Get days remaining until next birthday.
     */
    public static function getDaysUntilBirthday(CarbonInterface $birthDate, ?CarbonInterface $from = null): int
    {
        $from = ($from ?? now())->copy()->startOfDay();
        $nextBirthday = self::getNextBirthday($birthDate, $from);

        return (int) $from->diffInDays($nextBirthday, false);
    }

    /**
     * Base query for leads with birthdays accessible to the user.
     */
    public function baseQuery(User $user, ?string $search = null, ?int $branchId = null): Builder
    {
        $query = Lead::query()
            ->accessibleTo($user)
            ->with(['assignedUser', 'branch', 'status.stage', 'guardian'])
            ->whereNotNull('leads.birth_date');

        if ($branchId !== null && $branchId > 0) {
            $query->where('leads.branch_id', $branchId);
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('leads.name', 'like', $term)
                    ->orWhere('leads.phone', 'like', $term)
                    ->orWhere('leads.activity', 'like', $term);
            });
        }

        return $query;
    }

    /**
     * Get athletes with birthdays today.
     *
     * @return EloquentCollection<int, Lead>
     */
    public function getTodayBirthdays(User $user, ?string $search = null, ?int $branchId = null): EloquentCollection
    {
        $now = now();

        return $this->baseQuery($user, $search, $branchId)
            ->whereMonth('leads.birth_date', $now->month)
            ->whereDay('leads.birth_date', $now->day)
            ->orderBy('leads.name')
            ->get();
    }

    /**
     * Get athletes with birthdays this week (next 7 days).
     *
     * @return Collection<int, Lead>
     */
    public function getThisWeekBirthdays(User $user, ?string $search = null, ?int $branchId = null): Collection
    {
        $leads = $this->baseQuery($user, $search, $branchId)
            ->select('leads.*')
            ->selectRaw('
                DATEDIFF(
                    DATE_ADD(leads.birth_date, INTERVAL (YEAR(CURDATE()) - YEAR(leads.birth_date) + IF(DATE_FORMAT(CURDATE(), "%m%d") > DATE_FORMAT(leads.birth_date, "%m%d"), 1, 0)) YEAR),
                    CURDATE()
                ) AS days_until_birthday
            ')
            ->havingRaw('days_until_birthday BETWEEN 0 AND 7')
            ->orderBy('days_until_birthday', 'asc')
            ->get();

        return $leads;
    }

    /**
     * Get athletes with birthdays this month.
     *
     * @return EloquentCollection<int, Lead>
     */
    public function getThisMonthBirthdays(User $user, ?string $search = null, ?int $branchId = null): EloquentCollection
    {
        $now = now();

        return $this->baseQuery($user, $search, $branchId)
            ->whereMonth('leads.birth_date', $now->month)
            ->orderByRaw('DAY(leads.birth_date) ASC')
            ->get();
    }

    /**
     * Get upcoming birthdays ordered by soonest.
     *
     * @return Collection<int, Lead>
     */
    public function getUpcomingBirthdays(User $user, ?string $search = null, ?int $branchId = null, int $limit = 50): Collection
    {
        return $this->baseQuery($user, $search, $branchId)
            ->select('leads.*')
            ->selectRaw('
                DATEDIFF(
                    DATE_ADD(leads.birth_date, INTERVAL (YEAR(CURDATE()) - YEAR(leads.birth_date) + IF(DATE_FORMAT(CURDATE(), "%m%d") > DATE_FORMAT(leads.birth_date, "%m%d"), 1, 0)) YEAR),
                    CURDATE()
                ) AS days_until_birthday
            ')
            ->havingRaw('days_until_birthday >= 0')
            ->orderBy('days_until_birthday', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get summary counts for the birthday dashboard.
     *
     * @return array{today: int, week: int, month: int, total: int}
     */
    public function getCounts(User $user, ?int $branchId = null): array
    {
        $now = now();
        $base = $this->baseQuery($user, null, $branchId);

        $todayCount = (clone $base)
            ->whereMonth('leads.birth_date', $now->month)
            ->whereDay('leads.birth_date', $now->day)
            ->count();

        $monthCount = (clone $base)
            ->whereMonth('leads.birth_date', $now->month)
            ->count();

        $totalCount = (clone $base)->count();

        $weekCount = (int) DB::table('leads')
            ->whereNull('deleted_at')
            ->whereNotNull('birth_date')
            ->selectRaw('
                DATEDIFF(
                    DATE_ADD(birth_date, INTERVAL (YEAR(CURDATE()) - YEAR(birth_date) + IF(DATE_FORMAT(CURDATE(), "%m%d") > DATE_FORMAT(birth_date, "%m%d"), 1, 0)) YEAR),
                    CURDATE()
                ) AS days_until
            ')
            ->havingRaw('days_until BETWEEN 0 AND 7')
            ->count();

        return [
            'today' => $todayCount,
            'week' => $weekCount,
            'month' => $monthCount,
            'total' => $totalCount,
        ];
    }
}
