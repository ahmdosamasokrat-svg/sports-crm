@php
    $columnStageColor = $column['stage_color'] ?? ($column['status_color'] ?? '#3478f6');
    $columnStatusId = $column['status_id'] ?? $lead->lead_status_id;
    $columnStatusName = $column['name'] ?? ($column['status_name'] ?? '');
    $columnStageName = $column['stage_name'] ?? ($column['name'] ?? '');
    $cardScope = $scope ?? 'all';
    $canCreateFollowup = $canCreateFollowup ?? auth()->user()?->can('leads.followups.create');
    $phoneDigits = $lead->phone ? preg_replace('/[^0-9+]/', '', (string) $lead->phone) : '';
    $initial = mb_substr(trim($lead->name ?: 'U'), 0, 1);
    
    // Custom fields & temperature
    $cardCustom = is_array($lead->custom_fields) ? $lead->custom_fields : [];
    $cardTemp = $cardCustom['lead_temperature'] ?? null;

    // Follow-up status calculation
    $followupState = 'none';
    if ($lead->next_follow_up_at) {
        if ($lead->next_follow_up_at->isPast()) {
            $followupState = 'overdue';
        } elseif ($lead->next_follow_up_at->isToday()) {
            $followupState = 'today';
        } else {
            $followupState = 'upcoming';
        }
    }
@endphp
<article class="kanban-card" draggable="{{ $canCreateFollowup ? 'true' : 'false' }}" data-kanban-lead="{{ $lead->id }}" data-kanban-lead-name="{{ $lead->name }}" data-current-status-id="{{ $columnStatusId }}" data-current-status-name="{{ $columnStatusName }}" data-followup-url="{{ route('v2.leads.followups.index', $lead) }}" data-kanban-lead-scope="{{ $cardScope }}" style="--card-stage-color: {{ $columnStageColor }};">
 
 <!-- HEADER: Avatar, Lead Name & Stage Pill -->
 <div class="kc-header">
  <div class="kc-avatar" title="{{ $lead->name }}">{{ $initial }}</div>
  <div class="kc-title-wrap">
   <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
    <a class="kanban-card-name kc-name" href="{{ route('v2.leads.show', $lead) }}" data-kanban-customer-popup title="{{ $lead->name }}">
     {{ $lead->name }}
    </a>
    @if ($lead->branch)
     <span class="badge" style="font-size:10px;padding:1px 5px;border-radius:4px;font-weight:700;flex-shrink:0;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;" title="{{ __('crm.branch') }}">
      <i class="bi bi-geo-alt"></i> {{ $lead->branch->localizedName() }}
     </span>
    @endif
    @if ($cardTemp)
     @php
         $cardTempStyle = match($cardTemp) {
             'hot' => 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;',
             'warm' => 'background:#fef3c7;color:#d97706;border:1px solid #fcd34d;',
             'cold' => 'background:#e0f2fe;color:#0284c7;border:1px solid #7dd3fc;',
             default => 'background:var(--bg);color:var(--muted);'
         };
         $cardTempLabel = match($cardTemp) {
             'hot' => '🔥 Hot',
             'warm' => '⚡ Warm',
             'cold' => '❄️ Cold',
             default => ucfirst((string) $cardTemp)
         };
     @endphp
     <span class="badge" style="font-size:10px;padding:1px 5px;border-radius:4px;font-weight:700;flex-shrink:0;{{ $cardTempStyle }}">{{ $cardTempLabel }}</span>
    @endif
   </div>
   @if ($lead->company_name || $lead->source)
    <span class="kc-source" title="{{ $lead->company_name ?: $lead->source }}">
     <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="9" y1="18" x2="9" y2="18.01"/><line x1="15" y1="18" x2="15" y2="18.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/></svg>
     <span>{{ $lead->company_name ?: $lead->source }}</span>
    </span>
   @endif
  </div>
 </div>
  </div>
 </div>

 <!-- DETAILS: Assigned User & Next Followup -->
 <div class="kc-meta-grid">
  <!-- Assigned User row -->
  <div class="kc-meta-item">
   <span class="kc-meta-icon" style="color:#64748b;">
    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
   </span>
   <span class="kc-meta-val" title="{{ $lead->assignedUser?->name ?? ($lead->assigned_employee ?: __('crm.unassigned')) }}">
    {{ $lead->assignedUser?->name ?? ($lead->assigned_employee ?: __('crm.unassigned')) }}
   </span>
  </div>

  <!-- Follow-up Time Badge -->
  @if ($lead->next_follow_up_at)
   <div class="kc-followup-badge kc-followup-{{ $followupState }}">
    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <span>{{ $lead->next_follow_up_at->format('d/m/Y h:i A') }}</span>
    @if ($followupState === 'overdue')
     <strong class="kc-badge-tag">{{ __('crm.overdue_short') ?? 'متأخر' }}</strong>
    @elseif ($followupState === 'today')
     <strong class="kc-badge-tag">{{ __('اليوم') }}</strong>
    @endif
   </div>
  @endif
 </div>

 <!-- ACTIONS BAR -->
 <div class="kanban-card-actions kc-actions">
  @if ($lead->phone)
   <a class="btn call kc-action-call" @if ($canCreateFollowup) data-kanban-call-dial-popup @endif data-tel-href="tel:{{ $phoneDigits }}" href="tel:{{ $phoneDigits }}" draggable="false" title="{{ __('crm.call') }}">
    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    <span>{{ __('crm.call') }}</span>
   </a>
  @endif

  @if ($canCreateFollowup)
   <a class="btn kc-action-followup" href="{{ route('v2.leads.followups.index', $lead) }}" data-kanban-followup-popup draggable="false" title="{{ __('crm.log_followup') }}">
    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    <span>{{ app()->getLocale() === 'en' ? 'Follow-up' : 'متابعة' }}</span>
   </a>
  @endif
 </div>

</article>
