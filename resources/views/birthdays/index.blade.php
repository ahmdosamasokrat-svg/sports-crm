<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SokratCRM — {{ __('crm.athletes_birthdays') ?? 'أعياد ميلاد اللاعبين والعملاء' }}</title>
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
  --red-hover: #dc2626;
  --primary: #ef4444;
  --primary-hover: #dc2626;
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
.bday-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}
.bday-stat-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform .15s ease, border-color .15s ease;
}
.bday-stat-card:hover {
  border-color: #cbd5e1;
  transform: translateY(-2px);
}
.bday-stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 0;
  background: transparent !important;
  border: none !important;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.bday-stat-title {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--muted);
  margin-bottom: 4px;
}
.bday-stat-value {
  font-size: 24px;
  font-weight: 800;
  color: var(--dark);
}

/* Filter Shell */
.bday-filter-shell {
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
.bday-tabs {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.bday-tab-btn {
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
.bday-tab-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}
.bday-tab-btn.active {
  background: #ec4899;
  border-color: #ec4899;
  color: #fff;
  box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
}
.bday-tab-badge {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 800;
  background: #f1f5f9;
  color: var(--dark);
}
.bday-tab-btn.active .bday-tab-badge {
  background: rgba(255, 255, 255, 0.25);
  color: #fff;
}

.bday-search-form {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.bday-search-input-wrap {
  position: relative;
  min-width: 240px;
}
.bday-search-input-wrap i {
  position: absolute;
  inset-inline-start: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted);
  pointer-events: none;
  font-size: 13px;
}
.bday-search-input {
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
[dir="rtl"] .bday-search-input {
  padding: 0 14px;
  padding-inline-start: 36px;
}
.bday-search-input:focus {
  outline: none;
  border-color: #ec4899;
  box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.12);
}
.bday-branch-select {
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
.bday-branch-select:focus {
  outline: none;
  border-color: #ec4899;
  box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.12);
}

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
  background: #ec4899;
  border-color: #ec4899;
  color: #fff;
  box-shadow: 0 2px 6px rgba(236, 72, 153, 0.25);
}
.btn.primary:hover {
  background: #db2777;
  border-color: #db2777;
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

/* Athlete Cards Grid */
.bday-athletes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}
.bday-athlete-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 14px;
  transition: all .15s ease;
  position: relative;
  overflow: hidden;
}
.bday-athlete-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
  transform: translateY(-2px);
}
.bday-athlete-card.is-today {
  border-color: #f43f5e;
  background: linear-gradient(135deg, rgba(254, 242, 242, 0.7) 0%, rgba(255, 255, 255, 1) 100%);
}
.bday-ribbon-today {
  position: absolute;
  top: 12px;
  background: #f43f5e;
  color: #fff;
  font-size: 10.5px;
  font-weight: 800;
  padding: 4px 30px;
  box-shadow: 0 2px 8px rgba(244, 63, 94, 0.35);
  text-align: center;
  z-index: 2;
  white-space: nowrap;
  line-height: 1.4;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}
html[dir="rtl"] .bday-ribbon-today,
body[dir="rtl"] .bday-ribbon-today,
[dir="rtl"] .bday-ribbon-today {
  left: -28px;
  right: auto;
  transform: rotate(-45deg);
}
html[dir="ltr"] .bday-ribbon-today,
body[dir="ltr"] .bday-ribbon-today,
[dir="ltr"] .bday-ribbon-today {
  right: -28px;
  left: auto;
  transform: rotate(45deg);
}

