<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ __('crm.app_name', ['default' => 'SokratCRM']) }} — {{ __('crm.followup_for_lead', ['name' => $lead->name]) }}</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v=1.0.0">
<script>
(() => {
    try {
        const theme = localStorage.getItem('sokrat.crm.theme');
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    } catch (e) {}
})();
</script>

<style>
:root {
  --red: #ef4444;
  --red-hover: #dc2626;
  --dark: #182033;
  --muted: #64748b;
  --line: #e2e8f0;
  --bg: #f8fafc;
  --card: #ffffff;
  --shadow: none;
  --radius: 16px;
  --font-primary: 'Plus Jakarta Sans', 'Cairo', sans-serif;
  --font-mono: 'JetBrains Mono', 'Plus Jakarta Sans', 'Cairo', monospace;
}

html.dark-mode {
  --dark: #f1f5f9;
  --muted: #94a3b8;
  --line: #334155;
  --bg: #0f172a;
  --card: #1e293b;
  --shadow: none;
}

* { box-sizing: border-box; }
body {
  margin: 0;
  min-width: 320px;
  background: var(--bg);
  color: var(--dark);
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  font-size: 14px;
  line-height: 1.5;
}
button, input, select, textarea { font: inherit; }
a { color: inherit; text-decoration: none; }

.crm-app { display: flex; min-height: 100vh; }
.crm-main { flex: 1; min-width: 0; padding: 24px 32px 60px; }

.top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 44px;
  min-height: 44px;
  padding: 0 18px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: var(--card);
  color: var(--dark);
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s ease;
  font-size: 13px;
  white-space: nowrap;
}
.btn:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
  transform: translateY(-1px);
}
html.dark-mode .btn {
  background: rgba(255, 255, 255, 0.05);
  border-color: var(--line);
  color: var(--dark);
}
html.dark-mode .btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: #475569;
}
.btn.primary {
  background: var(--red);
  border-color: var(--red);
  color: #fff;
  box-shadow: 0 4px 14px rgba(220, 38, 55, 0.25);
}
.btn.primary:hover {
  background: var(--red-hover);
  border-color: var(--red-hover);
  color: #fff;
}
.btn.soft {
  background: #f1f5f9;
  border-color: transparent;
  color: #334155;
}
html.dark-mode .btn.soft {
  background: rgba(255, 255, 255, 0.08);
  color: #f1f5f9;
}
.btn.soft:hover {
  background: #e2e8f0;
}
.btn.small {
  height: 38px;
  min-height: 38px;
  padding: 0 12px;
  font-size: 12px;
  border-radius: 10px;
}
.topbar-left .btn.small {
  width: 44px;
  height: 44px;
  min-height: 44px;
  padding: 0;
  display: inline-grid;
  place-items: center;
  border-radius: 12px;
}

/* Client Identity Card */
.client-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px;
  margin-bottom: 24px;
}
.client-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--line);
}
.client-identity {
  display: flex;
  align-items: center;
  gap: 16px;
}
.client-avatar {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: #fef2f2;
  color: var(--red);
  display: grid;
  place-items: center;
  font-size: 22px;
  font-weight: 900;
  flex-shrink: 0;
}
.client-copy h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 900;
  color: var(--dark);
}
.client-copy p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.client-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* Client Quick Data Grid */
.client-data {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 12px;
  margin-top: 18px;
}
.client-data-item {
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 10px 14px;
}
.client-data-item small {
  display: block;
  font-size: 11px;
  font-weight: 800;
  color: var(--muted);
  margin-bottom: 3px;
}
.client-data-item strong {
  display: block;
  font-size: 13px;
  font-weight: 900;
  color: var(--dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Workspace Layout (2 Columns) */
.workspace {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 24px;
  align-items: start;
}

/* Panels */
.panel {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-bottom: 20px;
}
.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--line);
  background: var(--bg);
}
.panel-head h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 900;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 8px;
}
.panel-head p {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  font-weight: 700;
}
.panel-body {
  padding: 20px;
}

/* Form Elements */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field.full {
  grid-column: 1 / -1;
}
.field label {
  font-size: 12px;
  font-weight: 800;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 4px;
}
.field label .required {
  color: var(--red);
}
.control {
  width: 100%;
  min-height: 40px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 8px 12px;
  background: var(--card);
  color: var(--dark);
  font-size: 13px;
  font-weight: 600;
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
textarea.control {
  min-height: 100px;
  resize: vertical;
  line-height: 1.6;
}
.control:focus {
  border-color: var(--red);
  box-shadow: 0 0 0 2px rgba(220, 38, 55, 0.12);
}
html.dark-mode .control {
  background: rgba(30, 41, 59, 0.6);
  border-color: var(--line);
  color: var(--dark);
}
.field small {
  color: var(--muted);
  font-size: 11px;
}

/* Communication Channel Selector */
.channel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: 8px;
}
.channel-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 8px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: var(--bg);
  cursor: pointer;
  transition: all 0.15s ease;
  user-select: none;
  font-size: 12px;
  font-weight: 800;
  color: var(--dark);
  text-align: center;
}
.channel-card i {
  font-size: 18px;
  color: var(--muted);
  transition: color 0.15s ease;
}
.channel-card:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}
.channel-card.active {
  border-color: var(--red);
  background: #fef2f2;
  color: var(--red);
}
.channel-card.active i {
  color: var(--red);
}
html.dark-mode .channel-card.active {
  background: rgba(220, 38, 55, 0.15);
  border-color: rgba(220, 38, 55, 0.4);
}

