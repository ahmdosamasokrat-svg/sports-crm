<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Security\CrmPermission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasPermission(CrmPermission::AUDIT_LOGS_VIEW), 403);

        $query = ActivityLog::query()
            ->with(['user'])
            ->latest('created_at')
            ->latest('id');

        if ($request->filled('user_id')) {
            $query->forUser($request->input('user_id'));
        }

        if ($request->filled('module')) {
            $query->forModule($request->input('module'));
        }

        if ($request->filled('action')) {
            $query->forAction($request->input('action'));
        }

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateBetween($request->input('date_from'), $request->input('date_to'));
        }

        $logs = $query->paginate(30)->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->select(['id', 'name', 'username'])
            ->get();

        $modules = [
            'leads' => __('crm.leads') ?? 'العملاء والمتابعات',
            'quotation' => __('crm.quotations') ?? 'عروض الأسعار',
            'campaign' => __('crm.campaigns') ?? 'الحملات',
            'users' => __('crm.users') ?? 'المستخدمون والأمان',
            'group' => __('crm.groups') ?? 'المجموعات والصلاحيات',
            'pipelinestage' => __('crm.stages_and_statuses') ?? 'مراحل البيع',
            'pipelinestagefield' => 'الحقول المخصصة',
            'calendarevent' => __('crm.calendar_and_events') ?? 'التقويم والأحداث',
            'notificationrule' => __('crm.notifications') ?? 'الإشعارات',
            'technicalsupportticket' => __('crm.support_tickets') ?? 'تذاكر الدعم',
            'technicalsupporttask' => __('crm.support_tasks') ?? 'مهام الدعم',
            'settings' => __('crm.settings') ?? 'الإعدادات العامة',
        ];

        return view('settings.audit-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'modules' => $modules,
            'filters' => [
                'user_id' => $request->input('user_id'),
                'module' => $request->input('module'),
                'action' => $request->input('action'),
                'q' => $request->input('q'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
            'totalLogs' => ActivityLog::query()->count(),
            'todayLogs' => ActivityLog::query()->whereDate('created_at', today())->count(),
        ]);
    }
}
