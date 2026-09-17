@php
    $columnStageColor = $column['stage_color'] ?? ($column['status_color'] ?? '#3478f6');
    $columnStatusId = $column['status_id'] ?? $lead->lead_status_id;
    $columnStatusName = $column['name'] ?? ($column['status_name'] ?? '');
    $columnStageName = $column['stage_name'] ?? ($column['name'] ?? '');
    $cardScope = $scope ?? 'all';
    $canCreateFollowup = $canCreateFollowup ?? auth()->user()?->can('leads.followups.create');
    $phoneDigits = $lead->phone ? preg_replace('/[^0-9+]/', '', (string) $lead->phone) : '';
    $cardCustom = is_array($lead->custom_fields) ? $lead->custom_fields : [];
    $cardTemp = $cardCustom['lead_temperature'] ?? null;
@endphp
<article class="kanban-card" draggable="{{ $canCreateFollowup ? 'true' : 'false' }}" data-kanban-lead="{{ $lead->id }}" data-kanban-lead-name="{{ $lead->name }}" data-current-status-id="{{ $columnStatusId }}" data-current-status-name="{{ $columnStatusName }}" data-followup-url="{{ route('v2.leads.followups.index', $lead) }}" data-kanban-lead-scope="{{ $cardScope }}" style="--card-stage-color: {{ $columnStageColor }};">
 <div class="kanban-card-head">
  <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1;">
   <a class="kanban-card-name" href="{{ route('v2.leads.show', $lead) }}">{{ $lead->name }}</a>
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
  <span class="kanban-card-stage">{{ $columnStageName }}</span>
 </div>
 <div class="kanban-card-info">
  <div class="kanban-card-row">
   <span><i class="bi bi-telephone"></i> {{ __('crm.phone') }}</span>
   <strong>@if ($lead->phone)<a class="kanban-phone" href="tel:{{ $phoneDigits }}">{{ $lead->phone }}</a>@else{{ __('crm.not_registered') }}@endif</strong>
  </div>
  <div class="kanban-card-row">
   <span><i class="bi bi-building"></i> {{ __('crm.company_or_source') }}</span>
   <strong>{{ $lead->company_name ?: ($lead->source ?: __('crm.not_specified')) }}</strong>
  </div>
  <div class="kanban-card-row">
   <span><i class="bi bi-person-badge"></i> {{ __('crm.employee') }}</span>
   <strong>{{ $lead->assignedUser?->name ?? ($lead->assigned_employee ?: __('crm.unassigned')) }}</strong>
  </div>
  <div class="kanban-card-row">
   <span><i class="bi bi-clock-history"></i> {{ __('crm.followup_date') }}</span>
   <strong>{{ $lead->next_follow_up_at?->format('d/m/Y H:i') ?? __('crm.no_date') }}</strong>
  </div>
 </div>
 <div class="kanban-card-actions">
  @if ($lead->phone)
   <a class="btn call" @if ($canCreateFollowup) data-kanban-call-dial-popup @endif data-tel-href="tel:{{ $phoneDigits }}" href="tel:{{ $phoneDigits }}" draggable="false" title="{{ __('crm.call') }}"><i class="bi bi-telephone-outbound-fill"></i> {{ __('crm.call') }}</a>
  @endif
  <a class="btn light" href="{{ route('v2.leads.show', $lead) }}" data-kanban-customer-popup draggable="false"><i class="bi bi-eye"></i> {{ __('crm.view_lead') }}</a>
  @if ($canCreateFollowup)
   <a class="btn" href="{{ route('v2.leads.followups.index', $lead) }}" data-kanban-followup-popup draggable="false"><i class="bi bi-plus-circle"></i> {{ __('crm.log_followup') }}</a>
  @endif
 </div>
</article>
