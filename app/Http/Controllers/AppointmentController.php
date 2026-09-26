<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppointmentSetting;
use App\Models\Branch;
use App\Models\Lead;
use App\Services\AppointmentService;
use App\Support\CrmDatabaseGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    public function index(Request $request): View
    {
        CrmDatabaseGuard::ensureConnected();

        $setting = AppointmentSetting::current();
        abort_unless(
            $setting->is_enabled,
            403,
            __('crm.appointments_module_disabled') ?? 'نظام إدارة المواعيد معطل حالياً من إعدادات النظام.'
        );

        $user = $request->user();
        $tab = (string) $request->query('tab', 'today');
        if (! in_array($tab, ['today', 'upcoming', 'no_shows', 'attended', 'all'], true)) {
            $tab = 'today';
        }

        $filters = [
            'branch_id' => $request->filled('branch_id') ? $request->integer('branch_id') : null,
            'activity' => $request->filled('activity') ? (string) $request->query('activity') : null,
            'coach' => $request->filled('coach') ? (string) $request->query('coach') : null,
            'search' => $request->filled('search') ? (string) $request->query('search') : null,
        ];

        $appointments = $this->appointmentService->getAppointments($user, $tab, $filters);
        $counts = $this->appointmentService->getCounts($user, $filters['branch_id']);
        $branches = Branch::query()->orderBy('name_ar')->get();
        $mappedStages = $setting->getMappedStages();

        return view('appointments.index', [
            'tab' => $tab,
            'appointments' => $appointments,
            'counts' => $counts,
            'branches' => $branches,
            'mappedStages' => $mappedStages,
            'setting' => $setting,
            'filters' => $filters,
        ]);
    }

    public function markAttended(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();
        $setting = AppointmentSetting::current();
        abort_unless($setting->is_enabled, 403);
        Gate::authorize('update', $lead);

        $notes = $request->filled('notes') ? (string) $request->input('notes') : null;
        $this->appointmentService->markAttended($lead, $request->user(), $notes);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('crm.marked_attended') ?? 'تم تسجيل الحضور بنجاح']);
        }

        return back()->with('success', __('crm.marked_attended') ?? 'تم تسجيل الحضور بنجاح');
    }

    public function markNoShow(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();
        $setting = AppointmentSetting::current();
        abort_unless($setting->is_enabled, 403);
        Gate::authorize('update', $lead);

        $reason = $request->filled('reason') ? (string) $request->input('reason') : null;
        $this->appointmentService->markNoShow($lead, $request->user(), $reason);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('crm.marked_no_show') ?? 'تم تسجيل عدم الحضور']);
        }

        return back()->with('success', __('crm.marked_no_show') ?? 'تم تسجيل عدم الحضور');
    }

    public function reschedule(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();
        $setting = AppointmentSetting::current();
        abort_unless($setting->is_enabled, 403);
        Gate::authorize('update', $lead);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->appointmentService->reschedule(
            $lead,
            (string) $validated['date'],
            $validated['time'] ?? null,
            $request->user(),
            $validated['notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('crm.rescheduled_successfully') ?? 'تمت إعادة الجدولة بنجاح']);
        }

        return back()->with('success', __('crm.rescheduled_successfully') ?? 'تمت إعادة الجدولة بنجاح');
    }

    public function cancel(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        CrmDatabaseGuard::ensureConnected();
        $setting = AppointmentSetting::current();
        abort_unless($setting->is_enabled, 403);
        Gate::authorize('update', $lead);

        $reason = $request->filled('reason') ? (string) $request->input('reason') : null;
        $this->appointmentService->cancel($lead, $request->user(), $reason);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('crm.appointment_canceled') ?? 'تم إلغاء الموعد']);
        }

        return back()->with('success', __('crm.appointment_canceled') ?? 'تم إلغاء الموعد');
    }
}
