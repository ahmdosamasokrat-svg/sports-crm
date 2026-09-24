<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SokratCRM — {{ __('crm.appointments_management') ?? 'إدارة المواعيد والتجارب' }}</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v=1.0.0">
<link rel="stylesheet" href="{{ asset('crm-dropdown.css') }}?v={{ time() }}">
<style>
:root {
  --red: #ef4444;
  --dark: #182033;
  --muted: #64748b;
  --line: #e2e8f0;
  --bg: #f8fafc;
  --card: #ffffff;
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

.crm-app {
  display: flex;
  min-height: 100vh;
}
.crm-main {
  flex: 1;
  min-width: 0;
  padding: 20px 24px;
}

/* Stat Cards */
.apt-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}
.apt-stat-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform .15s ease, border-color .15s ease;
}
.apt-stat-card:hover {
  border-color: #cbd5e1;
  transform: translateY(-2px);
}
.apt-stat-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent !important;
  border: none !important;
  flex-shrink: 0;
}
.apt-stat-title {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--muted);
  margin-bottom: 4px;
}
.apt-stat-value {
  font-size: 24px;
  font-weight: 800;
  color: var(--dark);
}

/* Filter Shell */
.apt-filter-shell {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 16px 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}
.apt-tabs {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.apt-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  background: var(--card);
  border: 1px solid var(--line);
  color: var(--dark);
  cursor: pointer;
  text-decoration: none;
  transition: all .15s ease;
}
.apt-tab-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}
.apt-tab-btn.active {
  background: #0284c7;
  border-color: #0284c7;
  color: #fff;
  box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);
}
.apt-tab-badge {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 800;
  background: #f1f5f9;
  color: var(--dark);
}
.apt-tab-btn.active .apt-tab-badge {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}

.apt-search-form {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.apt-search-input-wrap {
  position: relative;
  min-width: 240px;
}
.apt-search-input-wrap i {
  position: absolute;
  inset-inline-start: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted);
  pointer-events: none;
  font-size: 13px;
}
.apt-search-input {
  width: 100%;
  height: 40px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 0 14px;
  padding-inline-start: 36px;
  background: var(--card);
  color: var(--dark);
  font-size: 13px;
  font-weight: 600;
  transition: all .15s ease;
}
.apt-search-input:focus {
  outline: none;
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}
.apt-select-control {
  height: 40px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 0 14px;
  background: var(--card);
  color: var(--dark);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s ease;
}
.apt-select-control:focus {
  outline: none;
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

/* Appointment Table */
.apt-table-wrap {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  overflow: hidden;
}
.apt-table {
  width: 100%;
  border-collapse: collapse;
  text-align: start;
}
.apt-table th {
  background: var(--bg);
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 800;
  color: var(--muted);
  border-bottom: 1px solid var(--line);
  white-space: nowrap;
}
.apt-table td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--line);
  font-size: 13px;
  vertical-align: middle;
}
.apt-table tr:last-child td { border-bottom: none; }
.apt-table tr:hover td { background: rgba(240, 249, 255, 0.5); }

/* Global Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 40px;
  min-height: 40px;
  padding: 0 18px;
  border: 1px solid var(--line);
  border-radius: 10px;
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
.btn.primary {
  background: #0284c7;
  border-color: #0284c7;
  color: #fff;
  box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}
.btn.primary:hover {
  background: #0369a1;
  border-color: #0369a1;
  color: #fff;
  transform: translateY(-1px);
}
.btn.soft {
  background: #f1f5f9;
  border-color: #e2e8f0;
  color: #334155;
}
.btn.soft:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
  color: var(--dark);
}

/* Action Buttons */
.apt-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 32px;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s ease;
  border: 1px solid var(--line);
  background: var(--card);
  color: var(--dark);
  text-decoration: none;
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.apt-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}
.apt-action-btn.attended {
  background: #ecfdf5;
  color: #047857;
  border-color: #a7f3d0;
}
.apt-action-btn.attended:hover {
  background: #059669;
  border-color: #059669;
  color: #fff;
}
.apt-action-btn.noshow {
  background: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
}
.apt-action-btn.noshow:hover {
  background: #dc2626;
  border-color: #dc2626;
  color: #fff;
}
.apt-action-btn.reschedule {
  background: #f0f9ff;
  color: #0284c7;
  border-color: #bae6fd;
}
.apt-action-btn.reschedule:hover {
  background: #0284c7;
  border-color: #0284c7;
  color: #fff;
}

