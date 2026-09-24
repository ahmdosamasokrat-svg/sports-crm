<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BirthdayService;
use App\Support\BirthdayModuleGuard;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BirthdayController extends Controller
{
    public function __construct(
        private readonly BirthdayService $birthdayService,
    ) {}

    public function index(Request $request): View
    {
        CrmDatabaseGuard::ensureConnected();

        abort_unless(
            BirthdayModuleGuard::isEnabled(),
            403,
            __('crm.birthday_module_not_available') ?? 'نظام أعياد الميلاد غير مفعل حالياً. يجب تفعيل حقل تاريخ الميلاد أو العمر في حقول العميل أو المرحلة أولاً.'
        );

        $user = $request->user();
        $tab = (string) $request->query('tab', 'today');
        if (! in_array($tab, ['today', 'week', 'month', 'upcoming'], true)) {
            $tab = 'today';
        }

        $search = $request->filled('search') ? (string) $request->query('search') : null;
        $branchId = $request->filled('branch_id') ? $request->integer('branch_id') : null;

        $counts = $this->birthdayService->getCounts($user, $branchId);

        $athletes = match ($tab) {
            'today' => $this->birthdayService->getTodayBirthdays($user, $search, $branchId),
            'week' => $this->birthdayService->getThisWeekBirthdays($user, $search, $branchId),
            'month' => $this->birthdayService->getThisMonthBirthdays($user, $search, $branchId),
            'upcoming' => $this->birthdayService->getUpcomingBirthdays($user, $search, $branchId, 60),
        };

        $branches = Branch::query()
            ->orderBy('name_ar')
            ->get();

        $activeSources = BirthdayModuleGuard::getActiveSources();

        return view('birthdays.index', [
            'tab' => $tab,
            'athletes' => $athletes,
            'counts' => $counts,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'search' => $search,
            'activeSources' => $activeSources,
        ]);
    }
}
