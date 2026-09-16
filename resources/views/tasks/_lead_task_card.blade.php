@php
    $statusColor = $lead->status?->color ?? '#64748b';
    $lastFollowup = $lead->latestFollowup ?? $lead->followups->first();
    $rawPhone = preg_replace('/[^0-9+]/', '', (string) $lead->phone);
    $waPhone = preg_replace('/[^0-9]/', '', (string) $lead->phone);

    $cardTimeClass = 'no-date';
    $cardUrgencyText = __('crm.no_date_badge');

    if ($lead->next_follow_up_at) {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        if ($lead->next_follow_up_at->lt($todayStart)) {
            $cardTimeClass = 'overdue';
            $cardUrgencyText = __('crm.task_overdue_by', ['time' => $lead->next_follow_up_at->diffForHumans(null, true)]);
        } elseif ($lead->next_follow_up_at->gt($todayEnd)) {
            $cardTimeClass = 'upcoming';
            $cardUrgencyText = __('crm.task_due_upcoming', ['time' => $lead->next_follow_up_at->diffForHumans(null, true)]);
        } else {
            $cardTimeClass = 'today';
            if ($lead->next_follow_up_at->isPast()) {
                $cardUrgencyText = __('crm.task_overdue_by', ['time' => $lead->next_follow_up_at->diffForHumans(null, true)]);
            } else {
                $cardUrgencyText = __('crm.task_due_in', ['time' => $lead->next_follow_up_at->diffForHumans(null, true)]);
            }
        }
    }
@endphp

<article class="task-item-card task-ticket {{ $cardTimeClass }}" data-lead-id="{{ $lead->id }}" data-lead-name="{{ $lead->name }}" data-status-id="{{ $lead->lead_status_id ?? '' }}" data-next-followup="{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->toISOString() : '' }}">
    {{-- 1. Top Badges & Timing Row --}}
    <div class="task-ticket-top">
        <div class="task-ticket-urgency">
            <span class="task-ticket-dot {{ $cardTimeClass }}"></span>
            <span class="task-ticket-time {{ $cardTimeClass }}">
                <i class="bi bi-clock"></i>
                {{ $lead->next_follow_up_at ? $lead->next_follow_up_at->format('d/m • h:i A') : __('crm.without_followup_date') }}
            </span>
            <span class="task-urgency-pill {{ $cardTimeClass }}">{{ $cardUrgencyText }}</span>
        </div>

        <div class="task-ticket-pills">
            @if ($lead->status?->stage?->category)
                <span class="task-category-pill" style="background: {{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'18' : '#f1f5f9' }}; color: {{ $lead->status->stage->category->color ?: '#475569' }}; border-color: {{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'33' : '#e2e8f0' }};" title="{{ __('crm.stage_category') }}: {{ $lead->status->stage->category->name_ar }}">
                    <i class="bi {{ $lead->status->stage->category->icon ?: 'bi-collection' }}"></i>
                    {{ $lead->status->stage->category->name_ar }}
                </span>
            @endif
            <span class="task-status-pill" style="background: {{ $statusColor }}18; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                {{ $lead->status?->name_ar ?? '-' }}
            </span>
        </div>
    </div>

    {{-- 2. Lead Identity & Meta Row (Source removed as requested) --}}
    <div class="task-ticket-main">
        <div class="task-ticket-lead-header">
            <h3 class="task-ticket-name">
                <a href="{{ route('v2.leads.show', $lead) }}">{{ $lead->name }}</a>
            </h3>
            @if ($lead->company_name)
                <span class="task-ticket-company"><i class="bi bi-building"></i> {{ $lead->company_name }}</span>
            @endif
        </div>

        <div class="task-ticket-submeta">
            @if ($lead->phone)
                <a class="task-phone-link" href="tel:{{ $rawPhone }}" title="{{ __('crm.phone') }}: {{ $lead->phone }}">
                    <i class="bi bi-telephone-fill"></i>
                    <span>{{ $lead->phone }}</span>
                </a>
            @else
                <span class="task-phone-empty">
                    <i class="bi bi-telephone-x"></i>
                    <span>{{ __('crm.not_registered') }}</span>
                </span>
            @endif

            @if ($lead->assignedUser || $lead->assigned_employee)
                <span class="task-ticket-meta-tag" title="{{ __('crm.lead_assigned_to') }}">
                    <i class="bi bi-person"></i>
                    <span>{{ $lead->assignedUser?->name ?? $lead->assigned_employee }}</span>
                </span>
            @endif
        </div>
    </div>

    {{-- 3. Last Followup Note Preview --}}
    @if ($lastFollowup && !empty($lastFollowup->outcome))
        <div class="task-ticket-note">
            <i class="bi bi-chat-left-text"></i>
            <div class="task-note-content">
                <p class="task-note-text" title="{{ $lastFollowup->outcome }}">{{ $lastFollowup->outcome }}</p>
                <span class="task-note-meta">
                    {{ $lastFollowup->followed_up_at ? $lastFollowup->followed_up_at->diffForHumans() : '' }}
                    @if ($lastFollowup->user?->name || $lastFollowup->employee_name)
                        • {{ $lastFollowup->user?->name ?? $lastFollowup->employee_name }}
                    @endif
                </span>
            </div>
        </div>
    @else
        <div class="task-ticket-note empty">
            <i class="bi bi-chat-left-dots"></i>
            <span class="task-note-empty">{{ __('crm.no_previous_followups') }}</span>
        </div>
    @endif

    {{-- 4. High-Efficiency Action Dock --}}
    <div class="task-ticket-actions">
        {{-- Communication Row (Call & WhatsApp) --}}
        <div class="task-action-comm-group">
            @if ($lead->phone)
                <a class="task-btn-comm call" href="tel:{{ $rawPhone }}" data-task-action="call-followup" onclick="openQuickFollowupModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', {{ $lead->lead_status_id ?? 'null' }}, 'call');" title="{{ __('crm.call_and_followup') }}">
                    <i class="bi bi-telephone-fill"></i>
                    <span>{{ __('crm.call_and_log') }}</span>
                </a>
                <a class="task-btn-comm whatsapp" href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" title="{{ __('crm.quick_whatsapp') }}">
                    <i class="bi bi-whatsapp"></i>
                    <span>واتساب</span>
                </a>
            @else
                <button class="task-btn-comm disabled" disabled title="{{ __('crm.no_phone_abbr') }}">
                    <i class="bi bi-telephone-x"></i>
                    <span>{{ __('crm.no_phone_abbr') }}</span>
                </button>
            @endif
        </div>

        {{-- Tools Row (Quick Followup, Reschedule, View Profile) --}}
        <div class="task-action-tools-group">
            <button class="task-tool-btn" type="button" data-task-action="quick-followup" onclick="openQuickFollowupModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', {{ $lead->lead_status_id ?? 'null' }})" title="{{ __('crm.quick_log_followup') }}">
                <i class="bi bi-pencil-square"></i>
                <span>متابعة</span>
            </button>
            <button class="task-tool-btn" type="button" data-task-action="reschedule" onclick="openRescheduleModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->toISOString() : '' }}')" title="{{ __('crm.reschedule_task') }}">
                <i class="bi bi-calendar-event"></i>
                <span>تأجيل</span>
            </button>
            <a class="task-tool-btn" href="{{ route('v2.leads.show', $lead) }}" title="{{ __('crm.view_lead') }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>تفاصيل</span>
            </a>
        </div>
    </div>
</article>