/* Modal */
.apt-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.apt-modal {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 18px;
  width: 100%;
  max-width: 480px;
  padding: 26px;
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.2);
}
.apt-modal-input {
  width: 100%;
  height: 40px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 0 12px;
  background: var(--bg);
  color: var(--dark);
  font-size: 13px;
  font-weight: 600;
  transition: all .15s ease;
}
.apt-modal-input:focus {
  outline: none;
  border-color: #0284c7;
  background: var(--card);
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}
.apt-modal-textarea {
  width: 100%;
  min-height: 80px;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 10px 12px;
  background: var(--card);
  color: var(--dark);
  font-size: 13px;
  font-family: inherit;
  transition: all .15s ease;
}
.apt-modal-textarea:focus {
  outline: none;
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}
</style>
</head>
<body>
<div class="crm-app">
    @include('partials.crm-sidebar')

    <main class="crm-main">
        @include('partials.topbar', [
            'title' => __('crm.appointments_management'),
            'subtitle' => '<span>' . __('crm.appointments_subheading') . '</span>',
            'icon' => 'bi-calendar2-check-fill',
        ])

        @if(session('success'))
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:12px; padding:12px 18px; margin-bottom:20px; font-weight:700; display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- 1. STATS METRICS -->
        <div class="apt-stats-grid">
            <div class="apt-stat-card">
                <div class="apt-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        <circle cx="12" cy="15" r="2.5" fill="none" stroke="#0284c7" stroke-width="2"/>
                    </svg>
                </div>
                <div>
                    <div class="apt-stat-title">{{ __('crm.today_appointments') ?? 'مواعيد اليوم' }}</div>
                    <div class="apt-stat-value" style="color:#0284c7;">{{ number_format($counts['today'] ?? 0) }}</div>
                </div>
            </div>

            <div class="apt-stat-card">
                <div class="apt-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        <line x1="10" y1="14" x2="14" y2="18"/><line x1="14" y1="14" x2="10" y2="18"/>
                    </svg>
                </div>
                <div>
                    <div class="apt-stat-title">{{ __('crm.upcoming_appointments') ?? 'المواعيد القادمة' }}</div>
                    <div class="apt-stat-value" style="color:#d97706;">{{ number_format($counts['upcoming'] ?? 0) }}</div>
                </div>
            </div>

            <div class="apt-stat-card">
                <div class="apt-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/>
                    </svg>
                </div>
                <div>
                    <div class="apt-stat-title">{{ __('crm.no_shows') ?? 'لم يحضروا' }}</div>
                    <div class="apt-stat-value" style="color:#ef4444;">{{ number_format($counts['no_shows'] ?? 0) }}</div>
                </div>
            </div>

            <div class="apt-stat-card">
                <div class="apt-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <div>
                    <div class="apt-stat-title">{{ __('crm.attended_completed') ?? 'أتموا الحضور' }}</div>
                    <div class="apt-stat-value" style="color:#059669;">{{ number_format($counts['attended'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- 2. FILTER & SEARCH SHELL -->
        <div class="apt-filter-shell">
            <div class="apt-tabs">
                <a href="{{ route('v2.appointments.index', array_merge(request()->query(), ['tab' => 'today'])) }}"
                   class="apt-tab-btn {{ $tab === 'today' ? 'active' : '' }}">
                    <span>{{ __('crm.today') }}</span>
                    <span class="apt-tab-badge">{{ number_format($counts['today'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.appointments.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}"
                   class="apt-tab-btn {{ $tab === 'upcoming' ? 'active' : '' }}">
                    <span>{{ __('crm.upcoming') }}</span>
                    <span class="apt-tab-badge">{{ number_format($counts['upcoming'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.appointments.index', array_merge(request()->query(), ['tab' => 'no_shows'])) }}"
                   class="apt-tab-btn {{ $tab === 'no_shows' ? 'active' : '' }}">
                    <span>{{ __('crm.no_shows') }}</span>
                    <span class="apt-tab-badge">{{ number_format($counts['no_shows'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.appointments.index', array_merge(request()->query(), ['tab' => 'attended'])) }}"
                   class="apt-tab-btn {{ $tab === 'attended' ? 'active' : '' }}">
                    <span>{{ __('crm.attended') }}</span>
                    <span class="apt-tab-badge">{{ number_format($counts['attended'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.appointments.index', array_merge(request()->query(), ['tab' => 'all'])) }}"
                   class="apt-tab-btn {{ $tab === 'all' ? 'active' : '' }}">
                    <span>{{ __('crm.all_appointments_tab') }}</span>
                </a>
            </div>

            <form method="GET" action="{{ route('v2.appointments.index') }}" class="apt-search-form">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($branches->count() > 1)
                <select name="branch_id" class="apt-select-control" onchange="this.form.submit()">
                    <option value="">-- {{ __('crm.all_branches') }} --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string) ($filters['branch_id'] ?? '') === (string) $b->id ? 'selected' : '' }}>
                            {{ $b->name_ar ?: $b->name }}
                        </option>
                    @endforeach
                </select>
                @endif

                <div class="apt-search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="apt-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('crm.search_name_or_phone') }}">
                </div>

                <button type="submit" class="btn soft" style="height:38px; min-height:38px; padding:0 14px;">
                    {{ __('crm.search') }}
                </button>

                @if(!empty($filters['search']) || !empty($filters['branch_id']))
                <a href="{{ route('v2.appointments.index', ['tab' => $tab]) }}" class="btn soft" style="height:38px; min-height:38px; padding:0 12px; color:var(--red);">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>

        <!-- 3. APPOINTMENTS TABLE -->
        @if($appointments->isNotEmpty())
            <div class="apt-table-wrap">
                <table class="apt-table">
                    <thead>
                        <tr>
                            <th>{{ __('crm.player_or_client') }}</th>
                            <th>{{ __('crm.appointment_time') }}</th>
                            <th>{{ __('crm.activity_and_branch') }}</th>
                            <th>{{ __('crm.coach') }}</th>
                            <th>{{ __('crm.stage_and_status') }}</th>
                            <th>{{ __('crm.responsible_employee') }}</th>
                            @if($setting->allow_quick_actions)
                                <th style="text-align:end;">{{ __('crm.quick_action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $lead)
                            @php
                                $meta = $lead->appointment_meta;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:800; font-size:14px;">
                                        <a href="{{ route('v2.leads.show', $lead) }}" style="color:var(--dark);">
                                            {{ $lead->name }}
                                        </a>
                                    </div>
                                    @if($lead->phone)
                                        <div style="font-size:11.5px; color:var(--muted); font-family:monospace; margin-top:2px;" dir="ltr">
                                            {{ $lead->phone }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div style="font-weight:700; color:#0284c7; font-size:13px;">
                                        {{ $meta['date'] ?: __('crm.unspecified') }}
                                    </div>
                                    <div style="font-size:11px; color:var(--muted);">
                                        {{ $meta['time'] ?: '—' }}
                                    </div>
                                </td>

                                <td>
                                    <div style="font-weight:700;">{{ $lead->activity ?: '—' }}</div>
                                    <div style="font-size:11px; color:var(--muted);">{{ $lead->branch?->localizedName() ?: __('crm.main_branch') }}</div>
                                </td>

                                <td>
                                    <span style="font-size:12.5px; font-weight:600;">{{ $meta['coach'] ?: '—' }}</span>
                                </td>

                                <td>
                                    <span class="badge" style="background:transparent; border:1px solid var(--line); font-size:11.5px; font-weight:700;">
                                        {{ $meta['status'] }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:12px; color:var(--muted);">{{ $lead->assignedUser?->name ?? '—' }}</span>
                                </td>

                                @if($setting->allow_quick_actions)
                                <td style="text-align:end;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        @if(!$meta['is_attended'])
                                            <form action="{{ route('v2.appointments.attended', $lead) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="apt-action-btn attended" title="{{ __('crm.mark_attended') }}">
                                                    <i class="bi bi-check-lg"></i> {{ __('crm.attended') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if(!$meta['is_attended'] && !$meta['is_no_show'])
                                            <form action="{{ route('v2.appointments.no_show', $lead) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="apt-action-btn noshow" title="{{ __('crm.mark_no_show') }}">
                                                    <i class="bi bi-x-lg"></i> {{ __('crm.no_show') }}
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="apt-action-btn reschedule" onclick="openRescheduleModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $meta['date'] }}', '{{ $meta['time'] }}')">
                                            <i class="bi bi-arrow-repeat"></i> {{ __('crm.reschedule') }}
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px;">
                {{ $appointments->links() }}
            </div>
        @else
            <div style="background:var(--card); border:2px dashed var(--line); border-radius:var(--radius); padding:48px 24px; text-align:center; color:var(--muted);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none; margin-bottom:12px; display:inline-block;" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h3 style="margin:0 0 6px; font-size:16px; color:var(--dark);">
                    {{ __('crm.no_appointments_in_tab') }}
                </h3>
                <p style="margin:0; font-size:13px;">
                    {{ __('crm.no_appointments_desc') }}
                </p>
            </div>
        @endif
    </main>
</div>

<!-- RESCHEDULE MODAL -->
<div id="rescheduleModal" class="apt-modal-backdrop" style="display:none;">
    <div class="apt-modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:12px;">
            <h3 style="margin:0; font-size:16px; font-weight:800; color:var(--dark);">
                {{ __('crm.reschedule_appointment') }}
            </h3>
            <button type="button" onclick="closeRescheduleModal()" style="background:transparent; border:none; font-size:18px; color:var(--muted); cursor:pointer;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="rescheduleForm" method="POST" action="">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px; color:var(--muted);">
                    {{ __('crm.client_athlete_name') }}
                </label>
                <input type="text" id="modalLeadName" class="apt-modal-input" readonly>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px; color:var(--dark);">
                        {{ __('crm.new_date') }} <span style="color:var(--red);">*</span>
                    </label>
                    <input type="date" name="date" id="modalDate" class="apt-modal-input" required>
                </div>
                <div>
                    <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px; color:var(--dark);">
                        {{ __('crm.time') }}
                    </label>
                    <input type="time" name="time" id="modalTime" class="apt-modal-input">
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px; color:var(--dark);">
                    {{ __('crm.reschedule_notes') }}
                </label>
                <textarea name="notes" id="modalNotes" rows="2" class="apt-modal-textarea"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeRescheduleModal()" class="btn soft" style="min-height:38px;">
                    {{ __('crm.cancel') }}
                </button>
                <button type="submit" class="btn primary" style="background:#0284c7; border-color:#0284c7; min-height:38px;">
                    {{ __('crm.confirm_new_date') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRescheduleModal(leadId, leadName, currentDate, currentTime) {
    document.getElementById('modalLeadName').value = leadName;
    document.getElementById('modalDate').value = currentDate || '';
    document.getElementById('modalTime').value = currentTime || '';
    document.getElementById('rescheduleForm').action = '/appointments/' + leadId + '/reschedule';
    document.getElementById('rescheduleModal').style.display = 'flex';
}
function closeRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'none';
}
</script>
</body>
</html>
