@once('crm-notification-center')
<link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v={{ filemtime(public_path('crm-notifications.css')) }}">

<div
 id="crmNotificationCenter"
 class="crm-notification-center"
 data-csrf="{{ csrf_token() }}"
 data-locale="{{ app()->getLocale() }}"
 data-index-url="{{ route('v2.notifications.index') }}"
 data-count-url="{{ route('v2.notifications.unread-count') }}"
 @can('tasks.view') data-due-followups-url="{{ route('v2.notifications.due-followups') }}" @endcan
 data-read-all-url="{{ route('v2.notifications.read-all') }}"
 data-base-url="{{ url('/notifications') }}"
 data-preferences-url="{{ route('v2.notifications.preferences.edit') }}"
 data-poll-seconds="{{ max(15, (int) config('crm_notifications.poll_seconds', 60)) }}"
 data-label-loading="{{ __('crm.notification_loading') }}"
 data-label-empty="{{ __('crm.notification_empty') }}"
 data-label-error="{{ __('crm.notification_load_error') }}"
 data-label-open="{{ __('crm.notification_open') }}"
 data-label-dismiss="{{ __('crm.notification_dismiss') }}"
 data-label-snooze="{{ __('crm.notification_snooze') }}"
 data-label-snoozed="{{ __('crm.notification_snoozed') }}"
 data-label-load-more="{{ __('crm.load_more') }}"
 data-label-tasks-loading="{{ __('crm.notification_tasks_loading') }}"
 data-label-tasks-empty="{{ __('crm.notification_tasks_empty') }}"
 data-label-tasks-overdue="{{ __('crm.overdue_short') }}"
 data-label-tasks-today="{{ __('crm.today') }}"
 data-label-tasks-tomorrow="{{ __('crm.tomorrow') }}"
 data-label-tasks-later="{{ __('crm.later') }}"
 data-label-tasks-truncated="{{ __('crm.notification_tasks_truncated') }}"
 data-label-view-all="{{ __('crm.notification_view_all_tasks') }}"
 data-daily-tasks-url="{{ route('v2.tasks.daily', ['employee_id' => auth()->id()]) }}"
 data-birthdays-url="{{ route('v2.birthdays.index') }}"
