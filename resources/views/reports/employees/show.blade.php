<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SokratCRM — {{ $employee->name }} — {{ __('crm.employee_drilldown') }}</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v=1.0.0">
<style>
:root {
  --red: #ef4444;
  --red-dark: #dc2626;
  --dark: #182033;
  --text: #4b5568;
  --muted: #8b94a5;
  --line: #e7e9ef;
  --bg: #f6f8fb;
  --card: #fff;
  --shadow: none;
}

* { box-sizing: border-box; }
html { overflow-x: clip; }
body {
  margin: 0;
  min-width: 320px;
  width: 100%;
  max-width: 100vw;
  background: var(--bg);
  color: var(--dark);
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  font-size: 15px;
  overflow-x: clip;
}
button, input, select, textarea { font: inherit; }
a { color: inherit; text-decoration: none; }

.app {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  min-height: 100vh;
  width: 100%;
  max-width: 100vw;
  overflow-x: clip;
  min-width: 0;
  background: transparent;
}
.main {
  order: 1;
  flex: 1 1 auto;
  width: calc(100% - 288px);
  min-height: 100vh;
  min-width: 0;
  padding: 24px clamp(16px, 2.5vw, 36px) 48px;
}
.overlay {
  display: none;
  position: fixed;
  inset: 0;
  border: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 99990 !important;
  cursor: pointer;
}
@media(max-width: 900px) {
  .app { display: block; }
  .main { width: 100% !important; padding: 14px 12px 36px; min-height: 100vh; }
  .crm-side-open .overlay,
  .transfer-side-open .overlay {
    display: block !important;
    opacity: 1 !important;
    pointer-events: auto !important;
  }
}

/* CRM V2 Dashboard Scoped Tokens */
.crm-dashboard-v2 {
  --d-bg: #f6f8fb;
  --d-surface: #ffffff;
  --d-surface-alt: #f8fafc;
  --d-surface-hover: #f1f5f9;
  --d-border: #e5e9f2;
  --d-border-subtle: #f1f5f9;
  --d-border-strong: #cbd5e1;
  --d-text: #0f172a;
  --d-text-muted: #64748b;
  --d-text-subtle: #94a3b8;
  --d-primary: #dc2637;
  --d-primary-subtle: rgba(220, 38, 55, 0.08);
  --d-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 6px 16px rgba(15, 23, 42, 0.03);
  --d-shadow-hover: 0 8px 24px -4px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
  --d-radius-sm: 10px;
  --d-radius-md: 14px;
  --d-radius-lg: 18px;
  --d-transition: 200ms cubic-bezier(0.16, 1, 0.3, 1);
  background-color: var(--d-bg);
  color: var(--d-text);
  width: 100%;
}
.crm-dashboard-v2[data-theme="dark"],
.dark-mode .crm-dashboard-v2,
html.dark .crm-dashboard-v2,
html.dark-mode .crm-dashboard-v2 {
  --d-bg: #0b0f19;
  --d-surface: #111827;
  --d-surface-alt: #161f30;
  --d-surface-hover: #1e293b;
  --d-border: #1f293d;
  --d-border-subtle: #172033;
  --d-border-strong: #334155;
  --d-text: #f8fafc;
  --d-text-muted: #94a3b8;
  --d-text-subtle: #64748b;
  --d-primary: #ef4444;
  --d-primary-subtle: rgba(239, 68, 68, 0.15);
  --d-shadow: 0 4px 20px rgba(0, 0, 0, 0.35), 0 1px 3px rgba(0, 0, 0, 0.2);
  --d-shadow-hover: 0 10px 30px rgba(0, 0, 0, 0.5), 0 2px 8px rgba(0, 0, 0, 0.3);
}