.bday-athlete-header {
  display: flex;
  align-items: center;
  gap: 12px;
}
.bday-avatar {
  width: 40px;
  height: 40px;
  border-radius: 0;
  background: transparent !important;
  border: none !important;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.bday-athlete-name {
  font-size: 15px;
  font-weight: 800;
  color: var(--dark);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}
.bday-athlete-name a:hover {
  color: var(--red);
  text-decoration: underline;
}
.bday-athlete-sub {
  font-size: 12px;
  color: var(--muted);
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.bday-info-rows {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 12.5px;
  border-top: 1px dashed var(--line);
  border-bottom: 1px dashed var(--line);
  padding: 10px 0;
}
.bday-info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.bday-info-label {
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 6px;
}
.bday-info-val {
  font-weight: 700;
  color: var(--dark);
}

.bday-athlete-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
}
.bday-reminder-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11.5px;
  font-weight: 700;
  color: #0369a1;
  background: transparent !important;
  border: none !important;
  padding: 0;
}
.bday-turning-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 800;
  color: #9d174d;
  background: transparent !important;
  border: none !important;
  padding: 0;
}

.bday-empty-state {
  background: var(--card);
  border: 2px dashed var(--line);
  border-radius: var(--radius);
  padding: 48px 24px;
  text-align: center;
  color: var(--muted);
}
.bday-empty-icon {
  font-size: 48px;
  color: #cbd5e1;
  margin-bottom: 12px;
}
</style>
</head>
<body>
<div class="crm-app">
    @include('partials.crm-sidebar')

    <main class="crm-main">
        @include('partials.topbar', [
            'title' => __('crm.athletes_birthdays'),
            'subtitle' => '<span>' . __('crm.birthdays_subheading') . '</span>',
            'icon' => 'bi-cake2-fill',
        ])


        <!-- 1. STATS TILES -->
        <div class="bday-stats-grid">
            <div class="bday-stat-card">
                <div class="bday-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/>
                        <path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/>
                        <path d="M2 21h20"/>
                        <path d="M7 8v2"/><path d="M12 8v2"/><path d="M17 8v2"/>
                        <circle cx="7" cy="4" r="1.2" fill="#ef4444"/>
                        <circle cx="12" cy="4" r="1.2" fill="#ef4444"/>
                        <circle cx="17" cy="4" r="1.2" fill="#ef4444"/>
                    </svg>
                </div>
                <div>
                    <div class="bday-stat-title">{{ __('crm.birthdays_today') ?? 'أعياد ميلاد اليوم' }}</div>
                    <div class="bday-stat-value" style="color:#ef4444;">{{ number_format($counts['today'] ?? 0) }}</div>
                </div>
            </div>

            <div class="bday-stat-card">
                <div class="bday-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/>
                        <path d="M8 18h.01"/><path d="M12 18h.01"/>
                    </svg>
                </div>
                <div>
                    <div class="bday-stat-title">{{ __('crm.birthdays_this_week') ?? 'أعياد ميلاد الأسبوع' }}</div>
                    <div class="bday-stat-value" style="color:#d97706;">{{ number_format($counts['week'] ?? 0) }}</div>
                </div>
            </div>

            <div class="bday-stat-card">
                <div class="bday-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <circle cx="12" cy="15" r="2.5" fill="none" stroke="#db2777" stroke-width="2"/>
                    </svg>
                </div>
                <div>
                    <div class="bday-stat-title">{{ __('crm.birthdays_this_month') ?? 'أعياد ميلاد الشهر' }}</div>
                    <div class="bday-stat-value" style="color:#db2777;">{{ number_format($counts['month'] ?? 0) }}</div>
                </div>
            </div>

            <div class="bday-stat-card">
                <div class="bday-stat-icon">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <div class="bday-stat-title">{{ __('crm.total_tracked_birthdays') ?? 'إجمالي المسجلين بتواريخ ميلاد' }}</div>
                    <div class="bday-stat-value" style="color:#0284c7;">{{ number_format($counts['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- 2. FILTER & SEARCH SHELL -->
        <div class="bday-filter-shell">
            <div class="bday-tabs">
                <a href="{{ route('v2.birthdays.index', array_merge(request()->query(), ['tab' => 'today'])) }}"
                   class="bday-tab-btn {{ $tab === 'today' ? 'active' : '' }}">
                    <i class="bi bi-stars"></i>
                    <span>{{ __('crm.today') }}</span>
                    <span class="bday-tab-badge">{{ number_format($counts['today'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.birthdays.index', array_merge(request()->query(), ['tab' => 'week'])) }}"
                   class="bday-tab-btn {{ $tab === 'week' ? 'active' : '' }}">
                    <i class="bi bi-calendar-week"></i>
                    <span>{{ __('crm.this_week') }}</span>
                    <span class="bday-tab-badge">{{ number_format($counts['week'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.birthdays.index', array_merge(request()->query(), ['tab' => 'month'])) }}"
                   class="bday-tab-btn {{ $tab === 'month' ? 'active' : '' }}">
                    <i class="bi bi-calendar-month"></i>
                    <span>{{ __('crm.this_month') }}</span>
                    <span class="bday-tab-badge">{{ number_format($counts['month'] ?? 0) }}</span>
                </a>

                <a href="{{ route('v2.birthdays.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}"
                   class="bday-tab-btn {{ $tab === 'upcoming' ? 'active' : '' }}">
                    <i class="bi bi-arrow-right-circle"></i>
                    <span>{{ __('crm.upcoming_sorted') }}</span>
                </a>
            </div>

            <form method="GET" action="{{ route('v2.birthdays.index') }}" class="bday-search-form">
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($branches->count() > 1)
                <select name="branch_id" class="bday-branch-select" onchange="this.form.submit()">
                    <option value="">-- {{ __('crm.all_branches') }} --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string) $selectedBranchId === (string) $b->id ? 'selected' : '' }}>
                            {{ $b->name_ar ?: $b->name }}
                        </option>
                    @endforeach
                </select>
                @endif

                <div class="bday-search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="bday-search-input" value="{{ $search }}" placeholder="{{ __('crm.search_name_or_phone') }}">
                </div>

                <button type="submit" class="btn soft" style="height:38px; min-height:38px; padding:0 14px;">
                    {{ __('crm.search') }}
                </button>

                @if($search || $selectedBranchId)
                <a href="{{ route('v2.birthdays.index', ['tab' => $tab]) }}" class="btn soft" style="height:38px; min-height:38px; padding:0 12px; color:var(--red);">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </form>
        </div>

        <!-- 3. ATHLETES LIST / CARDS -->
        @if($athletes->isNotEmpty())
            <div class="bday-athletes-grid">
                @foreach($athletes as $athlete)
                    @php
                        $birthDate = $athlete->birth_date;
                        $nextBirthday = $birthDate ? \App\Services\BirthdayService::getNextBirthday($birthDate) : null;
                        $turningAge = $birthDate ? \App\Services\BirthdayService::getAgeOnNextBirthday($birthDate) : null;
                        $daysRemaining = $birthDate ? \App\Services\BirthdayService::getDaysUntilBirthday($birthDate) : null;
                        $isToday = $daysRemaining === 0;
                    @endphp

                    <div class="bday-athlete-card {{ $isToday ? 'is-today' : '' }}">
                        @if($isToday)
                            <div class="bday-ribbon-today">
                                {{ __('crm.today_exclamation') }}
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="display:inline-block; vertical-align:middle; background:transparent; border:none; margin-inline-start:2px;" aria-hidden="true">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                        @endif

                        <div class="bday-athlete-header">
                            <div class="bday-avatar" style="background:transparent; border:none;">
                                @if($isToday)
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                                        <rect x="3" y="8" width="18" height="14" rx="2"/>
                                        <path d="M12 8v14"/>
                                        <path d="M3 13h18"/>
                                        <path d="M8 8a3 3 0 0 1 0-6c1.5 0 4 3 4 3s2.5-3 4-3a3 3 0 0 1 0 6"/>
                                    </svg>
                                @else
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                @endif
                            </div>
                            <div style="min-width:0; flex:1;">
                                <h4 class="bday-athlete-name">
                                    <a href="{{ route('v2.leads.show', $athlete) }}" title="{{ __('crm.view_athlete_details') }}">
                                        {{ $athlete->name }}
                                    </a>
                                </h4>
                                <div class="bday-athlete-sub">
                                    <span><i class="bi bi-activity"></i> {{ $athlete->activity ?: __('crm.activity_unspecified') }}</span>
                                    @if($athlete->branch)
                                        <span>•</span>
                                        <span><i class="bi bi-geo-alt"></i> {{ $athlete->branch->name_ar ?: $athlete->branch->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="bday-info-rows">
                            <div class="bday-info-item">
                                <span class="bday-info-label"><i class="bi bi-calendar2-heart"></i> {{ __('crm.birth_date') }}</span>
                                <span class="bday-info-val">{{ $birthDate?->format('Y-m-d') }}</span>
                            </div>

                            <div class="bday-info-item">
                                <span class="bday-info-label"><i class="bi bi-arrow-up-right-circle"></i> {{ __('crm.stage_and_status') }}</span>
                                <span class="bday-info-val">
                                    <span class="badge" style="background:#f1f5f9; color:#475569; font-size:11px;">
                                        {{ $athlete->status?->stage?->name_ar ?? $athlete->status?->name_ar ?? 'غير مصنف' }}
                                    </span>
                                </span>
                            </div>

                            <div class="bday-info-item">
                                <span class="bday-info-label"><i class="bi bi-person-badge"></i> {{ __('crm.responsible_employee') }}</span>
                                <span class="bday-info-val">{{ $athlete->assignedUser?->name ?? __('crm.unassigned') }}</span>
                            </div>

                            @if($athlete->phone)
                            <div class="bday-info-item">
                                <span class="bday-info-label"><i class="bi bi-telephone"></i> {{ __('crm.phone') }}</span>
                                <span class="bday-info-val" dir="ltr" style="font-family:monospace;">{{ $athlete->phone }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="bday-athlete-footer">
                            <span class="bday-turning-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9d174d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none; vertical-align:middle; display:inline-block;" aria-hidden="true">
                                    <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/>
                                    <path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/>
                                    <path d="M2 21h20"/><path d="M7 8v2"/><path d="M12 8v2"/><path d="M17 8v2"/>
                                </svg>
                                @if($isToday)
                                    <b>{{ __('crm.completed_today_age', ['age' => $turningAge]) }}</b>
                                @else
                                    {{ __('crm.turning_age_date', ['age' => $turningAge, 'date' => $nextBirthday?->format('d/m')]) }}
                                @endif
                            </span>

                            <span class="bday-reminder-badge" title="{{ __('crm.internal_reminder_tooltip') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none; vertical-align:middle; display:inline-block;" aria-hidden="true">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                @if($isToday)
                                    <b>{{ __('crm.reminder_today') }}</b>
                                @elseif($daysRemaining === 1)
                                    {{ __('crm.tomorrow') }}
                                @else
                                    {{ __('crm.after_days', ['days' => $daysRemaining]) }}
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bday-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none; margin-bottom:12px; display:inline-block;" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <line x1="10" y1="14" x2="14" y2="18"/>
                    <line x1="14" y1="14" x2="10" y2="18"/>
                </svg>
                <h3 style="margin:0 0 6px; font-size:16px; color:var(--dark);">
                    @if($tab === 'today')
                        {{ __('crm.no_birthdays_today') }}
                    @elseif($tab === 'week')
                        {{ __('crm.no_birthdays_this_week') }}
                    @elseif($tab === 'month')
                        {{ __('crm.no_birthdays_this_month') }}
                    @else
                        {{ __('crm.no_birthdays_matching_search') }}
                    @endif
                </h3>
                <p style="margin:0; font-size:13px;">
                    {{ __('crm.birthdays_calculated_auto_desc') }}
                </p>
            </div>
        @endif
    </main>
</div>
</body>
</html>
