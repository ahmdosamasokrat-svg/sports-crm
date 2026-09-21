@extends('leads.transfer-layout')

@section('title', __('crm.daily_tasks'))
@section('page-title', __('crm.daily_tasks'))
@section('page-description', __('crm.daily_tasks_subtitle'))

@section('top-actions')
 <div class="task-top-actions">
  <span class="task-date-pill">
   <i class="bi bi-calendar3"></i>
   {{ $todayDateFormatted }}
  </span>
 </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
 .daily-tasks-page {
  --task-red: #dc2637;
  --task-blue: #3478f6;
  --task-green: #169a64;
  --task-amber: #e59b16;
  --task-purple: #7b61df;
  --task-border: #e4e8ef;
  --task-bg-soft: #f8fafc;
  --task-card-shadow: 0 10px 30px #1720330a;
  --task-card-shadow-hover: 0 16px 36px #17203314;
 }

 html.dark-mode .daily-tasks-page {
  --task-border: rgba(255, 255, 255, 0.08);
  --task-bg-soft: rgba(255, 255, 255, 0.04);
  --task-card-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  --task-card-shadow-hover: 0 16px 36px rgba(0, 0, 0, 0.7);
 }

 html.dark-mode .task-kpi-card {
  background: var(--bg-card, rgba(24, 24, 27, 0.75)) !important;
  border-color: var(--task-border) !important;
  backdrop-filter: var(--glass-blur, blur(16px)) !important;
  -webkit-backdrop-filter: var(--glass-blur, blur(16px)) !important;
 }

 html.dark-mode .task-kpi-info strong {
  color: #f4f4f5 !important;
 }

 html.dark-mode .task-kpi-info span {
  color: var(--text-muted, #a1a1aa) !important;
 }

 html.dark-mode .task-progress-box,
 html.dark-mode .task-filters-card,
 html.dark-mode .task-section-block {
  background: var(--bg-card, rgba(24, 24, 27, 0.75)) !important;
  border-color: var(--task-border) !important;
  backdrop-filter: var(--glass-blur, blur(16px)) !important;
 }

 html.dark-mode .task-progress-head,
 html.dark-mode .task-section-header h2 {
  color: #f4f4f5 !important;
 }

 html.dark-mode .task-section-header {
  background: rgba(255, 255, 255, 0.03) !important;
  border-bottom-color: rgba(255, 255, 255, 0.08) !important;
 }

 html.dark-mode .task-item-card {
  background: rgba(255, 255, 255, 0.03) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
 }

 html.dark-mode .task-item-card:hover {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
 }

 /* Top summary & KPI cards */
 .task-kpis-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 18px;
 }

 .task-kpi-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border: 1px solid var(--task-border);
  border-radius: 16px;
  background: #fff;
  color: inherit;
  text-decoration: none;
  box-shadow: var(--task-card-shadow);
  transition: transform .18s, border-color .18s, box-shadow .18s;
  position: relative;
  overflow: hidden;
 }

 .task-kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--task-card-shadow-hover);
  border-color: #cbd5e1;
 }

 .task-kpi-card.active {
  border-color: currentColor;
  box-shadow: 0 0 0 2px currentColor;
 }

 .task-kpi-icon {
  width: 44px;
  height: 44px;
  flex: 0 0 44px;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: transparent !important;
  border-radius: 0 !important;
  padding: 0 !important;
 }

 .task-kpi-icon svg {
  display: block;
  width: 28px;
  height: 28px;
  margin: auto;
 }

 .task-kpi-card.overdue { color: var(--task-red, #ef4444); }
 .task-kpi-card.overdue .task-kpi-icon { background: transparent !important; color: var(--task-red, #ef4444); }

 .task-kpi-card.today { color: var(--task-blue, #3b82f6); }
 .task-kpi-card.today .task-kpi-icon { background: transparent !important; color: var(--task-blue, #3b82f6); }

 .task-kpi-card.completed { color: var(--task-green, #10b981); }
 .task-kpi-card.completed .task-kpi-icon { background: transparent !important; color: var(--task-green, #10b981); }

 .task-kpi-card.no-date { color: var(--task-amber, #f59e0b); }
 .task-kpi-card.no-date .task-kpi-icon { background: transparent !important; color: var(--task-amber, #f59e0b); }

 .task-kpi-info {
  min-width: 0;
  flex: 1;
 }

 .task-kpi-info strong {
  display: block;
  font-size: 22px;
  font-weight: 800;
  color: #1e293b;
  font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace !important;
  font-variant-numeric: tabular-nums;
  line-height: 1.2;
  overflow-wrap: anywhere;
 }

 .task-kpi-info span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  line-height: 1.3;
  overflow-wrap: anywhere;
 }

 /* Progress bar strip */
 .task-progress-box {
  margin-bottom: 20px;
  padding: 14px 18px;
  border: 1px solid var(--task-border);
  border-radius: 14px;
  background: #fff;
  box-shadow: var(--task-card-shadow);
 }

 .task-progress-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #334155;
 }

 .task-progress-ratio {
  color: #64748b;
  font-size: 12px;
 }

 .task-progress-bar-bg {
  height: 8px;
  border-radius: 999px;
  background: #e2e8f0;
  overflow: hidden;
 }

 .task-progress-bar-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #10b981, #059669);
 }

 /* Filters and control bar - SINGLE COMPACT ROW */
 .task-filters-card {
  margin-bottom: 12px;
  padding: 6px 10px;
  border: 1px solid var(--task-border);
  border-radius: 10px;
  background: #fff;
  box-shadow: var(--task-card-shadow);
 }

 .task-filters-form {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  width: 100%;
 }

 .task-scope-pills {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0;
  border: none;
  flex: 1 1 auto;
  flex-wrap: wrap;
 }

 .task-scope-pill {
  min-height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 2px 8px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: #f8fafc;
  color: #475569;
  text-decoration: none;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  flex: 1 1 auto;
  text-align: center;
  transition: all .16s;
 }

 .task-scope-pill:hover {
  background: #f1f5f9;
  color: #1e293b;
 }

 .task-scope-pill.active {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #2563eb;
 }

 .task-scope-pill.overdue.active {
  border-color: var(--task-red);
  background: #fef2f2;
  color: var(--task-red);
 }

 .task-scope-pill.today.active {
  border-color: var(--task-blue);
  background: #eff6ff;
  color: var(--task-blue);
 }

 .task-scope-pill.academy-trials {
  border-color: #cbd5e1;
 }
 .task-scope-pill.academy-trials.active {
  border-color: #0284c7;
  background: #f0f9ff;
  color: #0284c7;
 }

 .task-scope-pill.academy-attended {
  border-color: #cbd5e1;
 }
 .task-scope-pill.academy-attended.active {
  border-color: #d97706;
  background: #fffbeb;
  color: #d97706;
 }

 .task-scope-pill.academy-noshow {
  border-color: #cbd5e1;
 }
 .task-scope-pill.academy-noshow.active {
  border-color: #e11d48;
  background: #fff1f2;
  color: #e11d48;
 }

 .task-scope-pill.upcoming.active {
  border-color: var(--task-purple);
  background: #faf5ff;
  color: var(--task-purple);
 }

 .task-scope-pill.no-date.active {
  border-color: var(--task-amber);
  background: #fffbeb;
  color: #b45309;
 }

 .task-scope-pill.completed.active {
  border-color: var(--task-green);
  background: #f0fdf4;
  color: var(--task-green);
 }

 .task-pill-badge {
  display: inline-block;
  padding: 1px 5px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #1e293b;
  font-size: 10px;
  font-weight: 800;
  line-height: 1.2;
 }

 .task-scope-pill.active .task-pill-badge {
  background: #dbeafe;
  color: #1d4ed8;
 }

 .task-scope-pill.overdue.active .task-pill-badge {
  background: #fee2e2;
  color: #b91c1c;
 }

 .task-scope-pill.today.active .task-pill-badge {
  background: #dbeafe;
  color: #1d4ed8;
 }

 .task-scope-pill.upcoming.active .task-pill-badge {
  background: #f3e8ff;
  color: #6b21a8;
 }

 .task-scope-pill.no-date.active .task-pill-badge {
  background: #fef3c7;
  color: #92400e;
 }

 .task-scope-pill.completed.active .task-pill-badge {
  background: #dcfce7;
  color: #15803d;
 }

 .task-filters-row {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex: 2 1 auto;
  flex-wrap: wrap;
 }

 .task-search-input-wrap {
  flex: 2 1 160px;
  min-width: 140px;
  position: relative;
 }

 .task-search-input-wrap i {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 12px;
 }

 [dir="rtl"] .task-search-input-wrap i { right: 9px; }
 [dir="ltr"] .task-search-input-wrap i { left: 9px; }

 .task-search-input {
  width: 100%;
  height: 32px;
  border: 1px solid #cbd5e1;
  border-radius: 7px;
  background: #f8fafc;
  color: #1e293b;
  font-size: 11.5px;
  outline: none;
  transition: border-color .18s, background .18s;
 }

 [dir="rtl"] .task-search-input { padding: 0 26px 0 8px; }
 [dir="ltr"] .task-search-input { padding: 0 8px 0 26px; }

 .task-search-input:focus {
  border-color: #3b82f6;
  background: #fff;
  box-shadow: 0 0 0 2px #3b82f61a;
 }

 .task-search-btn {
  height: 32px;
  min-height: 32px !important;
  padding: 0 10px;
  border-radius: 7px;
  font-size: 11.5px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
 }

 .task-clear-btn {
  height: 32px;
  min-height: 32px !important;
  padding: 0 8px;
  border-radius: 7px;
  font-size: 11.5px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
 }

 .task-select-filter {
  height: 32px;
  min-width: 105px;
  padding: 0 8px;
  border: 1px solid #cbd5e1;
  border-radius: 7px;
  background: #f8fafc;
  color: #334155;
  font-size: 11.5px;
  outline: none;
  cursor: pointer;
 }

 .task-select-filter:focus {
  border-color: #3b82f6;
  background: #fff;
 }

 .task-filters-card .crm-dropdown {
  min-width: 110px;
  flex: 1 1 120px;
  width: auto;
 }

 .task-filters-card .crm-dropdown-trigger {
  height: 32px !important;
  min-height: 32px !important;
  padding: 0 8px !important;
  font-size: 11.5px !important;
  border-radius: 7px !important;
  gap: 4px !important;
  width: 100%;
 }

 .task-filters-card .crm-dropdown-text {
  font-size: 11.5px !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
 }

 .task-view-toggle {
  display: inline-flex;
  align-items: center;
  border: 1px solid #cbd5e1;
  border-radius: 7px;
  overflow: hidden;
  background: #f8fafc;
  height: 32px;
 }

 .task-view-btn {
  height: 30px;
  padding: 0 8px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all .16s;
 }

 .task-view-btn.active {
  background: #fff;
  color: #1e293b;
  box-shadow: 0 2px 6px #0000000d;
 }

 /* Sections styling */
 .task-section-block {
  margin-bottom: 28px;
  border: 1px solid var(--task-border);
  border-radius: 18px;
  background: #fff;
  box-shadow: var(--task-card-shadow);
  overflow: hidden;
 }

 .task-section-block.overdue-block {
  border-color: #fca5a5;
 }

 .task-section-block.today-block {
  border-color: #bfdbfe;
 }

 .task-section-block.upcoming-block {
  border-color: #e9d5ff;
 }

 .task-section-block.no-date-block {
  border-color: #fde68a;
 }

 .task-section-block.completed-block {
  border-color: #bbf7d0;
 }

 .task-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  background: #f8fafc;
 }

 .task-section-title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
 }

 .task-section-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #94a3b8;
 }

 .overdue-block .task-section-indicator { background: var(--task-red); }
 .today-block .task-section-indicator { background: var(--task-blue); }
 .upcoming-block .task-section-indicator { background: var(--task-purple); }
 .no-date-block .task-section-indicator { background: var(--task-amber); }
 .completed-block .task-section-indicator { background: var(--task-green); }

 .task-section-header h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 800;
  color: #1e293b;
 }

 .task-section-header p {
  margin: 3px 0 0;
  color: #64748b;
  font-size: 12px;
 }

 .task-section-count-badge {
  min-width: 32px;
  height: 28px;
  display: inline-grid;
  place-items: center;
  padding: 0 8px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #334155;
  font-size: 12px;
  font-weight: 800;
 }

 /* Task Cards Grid */
 .task-items-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
  padding: 20px;
  width: 100%;
 }

 .task-item-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 18px;
  border: 1px solid var(--task-border);
  border-radius: 14px;
  background: #fff;
  transition: transform .18s, box-shadow .18s, border-color .18s;
  position: relative;
 }

 .task-item-card:hover {
  transform: translateY(-2px);
  border-color: #cbd5e1;
  box-shadow: 0 12px 28px #1720330f;
 }

 .task-card-topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
 }

 .task-lead-info {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  flex: 1;
 }

 .task-lead-avatar {
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  background: #e0e7ff;
  color: #3730a3;
  font-size: 16px;
  font-weight: 800;
 }

 .task-lead-names {
  min-width: 0;
 }

 .task-lead-names strong a {
  color: #1e293b;
  font-size: 15px;
  font-weight: 800;
  text-decoration: none;
 }

 .task-lead-names strong a:hover {
  color: #2563eb;
 }

 .task-lead-names small {
  display: block;
  margin-top: 2px;
  color: #64748b;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
 }

 .task-card-top-pills {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
 }

 .task-category-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border: 1px solid transparent;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
 }

 .task-status-pill {
  padding: 4px 10px;
  border: 1px solid transparent;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
 }

 /* Urgency and follow-up time strip */
 .task-time-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 12px;
  margin-bottom: 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
 }

 .task-time-strip.overdue {
  background: #fef2f2;
  color: var(--task-red);
 }

 .task-time-strip.today {
  background: #eff6ff;
  color: var(--task-blue);
 }

 .task-time-strip.upcoming {
  background: #faf5ff;
  color: var(--task-purple);
 }

 .task-time-strip.no-date {
  background: #fffbeb;
  color: var(--task-amber);
 }

 /* Key metadata grid */
 .task-meta-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-bottom: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #f8fafc;
  font-size: 12px;
 }

 .task-meta-item span {
  display: block;
  color: #94a3b8;
  font-size: 10px;
  font-weight: 700;
  margin-bottom: 2px;
 }

 .task-meta-item strong {
  display: block;
  color: #334155;
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
 }

 .task-phone-link {
  color: #0284c7;
  text-decoration: none;
  font-weight: 700;
  direction: ltr;
  display: inline-block;
 }

 /* Last followup summary box */
 .task-last-followup {
  margin-bottom: 14px;
  padding: 10px 12px;
  border: 1px solid #f1f5f9;
  background: #f8fafc;
  border-radius: 8px;
  font-size: 12px;
 }

 .task-last-followup-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
 }

 .task-last-followup-note {
  color: #334155;
  font-size: 12px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
 }

 /* Action buttons grid */
 .task-actions-toolbar {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  gap: 6px;
  padding-top: 10px;
  border-top: 1px solid #f1f5f9;
 }

 .task-btn-main {
  height: 44px;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 14px;
  border-radius: 10px;
  background: #2563eb;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: background .16s;
 }

 .task-btn-main:hover {
  background: #1d4ed8;
 }

 .task-btn-icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  display: inline-grid;
  place-items: center;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #475569;
  font-size: 16px;
  text-decoration: none;
  cursor: pointer;
  transition: all .16s;
 }

 .task-btn-icon:hover {
  background: #f1f5f9;
  color: #1e293b;
  border-color: #94a3b8;
 }

 .task-btn-icon.whatsapp {
  color: #16a34a;
  border-color: #bbf7d0;
  background: #f0fdf4;
 }

  .task-btn-icon.whatsapp:hover {
   background: #dcfce7;
  }

  /* =========================================================
     Option 3: Notion / Linear Minimal Ticket Card
     ========================================================= */
  .task-item-card.task-ticket {
   display: flex;
   flex-direction: column;
   justify-content: space-between;
   padding: 10px 14px;
   border: 1px solid var(--task-border);
   border-radius: 10px;
   background: #fff;
   gap: 6px;
   position: relative;
   min-height: auto;
   transition: border-color .16s, box-shadow .16s, transform .16s;
  }

  [dir="rtl"] .task-item-card.task-ticket { border-right-width: 3.5px; }
  [dir="ltr"] .task-item-card.task-ticket { border-left-width: 3.5px; }

  [dir="rtl"] .task-item-card.task-ticket.overdue { border-right-color: var(--task-red); }
  [dir="ltr"] .task-item-card.task-ticket.overdue { border-left-color: var(--task-red); }
  [dir="rtl"] .task-item-card.task-ticket.today { border-right-color: var(--task-blue); }
  [dir="ltr"] .task-item-card.task-ticket.today { border-left-color: var(--task-blue); }
  [dir="rtl"] .task-item-card.task-ticket.upcoming { border-right-color: var(--task-purple); }
  [dir="ltr"] .task-item-card.task-ticket.upcoming { border-left-color: var(--task-purple); }
  [dir="rtl"] .task-item-card.task-ticket.no-date { border-right-color: var(--task-amber); }
  [dir="ltr"] .task-item-card.task-ticket.no-date { border-left-color: var(--task-amber); }

  .task-item-card.task-ticket:hover {
   transform: translateY(-1.5px);
   border-color: #cbd5e1;
   box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
  }

  /* Tier 1: Header Line */
  .task-ticket-header {
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 8px;
   flex-wrap: wrap;
  }

  .task-ticket-lead {
   display: inline-flex;
   align-items: center;
   gap: 6px;
   min-width: 0;
   flex: 1 1 auto;
  }

  .task-ticket-dot {
   width: 7px;
   height: 7px;
   border-radius: 50%;
   flex: 0 0 7px;
  }
  .task-ticket-dot.overdue { background: var(--task-red); box-shadow: 0 0 0 2px #fee2e2; }
  .task-ticket-dot.today { background: var(--task-blue); box-shadow: 0 0 0 2px #dbeafe; }
  .task-ticket-dot.upcoming { background: var(--task-purple); box-shadow: 0 0 0 2px #f3e8ff; }
  .task-ticket-dot.no-date { background: var(--task-amber); box-shadow: 0 0 0 2px #fef3c7; }

  .task-ticket-time {
   font-size: 11px;
   font-weight: 700;
   white-space: nowrap;
   padding: 1px 6px;
   border-radius: 4px;
   display: inline-flex;
   align-items: center;
   gap: 3px;
  }
  .task-ticket-time.overdue { background: #fef2f2; color: var(--task-red); }
  .task-ticket-time.today { background: #eff6ff; color: var(--task-blue); }
  .task-ticket-time.upcoming { background: #faf5ff; color: var(--task-purple); }
  .task-ticket-time.no-date { background: #fffbeb; color: #b45309; }

  .task-ticket-name {
   font-size: 13.5px;
   font-weight: 800;
   white-space: nowrap;
   overflow: hidden;
   text-overflow: ellipsis;
   max-width: 170px;
  }

  .task-ticket-name a {
   color: #0f172a;
   text-decoration: none;
  }
  .task-ticket-name a:hover {
   color: #2563eb;
  }

  .task-ticket-sep {
   color: #cbd5e1;
   font-size: 11px;
  }

  .task-ticket-company {
   color: #64748b;
   font-size: 12px;
   font-weight: 600;
   white-space: nowrap;
   overflow: hidden;
   text-overflow: ellipsis;
   max-width: 140px;
  }

  .task-ticket-badges {
   display: inline-flex;
   align-items: center;
   gap: 4px;
   flex-shrink: 0;
  }

  .task-ticket-user {
   display: inline-flex;
   align-items: center;
   gap: 3px;
   padding: 2px 7px;
   border-radius: 999px;
   background: #f1f5f9;
   color: #475569;
   font-size: 11px;
   font-weight: 700;
   white-space: nowrap;
   max-width: 120px;
   overflow: hidden;
   text-overflow: ellipsis;
  }

  /* Tier 2: Note / Context */
  .task-ticket-body {
   margin: 0;
  }

  .task-ticket-note {
   display: flex;
   align-items: center;
   gap: 6px;
   padding: 4px 8px;
   background: #f8fafc;
   border: 1px solid #f1f5f9;
   border-radius: 6px;
   font-size: 11.5px;
   line-height: 1.4;
   color: #475569;
  }

  .task-ticket-note i {
   color: #94a3b8;
   font-size: 11px;
   flex-shrink: 0;
  }

  .task-note-text {
   color: #334155;
   font-weight: 600;
   white-space: nowrap;
   overflow: hidden;
   text-overflow: ellipsis;
   flex: 1 1 auto;
  }

  .task-note-meta {
   color: #94a3b8;
   font-size: 10.5px;
   white-space: nowrap;
   flex-shrink: 0;
  }

  .task-note-empty {
   color: #94a3b8;
   font-style: italic;
   font-size: 11px;
  }

  /* =========================================================
     Redesigned Actions Layout: Purpose-Driven Dual-Row Dock
     ========================================================= */
  .task-ticket-actions {
   display: flex;
   flex-direction: column;
   gap: 5px;
   padding-top: 8px;
   border-top: 1px solid #f1f5f9;
   margin-top: 4px;
  }

  .task-btn-svg {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   flex-shrink: 0;
  }

  /* 1. Communication Buttons Group (Call + WhatsApp) */
  .task-action-comm-group {
   display: flex;
   align-items: center;
   gap: 6px;
   width: 100%;
  }

  .task-btn-comm {
   height: 32px;
   border-radius: 7px;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   gap: 6px;
   text-decoration: none;
   font-size: 12px;
   font-weight: 700;
   border: 1px solid transparent;
   cursor: pointer;
   transition: all .16s ease;
   box-sizing: border-box;
  }

  .task-btn-comm.call {
   flex: 3 1 auto;
   background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
   color: #ffffff;
   box-shadow: 0 2px 6px rgba(37, 99, 235, 0.22);
  }
  .task-btn-comm.call:hover {
   background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
   box-shadow: 0 4px 10px rgba(37, 99, 235, 0.32);
   transform: translateY(-0.5px);
  }

  .task-btn-comm.whatsapp {
   flex: 2 1 auto;
   background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
   color: #ffffff;
   box-shadow: 0 2px 6px rgba(22, 163, 74, 0.22);
  }
  .task-btn-comm.whatsapp:hover {
   background: linear-gradient(135deg, #166534 0%, #15803d 100%);
   box-shadow: 0 4px 10px rgba(22, 163, 74, 0.32);
   transform: translateY(-0.5px);
  }

  .task-btn-comm.disabled {
   width: 100%;
   background: #f8fafc;
   color: #94a3b8;
   border-color: #e2e8f0;
   cursor: not-allowed;
   box-shadow: none;
  }

  /* 2. Management & Logging Tools Group (Segmented 3-Column Bar) */
  .task-action-tools-group {
   display: grid;
   grid-template-columns: repeat(3, 1fr);
   gap: 5px;
   width: 100%;
  }

  .task-tool-btn {
   height: 27px;
   border-radius: 6px;
   background: #ffffff;
   border: 1px solid #cbd5e1;
   color: #334155;
   font-size: 11px;
   font-weight: 700;
   display: inline-flex;
   align-items: center;
   justify-content: center;
   gap: 5px;
   text-decoration: none;
   cursor: pointer;
   transition: all .14s ease;
   padding: 0 6px;
   white-space: nowrap;
   box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
  }

  .task-tool-btn.followup {
   color: #1e40af;
  }
  .task-tool-btn.followup svg {
   color: #2563eb;
  }
  .task-tool-btn.followup:hover {
   background: #eff6ff;
   border-color: #93c5fd;
   color: #1d4ed8;
  }

  .task-tool-btn.reschedule {
   color: #b45309;
  }
  .task-tool-btn.reschedule svg {
   color: #d97706;
  }
  .task-tool-btn.reschedule:hover {
   background: #fffbeb;
   border-color: #fde68a;
   color: #92400e;
  }

  .task-tool-btn.details {
   color: #0369a1;
  }
  .task-tool-btn.details svg {
   color: #0284c7;
  }
  .task-tool-btn.details:hover {
   background: #f0f9ff;
   border-color: #bae6fd;
   color: #075985;
  }

  /* Dark Mode Support for Tickets */
  html.dark-mode .task-item-card.task-ticket {
   background: var(--bg-card, rgba(24, 24, 27, 0.75)) !important;
   border-color: var(--task-border) !important;
  }
  html.dark-mode .task-ticket-name a {
   color: #f1f5f9 !important;
  }
  html.dark-mode .task-ticket-note {
   background: rgba(255, 255, 255, 0.04) !important;
   border-color: rgba(255, 255, 255, 0.08) !important;
   color: #cbd5e1 !important;
  }
  html.dark-mode .task-note-text {
   color: #e2e8f0 !important;
  }
  html.dark-mode .task-ticket-actions {
   border-top-color: rgba(255, 255, 255, 0.08) !important;
  }
  html.dark-mode .task-btn-comm.call {
   box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
  }
  html.dark-mode .task-btn-comm.whatsapp {
   box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
  }
  html.dark-mode .task-tool-btn {
   background: rgba(255, 255, 255, 0.05) !important;
   border-color: rgba(255, 255, 255, 0.12) !important;
   color: #f1f5f9 !important;
   box-shadow: none;
  }
  html.dark-mode .task-tool-btn.followup:hover {
   background: rgba(37, 99, 235, 0.15) !important;
   border-color: rgba(37, 99, 235, 0.4) !important;
   color: #93c5fd !important;
  }
  html.dark-mode .task-tool-btn.reschedule:hover {
   background: rgba(217, 119, 6, 0.15) !important;
   border-color: rgba(217, 119, 6, 0.4) !important;
   color: #fde68a !important;
  }
  html.dark-mode .task-tool-btn.details:hover {
   background: rgba(2, 132, 199, 0.15) !important;
   border-color: rgba(2, 132, 199, 0.4) !important;
   color: #7dd3fc !important;
  }
  html.dark-mode .task-ticket-user {
   background: rgba(255, 255, 255, 0.08) !important;
   color: #cbd5e1 !important;
  }
 /* Table View */
 .task-table-wrap {
  overflow-x: auto;
  padding: 10px 20px 20px;
 }

 .task-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13px;
 }

 .task-table th {
  padding: 12px 14px;
  background: #f8fafc;
  color: #64748b;
  font-weight: 700;
  font-size: 11px;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
 }

 [dir="rtl"] .task-table th { text-align: right; }
 [dir="ltr"] .task-table th { text-align: left; }

 .task-table td {
  padding: 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #1e293b;
  vertical-align: middle;
 }

 .task-table tr:hover td {
  background: #f8fafc;
 }

 .task-table-lead-link {
  color: #1e293b;
  text-decoration: none;
 }
 .task-table-lead-link:hover {
  color: #2563eb;
 }
 html.dark-mode .task-table-lead-link {
  color: #f4f4f5 !important;
 }
 .task-table-company,
 .task-table-stage,
 .task-table-last-time {
  display: block;
  color: #64748b;
 }
 html.dark-mode .task-table-company,
 html.dark-mode .task-table-stage,
 html.dark-mode .task-table-last-time {
  color: var(--text-muted, #a1a1aa) !important;
 }
 .task-table-date {
  font-weight: 700;
  color: #1e293b;
 }
 html.dark-mode .task-table-date {
  color: #f4f4f5 !important;
 }
 .task-table-last-outcome {
  font-size: 12px;
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
 }
 html.dark-mode .task-table-last-outcome {
  color: #d4d4d8 !important;
 }
 .task-table-empty-followup {
  color: #94a3b8;
  font-size: 11px;
  font-style: italic;
 }
 html.dark-mode .task-table-empty-followup {
  color: #71717a !important;
 }
 /* Empty State */
 .task-empty-card {
  padding: 42px 20px;
  text-align: center;
  color: #64748b;
 }

 .task-empty-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 14px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #f1f5f9;
  color: #94a3b8;
  font-size: 26px;
 }

 .task-empty-card strong {
  display: block;
  font-size: 16px;
  color: #334155;
  margin-bottom: 6px;
 }

 /* Modal Dialogs */
 .task-modal {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 1000;
  align-items: center;
  justify-content: center;
  padding: 20px;
 }

 .task-modal.open {
  display: flex;
 }

 .task-modal-backdrop {
  position: absolute;
  inset: 0;
  background: #0f172a80;
  backdrop-filter: blur(4px);
 }

 .task-modal-content {
  position: relative;
  width: min(520px, 100%);
  max-height: calc(100vh - 40px);
  overflow-y: auto;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 25px 60px #0f172a33;
  padding: 24px;
 }

 .task-modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 14px;
  margin-bottom: 18px;
  border-bottom: 1px solid #e2e8f0;
 }

 .task-modal-head h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #1e293b;
 }

 .task-modal-close {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  padding: 0;
  cursor: pointer;
  transition: all .16s ease;
 }

 .task-modal-close:hover {
  background: #fee2e2;
  color: var(--task-red);
  transform: scale(1.05);
 }

 .task-form-group {
  margin-bottom: 16px;
 }

 .task-form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  font-weight: 700;
  color: #334155;
 }

 .task-form-control {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #f8fafc;
  color: #1e293b;
  font-size: 13px;
  outline: none;
 }

 .task-form-control:focus {
  border-color: #3b82f6;
  background: #fff;
  box-shadow: 0 0 0 3px #3b82f61a;
 }

 .task-quick-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
 }

 .task-preset-btn {
  padding: 4px 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  background: #fff;
  color: #475569;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
 }

 .task-preset-btn:hover {
  background: #eff6ff;
  border-color: #3b82f6;
  color: #2563eb;
 }

 .task-modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 16px;
  margin-top: 20px;
  border-top: 1px solid #e2e8f0;
 }

 /* Toast Notification */
 .task-toast {
  position: fixed;
  bottom: 24px;
  z-index: 2000;
  padding: 12px 20px;
  border-radius: 12px;
  background: #1e293b;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  box-shadow: 0 10px 30px #00000026;
  display: flex;
  align-items: center;
  gap: 8px;
  opacity: 0;
  transform: translateY(20px);
  transition: all .25s ease;
  pointer-events: none;
 }

 [dir="rtl"] .task-toast { right: 24px; }
 [dir="ltr"] .task-toast { left: 24px; }

 .task-toast.show {
  opacity: 1;
  transform: translateY(0);
 }

 .task-date-pill {
  min-height: 38px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  color: #334155;
  font-size: 12px;
  font-weight: 700;
 }

 @media (max-width: 1024px) {
  .task-kpis-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .task-items-cards { grid-template-columns: 1fr; }
 }

 @media (max-width: 640px) {
  .task-kpis-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
  .task-kpi-card { padding: 10px 12px; }
  .task-kpi-icon { width: 40px; height: 40px; flex: 0 0 40px; font-size: 18px; }
  .task-kpi-info strong { font-size: 20px; }
  .task-kpi-info span { font-size: 10px; }
  .task-filters-row { flex-direction: column; align-items: stretch; }
  .task-actions-toolbar { grid-template-columns: 1fr repeat(auto-fill, 44px); gap: 6px; }
  .task-table-wrap { padding: 8px 10px 16px; -webkit-overflow-scrolling: touch; }
  .task-table { min-width: 800px; }
 }
 @media (max-width: 360px) {
  .task-kpis-grid { grid-template-columns: 1fr; }
 }
</style>
@endpush

@section('content')
<div class="daily-tasks-page">

 {{-- 1. KPI Cards Grid --}}
 <div class="task-kpis-grid">
  <a class="task-kpi-card overdue {{ $scope === 'overdue' ? 'active' : '' }}" href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'overdue'])) }}">
   <div class="task-kpi-icon">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
     <line x1="12" y1="9" x2="12" y2="13"></line>
     <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>
   </div>
   <div class="task-kpi-info">
    <strong>{{ number_format($overdueCount) }}</strong>
    <span>{{ __('crm.tasks_overdue') }}</span>
   </div>
  </a>

  <a class="task-kpi-card today {{ $scope === 'today' ? 'active' : '' }}" href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'today'])) }}">
   <div class="task-kpi-icon">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
     <line x1="16" y1="2" x2="16" y2="6"></line>
     <line x1="8" y1="2" x2="8" y2="6"></line>
     <line x1="3" y1="10" x2="21" y2="10"></line>
     <path d="M9 16l2 2 4-4"></path>
    </svg>
   </div>
   <div class="task-kpi-info">
    <strong>{{ number_format($todayCount) }}</strong>
    <span>{{ __('crm.tasks_due_today') }}</span>
   </div>
  </a>

  <a class="task-kpi-card completed {{ $scope === 'completed' ? 'active' : '' }}" href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'completed'])) }}">
   <div class="task-kpi-icon">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
     <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
   </div>
   <div class="task-kpi-info">
    <strong>{{ number_format($completedTodayCount) }}</strong>
    <span>{{ __('crm.tasks_completed_today') }}</span>
   </div>
  </a>

  <a class="task-kpi-card no-date {{ $scope === 'no_date' ? 'active' : '' }}" href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'no_date'])) }}">
   <div class="task-kpi-icon">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <circle cx="12" cy="12" r="10"></circle>
     <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
     <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>
   </div>
   <div class="task-kpi-info">
    <strong>{{ number_format($noDateCount) }}</strong>
    <span>{{ __('crm.tasks_without_date') }}</span>
   </div>
  </a>
 </div>

 {{-- 2. Progress Box --}}
 <div class="task-progress-box">
  <div class="task-progress-head">
   <span>{{ __('crm.daily_progress') }}</span>
   <span class="task-progress-ratio">
    {{ __('crm.tasks_completed_ratio', [
        'completed' => number_format($completedTodayCount),
        'total' => number_format($completedTodayCount + $totalDueToday),
        'percent' => $completionRate
    ]) }}
   </span>
  </div>
  <div class="task-progress-bar-bg">
   <div class="task-progress-bar-fill" style="width: {{ min(100, $completionRate) }}%;"></div>
  </div>
 </div>

 {{-- 3. Search and Filter Bar (One Compact Row) --}}
 <div class="task-filters-card">
  <form class="task-filters-form" method="GET" action="{{ route('v2.tasks.daily') }}">
   <input type="hidden" name="scope" value="{{ $scope }}">
   <input type="hidden" name="view" value="{{ $viewMode }}">

   {{-- Scope pills --}}
   <div class="task-scope-pills">
    <a
     class="task-scope-pill {{ $scope === 'all' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'all'])) }}"
    >
     <span>{{ __('crm.scope_all') }}</span>
     <span class="task-pill-badge">{{ number_format($totalDueToday) }}</span>
    </a>

    <a
     class="task-scope-pill overdue {{ $scope === 'overdue' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'overdue'])) }}"
    >
     <span>{{ __('crm.scope_overdue') }}</span>
     <span class="task-pill-badge">{{ number_format($overdueCount) }}</span>
    </a>

    <a
     class="task-scope-pill today {{ $scope === 'today' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'today'])) }}"
    >
     <span>{{ __('crm.scope_today') }}</span>
     <span class="task-pill-badge">{{ number_format($todayCount) }}</span>
    </a>

    <a
     class="task-scope-pill upcoming {{ $scope === 'upcoming' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'upcoming'])) }}"
    >
     <span>{{ __('crm.scope_upcoming') }}</span>
     <span class="task-pill-badge">{{ number_format($upcomingCount) }}</span>
    </a>

    <a
     class="task-scope-pill no-date {{ $scope === 'no_date' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'no_date'])) }}"
    >
     <span>{{ __('crm.scope_no_date') }}</span>
     <span class="task-pill-badge">{{ number_format($noDateCount) }}</span>
    </a>

    <a
     class="task-scope-pill completed {{ $scope === 'completed' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'completed'])) }}"
    >
     <span>{{ __('crm.scope_completed') }}</span>
     <span class="task-pill-badge">{{ number_format($completedTodayCount) }}</span>
    </a>

    {{-- Academy-Specific Filters (Spec 31 & Loop 2) --}}
    <span style="display:inline-block; width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></span>

    <a
     class="task-scope-pill academy-trials {{ $scope === 'today_trials' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'today_trials'])) }}"
     title="تجارب اليوم المحجوزة بالأكاديمية"
    >
     <i class="bi bi-calendar-event"></i>
     <span>تجارب اليوم</span>
     <span class="task-pill-badge" style="{{ $todayTrialsCount > 0 ? 'background:#0284c7; color:#fff;' : '' }}">{{ number_format($todayTrialsCount) }}</span>
    </a>

    <a
     class="task-scope-pill academy-attended {{ $scope === 'attended_not_subscribed' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'attended_not_subscribed'])) }}"
     title="حضروا التجربة ولم يشتركوا بعد"
    >
     <i class="bi bi-person-check"></i>
     <span>حضروا ولم يشتركوا</span>
     <span class="task-pill-badge" style="{{ $attendedNotSubscribedCount > 0 ? 'background:#d97706; color:#fff;' : '' }}">{{ number_format($attendedNotSubscribedCount) }}</span>
    </a>

    <a
     class="task-scope-pill academy-noshow {{ $scope === 'trial_no_shows' ? 'active' : '' }}"
     href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => 'trial_no_shows'])) }}"
     title="لم يحضروا التجربة المحددة"
    >
     <i class="bi bi-person-x"></i>
     <span>لم يحضروا التجربة</span>
     <span class="task-pill-badge" style="{{ $trialNoShowsCount > 0 ? 'background:#e11d48; color:#fff;' : '' }}">{{ number_format($trialNoShowsCount) }}</span>
    </a>

    @if (!empty($customQuestionFilters) && $customQuestionFilters->isNotEmpty())
     <span style="display:inline-block; width:1px; height:18px; background:#cbd5e1; margin:0 2px;"></span>
     @foreach ($customQuestionFilters as $cqf)
      @php
       $cqfScope = 'q_' . $cqf->id;
       $cqfCount = (int) ($customQuestionCounts[$cqf->id] ?? 0);
       $cqfLabel = $cqf->localizedLabel();
       if (!empty($cqf->daily_tasks_filter_values) && is_array($cqf->daily_tasks_filter_values)) {
           $cqfLabel .= ': ' . implode('/', $cqf->daily_tasks_filter_values);
       }
      @endphp
      <a
       class="task-scope-pill {{ $scope === $cqfScope ? 'active' : '' }}"
       href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['scope' => $cqfScope])) }}"
       title="{{ $cqf->stage?->name_ar }}: {{ $cqf->localizedLabel() }}"
       style="{{ $scope === $cqfScope ? 'border-color:#4f46e5; background:#eef2ff; color:#4f46e5;' : 'border-color:#e0e7ff; background:#f5f3ff; color:#4338ca;' }}"
      >
       <i class="bi bi-filter-circle"></i>
       <span>{{ $cqfLabel }}</span>
       <span class="task-pill-badge" style="{{ $cqfCount > 0 ? 'background:#4f46e5; color:#fff;' : '' }}">{{ number_format($cqfCount) }}</span>
      </a>
     @endforeach
    @endif
   </div>
    <div class="task-search-input-wrap">
     <i class="bi bi-search"></i>
     <input
      class="task-search-input"
      type="text"
      name="search"
      value="{{ $search }}"
      placeholder="{{ __('crm.search_tasks_placeholder') }}"
     >
    </div>

    <button class="btn soft task-search-btn" type="submit" title="{{ __('crm.search') }}">
     <i class="bi bi-search"></i>
     <span>{{ __('crm.search') }}</span>
    </button>

    <select class="task-select-filter" name="status_id" onchange="this.form.submit()">
     <option value="">{{ __('crm.filter_by_status') }} ({{ __('crm.all') }})</option>
     @foreach ($statuses as $st)
      <option value="{{ $st->id }}" {{ (string)$statusId === (string)$st->id ? 'selected' : '' }}>
       {{ $st->name_ar }}
      </option>
     @endforeach
    </select>

    <select class="task-select-filter" name="stage_id" onchange="this.form.submit()">
     <option value="">{{ __('crm.filter_by_stage') }} ({{ __('crm.all') }})</option>
     @foreach ($stages as $sg)
      <option value="{{ $sg->id }}" {{ (string)$stageId === (string)$sg->id ? 'selected' : '' }}>
       {{ $sg->name_ar }}
      </option>
     @endforeach
    </select>

    @if ($assignableUsers->count() > 1)
     <select class="task-select-filter" name="employee_id" onchange="this.form.submit()">
      <option value="">{{ __('crm.filter_by_employee') }} ({{ __('crm.all') }})</option>
      @foreach ($assignableUsers as $u)
       <option value="{{ $u->id }}" {{ (string)$employeeId === (string)$u->id ? 'selected' : '' }}>
        {{ $u->name }}
       </option>
      @endforeach
     </select>
    @endif

    <select class="task-select-filter" name="sort" onchange="this.form.submit()">
     <option value="followup_asc" {{ $sort === 'followup_asc' ? 'selected' : '' }}>{{ __('crm.sort_followup_asc') }}</option>
     <option value="followup_desc" {{ $sort === 'followup_desc' ? 'selected' : '' }}>{{ __('crm.sort_followup_desc') }}</option>
     <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>{{ __('crm.sort_name_asc') }}</option>
     <option value="created_desc" {{ $sort === 'created_desc' ? 'selected' : '' }}>{{ __('crm.sort_created_desc') }}</option>
    </select>

    <div class="task-view-toggle">
     <a
      class="task-view-btn {{ $viewMode === 'cards' ? 'active' : '' }}"
      href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['view' => 'cards'])) }}"
      title="{{ __('crm.view_cards') }}"
     >
      <i class="bi bi-grid"></i>
     </a>
     <a
      class="task-view-btn {{ $viewMode === 'table' ? 'active' : '' }}"
      href="{{ route('v2.tasks.daily', array_merge(request()->query(), ['view' => 'table'])) }}"
      title="{{ __('crm.view_table') }}"
     >
      <i class="bi bi-list-ul"></i>
     </a>
    </div>

    @if ($search !== '' || $statusId !== null || $stageId !== null || $employeeId !== null || $scope !== 'all')
     <a class="btn soft task-clear-btn" href="{{ route('v2.tasks.daily') }}" title="{{ __('crm.clear_filters') }}">
      <i class="bi bi-x-circle"></i>
     </a>
    @endif
   </div>
  </form>
 </div>

 {{-- 4. Content Area --}}
 @if ($scope === 'all')
  {{-- SECTION 1: Overdue Tasks --}}
  @if ($overdueTasks->isNotEmpty())
   <section class="task-section-block overdue-block">
    <header class="task-section-header">
     <div class="task-section-title-wrap">
      <span class="task-section-indicator"></span>
      <div>
       <h2>{{ __('crm.overdue_section_title') }}</h2>
       <p>{{ __('crm.overdue_section_desc') }}</p>
      </div>
     </div>
     <span class="task-section-count-badge">{{ number_format($overdueTasks->count()) }}</span>
    </header>

    @if ($viewMode === 'table')
     @include('tasks._table_view', ['leads' => $overdueTasks, 'timeClass' => 'overdue'])
    @else
     <div class="task-items-cards">
      @foreach ($overdueTasks as $lead)
       @include('tasks._lead_task_card', ['lead' => $lead, 'timeClass' => 'overdue'])
      @endforeach
     </div>
    @endif
   </section>
  @endif

  {{-- SECTION 2: Today's Tasks --}}
  <section class="task-section-block today-block">
   <header class="task-section-header">
    <div class="task-section-title-wrap">
     <span class="task-section-indicator"></span>
     <div>
      <h2>{{ __('crm.today_section_title') }}</h2>
      <p>{{ __('crm.today_section_desc') }}</p>
     </div>
    </div>
    <span class="task-section-count-badge">{{ number_format($todayTasks->count()) }}</span>
   </header>

   @if ($todayTasks->isEmpty() && $overdueTasks->isEmpty())
    <div class="task-empty-card">
     <div class="task-empty-icon"><i class="bi bi-check2-circle"></i></div>
     <strong>{{ __('crm.all_caught_up') }}</strong>
     <p>{{ __('crm.all_tasks_completed_cheer') }}</p>
    </div>
   @elseif ($todayTasks->isEmpty())
    <div class="task-empty-card">
     <strong>{{ __('crm.all_tasks_completed_cheer') }}</strong>
    </div>
   @else
    @if ($viewMode === 'table')
     @include('tasks._table_view', ['leads' => $todayTasks, 'timeClass' => 'today'])
    @else
     <div class="task-items-cards">
      @foreach ($todayTasks as $lead)
       @include('tasks._lead_task_card', ['lead' => $lead, 'timeClass' => 'today'])
      @endforeach
     </div>
    @endif
   @endif
  </section>

  {{-- SECTION 3: Upcoming Tasks (collapsible or preview) --}}
  @if ($upcomingTasks->isNotEmpty())
   <section class="task-section-block upcoming-block">
    <header class="task-section-header">
     <div class="task-section-title-wrap">
      <span class="task-section-indicator"></span>
      <div>
       <h2>{{ __('crm.upcoming_section_title') }}</h2>
       <p>{{ __('crm.upcoming_section_desc') }}</p>
      </div>
     </div>
     <a class="btn soft" href="{{ route('v2.tasks.daily', ['scope' => 'upcoming']) }}">
      {{ __('crm.tasks_upcoming_count') }} ({{ number_format($upcomingCount) }}) &rarr;
     </a>
    </header>

    @if ($viewMode === 'table')
     @include('tasks._table_view', ['leads' => $upcomingTasks, 'timeClass' => 'upcoming'])
    @else
     <div class="task-items-cards">
      @foreach ($upcomingTasks as $lead)
       @include('tasks._lead_task_card', ['lead' => $lead, 'timeClass' => 'upcoming'])
      @endforeach
     </div>
    @endif
   </section>
  @endif

  {{-- SECTION 4: Leads without Date --}}
  @if ($noDateTasks->isNotEmpty())
   <section class="task-section-block no-date-block">
    <header class="task-section-header">
     <div class="task-section-title-wrap">
      <span class="task-section-indicator"></span>
      <div>
       <h2>{{ __('crm.no_date_section_title') }}</h2>
       <p>{{ __('crm.no_date_section_desc') }}</p>
      </div>
     </div>
     <a class="btn soft" href="{{ route('v2.tasks.daily', ['scope' => 'no_date']) }}">
      {{ __('crm.tasks_without_date') }} ({{ number_format($noDateCount) }}) &rarr;
     </a>
    </header>

    @if ($viewMode === 'table')
     @include('tasks._table_view', ['leads' => $noDateTasks, 'timeClass' => 'no-date'])
    @else
     <div class="task-items-cards">
      @foreach ($noDateTasks as $lead)
       @include('tasks._lead_task_card', ['lead' => $lead, 'timeClass' => 'no-date'])
      @endforeach
     </div>
    @endif
   </section>
  @endif

 @elseif ($scope === 'completed')
  {{-- COMPLETED TODAY FOLLOWUPS --}}
  <section class="task-section-block completed-block">
   <header class="task-section-header">
    <div class="task-section-title-wrap">
     <span class="task-section-indicator"></span>
     <div>
      <h2>{{ __('crm.completed_today_section_title') }}</h2>
      <p>{{ __('crm.completed_today_section_desc') }}</p>
     </div>
    </div>
    <span class="task-section-count-badge">{{ number_format($completedTodayFollowups ? $completedTodayFollowups->total() : 0) }}</span>
   </header>

   @if (!$completedTodayFollowups || $completedTodayFollowups->isEmpty())
    <div class="task-empty-card">
     <div class="task-empty-icon"><i class="bi bi-clock-history"></i></div>
     <strong>{{ __('crm.no_followups_currently') }}</strong>
     <p>{{ __('crm.followups_appear_here') }}</p>
    </div>
   @else
    <div class="task-table-wrap">
     <table class="task-table">
      <thead>
       <tr>
        <th>{{ __('crm.client') }}</th>
        <th>{{ __('crm.phone') }}</th>
        <th>{{ __('crm.followup_type') }}</th>
        <th>{{ __('crm.status') }}</th>
        <th>{{ __('crm.employee') }}</th>
        <th>{{ __('crm.date') }}</th>
        <th>{{ __('crm.followup_notes') }}</th>
        <th>{{ __('crm.view_lead') }}</th>
       </tr>
      </thead>
      <tbody>
       @foreach ($completedTodayFollowups as $f)
        <tr>
         <td>
          <strong>{{ $f->lead?->name ?? __('crm.unavailable_client') }}</strong>
          @if ($f->lead?->company_name)
           <small style="display:block; color:#64748b;">{{ $f->lead->company_name }}</small>
          @endif
         </td>
         <td>
          @if ($f->lead?->phone)
           <a class="task-phone-link" href="tel:{{ $f->lead->phone }}">{{ $f->lead->phone }}</a>
          @else
           <span style="color:#94a3b8;">{{ __('crm.not_registered') }}</span>
          @endif
         </td>
         <td>
          <span style="font-weight:700;">
           {{ $communicationTypes[$f->communication_type] ?? $f->communication_type }}
          </span>
         </td>
         <td>
          <span
           class="task-status-pill"
           style="background: {{ ($f->toStatus?->color ?? '#64748b') }}1a; color: {{ $f->toStatus?->color ?? '#64748b' }}; border-color: {{ ($f->toStatus?->color ?? '#64748b') }}33;"
          >
           {{ $f->toStatus?->name_ar ?? '-' }}
          </span>
         </td>
         <td>{{ $f->user?->name ?? $f->employee_name }}</td>
         <td>
          <span style="font-weight:700; color:#475569;">
           {{ $f->followed_up_at ? $f->followed_up_at->format('h:i A') : '-' }}
          </span>
         </td>
         <td style="max-width:320px;">
          <div style="font-size:12px; color:#334155; line-height:1.4;">{{ $f->outcome }}</div>
         </td>
         <td>
          @if ($f->lead)
           <a class="btn soft" href="{{ route('v2.leads.show', $f->lead) }}">
            <i class="bi bi-eye"></i>
           </a>
          @endif
         </td>
        </tr>
       @endforeach
      </tbody>
     </table>
    </div>

    @if ($completedTodayFollowups->hasPages())
     <div style="padding: 16px 20px;">
      {{ $completedTodayFollowups->links() }}
     </div>
    @endif
   @endif
  </section>

 @else
  {{-- SINGLE PAGINATED SCOPE (overdue, today, upcoming, no_date) --}}
  @php
   $blockClass = match($scope) {
       'overdue' => 'overdue-block',
       'today' => 'today-block',
       'upcoming' => 'upcoming-block',
       'no_date' => 'no-date-block',
       default => 'today-block',
   };
   $timeClass = match($scope) {
       'overdue' => 'overdue',
       'today' => 'today',
       'upcoming' => 'upcoming',
       'no_date' => 'no-date',
       default => 'today',
   };
   if (str_starts_with($scope, 'q_')) {
       $activeCqf = $customQuestionFilters->firstWhere('id', (int) substr($scope, 2));
       $scopeTitle = $activeCqf ? $activeCqf->localizedLabel() : __('crm.today_section_title');
       $scopeDesc = $activeCqf ? ('فلتر مخصص استناداً لمرحلة: ' . ($activeCqf->stage?->name_ar ?? '')) : '';
   } else {
       $scopeTitle = match($scope) {
           'overdue' => __('crm.overdue_section_title'),
           'today' => __('crm.today_section_title'),
           'upcoming' => __('crm.upcoming_section_title'),
           'no_date' => __('crm.no_date_section_title'),
           'today_trials' => 'تجارب اليوم المحجوزة',
           'attended_not_subscribed' => 'اللاعبون الذين حضروا ولم يشتركوا بعد',
           'trial_no_shows' => 'اللاعبون الذين لم يحضروا موعد التجربة',
           default => __('crm.today_section_title'),
       };
       $scopeDesc = match($scope) {
           'overdue' => __('crm.overdue_section_desc'),
           'today' => __('crm.today_section_desc'),
           'upcoming' => __('crm.upcoming_section_desc'),
           'no_date' => __('crm.no_date_section_desc'),
           'today_trials' => 'متابعة مواعيد تجارب اليوم والتأكد من الحضور والتأكيد',
           'attended_not_subscribed' => 'عملاء مؤهلون أتموا الحضور وبحاجة لمتابعة إغلاق الاشتراك',
           'trial_no_shows' => 'إعادة التواصل مع الغائبين لإعادة جدولة التجربة',
           default => __('crm.today_section_desc'),
       };
   }
  @endphp

  <section class="task-section-block {{ $blockClass }}">
   <header class="task-section-header">
    <div class="task-section-title-wrap">
     <span class="task-section-indicator"></span>
     <div>
      <h2>{{ $scopeTitle }}</h2>
      <p>{{ $scopeDesc }}</p>
     </div>
    </div>
    <span class="task-section-count-badge">{{ number_format($paginatedTasks ? $paginatedTasks->total() : 0) }}</span>
   </header>

   @if (!$paginatedTasks || $paginatedTasks->isEmpty())
    <div class="task-empty-card">
     <div class="task-empty-icon"><i class="bi bi-check2-circle"></i></div>
     <strong>{{ __('crm.all_tasks_completed_cheer') }}</strong>
    </div>
   @else
    @if ($viewMode === 'table')
     @include('tasks._table_view', ['leads' => $paginatedTasks, 'timeClass' => $timeClass])
    @else
     <div class="task-items-cards">
      @foreach ($paginatedTasks as $lead)
       @include('tasks._lead_task_card', ['lead' => $lead, 'timeClass' => $timeClass])
      @endforeach
     </div>
    @endif

    @if ($paginatedTasks->hasPages())
     <div style="padding: 16px 20px;">
      {{ $paginatedTasks->links() }}
     </div>
    @endif
   @endif
  </section>
 @endif

</div>

{{-- MODAL 1: Reschedule Modal --}}
<div class="task-modal" id="rescheduleModal">
 <div class="task-modal-backdrop" onclick="closeTaskModal('rescheduleModal')"></div>
 <div class="task-modal-content">
  <div class="task-modal-head">
   <h3><i class="bi bi-calendar-event"></i> {{ __('crm.reschedule_task') }}</h3>
   <button class="task-modal-close" type="button" onclick="closeTaskModal('rescheduleModal')" aria-label="{{ __('crm.close') }}" title="{{ __('crm.close') }}">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M12 4L4 12M4 4l8 8" />
    </svg>
   </button>
  </div>

  <form id="rescheduleForm" method="POST" onsubmit="submitReschedule(event)">
   @csrf
   <div class="task-form-group">
    <label for="rescheduleLeadName">{{ __('crm.client') }}</label>
    <input class="task-form-control" id="rescheduleLeadName" type="text" readonly disabled>
   </div>

   <div class="task-form-group">
    <label for="rescheduleDateInput">{{ __('crm.select_new_date') }}</label>
    <input class="task-form-control" id="rescheduleDateInput" name="next_follow_up_at" type="datetime-local" required>
    <div class="task-quick-presets">
     <button class="task-preset-btn" type="button" onclick="presetReschedule(2)">+2 {{ __('crm.hours_count') }}</button>
     <button class="task-preset-btn" type="button" onclick="presetReschedule(4)">+4 {{ __('crm.hours_count') }}</button>
     <button class="task-preset-btn" type="button" onclick="presetReschedule(24)">{{ __('crm.postpone_tomorrow') }}</button>
     <button class="task-preset-btn" type="button" onclick="presetReschedule(168)">{{ __('crm.postpone_next_week') }}</button>
    </div>
   </div>

   <div class="task-form-group">
    <label for="rescheduleReasonInput">{{ __('crm.followup_outcome_notes') }} ({{ __('crm.not_specified') }})</label>
    <input class="task-form-control" id="rescheduleReasonInput" name="reschedule_reason" type="text" placeholder="...">
   </div>

   <div class="task-modal-actions">
    <button class="btn soft" type="button" onclick="closeTaskModal('rescheduleModal')">{{ __('crm.cancel') }}</button>
    <button class="btn primary" type="submit">{{ __('crm.save_changes') }}</button>
   </div>
  </form>
 </div>
</div>

{{-- MODAL 2: Quick Follow-up Modal --}}
<div class="task-modal" id="quickFollowupModal">
 <div class="task-modal-backdrop" onclick="closeTaskModal('quickFollowupModal')"></div>
 <div class="task-modal-content">
  <div class="task-modal-head">
   <h3><i class="bi bi-pencil-square"></i> {{ __('crm.quick_log_followup') }}</h3>
   <button class="task-modal-close" type="button" onclick="closeTaskModal('quickFollowupModal')" aria-label="{{ __('crm.close') }}" title="{{ __('crm.close') }}">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
     <path d="M12 4L4 12M4 4l8 8" />
    </svg>
   </button>
  </div>

  <form id="quickFollowupForm" method="POST" onsubmit="submitQuickFollowup(event)">
   @csrf
   <div class="task-form-group">
    <label for="quickLeadName">{{ __('crm.client') }}</label>
    <input class="task-form-control" id="quickLeadName" type="text" readonly disabled>
   </div>

   <div class="task-form-group">
    <label for="quickCommunicationType">{{ __('crm.followup_type') }}</label>
    <select class="task-form-control" id="quickCommunicationType" name="communication_type" required>
     @foreach ($communicationTypes as $typeKey => $typeLabel)
      <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
     @endforeach
    </select>
   </div>

   <div class="task-form-group">
    <label for="quickOutcomeNotes">{{ __('crm.followup_outcome_notes') }}</label>
    <textarea class="task-form-control" id="quickOutcomeNotes" name="outcome" rows="3" required placeholder="{{ __('crm.followup_notes_placeholder') }}"></textarea>
   </div>

   <div class="task-form-group">
    <label for="quickStatusSelect">{{ __('crm.new_status_optional') }}</label>
    <select class="task-form-control" id="quickStatusSelect" name="lead_status_id">
     <option value="">-- {{ __('crm.select_lead_status') }} --</option>
     @foreach ($statuses as $st)
      <option value="{{ $st->id }}">{{ $st->name_ar }} ({{ $st->stage?->name_ar ?? '-' }})</option>
     @endforeach
    </select>
   </div>

   <div class="task-form-group">
    <label for="quickNextFollowup">{{ __('crm.next_followup_optional') }}</label>
    <input class="task-form-control" id="quickNextFollowup" name="next_follow_up_at" type="datetime-local">
   </div>

   <div class="task-modal-actions">
    <button class="btn soft" type="button" onclick="closeTaskModal('quickFollowupModal')">{{ __('crm.cancel') }}</button>
    <button class="btn primary" type="submit">{{ __('crm.save_followup_action') }}</button>
   </div>
  </form>
 </div>
</div>

{{-- Global Toast --}}
<div class="task-toast" id="taskToast">
 <i class="bi bi-check-circle-fill" style="color: #22c55e;"></i>
 <span id="taskToastMessage"></span>
</div>

@push('scripts')
<script>
 (() => {
  let activeLeadId = null;

  window.openRescheduleModal = function (leadId, leadName, currentFollowup) {
   activeLeadId = leadId;
   const modal = document.getElementById('rescheduleModal');
   const nameInput = document.getElementById('rescheduleLeadName');
   const dateInput = document.getElementById('rescheduleDateInput');
   const form = document.getElementById('rescheduleForm');

   nameInput.value = leadName || '';
   form.action = '/tasks/leads/' + leadId + '/reschedule';

   if (currentFollowup) {
    try {
     const d = new Date(currentFollowup);
     if (!isNaN(d.getTime())) {
      dateInput.value = d.toISOString().slice(0, 16);
     }
    } catch(e) {}
   } else {
    const d = new Date(Date.now() + 2 * 3600000);
    dateInput.value = d.toISOString().slice(0, 16);
   }

   modal.classList.add('open');
  };

  window.presetReschedule = function (hours) {
   const dateInput = document.getElementById('rescheduleDateInput');
   const target = new Date(Date.now() + hours * 3600000);
   dateInput.value = target.toISOString().slice(0, 16);
  };

  window.openQuickFollowupModal = function (leadId, leadName, currentStatusId, defaultCommType) {
   activeLeadId = leadId;
   const modal = document.getElementById('quickFollowupModal');
   const nameInput = document.getElementById('quickLeadName');
   const statusSelect = document.getElementById('quickStatusSelect');
   const commSelect = document.getElementById('quickCommunicationType');
   const notesArea = document.getElementById('quickOutcomeNotes');
   const form = document.getElementById('quickFollowupForm');

   nameInput.value = leadName || '';
   form.action = '/tasks/leads/' + leadId + '/quick-followup';

   if (commSelect) {
    commSelect.value = defaultCommType || 'call';
   }

   if (currentStatusId && statusSelect) {
    statusSelect.value = currentStatusId;
   }

   if (notesArea) {
    notesArea.value = '';
   }

   modal.classList.add('open');

   setTimeout(() => {
    if (notesArea) notesArea.focus();
   }, 100);
  };

  window.closeTaskModal = function (modalId) {
   const modal = document.getElementById(modalId);
   if (modal) modal.classList.remove('open');
  };

  window.showTaskToast = function (message) {
   const toast = document.getElementById('taskToast');
   const msg = document.getElementById('taskToastMessage');
   if (!toast || !msg) return;
   msg.textContent = message;
   toast.classList.add('show');
   setTimeout(() => { toast.classList.remove('show'); }, 3500);
  };

  window.submitReschedule = async function (e) {
   e.preventDefault();
   const form = e.target;
   const formData = new FormData(form);

   try {
    const response = await fetch(form.action, {
     method: 'POST',
     headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
     },
     body: formData
    });

    if (response.ok) {
     const data = await response.json();
     closeTaskModal('rescheduleModal');
     showTaskToast(data.message || '{{ __('crm.task_rescheduled_success') }}');
     setTimeout(() => { window.location.reload(); }, 600);
    } else {
     form.submit();
    }
   } catch (err) {
    form.submit();
   }
  };

  window.submitQuickFollowup = async function (e) {
   e.preventDefault();
   const form = e.target;
   const formData = new FormData(form);

   try {
    const response = await fetch(form.action, {
     method: 'POST',
     headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
     },
     body: formData
    });

    if (response.ok) {
     const data = await response.json();
     closeTaskModal('quickFollowupModal');
     showTaskToast(data.message || '{{ __('crm.task_followup_saved_success') }}');
     setTimeout(() => { window.location.reload(); }, 600);
    } else {
     form.submit();
    }
   } catch (err) {
    form.submit();
   }
  };

  // Keyboard shortcut to close modal
  document.addEventListener('keydown', (e) => {
   if (e.key === 'Escape') {
    closeTaskModal('rescheduleModal');
    closeTaskModal('quickFollowupModal');
   }
  });
 })();
</script>
@endpush
@endsection