/* KPI Cards Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 22px;
}
.kpi-card-modern {
  background: var(--d-surface);
  border: 1px solid var(--d-border);
  border-radius: var(--d-radius-lg);
  padding: 18px 20px;
  box-shadow: var(--d-shadow);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all var(--d-transition);
}
.kpi-card-modern:hover {
  transform: translateY(-3px);
  box-shadow: var(--d-shadow-hover);
  border-color: color-mix(in srgb, var(--card-accent, #3b82f6) 40%, var(--d-border));
}
.kpi-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.kpi-card-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--d-text-muted);
}
.kpi-card-icon {
  width: 40px;
  height: 40px;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-size: 22px;
  background: transparent !important;
  background-color: transparent !important;
  border: none !important;
  border-radius: 0 !important;
  color: var(--card-accent, #3b82f6);
}
.kpi-card-icon svg {
  width: 24px;
  height: 24px;
  display: block;
  margin: auto;
}
.kpi-card-value {
  font-size: 28px;
  font-weight: 800;
  color: var(--d-text);
  font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace !important;
  font-variant-numeric: tabular-nums;
  line-height: 1;
  margin-bottom: 6px;
}
.kpi-card-sub {
  font-size: 11px;
  font-weight: 700;
  color: var(--d-text-subtle);
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Pipeline Stage Strip Wrap */
.dash-pipeline-strip-wrap {
  background: var(--d-surface);
  border: 1px solid var(--d-border);
  border-radius: var(--d-radius-lg);
  padding: 16px 20px;
  margin-bottom: 22px;
  box-shadow: var(--d-shadow);
}
.dash-pipeline-strip-title {
  font-size: 14px;
  font-weight: 800;
  color: var(--d-text);
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 12px;
}
.dash-pipeline-strip {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.pipeline-stage-chip {
  padding: 8px 14px;
  border-radius: var(--d-radius-sm);
  border: 1px solid var(--d-border);
  background: var(--d-surface-alt);
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
}
.pipeline-stage-chip strong {
  font-size: 14px;
  font-weight: 800;
  color: var(--d-text);
  font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace !important;
  font-variant-numeric: tabular-nums;
}

/* Section Card */
.dash-chart-card {
  background: var(--d-surface);
  border: 1px solid var(--d-border);
  border-radius: var(--d-radius-lg);
  padding: 22px 24px;
  box-shadow: var(--d-shadow);
  margin-bottom: 22px;
  transition: all var(--d-transition);
}
.dash-chart-card:hover {
  box-shadow: var(--d-shadow-hover);
}
.dash-chart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 14px;
  margin-bottom: 16px;
  border-bottom: 1px solid var(--d-border-subtle);
}
.dash-chart-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
  color: var(--d-text);
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Activity Table */
.table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border: 1px solid var(--d-border);
  border-radius: var(--d-radius-md);
}
.dash-activity-table {
  width: 100%;
  border-collapse: collapse;
  text-align: right;
  font-size: 13px;
}
[dir="ltr"] .dash-activity-table {
  text-align: left;
}
.dash-activity-table th {
  padding: 12px 14px;
  border-bottom: 1px solid var(--d-border);
  color: var(--d-text-muted);
  font-size: 12px;
  font-weight: 800;
  background: var(--d-surface-alt);
  white-space: nowrap;
}
.dash-activity-table td {
  padding: 13px 14px;
  border-bottom: 1px solid var(--d-border-subtle);
  color: var(--d-text);
  font-size: 13px;
  vertical-align: middle;
  white-space: nowrap;
}
.dash-activity-table tbody tr:hover {
  background-color: var(--d-surface-alt);
}
.dash-activity-table tbody tr:last-child td {
  border-bottom: none;
}
.badge-group {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  background: var(--d-surface-alt);
  border: 1px solid var(--d-border);
  font-size: 11px;
  font-weight: 700;
  color: var(--d-text-muted);
}
</style>
</head>
<body>
@include('partials.page-loader')
<div class="app">
    @include('partials.crm-sidebar')
    <button class="overlay" id="overlay" type="button" aria-label="{{ __('crm.close') ?? 'إغلاق' }}"></button>

    <main class="main crm-dashboard-v2 employee-reports-v2">
        @include('partials.topbar', [
            'title' => $employee->name . ' (' . $employee->username . ')',
            'subtitle' => __('crm.employee_drilldown'),
            'icon' => 'bi-person-badge-fill',
            'backUrl' => route('v2.reports.employees.index'),
            'backTitle' => __('crm.employee_reports_title'),
        ])

        <!-- Profile & Metrics Summary Cards -->
        <section class="kpi-grid">
            <div class="kpi-card-modern" style="--card-accent: #3b82f6; --icon-bg: rgba(59, 130, 246, 0.1);">
                <div class="kpi-card-head">
                    <span class="kpi-card-label">{{ __('crm.assigned_leads') }}</span>
                    <div class="kpi-card-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <div>
                    <div class="kpi-card-value">{{ number_format($drilldown['metrics']['total_leads']) }}</div>
                    <div class="kpi-card-sub" style="color:#3b82f6;"><i class="bi bi-database"></i> {{ __('crm.all_records') }}</div>
                </div>
            </div>

            <div class="kpi-card-modern" style="--card-accent: #8b5cf6; --icon-bg: rgba(139, 92, 246, 0.1);">
                <div class="kpi-card-head">
                    <span class="kpi-card-label">{{ __('crm.kpi_total_followups') }}</span>
                    <div class="kpi-card-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                </div>
                <div>
                    <div class="kpi-card-value" style="color:#8b5cf6;">{{ number_format($drilldown['metrics']['total_followups']) }}</div>
                    <div class="kpi-card-sub" style="color:#8b5cf6;"><i class="bi bi-chat-dots"></i> {{ __('crm.current_period') ?? 'الفترة' }}</div>
                </div>
            </div>

            <div class="kpi-card-modern" style="--card-accent: #dc2637; --icon-bg: rgba(220, 38, 55, 0.1);">
                <div class="kpi-card-head">
                    <span class="kpi-card-label">{{ __('crm.kpi_overdue_followups') }}</span>
                    <div class="kpi-card-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                </div>
                <div>
                    <div class="kpi-card-value" style="color:#dc2637;">{{ number_format($drilldown['metrics']['overdue_count']) }}</div>
                    <div class="kpi-card-sub" style="color:#dc2637;"><i class="bi bi-clock-history"></i> {{ __('crm.kpi_overdue_followups') }}</div>
                </div>
            </div>

            <div class="kpi-card-modern" style="--card-accent: #10b981; --icon-bg: rgba(16, 185, 129, 0.1);">
                <div class="kpi-card-head">
                    <span class="kpi-card-label">{{ __('crm.kpi_conversion_rate') }}</span>
                    <div class="kpi-card-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </div>
                </div>
                <div>
                    <div class="kpi-card-value" style="color:#10b981;">{{ $drilldown['metrics']['conversion_rate'] }}%</div>
                    <div class="kpi-card-sub" style="color:#10b981;"><i class="bi bi-award"></i> {{ __('crm.kpi_conversion_rate') }}</div>
                </div>
            </div>

            @if (!empty($drilldown['voip']['available']))
                <div class="kpi-card-modern" style="--card-accent: #0284c7; --icon-bg: rgba(2, 132, 199, 0.1);">
                    <div class="kpi-card-head">
                        <span class="kpi-card-label">{{ __('crm.voip_total_calls') }}</span>
                        <div class="kpi-card-icon" style="color:#0284c7;">
                            <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="kpi-card-value" style="color:#0284c7;">{{ number_format($drilldown['voip']['total_calls']) }}</div>
                        <div class="kpi-card-sub" style="color:#10b981;">
                            <i class="bi bi-telephone-inbound"></i> {{ number_format($drilldown['voip']['answered_calls']) }} {{ __('crm.voip_answered') }}
                        </div>
                    </div>
                </div>

                <div class="kpi-card-modern" style="--card-accent: #0284c7; --icon-bg: rgba(2, 132, 199, 0.1);">
                    <div class="kpi-card-head">
                        <span class="kpi-card-label">{{ __('crm.voip_talk_time') }}</span>
                        <div class="kpi-card-icon" style="color:#0284c7;"><i class="bi bi-clock-history"></i></div>
                    </div>
                    <div>
                        <div class="kpi-card-value" style="color:#0284c7;">{{ $drilldown['voip']['talk_time_formatted'] }}</div>
                        <div class="kpi-card-sub" style="color:var(--d-text-muted);">
                            <span class="badge active" style="font-size:11px;font-family:monospace;">{{ __('crm.on_extension') }} {{ $drilldown['voip']['extension'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <!-- Current Pipeline Distribution -->
        <section class="dash-pipeline-strip-wrap">
            <div class="dash-pipeline-strip-title">
                <i class="bi bi-diagram-3-fill" style="color:var(--d-primary);"></i>
                {{ __('crm.current_pipeline_distribution') }}
            </div>
            <div class="dash-pipeline-strip">
                @foreach ($drilldown['stage_distribution'] as $st)
                    <div class="pipeline-stage-chip">
                        <span style="width:9px;height:9px;border-radius:50%;background:{{ $st['color'] }};display:inline-block;"></span>
                        <span style="font-weight:700;color:var(--d-text);">{{ $st['stage_name'] }}:</span>
                        <strong>{{ number_format($st['count']) }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Recent Assigned Leads -->
        <section class="dash-chart-card">
            <div class="dash-chart-header">
                <h3><i class="bi bi-people-fill" style="color:var(--d-primary);"></i> {{ __('crm.drilldown_leads') }}</h3>
            </div>
            <div class="table-wrap">
                <table class="dash-activity-table">
                    <thead>
                        <tr>
                            <th>{{ __('crm.lead_name') }}</th>
                            <th>{{ __('crm.company') }}</th>
                            <th>{{ __('crm.phone') }}</th>
                            <th>{{ __('crm.status') }}</th>
                            <th>{{ __('crm.next_followup') }}</th>
                            <th>{{ __('crm.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drilldown['recent_leads'] as $lead)
                            <tr>
                                <td><strong><a href="{{ route('v2.leads') }}?search={{ urlencode($lead->phone ?: $lead->name) }}" target="_blank" style="color:var(--d-primary);">{{ $lead->name }}</a></strong></td>
                                <td>{{ $lead->company_name ?: '—' }}</td>
                                <td>{{ $lead->phone ?: '—' }}</td>
                                <td><span class="badge-group">{{ $lead->status?->name_ar ?: '—' }}</span></td>
                                <td>{{ $lead->next_follow_up_at?->format('Y-m-d') ?: '—' }}</td>
                                <td>{{ $lead->created_at?->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:var(--d-text-muted);padding:24px;">{{ __('crm.no_employee_reports_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Recent Follow-ups by this employee -->
        <section class="dash-chart-card">
            <div class="dash-chart-header">
                <h3><i class="bi bi-chat-left-text-fill" style="color:#8b5cf6;"></i> {{ __('crm.drilldown_followups') }}</h3>
            </div>
            <div class="table-wrap">
                <table class="dash-activity-table">
                    <thead>
                        <tr>
                            <th>{{ __('crm.lead_name') }}</th>
                            <th>{{ __('crm.communication_channel') }}</th>
                            <th>{{ __('crm.date') }}</th>
                            <th>{{ __('crm.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drilldown['recent_followups'] as $f)
                            <tr>
                                <td><strong>{{ $f->lead?->name ?: '—' }}</strong></td>
                                <td><span class="badge-group">{{ $f->communication_type }}</span></td>
                                <td>{{ $f->followed_up_at?->format('Y-m-d H:i') ?: '—' }}</td>
                                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $f->outcome ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--d-text-muted);padding:24px;">{{ __('crm.no_employee_reports_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Activity Timeline (Status changes) -->
        <section class="dash-chart-card">
            <div class="dash-chart-header">
                <h3><i class="bi bi-clock-history" style="color:#0284c7;"></i> {{ __('crm.drilldown_activity') }}</h3>
            </div>
            <div class="table-wrap">
                <table class="dash-activity-table">
                    <thead>
                        <tr>
                            <th>{{ __('crm.lead_name') }}</th>
                            <th>{{ __('crm.changed_from') }}</th>
                            <th>{{ __('crm.changed_to') }}</th>
                            <th>{{ __('crm.date') }}</th>
                            <th>{{ __('crm.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drilldown['recent_activity'] as $act)
                            <tr>
                                <td><strong>{{ $act->lead?->name ?: '—' }}</strong></td>
                                <td>{{ $act->fromStatus?->name_ar ?: '—' }}</td>
                                <td><span class="badge-group">{{ $act->toStatus?->name_ar ?: '—' }}</span></td>
                                <td>{{ $act->changed_at?->format('Y-m-d H:i') ?: '—' }}</td>
                                <td>{{ $act->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:var(--d-text-muted);padding:24px;">{{ __('crm.no_employee_reports_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if (!empty($drilldown['voip']['available']))
            <!-- PBX Telephony Activity (Asterisk CDR) -->
            <section class="dash-chart-card">
                <div class="dash-chart-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="#0284c7" aria-hidden="true" style="vertical-align:-2px;margin-inline-end:6px;"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58z"/></svg>
                        {{ __('crm.voip_pbx_activity') }}
                    </h3>
                    <span class="badge active" style="font-family:monospace;font-size:12px;">{{ __('crm.on_extension') }} {{ $drilldown['voip']['extension'] }}</span>
                </div>
                <div class="table-wrap">
                    <table class="dash-activity-table">
                        <thead>
                            <tr>
                                <th>{{ __('crm.datetime') }}</th>
                                <th>{{ __('crm.direction') }}</th>
                                <th>{{ __('crm.other_party') }}</th>
                                <th>{{ __('crm.duration') }}</th>
                                <th>{{ __('crm.status') }}</th>
                                <th>{{ __('crm.recording') ?? 'التسجيل' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $recentPbxCalls = $drilldown['voip']['raw_stats']['recent_calls'] ?? [];
                            @endphp
                            @forelse ($recentPbxCalls as $call)
                                <tr>
                                    <td>{{ !empty($call['started_at']) ? \Carbon\Carbon::parse($call['started_at'])->format('Y-m-d H:i:s') : '—' }}</td>
                                    <td>
                                        @if (($call['direction'] ?? '') === 'outbound')
                                            <span style="color:#0284c7;font-weight:700;display:inline-flex;align-items:center;gap:3px;">
                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M14 2.5a.5.5 0 0 0-.5-.5h-6a.5.5 0 0 0 0 1h4.793L2.146 13.146a.5.5 0 0 0 .708.708L13 3.707V8.5a.5.5 0 0 0 1 0v-6z"/></svg>
                                                {{ __('crm.outgoing') }}
                                            </span>
                                        @elseif (($call['direction'] ?? '') === 'inbound')
                                            <span style="color:#10b981;font-weight:700;display:inline-flex;align-items:center;gap:3px;">
                                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M2 13.5a.5.5 0 0 0 .5.5h6a.5.5 0 0 0 0-1H3.707L13.854 2.854a.5.5 0 1 0-.708-.708L3 12.293V7.5a.5.5 0 0 0-1 0v6z"/></svg>
                                                {{ __('crm.incoming') }}
                                            </span>
                                        @else
                                            <span style="color:var(--d-text-muted);font-weight:700;">{{ __('crm.internal') }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $call['customer_number'] ?? '—' }}</strong></td>
                                    <td>{{ $call['duration_seconds'] ?? 0 }} {{ __('crm.seconds') }}</td>
                                    <td>
                                        <span class="badge {{ ($call['disposition'] ?? '') === 'ANSWERED' ? 'active' : 'inactive' }}">
                                            {{ $call['disposition'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (!empty($call['recording']['available']) && !empty($call['recording']['media_id']))
                                            @can('voip.recordings')
                                                <audio controls preload="none" style="height:28px;width:180px;">
                                                    <source src="{{ route('v2.voip.recordings.stream', $call['recording']['media_id']) }}" type="audio/wav">
                                                </audio>
                                            @else
                                                <span class="badge active" style="font-size:11px;">{{ __('crm.available') ?? 'متاح' }}</span>
                                            @endcan
                                        @else
                                            <span style="color:var(--d-text-muted);font-size:11px;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align:center;color:var(--d-text-muted);padding:24px;">{{ __('crm.no_calls_match_filters') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </main>
</div>
</body>
</html>
