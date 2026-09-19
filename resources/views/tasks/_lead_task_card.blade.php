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
            @if ($lead->branch)
                <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:3px;" title="{{ __('crm.branch') }}">
                    <i class="bi bi-geo-alt"></i> {{ $lead->branch->localizedName() }}
                </span>
            @endif
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
                    <svg class="task-btn-svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-1.57 1.97c-2.83-1.45-5.15-3.76-6.59-6.59l1.97-1.57c.28-.28.37-.68.25-1.02A11.36 11.36 0 0 1 8.57 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.62c0-.55-.45-1-1-1z"/>
                    </svg>
                    <span>{{ __('crm.call_and_log') }}</span>
                </a>
                <a class="task-btn-comm whatsapp" href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" title="{{ __('crm.quick_whatsapp') }}">
                    <svg class="task-btn-svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.41-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                    </svg>
                    <span>واتساب</span>
                </a>
            @else
                <button class="task-btn-comm disabled" disabled title="{{ __('crm.no_phone_abbr') }}">
                    <svg class="task-btn-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                        <path d="M9 9v.01M15 15v.01"></path>
                    </svg>
                    <span>{{ __('crm.no_phone_abbr') }}</span>
                </button>
            @endif
        </div>

        {{-- Tools Row (Quick Followup, Reschedule, View Profile) --}}
        <div class="task-action-tools-group">
            <button class="task-tool-btn followup" type="button" data-task-action="quick-followup" onclick="openQuickFollowupModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', {{ $lead->lead_status_id ?? 'null' }})" title="{{ __('crm.quick_log_followup') }}">
                <svg class="task-btn-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>متابعة</span>
            </button>
            <button class="task-tool-btn reschedule" type="button" data-task-action="reschedule" onclick="openRescheduleModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->toISOString() : '' }}')" title="{{ __('crm.reschedule_task') }}">
                <svg class="task-btn-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>تأجيل</span>
            </button>
            <a class="task-tool-btn details" href="{{ route('v2.leads.show', $lead) }}" title="{{ __('crm.view_lead') }}">
                <svg class="task-btn-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>تفاصيل</span>
            </a>
        </div>
    </div>
</article>
