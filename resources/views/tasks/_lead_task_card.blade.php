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
    {{-- Tier 1: Header Line (Status Dot + Time + Lead Name + Company + Status & Agent Pills) --}}
    <div class="task-ticket-header">
        <div class="task-ticket-lead">
            <span class="task-ticket-dot {{ $cardTimeClass }}" title="{{ $cardUrgencyText }}"></span>
            <span class="task-ticket-time {{ $cardTimeClass }}" title="{{ $cardUrgencyText }}">
                <i class="bi bi-clock"></i>
                {{ $lead->next_follow_up_at ? $lead->next_follow_up_at->format('h:i A') : __('crm.without_followup_date') }}
            </span>
            <strong class="task-ticket-name">
                <a href="{{ route('v2.leads.show', $lead) }}" title="{{ $lead->name }}">{{ $lead->name }}</a>
            </strong>
            @if ($lead->company_name)
                <span class="task-ticket-sep">•</span>
                <span class="task-ticket-company" title="{{ $lead->company_name }}">{{ $lead->company_name }}</span>
            @endif
        </div>

        <div class="task-ticket-badges">
            @if ($lead->status?->stage?->category)
                <span class="task-category-pill" style="background: {{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'18' : '#f1f5f9' }}; color: {{ $lead->status->stage->category->color ?: '#475569' }}; border-color: {{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'33' : '#e2e8f0' }};" title="{{ __('crm.stage_category') }}: {{ $lead->status->stage->category->name_ar }}">
                    <i class="bi {{ $lead->status->stage->category->icon ?: 'bi-collection' }}"></i>
                    {{ $lead->status->stage->category->name_ar }}
                </span>
            @endif
            <span class="task-status-pill" style="background: {{ $statusColor }}18; color: {{ $statusColor }}; border-color: {{ $statusColor }}33;">
                {{ $lead->status?->name_ar ?? '-' }}
            </span>
            @if ($lead->assignedUser || $lead->assigned_employee)
                <span class="task-ticket-user" title="{{ __('crm.lead_assigned_to') }}: {{ $lead->assignedUser?->name ?? $lead->assigned_employee }}">
                    <i class="bi bi-person-fill"></i>
                    <span>{{ $lead->assignedUser?->name ?? $lead->assigned_employee }}</span>
                </span>
            @endif
        </div>
    </div>

    {{-- Tier 2: Note / Context Preview (1 Line) --}}
    <div class="task-ticket-body">
        <div class="task-ticket-note">
            <i class="bi bi-chat-left-text"></i>
            @if ($lastFollowup && !empty($lastFollowup->outcome))
                <span class="task-note-text" title="{{ $lastFollowup->outcome }}">{{ $lastFollowup->outcome }}</span>
                <span class="task-note-meta">
                    ({{ $lastFollowup->followed_up_at ? $lastFollowup->followed_up_at->diffForHumans() : '' }}@if ($lastFollowup->user?->name || $lastFollowup->employee_name) • {{ $lastFollowup->user?->name ?? $lastFollowup->employee_name }}@endif)
                </span>
            @else
                <span class="task-note-empty">{{ __('crm.no_previous_followups') }}</span>
            @endif
        </div>
    </div>

    {{-- Tier 3: Contact Details & Quick Actions --}}
    <div class="task-ticket-footer">
        <div class="task-ticket-contact">
            @if ($lead->phone)
                <a class="task-phone-link" href="tel:{{ $rawPhone }}" title="{{ __('crm.phone') }}: {{ $lead->phone }}">
                    <i class="bi bi-telephone"></i>
                    <span>{{ $lead->phone }}</span>
                </a>
            @else
                <span class="task-phone-empty">
                    <i class="bi bi-telephone-x"></i>
                    <span>{{ __('crm.not_registered') }}</span>
                </span>
            @endif

            @if ($lead->source)
                <span class="task-ticket-source" title="{{ __('crm.source') }}: {{ $lead->source }}">
                    <i class="bi bi-tag"></i>
                    <span>{{ $lead->source }}</span>
                </span>
            @endif
        </div>

        <div class="task-ticket-actions">
            @if ($lead->phone)
                <a class="task-action-btn primary" href="tel:{{ $rawPhone }}" data-task-action="call-followup" onclick="openQuickFollowupModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', {{ $lead->lead_status_id ?? 'null' }}, 'call');" title="{{ __('crm.call_and_followup') }}">
                    <i class="bi bi-telephone-fill"></i>
                    <span>{{ __('crm.call_and_log') }}</span>
                </a>
                <a class="task-action-btn whatsapp" href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" title="{{ __('crm.quick_whatsapp') }}">
                    <i class="bi bi-whatsapp"></i>
                </a>
            @else
                <button class="task-action-btn primary disabled" disabled title="{{ __('crm.no_phone_abbr') }}">
                    <i class="bi bi-telephone-x"></i>
                    <span>{{ __('crm.no_phone_abbr') }}</span>
                </button>
            @endif

            <button class="task-action-btn icon" type="button" data-task-action="quick-followup" onclick="openQuickFollowupModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', {{ $lead->lead_status_id ?? 'null' }})" title="{{ __('crm.quick_log_followup') }}">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button class="task-action-btn icon" type="button" data-task-action="reschedule" onclick="openRescheduleModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->toISOString() : '' }}')" title="{{ __('crm.reschedule_task') }}">
                <i class="bi bi-calendar-plus"></i>
            </button>
            <a class="task-action-btn icon" href="{{ route('v2.leads.show', $lead) }}" title="{{ __('crm.view_lead') }}">
                <i class="bi bi-eye"></i>
            </a>
        </div>
    </div>
</article>