/* Sub-sections / Accordions */
.followup-stage-editor {
  margin-top: 18px;
  padding-top: 18px;
  border-top: 1px dashed var(--line);
}
.followup-stage-editor.is-hidden {
  display: none !important;
}
.followup-editor-head {
  margin-bottom: 12px;
}
.followup-editor-head h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 900;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 6px;
}
.followup-editor-head p {
  margin: 2px 0 0;
  font-size: 11px;
  color: var(--muted);
}
.followup-editor-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.followup-detail-panel.is-hidden {
  display: none !important;
}

/* Quick presets for next followup */
.presets-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  flex-wrap: wrap;
}
.preset-chip {
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid var(--line);
  background: var(--bg);
  color: var(--muted);
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.preset-chip:hover {
  border-color: var(--dark);
  color: var(--dark);
}

/* Timeline */
.timeline {
  position: relative;
  padding-inline-start: 24px;
  margin: 0;
}
.timeline::before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  inset-inline-start: 7px;
  width: 2px;
  background: var(--line);
}
.timeline-item {
  position: relative;
  margin-bottom: 18px;
}
.timeline-dot {
  position: absolute;
  inset-inline-start: -24px;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--red);
  border: 3px solid #fff;
  box-shadow: 0 0 0 2px var(--line);
}
.timeline-card {
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 14px 16px;
}
.timeline-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}
.timeline-employee {
  font-weight: 800;
  color: var(--dark);
  font-size: 13px;
}
.timeline-date {
  color: var(--muted);
  font-size: 12px;
  font-weight: 700;
}
.timeline-body {
  color: #334155;
  font-size: 13px;
  line-height: 1.6;
}
html.dark-mode .timeline-body { color: #d4d4d8; }
.timeline-badge {
  display: inline-flex;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 800;
  background: #e0f2fe;
  color: #0369a1;
  margin-inline-start: 6px;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  background: #f1f5f9;
  color: #475569;
}
.badge.active { background: #dcfce7; color: #166534; }

/* Alerts */
.flash-success {
  padding: 12px 16px;
  background: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 700;
}
.error-box {
  padding: 14px 18px;
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 12px;
  margin-bottom: 20px;
}
.error-box strong { display: block; margin-bottom: 6px; }
.error-box ul { margin: 0; padding-inline-start: 20px; }

/* Kanban popup embedded mode */
body.kanban-followup-popup {
  background: transparent !important;
}
body.kanban-followup-popup .crm-app {
  min-height: auto;
  display: block;
}
body.kanban-followup-popup .crm-main {
  padding: 14px;
}
body.kanban-followup-popup .crm-topbar,
body.kanban-followup-popup .topbar,
body.kanban-followup-popup .latest-followups-panel,
body.kanban-followup-popup .crm-side {
  display: none !important;
}
body.kanban-followup-popup .workspace {
  grid-template-columns: 1fr;
}
body.kanban-followup-popup .client-card {
  margin-bottom: 14px;
  box-shadow: none;
}
body.kanban-followup-popup .client-actions {
  display: none !important;
}

@media(max-width: 1024px) {
  .workspace { grid-template-columns: 1fr; }
}
@media(max-width: 768px) {
  .crm-main { padding: 16px 12px 60px; min-width: 0; width: 100%; max-width: 100%; }
  .top-actions { width: 100%; flex-wrap: wrap; gap: 8px; }
  .top-actions .btn { flex: 1 1 auto; min-height: 44px; }
  .form-grid, .followup-editor-grid { grid-template-columns: 1fr; }
  .client-head { flex-direction: column; align-items: stretch; gap: 14px; }
  .client-actions { width: 100%; justify-content: stretch; flex-wrap: wrap; gap: 8px; }
  .client-actions .btn { flex: 1 1 auto; min-height: 44px; }
}
</style>
</head>
<body class="{{ request()->boolean('kanban_popup') ? 'kanban-followup-popup' : '' }}">
@include('partials.page-loader')
<div class="crm-app lead-followups-page">
    @include('partials.crm-sidebar')

    <main class="crm-main">
        @php
            $followupTopActions = '<a href="' . route('v2.leads.show', $lead) . '" class="btn soft"><i class="bi bi-eye"></i> ' . __('crm.view_lead_data') . '</a>';
            if ($callPhone) {
                $followupTopActions .= '<a href="tel:' . $callPhone . '" class="btn primary"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="vertical-align:middle;flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> ' . __('crm.call_action') . '</a>';
            }
            $visibleCustomerAttributes = ($customerFields ?? collect())->keyBy('lead_attribute');
        @endphp

        @include('partials.topbar', [
            'title' => __('crm.followup_for_lead', ['name' => $lead->name]),
            'subtitle' => '<span>' . __('crm.lead_code_label') . ': #' . $lead->id . '</span> <span style="margin:0 6px">•</span> <span>' . __('crm.registered_at') . ': ' . ($lead->created_at?->format('Y-m-d') ?? '—') . '</span>',
            'icon' => 'bi-chat-dots-fill',
            'backUrl' => route('v2.leads.show', $lead),
            'backTitle' => __('crm.lead_details'),
            'actions' => $followupTopActions,
        ])

        <!-- CLIENT IDENTITY & OVERVIEW CARD -->
        <section class="client-card">
            <div class="client-head">
                <div class="client-identity">
                    <div class="client-avatar">
                        {{ mb_substr((string) $lead->name, 0, 1) }}
                    </div>
                    <div class="client-copy">
                        <h2>{{ $lead->name }}</h2>
                        <p>
                            @if ($visibleCustomerAttributes->has('company_name') && $lead->company_name)
                                <span><i class="bi bi-building"></i> {{ $lead->company_name }}</span>
                                <span>•</span>
                            @endif
                            @if ($visibleCustomerAttributes->has('governorate') && $lead->governorate)
                                <span><i class="bi bi-geo-alt"></i> {{ $lead->governorate }}</span>
                                <span>•</span>
                            @endif
                            <span><i class="bi bi-person-badge"></i> {{ $lead->assignedUser?->name ?? $lead->assigned_employee ?? __('crm.unassigned') }}</span>
                        </p>
                    </div>
                </div>

                <div class="client-actions">
                    <a href="{{ route('v2.leads.show', $lead) }}" class="btn soft small">
                        <i class="bi bi-person"></i> {{ __('crm.view_lead_data') }}
                    </a>
                    @if ($callPhone)
                        <a href="tel:{{ $callPhone }}" class="btn soft small" style="color:#2563eb">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="vertical-align:middle;flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> {{ __('crm.call_action') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="client-data">
                <div class="client-data-item">
                    <small>{{ __('crm.phone') }}</small>
                    <strong dir="ltr">
                        @if ($callPhone)
                            <a href="tel:{{ $callPhone }}" style="color:inherit">{{ $lead->phone }}</a>
                        @else
                            {{ $lead->phone ?: '—' }}
                        @endif
                    </strong>
                </div>
                <div class="client-data-item">
                    <small>{{ __('crm.email') }}</small>
                    <strong>{{ $lead->email ?: '—' }}</strong>
                </div>
                @if ($activityField = $visibleCustomerAttributes->get('activity'))
                    <div class="client-data-item">
                        <small>{{ $activityField->localizedLabel() }}</small>
                        <strong>{{ $lead->activity ?: '—' }}</strong>
                    </div>
                @endif
                <div class="client-data-item">
                    <small>{{ __('crm.current_stage') }}</small>
                    <strong>{{ $lead->status?->stage?->localizedName() ?? ($lead->status?->stage?->name_ar ?? '—') }}</strong>
                </div>
                <div class="client-data-item">
                    <small>{{ __('crm.current_status') }}</small>
                    <strong style="color:{{ $lead->status?->color ?? 'inherit' }}">{{ $lead->status?->name_ar ?? '—' }}</strong>
                </div>
                <div class="client-data-item">
                    <small>{{ __('crm.responsible_employee') }}</small>
                    <strong>{{ $lead->assignedUser?->name ?? $lead->assigned_employee ?: __('crm.unassigned') }}</strong>
                </div>
                <div class="client-data-item">
                    <small>{{ __('crm.next_followup') }}</small>
                    <strong>{{ $lead->next_follow_up_at ? $lead->next_follow_up_at->format('d/m/Y - h:i A') : '—' }}</strong>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="flash-success">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-box">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> {{ __('crm.review_followup_data') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="workspace">
            <!-- MAIN FORM COLUMN -->
            @can('leads.followups.create')
            <section class="panel">
                <div class="panel-head">
                    <h3><i class="bi bi-pencil-square"></i> {{ __('crm.new_followup_data') }}</h3>
                    <p><i class="bi bi-person-check"></i> الموظف المسجل: <strong>{{ $currentEmployee }}</strong></p>
                </div>

                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('v2.leads.followups.store', $lead, false) }}" id="followupForm">
                        @csrf

                        @if (request()->boolean('kanban_popup'))
                            <input type="hidden" name="kanban_popup" value="1">
                        @endif

                        @php
                            $selectedStatusId = (int) old('lead_status_id', $defaultStatusId ?? $lead->lead_status_id);
                            $selectedCommunicationType = (string) old('communication_type', $defaultCommunicationType);
                        @endphp

                        <div class="form-grid">
                            <!-- PIPELINE FILTER & STATUS/STAGE -->
                            @if (isset($categories) && $categories->isNotEmpty())
                                <div class="field">
                                    <label for="pipeline_filter">
                                        <i class="bi bi-diagram-3" style="color:var(--red, #ef4444); margin-inline-end:4px;"></i>
                                        {{ __('crm.sales_pipeline') }}
                                    </label>
                                    <select
                                        class="control"
                                        id="pipeline_filter"
                                        @if (request()->boolean('kanban_popup')) disabled @endif
                                    >
                                        <option value="all">{{ __('crm.all_stage_categories') }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) $selectedCategoryId === (string) $cat->id)>
                                                {{ $cat->localizedName() }}
                                            </option>
                                        @endforeach
                                        @if ($hasUncategorizedStages)
                                            <option value="uncategorized" @selected((string) $selectedCategoryId === 'uncategorized')>
                                                {{ __('crm.unassigned_stages_count') ?? 'مراحل غير مصنفة' }}
                                            </option>
                                        @endif
                                    </select>
                                    <small>{{ __('اختر المسار لتصفية المراحل المعروضة.') }}</small>
                                </div>
                                <div class="field">
                            @else
                                <div class="field full">
                            @endif
                                <label for="lead_status_id">
                                    {{ __('crm.status_stage') }} <span class="required">*</span>
                                </label>
                                <select
                                    class="control"
                                    id="lead_status_id"
                                    name="lead_status_id"
                                    required
                                    @if (request()->boolean('kanban_popup')) disabled @endif
                                >
                                    @foreach ($statusGroups as $stageName => $stageStatuses)
                                        @php
                                            $firstSt = $stageStatuses->first();
                                            $stageCatId = $firstSt?->stage?->pipeline_stage_category_id ? (string) $firstSt->stage->pipeline_stage_category_id : 'uncategorized';
                                        @endphp
                                        <optgroup label="{{ $stageName }}" data-category-id="{{ $stageCatId }}">
                                            @foreach ($stageStatuses as $status)
                                                @php
                                                    $stgId = (int) $status->pipeline_stage_id;
                                                    $transferRule = $stageTransferMap[$stgId] ?? null;
                                                    $effectiveStageId = $stgId;
                                                    $isFunnelHandoff = false;
                                                    if ($transferRule && ($transferRule['trigger_status_id'] === null || (int)$transferRule['trigger_status_id'] === (int)$status->id)) {
                                                        $effectiveStageId = (int) $transferRule['target_stage_id'];
                                                        $isFunnelHandoff = true;
                                                    }
                                                @endphp
                                                <option
                                                    value="{{ $status->id }}"
                                                    data-category-id="{{ $status->stage?->pipeline_stage_category_id ? (string) $status->stage->pipeline_stage_category_id : 'uncategorized' }}"
                                                    data-stage-id="{{ $status->pipeline_stage_id }}"
                                                    data-effective-stage-id="{{ $effectiveStageId }}"
                                                    data-is-funnel="{{ $isFunnelHandoff ? '1' : '0' }}"
                                                    data-target-pipeline="{{ $transferRule['target_pipeline_name'] ?? '' }}"
                                                    data-target-stage-name="{{ $transferRule['target_stage_name'] ?? '' }}"
                                                    data-stage-name="{{ $status->stage?->name_ar ?? '----' }}"
                                                    data-stage-description="{{ $status->stage?->description_ar ?? '----' }}"
                                                    data-stage-code="{{ $status->stage?->code ?? '----' }}"
                                                    data-stage-position="{{ $status->stage?->position ?? '----' }}"
                                                    data-stage-color="{{ $status->stage?->color ?? '#64748b' }}"
                                                    data-status-name="{{ $status->name_ar }}"
                                                    data-status-code="{{ $status->code }}"
                                                    data-status-color="{{ $status->color ?? '#64748b' }}"
                                                    data-terminal="{{ $status->is_terminal ? '1' : '0' }}"
                                                    @selected($selectedStatusId === (int) $status->id)
                                                >
                                                    {{ $status->name_ar }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>

                                @if (request()->boolean('kanban_popup'))
                                    <input type="hidden" name="lead_status_id" value="{{ $selectedStatusId }}">
                                @endif
                                <small>
                                    @if (request()->boolean('kanban_popup'))
                                        الحالة الجديدة محددة تلقائيًا من العمود الذي تم نقل العميل إليه.
                                    @else
                                        اختيار الحالة يحدد المرحلة الجديدة للعميل.
                                    @endif
                                </small>
                            </div>
                            <!-- DYNAMIC STAGE QUESTIONS CONTAINER -->
                            @php
                                $statusesCol = collect($statuses ?? []);
                                $selectedStatus = $statusesCol->firstWhere('id', $selectedStatusId);
                                $selStgId = (int) ($selectedStatus?->pipeline_stage_id ?? $lead->status?->pipeline_stage_id);
                                $selectedTransfer = $stageTransferMap[$selStgId] ?? null;
                                $selectedStageId = ($selectedTransfer && ($selectedTransfer['trigger_status_id'] === null || (int)$selectedTransfer['trigger_status_id'] === (int)$selectedStatusId))
                                    ? (int) $selectedTransfer['target_stage_id']
                                    : $selStgId;
                            @endphp
                            <div id="dynamicStageQuestionsSection" class="field full" style="margin-top:4px;">
                                <div id="funnelHandoffNotice" style="display:none; background:rgba(79, 70, 229, 0.08); border:1px solid rgba(79, 70, 229, 0.25); border-radius:12px; padding:12px 16px; margin-bottom:14px;">
                                    <div style="display:flex; align-items:center; gap:8px; font-weight:800; font-size:13px; color:#4f46e5;">
                                        <i class="bi bi-lightning-charge-fill"></i>
                                        <span id="funnelNoticeTitle">{{ __('مرحلة ترحيل تلقائي إلى مسار آخر') }}</span>
                                    </div>
                                    <p id="funnelNoticeDesc" style="margin:4px 0 0; font-size:12px; color:var(--dark);">
                                        {{ __('هذه المرحلة تعتبر نقطة عبور؛ سيتم نقل / استنساخ العميل واستكمال أسئلة المرحلة المستهدفة أدناه مباشرة.') }}
                                    </p>
                                </div>

                                @foreach (($activeStages ?? []) as $astage)
                                    @if ($astage->activeFields->isNotEmpty())
                                        @php
                                            $isTargetStage = (int) $astage->id === (int) $selectedStageId;
                                        @endphp
                                        <div class="stage-questions-block" id="stage_q_block_{{ $astage->id }}" data-stage-id="{{ $astage->id }}" style="{{ $isTargetStage ? 'display:block;' : 'display:none;' }} background:var(--bg); border:1px solid var(--line); border-radius:12px; padding:16px; margin-bottom:14px;">
                                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px; font-weight:800; font-size:14px; color:var(--dark);">
                                                <i class="bi bi-ui-checks" style="color:var(--red);"></i>
                                                <span>{{ __('crm.stage_questions') }} ({{ $astage->localizedName() }})</span>
                                                @if ($astage->category)
                                                    <span class="badge" style="font-size:11px; background:{{ $astage->category->color }}1a; color:{{ $astage->category->color }}; border:1px solid {{ $astage->category->color }}33;">
                                                        {{ $astage->category->localizedName() }}
                                                    </span>
                                                @endif
                                            </div>
                                            @php
                                                $prefilled = \App\Support\StageFieldSchema::prefillValues($lead, $astage);
                                                $mergedValues = array_merge($prefilled, old('stage_fields', []));
                                            @endphp
                                            @include('partials.stage-field-inputs', [
                                                'fields' => $astage->activeFields,
                                                'recordValues' => $mergedValues,
                                                'prefix' => 'stage_fields',
                                                'scope' => 'followup_' . $astage->id,
                                                'disabled' => ! $isTargetStage,
                                            ])
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- CUSTOMER DATA FIELDS (IF CONFIGURED - HIDDEN ON STAGE TRANSITION) -->
                            @if (!request()->boolean('kanban_popup') && ($customerFields ?? collect())->isNotEmpty())
                                <div class="field full" style="margin-top: 14px;">
                                    <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--dark);">
                                        <i class="bi bi-card-checklist" style="color: #0ea5e9;"></i> {{ __('crm.customer_data') ?: 'بيانات وتصنيف العميل' }}
                                    </h3>
                                    @foreach ($customerFields as $customerField)
                                        <input type="hidden" name="customer_field_presence[]" value="{{ $customerField->key }}">
                                    @endforeach
                                    @include('partials.stage-field-inputs', [
                                        'fields' => $customerFields,
                                        'recordValues' => $customerFieldValues ?? [],
                                        'prefix' => 'customer_fields',
                                        'scope' => 'followup_customer',
                                    ])
                                </div>
                            @endif

                            <!-- CAMPAIGN SELECTION (IF APPLICABLE) -->
                            @if ($manageableCampaigns->isNotEmpty())
                                <div class="field">
                                    <label for="followupCampaign"><i class="bi bi-megaphone"></i> {{ __('crm.campaign') }}</label>
                                    <select class="control" id="followupCampaign" name="campaign_id">
                                        <option value="" @selected(empty(old('campaign_id')))>{{ __('crm.no_campaign_change') }}</option>
                                        @foreach ($manageableCampaigns as $campaignOption)
                                            <option
                                                value="{{ $campaignOption->id }}"
                                                data-user-ids="{{ implode(',', $campaignOption->users->modelKeys()) }}"
                                                @selected((int) old('campaign_id', 0) === (int) $campaignOption->id)
                                            >
                                                {{ $campaignOption->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="followupCampaignAssignee"><i class="bi bi-person-check"></i> {{ __('crm.assigned_employee') }}</label>
                                    <select class="control" id="followupCampaignAssignee" name="assigned_user_id">
                                        <option value="">{{ __('crm.keep_current_assignee') }}</option>
                                        @foreach ($campaignAssignees as $campaignAssignee)
                                            <option
                                                value="{{ $campaignAssignee->id }}"
                                                @selected((int) old('assigned_user_id', $lead->assigned_user_id) === (int) $campaignAssignee->id)
                                            >
                                                {{ $campaignAssignee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- COMMUNICATION TYPE SELECTOR -->
                            <div class="field full">
                                <label>{{ __('crm.communication_type') }} <span class="required">*</span></label>
                                <input type="hidden" name="communication_type" id="communicationTypeInput" value="{{ $selectedCommunicationType }}">
                                <div class="channel-grid">
                                    @foreach ($communicationTypes as $commKey => $commLabel)
                                        @php
                                            $commIcon = match($commKey) {
                                                'call' => 'bi-telephone',
                                                'whatsapp' => 'bi-whatsapp',
                                                'meeting' => 'bi-people',
                                                'email' => 'bi-envelope',
                                                default => 'bi-clock-history',
                                            };
                                        @endphp
                                        <div
                                            class="channel-card {{ $selectedCommunicationType === $commKey ? 'active' : '' }}"
                                            data-channel="{{ $commKey }}"
                                            onclick="selectChannel('{{ $commKey }}')"
                                        >
                                            <i class="bi {{ $commIcon }}"></i>
                                            <span>{{ $commLabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- CALL & CONTACT ATTEMPT DETAILS (Appears when Call or general contact is selected) -->
                            <div class="field full" id="callDetailsSection" style="{{ $selectedCommunicationType === 'call' ? '' : 'display:none;' }}">
                                <div style="background:var(--bg); border:1px solid var(--line); border-radius:12px; padding:14px 16px; margin-bottom:4px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                        <span style="font-size:13px; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:6px;">
                                            <i class="bi bi-telephone-outbound" style="color:var(--red);"></i>
                                            تفاصيل محاولة الاتصال
                                        </span>
                                        <span class="badge" style="background:rgba(220, 38, 38, 0.1); color:var(--red); font-weight:800; padding:4px 8px; border-radius:6px; font-size:11px;">
                                            المحاولة رقم: <strong id="attemptNumberBadge">{{ $nextAttemptNumber ?? 1 }}</strong>
                                        </span>
                                    </div>
                                    <div class="form-grid">
                                        <div class="field">
                                            <label for="callStatusSelect">
                                                حالة الاتصال
                                            </label>
                                            <select class="control" id="callStatusSelect" name="call_status">
                                                <option value="">-- اختر حالة الاتصال --</option>
                                                @foreach ($callStatuses ?? [] as $csKey => $csLabel)
                                                    <option value="{{ $csKey }}" {{ old('call_status') === $csKey ? 'selected' : '' }}>
                                                        {{ $csLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for="outcomeCategorySelect">
                                                نتيجة التواصل المباشرة
                                            </label>
                                            <select class="control" id="outcomeCategorySelect" name="outcome_category">
                                                <option value="">-- اختر نتيجة التواصل --</option>
                                                @foreach ($outcomeCategories ?? [] as $ocKey => $ocLabel)
                                                    <option value="{{ $ocKey }}" {{ old('outcome_category') === $ocKey ? 'selected' : '' }}>
                                                        {{ $ocLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- OUTCOME NOTES (HIDDEN ON STAGE TRANSITION, OPTIONAL / STANDALONE REQUIRED) -->
                            @if (!request()->boolean('kanban_popup'))
                                <div class="field full">
                                    <label for="followupOutcome">
                                        {{ __('crm.followup_notes') }} <span class="required">*</span>
                                    </label>
                                    <textarea
                                        class="control"
                                        id="followupOutcome"
                                        name="outcome"
                                        rows="4"
                                        required
                                        placeholder="{{ __('crm.followup_notes_placeholder') }}"
                                    >{{ old('outcome') }}</textarea>
                                </div>
                            @endif

                            <!-- NEXT FOLLOWUP DATE -->
                            <div class="field full">
                                <label for="nextFollowUpAt">
                                    {{ __('crm.next_followup_date') }}
                                </label>
                                <input
                                    class="control"
                                    id="nextFollowUpAt"
                                    type="datetime-local"
                                    name="next_follow_up_at"
                                    value="{{ old('next_follow_up_at', $lead->next_follow_up_at ? $lead->next_follow_up_at->format('Y-m-d\TH:i') : '') }}"
                                >
                                <div class="presets-wrap">
                                    <span style="font-size:11px;color:var(--muted);font-weight:700">اقتراحات سريعة:</span>
                                    <button type="button" class="preset-chip" onclick="setNextDate(1, 10)">غداً 10:00 ص</button>
                                    <button type="button" class="preset-chip" onclick="setNextDate(3, 11)">بعد 3 أيام</button>
                                    <button type="button" class="preset-chip" onclick="setNextDate(7, 10)">بعد أسبوع</button>
                                </div>
                                <small>{{ __('crm.next_followup_hint') }}</small>
                            </div>
                        </div>

                        <!-- FORM ACTION BUTTONS -->
                        <div style="display:flex;align-items:center;gap:12px;margin-top:24px;padding-top:18px;border-top:1px solid var(--line)">
                            <button type="submit" class="btn primary" style="height:44px;min-height:44px;padding:0 24px;font-size:14px">
                                <i class="bi bi-check-lg"></i> {{ __('crm.save_followup') }}
                            </button>
                            @if (request()->boolean('kanban_popup'))
                                <button type="button" class="btn soft" id="kanbanPopupCancelBtn" style="height:44px;min-height:44px" onclick="cancelKanbanPopup()">
                                    {{ __('crm.cancel') }}
                                </button>
                            @else
                                <a href="{{ route('v2.leads.show', $lead) }}" class="btn soft" style="height:44px;min-height:44px">
                                    {{ __('crm.cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </section>
            @endcan

            <!-- PREVIOUS FOLLOWUPS TIMELINE -->
            <section class="panel latest-followups-panel">
                <div class="panel-head">
                    <h3><i class="bi bi-clock-history"></i> {{ __('crm.latest_followups') }}</h3>
                    <span class="badge">{{ $followups->count() }}</span>
                </div>

                <div class="panel-body">
                    @if ($followups->isNotEmpty())
                        <div class="timeline">
                            @foreach ($followups as $item)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-card">
                                        <div class="timeline-head">
                                            <span class="timeline-employee">
                                                <i class="bi bi-person"></i> {{ $item->user?->name ?? $item->employee_name }}
                                                @php
                                                    $commLabel = $communicationTypes[$item->communication_type] ?? $item->communication_type;
                                                    $callStatusLabel = $callStatuses[$item->call_status] ?? $item->call_status;
                                                    $outcomeCatLabel = $outcomeCategories[$item->outcome_category] ?? $item->outcome_category;
                                                @endphp
                                                <span class="timeline-badge">{{ $commLabel }}</span>
                                                @if ($item->communication_type === 'call' && $item->call_attempt_number)
                                                    <span class="timeline-badge" style="background:#fef3c7; color:#b45309;">المحاولة #{{ $item->call_attempt_number }}</span>
                                                @endif
                                                @if ($callStatusLabel)
                                                    <span class="timeline-badge" style="background:#e0e7ff; color:#4338ca;">{{ $callStatusLabel }}</span>
                                                @endif
                                                @if ($outcomeCatLabel)
                                                    <span class="timeline-badge" style="background:#dcfce7; color:#15803d;">{{ $outcomeCatLabel }}</span>
                                                @endif
                                            </span>
                                            <span class="timeline-date">
                                                {{ $item->followed_up_at ? $item->followed_up_at->format('d/m/Y - h:i A') : '—' }}
                                            </span>
                                        </div>

                                        @if ($item->fromStatus || $item->toStatus)
                                            <div style="font-size:12px;font-weight:800;color:var(--muted);margin-bottom:6px">
                                                المرحلة: <strong style="color:var(--dark)">{{ $item->toStatus?->stage?->name_ar ?? $item->toStatus?->name_ar ?? '—' }}</strong>
                                                @if ($item->toStatus)
                                                    • الحالة: <strong style="color:{{ $item->toStatus->color ?? 'var(--dark)' }}">{{ $item->toStatus->name_ar }}</strong>
                                                @endif
                                            </div>
                                        @endif

                                        <p class="timeline-body" style="margin:0;white-space:pre-line">{{ $item->outcome }}</p>

                                        @if (!empty($item->field_changes))
                                            <div style="margin-top:8px;padding:8px 10px;background:var(--card);border:1px solid var(--line);border-radius:8px;font-size:11px">
                                                <strong style="display:block;margin-bottom:4px;color:var(--dark)">تعديلات البيانات:</strong>
                                                @foreach ($item->field_changes as $chg)
                                                    <div style="color:var(--muted)">{{ $chg['label'] ?? 'حقل' }}: <span style="text-decoration:line-through;color:#b42332">{{ $chg['old'] ?? '—' }}</span> → <strong style="color:#15803d">{{ $chg['new'] ?? '—' }}</strong></div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($item->next_follow_up_at)
                                            <div style="margin-top:8px;font-size:11px;color:var(--muted);font-weight:700">
                                                <i class="bi bi-calendar-event"></i> المتابعة القادمة: <strong style="color:var(--dark)">{{ $item->next_follow_up_at->format('d/m/Y - h:i A') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center;padding:30px 16px;color:var(--muted)">
                            <i class="bi bi-chat-left-dots" style="font-size:28px;display:block;margin-bottom:6px"></i>
                            {{ __('crm.no_lead_followups') }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>
</div>

<script>
function selectChannel(key) {
    document.getElementById('communicationTypeInput').value = key;
    document.querySelectorAll('.channel-card').forEach(card => {
        card.classList.toggle('active', card.dataset.channel === key);
    });
    const callSec = document.getElementById('callDetailsSection');
    if (callSec) {
        callSec.style.display = (key === 'call') ? 'block' : 'none';
    }
}

function setNextDate(daysAhead, hour) {
    const d = new Date();
    d.setDate(d.getDate() + daysAhead);
    d.setHours(hour, 0, 0, 0);
    const pad = n => String(n).padStart(2, '0');
    const val = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    const input = document.getElementById('nextFollowUpAt');
    if (input) input.value = val;
}

(() => {
    const statusSelect = document.getElementById('lead_status_id');
    const pipelineFilter = document.getElementById('pipeline_filter');

    function filterStatusesByPipeline() {
        if (!pipelineFilter || !statusSelect) return;
        const selectedCat = pipelineFilter.value;

        let currentOptionStillVisible = false;
        const currentVal = statusSelect.value;

        const optgroups = statusSelect.querySelectorAll('optgroup');
        optgroups.forEach(og => {
            const ogCatId = og.getAttribute('data-category-id') || 'uncategorized';
            const matchesCat = (selectedCat === 'all') || (ogCatId === selectedCat);
            og.style.display = matchesCat ? '' : 'none';
            og.disabled = !matchesCat;

            og.querySelectorAll('option').forEach(opt => {
                const optCatId = opt.getAttribute('data-category-id') || 'uncategorized';
                const optMatches = (selectedCat === 'all') || (optCatId === selectedCat);
                opt.style.display = optMatches ? '' : 'none';
                opt.disabled = !optMatches;
                if (optMatches && String(opt.value) === String(currentVal)) {
                    currentOptionStillVisible = true;
                }
            });
        });

        if (!currentOptionStillVisible) {
            const firstVisible = Array.from(statusSelect.options).find(opt => !opt.disabled && opt.style.display !== 'none');
            if (firstVisible) {
                statusSelect.value = firstVisible.value;
            }
        }

        statusSelect.dispatchEvent(new CustomEvent('crm-dropdown:update'));
        statusSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (pipelineFilter) {
        pipelineFilter.addEventListener('change', filterStatusesByPipeline);
        if (pipelineFilter.value && pipelineFilter.value !== 'all') {
            filterStatusesByPipeline();
        }
    }
    const questionBlocks = document.querySelectorAll('.stage-questions-block');

    function syncStageQuestions() {
        const selectedOpt = statusSelect?.options?.[statusSelect.selectedIndex];
        const stageId = selectedOpt ? selectedOpt.getAttribute('data-stage-id') : null;
        const effectiveStageId = selectedOpt ? (selectedOpt.getAttribute('data-effective-stage-id') || stageId) : stageId;
        const isFunnel = selectedOpt ? selectedOpt.getAttribute('data-is-funnel') === '1' : false;
        const targetPipelineName = selectedOpt ? selectedOpt.getAttribute('data-target-pipeline') : '';
        const targetStageName = selectedOpt ? selectedOpt.getAttribute('data-target-stage-name') : '';

        const funnelNotice = document.getElementById('funnelHandoffNotice');
        const funnelTitle = document.getElementById('funnelNoticeTitle');
        const funnelDesc = document.getElementById('funnelNoticeDesc');

        if (funnelNotice) {
            if (isFunnel && targetPipelineName && targetStageName) {
                funnelNotice.style.display = 'block';
                if (funnelTitle) funnelTitle.textContent = `⚡ مرحلة عبور وترحيل تلقائي إلى مسار [${targetPipelineName}]`;
                if (funnelDesc) funnelDesc.textContent = `الأسئلة أدناه تتبع مرحلة [${targetStageName}] في مسار [${targetPipelineName}]. سيتم ترحيل العميل ونقل البيانات إليها فور حفظ المتابعة.`;
            } else {
                funnelNotice.style.display = 'none';
            }
        }

        let hasMatchingStageBlock = false;

        questionBlocks.forEach(block => {
            const blockStageId = block.getAttribute('data-stage-id');
            const isMatch = blockStageId && effectiveStageId && String(blockStageId) === String(effectiveStageId);
            if (isMatch) {
                hasMatchingStageBlock = true;
            }
            block.style.display = isMatch ? 'block' : 'none';
            block.querySelectorAll('input, select, textarea').forEach(input => {
                if (isMatch) {
                    input.removeAttribute('disabled');
                    const req = input.closest('.stage-field-item')?.getAttribute('data-sf-required') === '1';
                    if (req) {
                        input.setAttribute('required', 'required');
                    }
                } else {
                    input.setAttribute('disabled', 'disabled');
                    input.removeAttribute('required');
                }
            });
        });

        const hiddenStatus = document.querySelector('input[type="hidden"][name="lead_status_id"]');
        if (hiddenStatus && statusSelect && statusSelect.value) {
            hiddenStatus.value = statusSelect.value;
        }

        const genericFollowupField = document.getElementById('nextFollowUpAt')?.closest('.field');
        const genericFollowupInput = document.getElementById('nextFollowUpAt');
        const activeBlock = hasMatchingStageBlock ? Array.from(questionBlocks).find(b => b.style.display !== 'none') : null;
        const hasStageScheduling = activeBlock ? (activeBlock.querySelector('[data-sf-key="callback_at"]') !== null || activeBlock.querySelector('[data-sf-key="next_follow_up_at"]') !== null) : false;

        if (genericFollowupField && genericFollowupInput) {
            if (hasStageScheduling) {
                genericFollowupField.style.display = 'none';
                genericFollowupInput.setAttribute('disabled', 'disabled');
                genericFollowupInput.removeAttribute('required');
            } else {
                genericFollowupField.style.display = '';
                genericFollowupInput.removeAttribute('disabled');
            }
        }
    }

    const campaignSelect = document.getElementById('followupCampaign');
    const assigneeSelect = document.getElementById('followupCampaignAssignee');

    function syncCampaignAssignees() {
        if (!campaignSelect || !assigneeSelect) return;
        const selectedOpt = campaignSelect.options[campaignSelect.selectedIndex];
        const userIdsRaw = selectedOpt ? selectedOpt.getAttribute('data-user-ids') : null;
        const allowedIds = userIdsRaw ? userIdsRaw.split(',').map(s => s.trim()).filter(Boolean) : null;

        Array.from(assigneeSelect.options).forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }
            if (allowedIds !== null) {
                const isAllowed = allowedIds.includes(opt.value);
                opt.hidden = !isAllowed;
                opt.disabled = !isAllowed;
                if (!isAllowed && assigneeSelect.value === opt.value) {
                    assigneeSelect.value = '';
                }
            } else {
                opt.hidden = false;
                opt.disabled = false;
            }
        });
    }

    statusSelect?.addEventListener('change', syncStageQuestions);
    campaignSelect?.addEventListener('change', syncCampaignAssignees);
    syncStageQuestions();
    syncCampaignAssignees();
})();
</script>

@if ($callPhone && ($defaultCommunicationType === 'call' || request('channel') === 'call'))
<script>
(() => {
    const callPhone = @json($callPhone);
    if (!callPhone) return;

    const initiateCall = () => {
        const telUri = 'tel:' + encodeURIComponent(callPhone);
        try {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.setAttribute('src', telUri);
            document.body.appendChild(iframe);
            setTimeout(() => { iframe.remove(); }, 3000);
        } catch (e) {}

        setTimeout(() => {
            try { window.location.href = telUri; } catch (e) {}
        }, 150);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(initiateCall, 300));
    } else {
        setTimeout(initiateCall, 300);
    }
})();
</script>
@endif
<script src="{{ asset('crm-notifications.js') }}?v=1.0.0"></script>
<script>
function cancelKanbanPopup() {
    try {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({ type: 'crm-kanban-popup-close' }, window.location.origin);
            return;
        }
    } catch (e) {}
}
</script>
@if (request()->boolean('kanban_popup') && (request()->boolean('saved') || session('success')))
<script>
(() => {
    try {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({ type: 'crm-kanban-followup-saved' }, window.location.origin);
        }
    } catch (e) {}
})();
</script>
@endif
</body>
</html>