>
 <div class="crm-notification-backdrop" data-notification-close hidden></div>
 <section
  class="crm-notification-drawer"
  id="crmNotificationDrawer"
  role="dialog"
  aria-modal="true"
  aria-labelledby="crmNotificationTitle"
  aria-hidden="true"
 >
  <header class="crm-notification-head">
   <div class="crm-notification-title-group">
    <h2 id="crmNotificationTitle">
     <i class="bi bi-bell-fill" style="color: #ec4899;" aria-hidden="true"></i>
     {{ __('crm.notifications') }}
     <span class="crm-attention-total-badge" id="crmNotificationTasksTotal" hidden>0</span>
    </h2>
    <p>{{ __('crm.notification_center_subtitle') }}</p>
   </div>
   <button class="crm-notification-close" type="button" data-notification-close aria-label="{{ __('crm.close') }}">
    <i class="bi bi-x-lg" aria-hidden="true"></i>
   </button>
  </header>
  @if (\App\Support\BirthdayModuleGuard::isEnabled())
  <div id="crmNotificationBirthdayBanner" class="crm-bday-drawer-banner" style="display:none; margin: 0 16px 12px; padding: 10px 14px; background: linear-gradient(135deg, #fdf2f8 0%, #fff 100%); border: 1px solid #fbcfe8; border-radius: 12px; font-size: 12.5px; color: #831843; align-items: center; justify-content: space-between; gap: 8px;">
   <div style="display:flex; align-items:center; gap:8px;">
    <svg class="crm-bday-svg-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none; display:inline-block; vertical-align:middle; flex-shrink:0;" aria-hidden="true">
     <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/>
     <path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/>
     <path d="M2 21h20"/>
     <path d="M7 8v2"/>
     <path d="M12 8v2"/>
     <path d="M17 8v2"/>
     <circle cx="7" cy="4" r="1.2" fill="#db2777"/>
     <circle cx="12" cy="4" r="1.2" fill="#db2777"/>
     <circle cx="17" cy="4" r="1.2" fill="#db2777"/>
    </svg>
    <span><strong id="bdayMonthCountText">0</strong> {{ __('crm.birthdays_this_month') ?? 'أعياد ميلاد هذا الشهر' }} (<span id="bdayTodayCountText">0</span> {{ __('اليوم') }})</span>
   </div>
   <a href="{{ route('v2.birthdays.index') }}" style="font-weight:800; font-size:11.5px; color:#db2777; text-decoration:none; padding:4px 10px; background:#fce7f3; border-radius:8px; border:1px solid #fbcfe8; white-space:nowrap;">
    {{ __('عرض الكل') }} &rarr;
   </a>
  </div>
  @endif

  <div class="crm-attention-filters" role="tablist" aria-label="{{ __('crm.notification_filter') }}">
   <button class="crm-attention-pill is-overdue" type="button" data-attention-filter="overdue" role="tab" aria-selected="false">
    <span class="pill-dot dot-overdue"></span>
    <span class="pill-label">{{ __('crm.overdue_short') }}</span>
    <span class="pill-count" id="countOverdue">0</span>
   </button>
   <button class="crm-attention-pill is-today active" type="button" data-attention-filter="today" role="tab" aria-selected="true">
    <span class="pill-dot dot-today"></span>
    <span class="pill-label">{{ __('crm.today') }}</span>
    <span class="pill-count" id="countToday">0</span>
   </button>
   <button class="crm-attention-pill is-tomorrow" type="button" data-attention-filter="tomorrow" role="tab" aria-selected="false">
    <span class="pill-dot dot-tomorrow"></span>
    <span class="pill-label">{{ __('crm.tomorrow') }}</span>
    <span class="pill-count" id="countTomorrow">0</span>
   </button>
   <button class="crm-attention-pill is-later" type="button" data-attention-filter="later" role="tab" aria-selected="false">
    <span class="pill-dot dot-later"></span>
    <span class="pill-label">{{ __('crm.later') }}</span>
    <span class="pill-count" id="countLater">0</span>
   </button>
   @if (\App\Support\BirthdayModuleGuard::isEnabled())
   <button class="crm-attention-pill is-birthdays" type="button" data-attention-filter="birthdays" role="tab" aria-selected="false" title="{{ __('crm.birthdays_this_month') ?? 'أعياد ميلاد هذا الشهر' }}">
    <span class="pill-dot dot-birthdays" style="background:#ec4899;"></span>
    <span class="pill-label"><i class="bi bi-cake2"></i> {{ __('crm.birthdays') ?? 'المواليد' }}</span>
    <span class="pill-count" id="countBirthdays" style="background:rgba(236,72,153,0.15); color:#db2777;">0</span>
   </button>
   @endif
  </div>

  <section class="crm-attention-content" aria-labelledby="crmNotificationTitle">
   <div class="crm-notification-task-list crm-attention-list" id="crmNotificationTaskList" aria-live="polite"></div>
  </section>

  <footer class="crm-attention-footer" id="crmAttentionFooter">
   <span class="crm-attention-summary" id="crmAttentionSummary"></span>
   <a href="{{ route('v2.tasks.daily', ['employee_id' => auth()->id()]) }}" class="crm-attention-view-all" id="crmAttentionViewAll">
    <span>{{ __('crm.notification_view_all_tasks') }}</span>
    <i class="bi bi-arrow-{{ app()->getLocale() === 'en' ? 'right' : 'left' }}"></i>
   </a>
  </footer>
 </section>

 <div class="crm-notification-toast" id="crmNotificationToast" role="status" aria-live="polite" hidden>
  <strong id="crmNotificationToastTitle"></strong>
  <span id="crmNotificationToastBody"></span>
 </div>
</div>
<script>
(() => {
    const el = document.getElementById('crmNotificationCenter');
    if (el && el.parentElement && el.parentElement !== document.body) {
        document.body.appendChild(el);
    }
})();
</script>

<script src="{{ asset('crm-notifications.js') }}?v={{ filemtime(public_path('crm-notifications.js')) }}" defer></script>
@endonce
