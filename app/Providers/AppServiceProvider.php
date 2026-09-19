<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\PipelineStage;
use App\Models\Quotation;
use App\Models\TechnicalSupportTask;
use App\Models\User;
use App\Observers\CalendarEventNotificationObserver;
use App\Observers\LeadNotificationObserver;
use App\Policies\CalendarEventPolicy;
use App\Policies\GroupPolicy;
use App\Policies\LeadFollowupPolicy;
use App\Policies\LeadPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\UserPolicy;
use App\Security\CrmPermission;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(LeadFollowup::class, LeadFollowupPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(CalendarEvent::class, CalendarEventPolicy::class);

        Lead::observe(LeadNotificationObserver::class);
        CalendarEvent::observe(CalendarEventNotificationObserver::class);

        foreach (CrmPermission::cases() as $permission) {
            Gate::define(
                $permission->value,
                static fn (User $user): bool => $user->hasPermission($permission),
            );
        }

        Event::listen(Login::class, static function (Login $event): void {
            if ($event->user instanceof User) {
                $desc = app()->getLocale() === 'en'
                    ? "User logged in: {$event->user->name}"
                    : "تسجيل دخول المستخدم: {$event->user->name}";
                ActivityLogger::log(
                    action: 'auth.login',
                    module: 'users',
                    description: $desc,
                    subject: $event->user,
                    actor: $event->user,
                );
            }
        });

        Event::listen(Logout::class, static function (Logout $event): void {
            if ($event->user instanceof User) {
                $desc = app()->getLocale() === 'en'
                    ? "User logged out: {$event->user->name}"
                    : "تسجيل خروج المستخدم: {$event->user->name}";
                ActivityLogger::log(
                    action: 'auth.logout',
                    module: 'users',
                    description: $desc,
                    subject: $event->user,
                    actor: $event->user,
                );
            }
        });

        Event::listen(Failed::class, static function (Failed $event): void {
            $username = (string) ($event->credentials['username'] ?? ($event->credentials['email'] ?? 'unknown'));
            $desc = app()->getLocale() === 'en'
                ? "Failed login attempt for user: {$username}"
                : "محاولة تسجيل دخول فاشلة للمستخدم: {$username}";
            ActivityLogger::log(
                action: 'auth.failed',
                module: 'users',
                description: $desc,
                properties: [
                    'username' => $username,
                ],
            );
        });

        View::composer(
            'partials.crm-sidebar',
            static function ($view): void {
                $user = auth()->user();
                if ($user === null) {
                    $view->with([
                        'totalLeads' => 0,
                        'totalTasks' => 0,
                        'sidebarPipelineStages' => collect(),
                        'crmSidebarHasSupportTasks' => false,
                    ]);
                    return;
                }

                // Debounce / cache counts per user for 60 seconds to eliminate full-table queries on every page hit
                $counts = Cache::remember(
                    "crm.sidebar.counts.user_{$user->id}",
                    now()->addSeconds(60),
                    static function () use ($user): array {
                        $totalLeads = Gate::allows(CrmPermission::LEADS_VIEW->value)
                            ? Lead::query()->accessibleTo($user)->count()
                            : 0;

                        $totalTasks = Gate::allows(CrmPermission::TASKS_VIEW->value)
                            ? Lead::query()->accessibleTo($user)
                                ->whereNotNull('next_follow_up_at')
                                ->where('next_follow_up_at', '<=', now()->endOfDay())
                                ->count()
                            : 0;

                        return [
                            'totalLeads' => $totalLeads,
                            'totalTasks' => $totalTasks,
                        ];
                    }
                );

                $sidebarPipelineStages = PipelineStage::getActiveStagesForSidebar($user);

                // Only query technical support tasks if the user might need the fallback flag
                $crmSidebarHasSupportTasks = false;
                if (! Gate::allows('technical_support.view') && ! Gate::allows('technical_support.reports')) {
                    $crmSidebarHasSupportTasks = Cache::remember(
                        "crm.sidebar.has_support_tasks.user_{$user->id}",
                        now()->addMinutes(5),
                        static fn () => TechnicalSupportTask::query()->accessibleTo($user)->exists()
                    );
                }

                $view->with([
                    'totalLeads' => $counts['totalLeads'],
                    'totalTasks' => $counts['totalTasks'],
                    'sidebarPipelineStages' => $sidebarPipelineStages,
                    'crmSidebarHasSupportTasks' => $crmSidebarHasSupportTasks,
                ]);
            },
        );
    }
}
