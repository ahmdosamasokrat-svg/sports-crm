<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SokratCRM — {{ __('crm.view_leads') }}</title>
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

.crm-app { display: flex; min-height: 100vh; }
.crm-main { flex: 1; min-width: 0; padding: 20px 20px 60px; }

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
  box-shadow: none !important;
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
html.dark-mode .btn.soft:hover {
  background: rgba(255, 255, 255, 0.15);
}
.btn.small {
  height: 38px;
  min-height: 38px;
  padding: 0 12px;
  font-size: 12px;
  border-radius: 9px;
}

/* Stats Summary Cards */
/* Filters Panel */
.filter-panel {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 10px 14px;
  box-shadow: none !important;
  margin-bottom: 16px;
}
.filter-form-grid {
  display: grid;
  grid-template-columns: minmax(180px, 1.4fr) minmax(120px, 0.9fr) minmax(120px, 0.9fr) minmax(150px, 1.1fr) minmax(120px, 0.9fr) minmax(110px, 0.8fr) minmax(150px, 1fr) auto;
  gap: 8px 10px;
  align-items: end;
}
.filter-field {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
  width: 100%;
}
.filter-field.col-search {
  min-width: 180px;
}
.filter-field.col-employee {
  min-width: 150px;
}
.filter-actions-col {
  display: flex;
  align-items: center;
  gap: 6px;
  height: 38px;
  min-height: 38px;
  justify-self: start;
  align-self: end;
  white-space: nowrap;
}
.filter-field label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: var(--muted);
  white-space: nowrap;
  line-height: 1.2;
}
.filter-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}
.filter-input-icon {
  position: absolute;
  inset-inline-start: 10px;
  color: var(--muted);
  pointer-events: none;
  font-size: 12px;
}
.filter-control {
  width: 100%;
  height: 38px;
  min-height: 38px;
  border: 1px solid var(--line);
  border-radius: 9px;
  padding: 0 10px;
  background: var(--card);
  color: var(--dark);
  font-size: 12.5px;
  font-weight: 600;
  outline: none;
  transition: border-color 0.15s ease;
  box-sizing: border-box;
}
.filter-control.with-icon {
  padding-inline-start: 28px;
}
.filter-control:focus {
  border-color: var(--red);
  box-shadow: none !important;
}
html.dark-mode .filter-control {
  background: rgba(30, 41, 59, 0.7);
  border-color: var(--line);
  color: var(--dark);
}

/* Unified CRM Dropdown */
.crm-select-wrap {
  position: relative !important;
  display: inline-flex !important;
  align-items: center !important;
  width: 100% !important;
  height: 44px !important;
  min-height: 44px !important;
  background: var(--card, #ffffff) !important;
  border: 1px solid var(--line, #e2e8f0) !important;
  border-radius: 11px !important;
  box-shadow: none !important;
  transition: border-color 0.16s ease, background-color 0.16s ease !important;
  cursor: pointer !important;
  box-sizing: border-box !important;
}

.crm-select-wrap:hover {
  border-color: #cbd5e1 !important;
  background-color: var(--bg, #f8fafc) !important;
}

.crm-select-wrap:focus-within {
  border-color: var(--red, #ef4444) !important;
  background-color: var(--card, #ffffff) !important;
  box-shadow: none !important;
}

.crm-select-wrap .crm-select-icon {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding-inline-start: 12px !important;
  padding-inline-end: 4px !important;
  color: var(--muted, #64748b) !important;
  flex-shrink: 0 !important;
  pointer-events: none !important;
}

.crm-select-wrap .crm-select-icon svg {
  width: 15px !important;
  height: 15px !important;
  fill: currentColor !important;
}

.crm-select-wrap select.crm-select {
  appearance: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  width: 100% !important;
  height: 100% !important;
  min-height: 100% !important;
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding-inline-start: 8px !important;
  padding-inline-end: 32px !important;
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  color: var(--dark, #182033) !important;
  cursor: pointer !important;
  outline: none !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

.crm-select-wrap select.crm-select:focus {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
}

.crm-select-wrap .crm-select-chevron {
  position: absolute !important;
  inset-inline-end: 12px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #94a3b8 !important;
  pointer-events: none !important;
  transition: transform 0.18s ease, color 0.18s ease !important;
}

.crm-select-wrap .crm-select-chevron svg {
  width: 11px !important;
  height: 11px !important;
  fill: currentColor !important;
}

.crm-select-wrap:hover .crm-select-chevron {
  color: var(--muted, #64748b) !important;
}

.crm-select-wrap:focus-within .crm-select-chevron {
  color: var(--red, #ef4444) !important;
  transform: rotate(180deg) !important;
}

.crm-select-wrap select.crm-select option {
  background-color: var(--card, #ffffff) !important;
  color: var(--dark, #182033) !important;
  font-weight: 600 !important;
  padding: 8px 12px !important;
}

html.dark-mode .crm-select-wrap {
  background-color: var(--card, #1e293b) !important;
  border-color: var(--line, #334155) !important;
}

html.dark-mode .crm-select-wrap:hover {
  background-color: rgba(30, 41, 59, 0.9) !important;
  border-color: #475569 !important;
}

html.dark-mode .crm-select-wrap select.crm-select {
  color: var(--dark, #f1f5f9) !important;
}

html.dark-mode .crm-select-wrap select.crm-select option {
  background-color: #1e293b !important;
  color: #f1f5f9 !important;
}
.dynamic-filter-builder {
  grid-column: 1 / -1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding-top: 12px;
  border-top: 1px solid var(--line);
}
.dynamic-filter-context {
  min-width: 0;
}
.dynamic-filter-context strong {
  display: flex;
  align-items: center;
  gap: 7px;
  color: var(--dark);
  font-size: 13px;
}
.dynamic-filter-context small {
  display: block;
  margin-top: 3px;
  color: var(--muted);
  font-size: 11px;
}
.dynamic-field-picker {
  position: relative;
  flex: 0 0 auto;
}
.dynamic-field-picker-toggle {
  width: 100% !important;
  height: 38px !important;
  min-height: 38px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  padding: 0 10px !important;
  border: 1px solid var(--line, #e2e8f0) !important;
  border-radius: 9px !important;
  background: var(--card, #ffffff) !important;
  color: var(--dark, #172033) !important;
  font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif !important;
  font-size: 12.5px !important;
  font-weight: 600 !important;
  cursor: pointer !important;
  outline: none !important;
  transition: border-color 0.18s ease, background-color 0.18s ease !important;
  box-shadow: none !important;
  box-sizing: border-box !important;
}
.dynamic-field-picker-toggle:hover {
  border-color: #cbd5e1 !important;
  background-color: var(--bg, #f8fafc) !important;
}
.dynamic-field-picker-toggle:focus-visible,
.dynamic-field-picker-toggle[aria-expanded="true"] {
  border-color: var(--red, #ef4444) !important;
  background-color: var(--card, #ffffff) !important;
}
html.dark-mode .dynamic-field-picker-toggle {
  background: rgba(30, 41, 59, 0.7) !important;
  border-color: var(--line, #334155) !important;
  color: var(--dark, #f1f5f9) !important;
}
.dynamic-field-picker-count {
  width: 20px !important;
  height: 20px !important;
  min-width: 20px !important;
  min-height: 20px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0 !important;
  margin: 0 !important;
  border-radius: 999px !important;
  background: var(--red, #ef4444) !important;
  color: #ffffff !important;
  font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  font-variant-numeric: tabular-nums !important;
  line-height: 1 !important;
  text-align: center !important;
  box-sizing: border-box !important;
}
html.dark-mode .dynamic-field-picker-count {
  background: var(--red, #ef4444) !important;
  color: #ffffff !important;
}
.dynamic-field-picker-menu {
  position: absolute;
  inset-block-start: calc(100% + 7px);
  inset-inline-end: 0;
  z-index: 30;
  width: min(360px, calc(100vw - 40px));
  max-height: 340px;
  overflow-y: auto;
  padding: 8px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: var(--card);
  box-shadow: none !important;
}
.dynamic-field-picker-menu[hidden] { display: none; }
.dynamic-field-option {
  display: flex;
  align-items: center;
  gap: 9px;
  min-height: 42px;
  padding: 8px 9px;
  border-radius: 9px;
  color: var(--dark);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}
.dynamic-field-option:hover {
  background: #f8fafc;
}
html.dark-mode .dynamic-field-option:hover {
  background: rgba(255, 255, 255, 0.06);
}
.dynamic-field-option input {
  width: 18px;
  height: 18px;
  flex: 0 0 18px;
  accent-color: var(--red);
}
.dynamic-field-option i {
  color: var(--muted);
}
.dynamic-field-option-copy {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.dynamic-field-option-copy span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.dynamic-field-option-copy small {
  color: var(--muted);
  font-size: 10px;
  font-weight: 600;
}
.dynamic-field-picker-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 8px 9px 2px;
  border-top: 1px solid var(--line);
}
.dynamic-field-picker-footer small {
  color: var(--muted);
  font-size: 10px;
}
.dynamic-field-picker-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  white-space: nowrap;
}
.dynamic-field-clear {
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--red);
  font: inherit;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
}
.dynamic-field-select-all {
  color: var(--dark);
}
.dynamic-filter-field-stage {
  color: var(--muted);
  font-size: 9px;
  font-weight: 700;
}
.dynamic-filter-fields {
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
  gap: 10px 12px;
}
.dynamic-filter-field[hidden] { display: none; }
.dynamic-filter-empty {
  grid-column: 1 / -1;
  margin: 0;
  padding: 9px 11px;
  border-radius: 9px;
  background: #f8fafc;
  color: var(--muted);
  font-size: 11px;
  font-weight: 700;
}
html.dark-mode .dynamic-filter-empty {
  background: rgba(255, 255, 255, 0.04);
}

/* Bulk Actions Bar */
.bulk-actions-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 16px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  margin-bottom: 16px;
  color: #991b1b;
  font-weight: 800;
  font-size: 13px;
}
html.dark-mode .bulk-actions-bar {
  background: rgba(153, 27, 27, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}
.bulk-actions-bar[hidden] { display: none !important; }
.bulk-actions-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}
.bulk-clear-button {
  background: transparent;
  border: 1px solid #fca5a5;
  color: #991b1b;
  padding: 5px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  font-size: 12px;
}
html.dark-mode .bulk-clear-button {
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.4);
}
.bulk-export-button {
  background: var(--red);
  border: 0;
  color: #fff;
  padding: 6px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 800;
  font-size: 12px;
}

/* Table Card */
.table-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  width: 100%;
}
table {
  width: 100%;
  border-collapse: collapse;
  min-width: 0;
}
th, td {
  text-align: start;
  padding: 7px 8px;
  border-bottom: 1px solid var(--line);
  vertical-align: middle;
  font-size: 13.5px;
}
th {
  background: #f8fafc;
  color: var(--muted);
  font-size: 12.5px;
  font-weight: 700;
  white-space: nowrap;
}
html.dark-mode th {
  background: rgba(255, 255, 255, 0.03);
  color: var(--muted);
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #f8fafc; }
html.dark-mode tr:hover td { background: rgba(255, 255, 255, 0.02); }

/* Customer Cell */
.customer-name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}
.customer-avatar {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: #fef2f2;
  color: var(--red);
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 12px;
  flex-shrink: 0;
}
.customer-info {
  min-width: 0;
  overflow: hidden;
}
.customer-info strong {
  display: block;
  font-size: 13.5px;
  font-weight: 700;
  color: var(--dark);
  max-width: 155px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.customer-info small {
  display: block;
  color: var(--muted);
  font-size: 11px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2.5px 8px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 700;
  background: #f1f5f9;
  color: #475569;
  white-space: nowrap;
}
html.dark-mode .badge {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
}
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 2.5px 8px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 700;
  background: color-mix(in srgb, var(--status-color, #64748b) 12%, transparent);
  color: var(--status-color, #64748b);
  border: 1px solid color-mix(in srgb, var(--status-color, #64748b) 25%, transparent);
  white-space: nowrap;
}
.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--status-color, #64748b);
}
.stage-name {
  display: block;
  font-size: 11px;
  color: var(--muted);
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 110px;
}

/* Actions Cell */
.actions-cell {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
  white-space: nowrap;
}
.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 27px;
  height: 27px;
  min-width: 27px;
  min-height: 27px;
  border-radius: 7px;
  background: #f1f5f9;
  border: 1px solid var(--line);
  color: var(--dark);
  font-size: 12.5px;
  cursor: pointer;
  transition: all 0.15s ease;
  padding: 0;
  box-sizing: border-box;
}
.btn-action:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}
html.dark-mode .btn-action {
  background: rgba(255, 255, 255, 0.06);
  border-color: var(--line);
  color: var(--dark);
}
.btn-action.whatsapp { color: #16a34a; }
.btn-action.whatsapp:hover { background: #dcfce7; border-color: #86efac; }
.btn-action.call { color: #2563eb; }
.btn-action.call:hover { background: #eff6ff; border-color: #bfdbfe; }
.btn-action.followup { color: #d97706; }
.btn-action.followup:hover { background: #fef3c7; border-color: #fde68a; }
.btn-action.view { color: #475569; }
.btn-action.view:hover { background: #e2e8f0; border-color: #94a3b8; color: #0f172a; }
.btn-action.edit { color: #475569; }
.btn-action.edit:hover { background: #fef2f2; border-color: #fca5a5; color: #ef4444; }

/* Pagination */
.pagination-wrap {
  padding: 16px 20px;
  border-top: 1px solid var(--line);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  background: var(--card);
}
.pagination-info {
  font-size: 13px;
  font-weight: 700;
  color: var(--muted);
}
.crm-pagination-nav {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 0;
  padding: 0;
  list-style: none;
}
.crm-page-btn, .crm-page-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-width: 44px;
  height: 44px;
  min-height: 44px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid var(--line);
  background: var(--card);
  color: var(--dark);
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  transition: all 0.15s ease;
  cursor: pointer;
}
.crm-page-btn:hover:not(.disabled), .crm-page-num:hover:not(.active) {
  border-color: #cbd5e1;
  background: #f1f5f9;
  transform: translateY(-1px);
}
.crm-page-num.active {
  background: var(--red) !important;
  border-color: var(--red) !important;
  color: #ffffff !important;
}

@media(max-width: 1400px) {
  .filter-form-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }
}
@media(max-width: 900px) {
  .filter-form-grid { grid-template-columns: repeat(2, 1fr); }
  .top-actions { width: 100%; flex-wrap: wrap; gap: 8px; }
  .top-actions .btn { flex: 1 1 auto; min-height: 44px; }
}
@media(max-width: 600px) {
  .crm-main { padding: 16px 12px 60px; min-width: 0; width: 100%; max-width: 100%; }
  .filter-form-grid { grid-template-columns: 1fr; }
  .filter-field.col-search { grid-column: span 1; }
  .filter-actions-col { width: 100%; justify-self: stretch; }
  .filter-actions-col .btn { flex: 1; height: 38px; min-height: 38px; }
  .pagination-wrap { flex-direction: column; align-items: center; text-align: center; gap: 12px; }
  .crm-pagination-nav { flex-wrap: wrap; justify-content: center; }
  .btn-action { width: 38px; height: 38px; min-width: 38px; min-height: 38px; font-size: 15px; }
  .dynamic-filter-builder { align-items: stretch; flex-direction: column; }
  .dynamic-field-picker, .dynamic-field-picker-toggle { width: 100%; }
  .dynamic-field-picker-menu { inset-inline: 0; width: 100%; }
  .dynamic-filter-fields { grid-template-columns: 1fr; }
}



/* Bulk Assign Modal & Dropdown Stacking */
#bulkAssignModal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  z-index: 999999 !important;
  align-items: center;
  justify-content: center;
  padding: 16px;
  backdrop-filter: blur(3px);
}
#bulkAssignModal .crm-modal-dialog {
  background: var(--card, #ffffff);
  border: 1px solid var(--line, #e2e8f0);
  border-radius: 16px;
  width: 100%;
  max-width: 460px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: visible !important;
  position: relative;
  z-index: 1000000 !important;
}
#bulkAssignModal .modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--line);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--bg);
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  position: relative;
  z-index: 1;
}
#bulkAssignModal .modal-body {
  padding: 20px;
  position: relative;
  z-index: 1000010 !important;
  overflow: visible !important;
}
#bulkAssignModal .modal-footer {
  padding: 14px 20px;
  border-top: 1px solid var(--line);
  background: var(--bg);
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 16px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  position: relative;
  z-index: 1;
}
#bulkAssignModal .crm-dropdown,
#bulkAssignModal .crm-dropdown.is-open {
  position: relative !important;
  z-index: 1000020 !important;
  width: 100% !important;
}
#bulkAssignModal .crm-dropdown-menu {
  z-index: 1000050 !important;
  position: absolute !important;
  top: calc(100% + 6px) !important;
  inset-inline-start: 0 !important;
  width: 100% !important;
  max-width: 100% !important;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2) !important;
  background: var(--card, #ffffff) !important;
  border: 1px solid var(--line, #e2e8f0) !important;
}
html.dark-mode #bulkAssignModal .crm-dropdown-menu {
  background: #18181b !important;
  border-color: rgba(255, 255, 255, 0.12) !important;
  color: #f4f4f5 !important;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
}
</style>
</head>
<body>
<div class="crm-app leads-page">
    @include('partials.crm-sidebar')

    <main class="crm-main">
        @include('partials.topbar', [
            'title' => __('crm.view_leads'),
            'subtitle' => '<span>' . __('crm.total_leads') . ': ' . number_format($totalLeads) . '</span>',
            'icon' => 'bi-people-fill',
        ])

        @if (session('success'))
            <div style="padding:12px 16px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:12px;margin-bottom:16px;font-weight:700">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif



        <!-- FILTERS PANEL -->
        <section class="filter-panel">
            <form method="GET" action="{{ route('v2.leads') }}" class="filter-form-grid" id="leadFilters">
                <input
                    type="hidden"
                    name="field_ids"
                    value="{{ implode(',', $selectedStageFieldIds) }}"
                    data-selected-dynamic-field-ids
                >
                @if (!empty($filters['stage']))
                    <input type="hidden" name="stage" value="{{ $filters['stage'] }}">
                @endif

                <!-- Search Input -->
                <div class="filter-field col-search">
                    <label for="searchQuery"><i class="bi bi-search"></i> {{ __('crm.search_query_label') }}</label>
                    <div class="filter-input-wrap">
                        <i class="bi bi-search filter-input-icon"></i>
                        <input type="text" id="searchQuery" name="q" value="{{ $filters['q'] }}" class="filter-control with-icon" placeholder="{{ __('crm.lead_search_placeholder') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-field">
                    <label for="leadStatus"><i class="bi bi-tag"></i> {{ __('crm.status') }}</label>
                    <div style="width:100%;">
                        <select id="leadStatus" name="status" class="crm-custom-select filter-control" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2zm3.5 4a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/></svg>'>
                            <option value="">{{ __('crm.all_states') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->code }}" data-stage-id="{{ $status->pipeline_stage_id }}" @selected($filters['status'] === $status->code)>
                                    {{ $status->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Source Filter -->
                <div class="filter-field">
                    <label for="leadSource"><i class="bi bi-diagram-2"></i> {{ __('crm.source') }}</label>
                    <div style="width:100%;">
                        <select id="leadSource" name="source" class="crm-custom-select filter-control" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.022c.416 0 .816.165 1.11.459l1.41 1.41c.294.294.458.694.458 1.11V12a1.5 1.5 0 0 1-1.5 1.5h-.5a2.5 2.5 0 0 1-4.996 0H6.496a2.5 2.5 0 0 1-4.996 0H1.5A1.5 1.5 0 0 1 0 12V3.5zM4 11a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm7.5 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg>'>
                            <option value="">{{ __('crm.all_sources') }}</option>
                            @foreach ($sources as $source)
                                <option value="{{ $source }}" @selected($filters['source'] === $source)>
                                    {{ $source }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(auth()->user()?->hasPermission(\App\Security\CrmPermission::BRANCHES_SCOPE_ALL) && isset($branches) && $branches->isNotEmpty())
                <!-- Branch Filter -->
                <div class="filter-field">
                    <label for="leadBranch"><i class="bi bi-geo-alt"></i> {{ __('crm.branch') ?: 'الفرع' }}</label>
                    <div style="width:100%;">
                        <select id="leadBranch" name="branch" class="crm-custom-select filter-control" data-crm-dropdown>
                            <option value="">{{ __('crm.all_branches') ?: 'جميع الفروع' }}</option>
                            @foreach ($branches as $br)
                                <option value="{{ $br->id }}" @selected((int) ($selectedBranchId ?? 0) === (int) $br->id)>
                                    {{ $br->localizedName() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Temperature Filter -->
                @php
                    $dynamicCustomerFields = ($customerFields ?? collect())->filter(fn ($cf) => empty($cf->lead_attribute) || $cf->lead_attribute !== 'company_name');
                    $tempField = ($customerFields ?? collect())->firstWhere('key', 'lead_temperature');
                    $tempOptions = $tempField ? $tempField->normalizedOptions() : [
                        ['value' => 'hot', 'label_ar' => '🔥 حار (Hot)', 'label_en' => 'Hot'],
                        ['value' => 'warm', 'label_ar' => '⚡ متوسط (Warm)', 'label_en' => 'Warm'],
                        ['value' => 'cold', 'label_ar' => '❄️ بارد (Cold)', 'label_en' => 'Cold'],
                    ];
                @endphp
                @if ($tempField || count($tempOptions) > 0)
                <div class="filter-field">
                    <label for="leadTemperature"><i class="bi bi-thermometer-half"></i> {{ $tempField ? $tempField->localizedLabel() : __('حرارة العميل') }}</label>
                    <div style="width:100%;">
                        <select id="leadTemperature" name="temperature" class="crm-custom-select filter-control" data-crm-dropdown>
                            <option value="">{{ __('جميع التصنيفات') }}</option>
                            @foreach ($tempOptions as $tOpt)
                                <option value="{{ $tOpt['value'] }}" @selected(($filters['temperature'] ?? '') === $tOpt['value'])>
                                    {{ app()->getLocale() === 'en' && !empty($tOpt['label_en']) ? $tOpt['label_en'] : $tOpt['label_ar'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Employee Filter -->
                <div class="filter-field col-employee">
                    <label for="leadEmployee"><i class="bi bi-person-check"></i> {{ __('crm.assigned_employee') }}</label>
                    <div style="width:100%;">
                        <select id="leadEmployee" name="employee" class="crm-custom-select filter-control" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M10.02 12c.005-.184.02-.375.034-.555.056-.704.14-1.282.266-1.745A4.9 4.9 0 0 0 8 9c-1.378 0-2.496.53-2.92 1.077-.184.238-.309.522-.387.828a.5.5 0 0 0 .97.234c.05-.195.13-.38.252-.538C6.27 10.158 7.08 9.8 8 9.8c.92 0 1.73.358 2.085.801.074.092.127.202.164.321.037.119.06.252.073.403.014.16.023.325.027.475H3.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 .5-.5c0-.368-.008-.687-.02-1z"/></svg>'>
                            <option value="">{{ __('كل الموظفين') }}</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee }}" @selected($filters['employee'] === $employee)>
                                    {{ $employee }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Follow Up Filter -->
                <div class="filter-field">
                    <label for="leadFollowUp"><i class="bi bi-calendar-event"></i> {{ __('crm.next_followup') }}</label>
                    <div style="width:100%;">
                        <select id="leadFollowUp" name="follow_up" class="crm-custom-select filter-control" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>'>
                            <option value="">{{ __('crm.all_appointments') }}</option>
                            <option value="today" @selected($filters['follow_up'] === 'today')>{{ __('اليوم') }}</option>
                            <option value="upcoming" @selected($filters['follow_up'] === 'upcoming')>{{ __('crm.upcoming') }}</option>
                            <option value="overdue" @selected($filters['follow_up'] === 'overdue')>{{ __('crm.overdue') }}</option>
                            <option value="none" @selected($filters['follow_up'] === 'none')>{{ __('crm.no_date') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="filter-field">
                    <label for="leadSort"><i class="bi bi-sort-down"></i> {{ __('crm.sort') }}</label>
                    <div style="width:100%;">
                        <select id="leadSort" name="sort" class="crm-custom-select filter-control" data-crm-dropdown data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5zm-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/></svg>'>
                            <option value="latest" @selected($filters['sort'] === 'latest')>{{ __('crm.newest_first') }}</option>
                            <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __('crm.oldest_first') }}</option>
                            <option value="name" @selected($filters['sort'] === 'name')>{{ __('crm.by_name') }}</option>
                            <option value="followup" @selected($filters['sort'] === 'followup')>{{ __('crm.by_followup') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Choose Fields / Column Chooser Dropdown -->
                <div class="filter-field col-choose-fields" data-dynamic-field-picker style="position:relative;">
                    <label for="chooseFieldsToggle" style="font-size:12px;"><i class="bi bi-layout-three-columns"></i> {{ __('تحديد الأعمدة والفلاتر') }}</label>
                    <button
                        id="chooseFieldsToggle"
                        class="dynamic-field-picker-toggle"
                        type="button"
                        data-dynamic-field-picker-toggle
                        aria-expanded="false"
                        aria-controls="dynamicFieldPickerMenu"
                    >
                        <span style="pointer-events:none; display:inline-flex; align-items:center; gap:6px;"><i class="bi bi-ui-checks-grid"></i> {{ __('الأعمدة والحقول') }}</span>
                        <span class="dynamic-field-picker-count" data-dynamic-field-count style="pointer-events:none;">{{ count($selectedStageFieldIds) }}</span>
                    </button>

                    <div
                        class="dynamic-field-picker-menu"
                        id="dynamicFieldPickerMenu"
                        data-dynamic-field-picker-menu
                        role="group"
                        aria-labelledby="chooseFieldsToggle"
                        hidden
                        style="position:absolute; top:calc(100% + 6px); inset-inline-end:0; z-index:150; max-height:480px; overflow-y:auto; width:340px; background:var(--card); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow-dropdown); box-sizing:border-box;"
                    >
                        <div style="padding: 10px 12px 6px; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; border-bottom:1px solid var(--line);">
                            الأعمدة الأساسية (إلزامية واختيارية)
                        </div>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="client" checked disabled>
                            <i class="bi bi-person-badge"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.client') }}</span>
                                <small style="color:var(--muted)">إلزامي</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="status" checked disabled>
                            <i class="bi bi-tag"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.current_status') }}</span>
                                <small style="color:var(--muted)">إلزامي</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="category" checked disabled>
                            <i class="bi bi-collection"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.stage_category') }}</span>
                                <small style="color:var(--muted)">إلزامي</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="actions" checked disabled>
                            <i class="bi bi-gear"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.actions') }}</span>
                                <small style="color:var(--muted)">إلزامي</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="contact" checked>
                            <i class="bi bi-telephone"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.contact_data') }}</span>
                                <small>الهاتف / البريد</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="company_source" checked>
                            <i class="bi bi-building"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.company_source') }}</span>
                                <small>الشركة والمصدر</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="employee" checked>
                            <i class="bi bi-person-check"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.responsible_employee') }}</span>
                                <small>الموظف المسؤول</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="followup" checked>
                            <i class="bi bi-calendar-event"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.next_followup') }}</span>
                                <small>الموعد القادم</small>
                            </span>
                        </label>
                        <label class="dynamic-field-option">
                            <input type="checkbox" data-col-toggle="created_date" checked>
                            <i class="bi bi-clock-history"></i>
                            <span class="dynamic-field-option-copy">
                                <span>{{ __('crm.created_date') }}</span>
                                <small>تاريخ الإضافة</small>
                            </span>
                        </label>

                        @if ($dynamicCustomerFields->isNotEmpty())
                            <div style="padding: 12px 12px 6px; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; border-top: 1px solid var(--line); border-bottom:1px solid var(--line);">
                                {{ app()->getLocale() === 'en' ? 'Customer Profile Fields' : 'بيانات وحقول العميل' }}
                            </div>
                            @foreach ($dynamicCustomerFields as $custField)
                                @php
                                    $cfIcon = match ($custField->type) {
                                        'number' => 'bi-123',
                                        'date', 'datetime' => 'bi-calendar3',
                                        'select' => $custField->key === 'lead_temperature' ? 'bi-thermometer-half' : 'bi-list-check',
                                        'multiselect' => 'bi-ui-checks-grid',
                                        'checkbox' => 'bi-check2-square',
                                        'email' => 'bi-envelope',
                                        'tel' => 'bi-telephone',
                                        'url' => 'bi-link-45deg',
                                        'textarea' => 'bi-card-text',
                                        default => 'bi-input-cursor-text',
                                    };
                                @endphp
                                <label class="dynamic-field-option">
                                    <input
                                        type="checkbox"
                                        data-col-toggle="cf_{{ $custField->key }}"
                                        @checked($custField->key === 'lead_temperature')
                                    >
                                    <i class="bi {{ $cfIcon }}" aria-hidden="true"></i>
                                    <span class="dynamic-field-option-copy">
                                        <span>{{ $custField->localizedLabel() }}</span>
                                        <small>{{ $custField->lead_attribute ? (app()->getLocale() === 'en' ? 'Standard' : 'أساسي') : (app()->getLocale() === 'en' ? 'Custom' : 'حقل مخصص') }}</small>
                                    </span>
                                </label>
                            @endforeach
                        @endif

                        <div style="padding: 12px 12px 6px; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; border-top: 1px solid var(--line); border-bottom:1px solid var(--line);">
                            حقول وأسئلة المراحل (أعمدة وفلاتر ديناميكية)
                        </div>

                        @php
                            $activeStageModel = $selectedStage ?: ($selectedStatus?->stage);
                            $fieldsToDisplay = $activeStageModel
                                ? $availableStageFields->where('pipeline_stage_id', $activeStageModel->id)
                                : $availableStageFields;

                            $seenTargets = [];
                            $dedupedFields = $fieldsToDisplay->filter(function ($f) use (&$seenTargets) {
                                if ($f->isCanonical() && !empty($f->binding_target)) {
                                    if (in_array($f->binding_target, $seenTargets, true)) {
                                        return false;
                                    }
                                    $seenTargets[] = $f->binding_target;
                                }
                                return true;
                            });

                            $groupedFields = $dedupedFields->groupBy(fn ($f) => $f->stage?->localizedName() ?: 'المراحل');
                        @endphp

                        @foreach ($groupedFields as $stgName => $fList)
                            <div class="picker-stage-group-header" data-stage-name="{{ $stgName }}" style="padding: 8px 12px 2px; font-size: 11px; font-weight: 700; color: #4f46e5; background:rgba(79,70,229,0.04);">
                                • {{ $stgName }}
                            </div>
                            @foreach ($fList as $field)
                                @php
                                    $fieldIcon = match ($field->type) {
                                        'number', 'currency' => 'bi-123',
                                        'date', 'datetime' => 'bi-calendar3',
                                        'select', 'multiselect' => 'bi-list-check',
                                        'checkbox', 'boolean' => 'bi-check2-square',
                                        'email' => 'bi-envelope',
                                        'tel' => 'bi-telephone',
                                        'file', 'image', 'pdf' => 'bi-paperclip',
                                        default => 'bi-input-cursor-text',
                                    };
                                    $isFieldChecked = in_array((int) $field->id, $selectedStageFieldIds, true);
                                @endphp
                                <label class="dynamic-field-option" data-stage-id="{{ $field->pipeline_stage_id }}">
                                    <input
                                        type="checkbox"
                                        value="{{ $field->id }}"
                                        data-col-toggle="stage_field_{{ $field->id }}"
                                        data-dynamic-field-option
                                        @checked($isFieldChecked)
                                    >
                                    <i class="bi {{ $fieldIcon }}" aria-hidden="true"></i>
                                    <span class="dynamic-field-option-copy">
                                        <span>{{ $field->localizedLabel() }}</span>
                                        <small>{{ $field->stage?->localizedName() }}</small>
                                    </span>
                                </label>
                            @endforeach
                        @endforeach

                        <div class="dynamic-field-picker-footer" style="position: sticky; bottom: 0; background: var(--card); border-top: 1px solid var(--line); padding: 8px 12px;">
                            <div class="dynamic-field-picker-actions" style="display: flex; gap: 6px; flex-wrap: wrap; width: 100%;">
                                <button class="btn small soft" type="button" id="restoreDefaultColsBtn" style="flex:1; color: #4f46e5; font-weight: 700; font-size:11px;">
                                    استعادة الأعمدة الافتراضية
                                </button>
                                <button class="btn small soft" type="button" id="selectAllColsBtn" style="font-size:11px;" data-select-all-dynamic-fields>
                                    {{ __('crm.select_all_fields') }}
                                </button>
                                <button class="btn small soft" type="button" id="clearOptionalColsBtn" style="font-size:11px;" data-clear-dynamic-fields>
                                    {{ __('crm.clear_field_selection') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <div class="filter-actions-col">
                    <label style="font-size:12px; visibility:hidden;">Reset</label>
                    <a href="{{ route('v2.leads') }}" class="btn soft small" id="resetFiltersBtn" title="{{ __('crm.reset') }}" style="height:40px; display:inline-flex; align-items:center; gap:6px; font-weight:700;">
                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('crm.reset') }}
                    </a>
                </div>

                @if ($availableStageFields->isNotEmpty())
                    <div class="dynamic-filter-fields" data-dynamic-filter-fields style="grid-column: 1 / -1; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-top:8px;">
                        @foreach ($availableStageFields as $field)
                            @php
                                $fieldId = (int) $field->id;
                                $fieldSelected = in_array($fieldId, $selectedStageFieldIds, true);
                                $fieldValue = (string) ($stageFieldFilters[$fieldId] ?? '');
                                $fieldInputId = 'stageFieldFilter_' . $fieldId;
                            @endphp
                            <div
                                class="filter-field dynamic-filter-field"
                                data-dynamic-filter-field="{{ $fieldId }}"
                                data-stage-id="{{ $field->pipeline_stage_id }}"
                                @if (! $fieldSelected) hidden @endif
                            >
                                <label for="{{ $fieldInputId }}">
                                    <i class="bi bi-funnel"></i>
                                    {{ $field->localizedLabel() }}
                                    <span class="dynamic-filter-field-stage">· {{ $field->stage?->localizedName() }}</span>
                                </label>

                                @if (in_array($field->type, ['select', 'multiselect'], true))
                                    <select
                                        id="{{ $fieldInputId }}"
                                        name="field_filters[{{ $fieldId }}]"
                                        class="filter-control"
                                        @disabled(! $fieldSelected)
                                    >
                                        <option value="">{{ __('crm.all_field_values') }}</option>
                                        @foreach ($field->normalizedOptions() as $option)
                                            <option value="{{ $option['value'] }}" @selected($fieldValue === (string) $option['value'])>
                                                {{ app()->getLocale() === 'en' ? $option['label_en'] : $option['label_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif ($field->type === 'checkbox')
                                    <select
                                        id="{{ $fieldInputId }}"
                                        name="field_filters[{{ $fieldId }}]"
                                        class="filter-control"
                                        @disabled(! $fieldSelected)
                                    >
                                        <option value="">{{ __('crm.all_field_values') }}</option>
                                        <option value="1" @selected($fieldValue === '1')>{{ __('crm.yes') }}</option>
                                        <option value="0" @selected($fieldValue === '0')>{{ __('crm.no') }}</option>
                                    </select>
                                @else
                                    @php
                                        $filterInputType = match ($field->type) {
                                            'number', 'currency' => 'number',
                                            'date' => 'date',
                                            'datetime' => 'datetime-local',
                                            default => 'text',
                                        };
                                    @endphp
                                    <input
                                        id="{{ $fieldInputId }}"
                                        name="field_filters[{{ $fieldId }}]"
                                        type="{{ $filterInputType }}"
                                        @if (in_array($field->type, ['number', 'currency'], true)) step="any" @endif
                                        value="{{ $fieldValue }}"
                                        class="filter-control"
                                        placeholder="{{ $field->localizedPlaceholder() ?: __('crm.enter_filter_value') }}"
                                        @disabled(! $fieldSelected)
                                    >
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </form>
        </section>

        <!-- BULK SELECTION TOOLBAR (DIRECTLY BELOW FILTERS / ABOVE TABLE) -->
        <div class="bulk-toolbar" id="leadsBulkToolbar" style="display:none; margin-bottom:16px; padding:12px 20px; background:var(--card); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow-card); align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span class="badge" style="background:#eef2ff; color:#4f46e5; font-size:12px; padding:4px 10px; font-weight:800;">
                    <span id="selectedLeadsCounter">0</span> {{ __('عملاء محددين') }}
                </span>
                <button type="button" class="btn small soft" id="clearLeadsSelectionBtn" style="font-size:12px; padding:0 12px;">
                    {{ __('إلغاء التحديد') }}
                </button>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                @can('leads.assign')
                    @if (isset($assignableUsers) && $assignableUsers->isNotEmpty())
                        <button type="button" class="btn small primary" id="bulkAssignBtn" onclick="openBulkAssignModal()" style="font-size:12px;">
                            <i class="bi bi-person-check-fill"></i> {{ __('تعيين لموظف') }}
                        </button>
                    @endif
                @endcan
                @can('leads.export')
                    <button type="button" class="btn small soft" id="bulkExportBtn" onclick="submitBulkExport()" style="font-size:12px; color:#16a34a; border-color:#bbf7d0; background:#f0fdf4;">
                        <i class="bi bi-file-earmark-excel"></i> {{ __('تصدير') }}
                    </button>
                @endcan
                @can('leads.delete')
                    <button type="button" class="btn small danger" id="bulkDeleteBtn" onclick="confirmBulkDelete()" style="font-size:12px;">
                        <i class="bi bi-trash"></i> {{ __('حذف') }}
                    </button>
                @endcan
            </div>
        </div>

        <!-- HIDDEN FORM FOR BULK EXPORT -->
        <form id="bulkExportForm" method="POST" action="{{ route('v2.leads.export-selected') }}" style="display:none;">
            @csrf
            <div id="bulkExportInputs"></div>
        </form>

        <!-- HIDDEN FORM FOR BULK DELETE (TRASH) -->
        <form id="bulkDeleteForm" method="POST" action="{{ route('v2.leads.bulk-delete') }}" style="display:none;">
            @csrf
            <div id="bulkDeleteInputs"></div>
        </form>

        @can('leads.assign')
            @if (isset($assignableUsers) && $assignableUsers->isNotEmpty())
                <!-- BULK ASSIGN MODAL -->
                <div id="bulkAssignModal" class="crm-modal">
                    <div class="crm-modal-dialog">
                        <div class="modal-header">
                            <h3 style="margin:0; font-size:15px; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:8px;">
                                <i class="bi bi-person-check-fill" style="color:var(--red);"></i> {{ __('تعيين العملاء لموظف') }}
                            </h3>
                            <button type="button" onclick="closeBulkAssignModal()" style="background:none; border:none; font-size:20px; line-height:1; color:var(--muted); cursor:pointer; padding:0 4px;">&times;</button>
                        </div>
                        <form id="bulkAssignForm" method="POST" action="{{ route('v2.leads.bulk-assign') }}" style="margin:0;">
                            @csrf
                            <div class="modal-body">
                                <div style="margin-bottom:14px; padding:10px 14px; border-radius:10px; background:color-mix(in srgb, var(--red) 8%, var(--card)); border:1px solid color-mix(in srgb, var(--red) 20%, transparent); display:flex; align-items:center; gap:10px;">
                                    <i class="bi bi-info-circle-fill" style="color:var(--red); font-size:16px;"></i>
                                    <span style="font-size:12.5px; font-weight:700; color:var(--dark);">
                                        {{ __('سيتم إسناد') }} <strong id="bulkAssignCountText" style="color:var(--red);">0</strong> {{ __('عملاء تم تحديدهم') }}
                                    </span>
                                </div>
                                <div class="field" style="position:relative; z-index:1000020; overflow:visible;">
                                    <label for="bulk_target_user_id" style="display:block; margin-bottom:6px; font-size:12px; font-weight:800; color:var(--dark);">
                                        {{ __('اختر الموظف المسؤول') }} <span style="color:var(--red)">*</span>
                                    </label>
                                    <select
                                        id="bulk_target_user_id"
                                        name="target_user_id"
                                        class="crm-custom-select filter-control"
                                        data-crm-dropdown
                                        data-icon='<svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M10.02 12c.005-.184.02-.375.034-.555.056-.704.14-1.282.266-1.745A4.9 4.9 0 0 0 8 9c-1.378 0-2.496.53-2.92 1.077-.184.238-.309.522-.387.828a.5.5 0 0 0 .97.234c.05-.195.13-.38.252-.538C6.27 10.158 7.08 9.8 8 9.8c.92 0 1.73.358 2.085.801.074.092.127.202.164.321.037.119.06.252.073.403.014.16.023.325.027.475H3.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 .5-.5c0-.368-.008-.687-.02-1z"/></svg>'
                                        required
                                        style="width:100%; height:42px; border:1px solid var(--line); border-radius:10px; padding:0 12px; font-size:13px; background:var(--bg); color:var(--dark); font-weight:600;"
                                    >
                                        <option value="">{{ __('اختر الموظف المسؤول...') }}</option>
                                        @foreach ($assignableUsers as $assignUser)
                                            <option value="{{ $assignUser->id }}">{{ $assignUser->name }} ({{ $assignUser->username }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="bulkAssignInputs"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn small soft" onclick="closeBulkAssignModal()">{{ __('إلغاء') }}</button>
                                <button type="submit" class="btn small primary">{{ __('تأكيد التعيين') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endcan

        <!-- CUSTOMERS TABLE -->
        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th data-col="select" style="width:32px; text-align:center; padding:0 4px;">
                                <input type="checkbox" id="selectAllLeads" title="تحديد الكل في هذه الصفحة" style="width:15px; height:15px; cursor:pointer;">
                            </th>
                            <th data-col="client" style="min-width:160px">{{ __('crm.client') }}</th>
                            <th data-col="contact" style="min-width:95px">{{ __('crm.contact_data') }}</th>
                            <th data-col="company_source" style="min-width:105px">{{ __('crm.company_source') }}</th>
                            @foreach ($dynamicCustomerFields as $custField)
                                <th data-col="cf_{{ $custField->key }}" style="min-width:110px;" @if($custField->key !== 'lead_temperature') hidden @endif>
                                    {{ $custField->localizedLabel() }}
                                </th>
                            @endforeach
                            <th data-col="status" style="min-width:95px">{{ __('crm.current_status') }}</th>
                            <th data-col="category" style="min-width:85px">{{ __('crm.stage_category') }}</th>
                            <th data-col="employee" style="min-width:85px">{{ __('crm.responsible_employee') }}</th>
                            <th data-col="followup" style="min-width:105px">{{ __('crm.next_followup') }}</th>
                            <th data-col="created_date" style="min-width:80px">{{ __('crm.created_date') }}</th>
                            @foreach ($availableStageFields as $sField)
                                <th data-col="stage_field_{{ $sField->id }}" data-dynamic-stage-col="{{ $sField->id }}" style="min-width:105px;" @if(!in_array((int)$sField->id, $selectedStageFieldIds, true)) hidden @endif>
                                    {{ $sField->localizedLabel() }}
                                    <small style="display:block;font-size:10px;color:var(--muted)">{{ $sField->stage?->localizedName() }}</small>
                                </th>
                            @endforeach
                            <th data-col="actions" style="min-width:135px;text-align:center">{{ __('crm.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            @php
                                $leadStatusColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $lead->status?->color) ? $lead->status->color : '#64748b';
                                $leadCust = is_array($lead->custom_fields) ? $lead->custom_fields : [];
                                $leadPhoneRaw = trim((string) $lead->phone);
                                $leadPhoneDigits = preg_replace('/\D+/', '', $leadPhoneRaw) ?? '';
                                $callPhone = preg_match('/^[0-9]{2,20}$/', $leadPhoneDigits) === 1 ? $leadPhoneDigits : null;
                                $whatsappPhone = null;

                                if (str_starts_with($leadPhoneDigits, '0020')) {
                                    $whatsappPhone = substr($leadPhoneDigits, 2);
                                } elseif (preg_match('/^01[0125][0-9]{8}$/', $leadPhoneDigits) === 1) {
                                    $whatsappPhone = '20'.substr($leadPhoneDigits, 1);
                                } elseif (preg_match('/^20[0-9]{10}$/', $leadPhoneDigits) === 1 || preg_match('/^[1-9][0-9]{7,14}$/', $leadPhoneDigits) === 1) {
                                    $whatsappPhone = $leadPhoneDigits;
                                }
                            @endphp
                            <tr class="lead-row">
                                <td data-col="select" style="width:32px; text-align:center; padding:0 4px;">
                                    <input type="checkbox" class="lead-select-checkbox" value="{{ $lead->id }}" style="width:15px; height:15px; cursor:pointer;" onchange="handleRowSelectionChange()">
                                </td>

                                <td data-col="client">
                                    <div class="customer-name-cell">
                                        <div class="customer-avatar">
                                            {{ mb_substr((string) $lead->name, 0, 1) }}
                                        </div>
                                        <div class="customer-info">
                                            <a href="{{ route('v2.leads.show', array_merge(request()->query(), ['lead' => $lead->id])) }}">
                                                <strong>{{ $lead->name }}</strong>
                                            </a>
                                            @if($lead->branch)
                                                <span class="badge" style="background:#ecfdf5;color:#065f46;font-size:10px;padding:2px 6px;margin-inline-start:4px" title="{{ __('crm.branch') }}">
                                                    <i class="bi bi-geo-alt"></i> {{ $lead->branch->localizedName() }}
                                                </span>
                                            @endif
                                            <small>{{ $lead->email ?: __('crm.no_email') }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td data-col="contact">
                                    @if ($lead->phone)
                                        @can('leads.followups.view')
                                            @if ($callPhone)
                                                <a
                                                    class="js-call-followup"
                                                    href="{{ route('v2.leads.followups.index', ['lead' => $lead, 'channel' => 'call']) }}"
                                                    data-call-href="tel:{{ $callPhone }}"
                                                    title="{{ __('crm.open_microsip_followup') }}"
                                                    style="font-weight:700;color:inherit"
                                                    dir="ltr"
                                                >
                                                    {{ $lead->phone }}
                                                </a>
                                            @else
                                                <a href="tel:{{ $lead->phone }}" style="font-weight:700;color:inherit" dir="ltr">
                                                    {{ $lead->phone }}
                                                </a>
                                            @endif
                                        @else
                                            <a href="tel:{{ $lead->phone }}" style="font-weight:700;color:inherit" dir="ltr">
                                                {{ $lead->phone }}
                                            </a>
                                        @endcan
                                    @else
                                        <span style="color:var(--muted)">—</span>
                                    @endif
                                </td>

                                <td data-col="company_source">
                                    <strong>{{ $lead->company_name ?: __('crm.no_company') }}</strong>
                                    <span class="stage-name">{{ $lead->source ? __($lead->source) : __('غير محدد') }}</span>
                                </td>

                                @foreach ($dynamicCustomerFields as $custField)
                                    @php
                                        $rawVal = $custField->lead_attribute
                                            ? $lead->getAttribute($custField->lead_attribute)
                                            : ($leadCust[$custField->key] ?? null);
                                    @endphp
                                    <td data-col="cf_{{ $custField->key }}" @if($custField->key !== 'lead_temperature') hidden @endif>
                                        @if ($custField->key === 'lead_temperature')
                                            @if ($rawVal)
                                                @php
                                                    $tempStyle = match((string) $rawVal) {
                                                        'hot' => 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;',
                                                        'warm' => 'background:#fef3c7;color:#d97706;border:1px solid #fcd34d;',
                                                        'cold' => 'background:#e0f2fe;color:#0284c7;border:1px solid #7dd3fc;',
                                                        default => 'background:var(--card);color:var(--dark);border:1px solid var(--line);'
                                                    };
                                                    $tempOptMap = collect($custField->normalizedOptions())->keyBy('value');
                                                    $opt = $tempOptMap->get((string) $rawVal);
                                                    $tempLabel = $opt
                                                        ? (app()->getLocale() === 'en' && !empty($opt['label_en']) ? $opt['label_en'] : $opt['label_ar'])
                                                        : match((string) $rawVal) {
                                                            'hot' => '🔥 ' . (app()->getLocale() === 'en' ? 'Hot' : 'حار'),
                                                            'warm' => '⚡ ' . (app()->getLocale() === 'en' ? 'Warm' : 'متوسط'),
                                                            'cold' => '❄️ ' . (app()->getLocale() === 'en' ? 'Cold' : 'بارد'),
                                                            default => (string) $rawVal
                                                        };
                                                @endphp
                                                <span class="badge" style="font-size:11px;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;font-weight:700;white-space:nowrap;{{ $tempStyle }}">
                                                    <i class="bi bi-thermometer-half"></i> {{ $tempLabel }}
                                                </span>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        @elseif (in_array($custField->type, ['select', 'multiselect'], true))
                                            @if ($rawVal !== null && $rawVal !== '' && $rawVal !== [])
                                                @php
                                                    $optMap = collect($custField->normalizedOptions())->keyBy('value');
                                                    $valItems = (array) $rawVal;
                                                    $labels = array_map(function ($item) use ($optMap) {
                                                        $opt = $optMap->get((string) $item);
                                                        return $opt ? (app()->getLocale() === 'en' && !empty($opt['label_en']) ? $opt['label_en'] : $opt['label_ar']) : (string) $item;
                                                    }, $valItems);
                                                @endphp
                                                <span class="badge soft" style="font-size:11px;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;font-weight:600;background:rgba(99,102,241,0.08);color:#4f46e5;border:1px solid rgba(99,102,241,0.2);white-space:nowrap;">
                                                    {{ implode('، ', $labels) }}
                                                </span>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        @elseif ($custField->type === 'checkbox')
                                            @if ($rawVal !== null && $rawVal !== '')
                                                @if (filter_var($rawVal, FILTER_VALIDATE_BOOLEAN))
                                                    <span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-size:11px;padding:2px 6px;">{{ __('crm.yes') }}</span>
                                                @else
                                                    <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:11px;padding:2px 6px;">{{ __('crm.no') }}</span>
                                                @endif
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        @elseif ($custField->type === 'url')
                                            @if ($rawVal)
                                                <a href="{{ $rawVal }}" target="_blank" rel="noopener noreferrer" style="color:#2563eb;text-decoration:underline;font-size:12px;">{{ Str::limit((string) $rawVal, 20) }}</a>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        @else
                                            @if ($rawVal !== null && $rawVal !== '')
                                                <span style="font-size:12px;font-weight:600;">{{ $rawVal }}</span>
                                            @else
                                                <span style="color:var(--muted)">—</span>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach

                                <td data-col="status">
                                    <span class="status-badge" style="--status-color:{{ $leadStatusColor }}">
                                        <i class="status-dot"></i>
                                        {{ $lead->status?->name_ar ? __($lead->status->name_ar) : __('crm.no_status') }}
                                    </span>
                                </td>

                                <td data-col="category">
                                    @if ($lead->status?->stage?->category)
                                        <span class="badge" style="background:{{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'18' : '#f1f5f9' }}; color:{{ $lead->status->stage->category->color ?: '#475569' }}; border:1px solid {{ $lead->status->stage->category->color ? $lead->status->stage->category->color.'33' : '#e2e8f0' }}; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                                            <i class="bi {{ $lead->status->stage->category->icon ?: 'bi-collection' }}"></i>
                                            {{ $lead->status->stage->category->name_ar }}
                                        </span>
                                    @else
                                        <span style="color:var(--muted); font-size:12px;">—</span>
                                    @endif
                                </td>

                                <td data-col="employee">
                                    {{ $lead->assignedUser?->name ?? $lead->assigned_employee ?: __('crm.unassigned') }}
                                </td>

                                <td data-col="followup">
                                    @if ($lead->next_follow_up_at)
                                        <span class="badge {{ $lead->next_follow_up_at->isPast() ? 'overdue' : ($lead->next_follow_up_at->isToday() ? 'today' : '') }}">
                                            <i class="bi bi-clock"></i> {{ $lead->next_follow_up_at->format('d/m/Y - h:i A') }}
                                        </span>
                                    @else
                                        <span style="color:var(--muted)">—</span>
                                    @endif
                                </td>

                                <td data-col="created_date">
                                    {{ $lead->created_at?->format('d/m/Y') ?? '—' }}
                                </td>

                                @foreach ($availableStageFields as $sField)
                                    @php
                                        $sVal = $lead->stageValues->firstWhere('pipeline_stage_field_id', $sField->id)?->value;
                                    @endphp
                                    <td data-col="stage_field_{{ $sField->id }}" data-dynamic-stage-col="{{ $sField->id }}" @if(!in_array((int)$sField->id, $selectedStageFieldIds, true)) hidden @endif>
                                        @if ($sVal !== null && $sVal !== '')
                                            @if (in_array($sField->type, ['file', 'image', 'pdf'], true))
                                                <a href="{{ Storage::disk('local')->url($sVal) }}" target="_blank" class="badge" style="background:#e0f2fe; color:#0369a1;">
                                                    <i class="bi bi-paperclip"></i> ملف
                                                </a>
                                            @else
                                                <strong>{{ $sVal }}</strong>
                                            @endif
                                        @else
                                            <span style="color:var(--muted)">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td data-col="actions" style="text-align:center; padding:4px 6px;">
                                    <div class="actions-cell">
                                        @if ($whatsappPhone)
                                            <a
                                                class="btn-action whatsapp"
                                                href="https://wa.me/{{ $whatsappPhone }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="{{ __('crm.whatsapp') }}"
                                            >
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                        @endif

                                        @if ($callPhone)
                                            @can('leads.followups.view')
                                                <a
                                                    class="btn-action call js-call-followup"
                                                    href="{{ route('v2.leads.followups.index', ['lead' => $lead, 'channel' => 'call']) }}"
                                                    data-call-href="tel:{{ $callPhone }}"
                                                    title="{{ __('crm.call_action') }}"
                                                >
                                                    <i class="bi bi-telephone"></i>
                                                </a>
                                            @else
                                                <a class="btn-action call" href="tel:{{ $callPhone }}" title="{{ __('crm.call_action') }}">
                                                    <i class="bi bi-telephone"></i>
                                                </a>
                                            @endcan
                                        @endif

                                        @can('leads.followups.view')
                                            <a
                                                class="btn-action followup"
                                                href="{{ route('v2.leads.followups.index', $lead) }}"
                                                title="{{ __('crm.log_new_followup') }}"
                                            >
                                                <i class="bi bi-clock-history"></i>
                                            </a>
                                        @endcan

                                        <a
                                            class="btn-action view"
                                            href="{{ route('v2.leads.show', array_merge(request()->query(), ['lead' => $lead->id])) }}"
                                            title="{{ __('crm.view_lead') }}"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('leads.update')
                                            <a
                                                class="btn-action edit"
                                                href="{{ route('v2.leads.edit', $lead) }}"
                                                title="{{ __('crm.edit_data') }}"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" style="text-align:center;padding:40px 20px;color:var(--muted)">
                                    <i class="bi bi-people" style="font-size:32px;display:block;margin-bottom:8px"></i>
                                    <strong>{{ $activeQuery === [] ? __('لا يوجد عملاء حتى الآن') : __('لا توجد نتائج مطابقة') }}</strong>
                                    <p style="margin:4px 0 0;font-size:12px">
                                        @if ($activeQuery === [])
                                            لم تتم إضافة أي عميل إلى قاعدة CRM حتى الآن.
                                        @else
                                            جرّب تغيير كلمات البحث أو إزالة بعض الفلاتر الحالية.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if ($leads->hasPages())
                {{ $leads->links() }}
            @endif
        </section>
    </main>
</div>

<script>
(() => {
    const form = document.getElementById('leadFilters');
    const searchInput = document.getElementById('searchQuery');
    const tableWrap = document.querySelector('.table-wrap');
    const summaryCard = document.querySelector('.summary');
    const fieldPickerToggle = document.getElementById('chooseFieldsToggle') || document.querySelector('[data-dynamic-field-picker-toggle]');
    const fieldPickerMenu = document.getElementById('dynamicFieldPickerMenu') || document.querySelector('[data-dynamic-field-picker-menu]');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const restoreDefaultColsBtn = document.getElementById('restoreDefaultColsBtn');
    const selectAllColsBtn = document.getElementById('selectAllColsBtn');
    const clearOptionalColsBtn = document.getElementById('clearOptionalColsBtn');

    let abortController = null;
    let searchDebounce = null;

    // 1. Column Management (Mandatory + Optional + Dynamic)
    const MANDATORY_COLS = ['select', 'client', 'status', 'category', 'actions'];
    const DEFAULT_COLS = ['client', 'contact', 'company_source', 'cf_lead_temperature', 'status', 'category', 'employee', 'followup', 'created_date', 'actions'];

    const getStoredColumns = () => {
        try {
            const saved = localStorage.getItem('sokrat.crm.leads.columns');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    if (!parsed.includes('cf_lead_temperature') && !localStorage.getItem('sokrat.crm.leads.columns.v2_temp_col')) {
                        const idx = parsed.indexOf('company_source');
                        if (idx !== -1) {
                            parsed.splice(idx + 1, 0, 'cf_lead_temperature');
                        } else {
                            parsed.push('cf_lead_temperature');
                        }
                        localStorage.setItem('sokrat.crm.leads.columns.v2_temp_col', '1');
                        saveColumns(parsed);
                    }
                    return parsed;
                }
            }
        } catch (e) {}
        return DEFAULT_COLS;
    };

    const saveColumns = (cols) => {
        try {
            localStorage.setItem('sokrat.crm.leads.columns', JSON.stringify(cols));
        } catch (e) {}
    };

    const applyColumnVisibility = () => {
        const activeCols = new Set(getStoredColumns());
        MANDATORY_COLS.forEach(c => activeCols.add(c));

        // Sync checkboxes in column chooser
        document.querySelectorAll('[data-col-toggle]').forEach(cb => {
            const col = cb.dataset.colToggle;
            if (MANDATORY_COLS.includes(col)) {
                cb.checked = true;
                cb.disabled = true;
            } else {
                cb.checked = activeCols.has(col);
            }
        });

        // Toggle table headers and cells
        document.querySelectorAll('th[data-col], td[data-col]').forEach(el => {
            const col = el.dataset.col;
            if (col === 'select') {
                el.hidden = false;
                return;
            }
            el.hidden = !activeCols.has(col);
        });

        // Sync dynamic filter inputs above the table
        document.querySelectorAll('.dynamic-filter-field').forEach(field => {
            const fieldId = field.dataset.dynamicFilterField;
            const colKey = `stage_field_${fieldId}`;
            const isVisible = activeCols.has(colKey);
            field.hidden = !isVisible;
            field.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = !isVisible;
            });
        });

        const dynamicEmpty = document.querySelector('[data-dynamic-filter-empty]');
        const dynamicCountEl = document.querySelector('[data-dynamic-field-count]');
        const dynamicActiveCount = Array.from(activeCols).filter(c => c.startsWith('stage_field_') || c.startsWith('cf_')).length;
        if (dynamicCountEl) dynamicCountEl.textContent = String(dynamicActiveCount);
        if (dynamicEmpty) dynamicEmpty.hidden = dynamicActiveCount > 0;

        const selectedIdsInput = document.querySelector('[data-selected-dynamic-field-ids]');
        if (selectedIdsInput) {
            const stageFieldIds = Array.from(activeCols)
                .filter(c => c.startsWith('stage_field_'))
                .map(c => c.replace('stage_field_', ''));
            selectedIdsInput.value = stageFieldIds.join(',');
        }
    };

    // 2. Reactive Fetching Architecture (No Apply Button, URL remains state)
    const fetchFilteredLeads = (targetUrl, pushState = true) => {
        if (abortController) {
            abortController.abort(); // Cancel previous in-flight request
        }
        abortController = new AbortController();

        if (tableWrap) {
            tableWrap.style.opacity = '0.5';
            tableWrap.style.pointerEvents = 'none';
        }

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: abortController.signal
        })
        .then(res => res.text())
        .then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.table-card');
            const currentTable = document.querySelector('.table-card');
            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
            }

            const newSummary = doc.querySelector('.summary');
            if (newSummary && summaryCard) {
                summaryCard.innerHTML = newSummary.innerHTML;
            }

            if (pushState) {
                window.history.pushState(null, '', targetUrl);
            }

            applyColumnVisibility();
            wirePaginationAndCallLinks();
            updateBulkToolbar();
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Filtering failed:', err);
            }
        })
        .finally(() => {
            if (tableWrap) {
                tableWrap.style.opacity = '';
                tableWrap.style.pointerEvents = '';
            }
        });
    };

    const buildFilterUrl = () => {
        if (!form) return window.location.href;
        const formData = new FormData(form);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) {
                params.set(key, value);
            }
        }

        const base = form.action || window.location.pathname;
        const qs = params.toString();
        return qs ? `${base}?${qs}` : base;
    };

    const triggerReactiveFilter = () => {
        const url = buildFilterUrl();
        fetchFilteredLeads(url, true);
    };

    // Listeners for reactive filter inputs
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(triggerReactiveFilter, 280);
    });

    ['leadStatus', 'leadSource', 'leadEmployee', 'leadFollowUp', 'leadSort'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', triggerReactiveFilter);
    });
    const syncStatusSpecificFields = () => {
        const statusSelect = document.getElementById('leadStatus');
        const selectedOpt = statusSelect?.selectedOptions?.[0];
        const statusVal = statusSelect?.value || '';
        const stageId = selectedOpt?.dataset?.stageId || '';

        document.querySelectorAll('.dynamic-field-picker-menu .dynamic-field-option[data-stage-id]').forEach(opt => {
            if (!statusVal || !stageId) {
                opt.style.display = '';
            } else {
                const optStageId = opt.dataset.stageId;
                opt.style.display = (optStageId === stageId) ? '' : 'none';
            }
        });
        document.querySelectorAll('.picker-stage-group-header').forEach(hdr => {
            hdr.style.display = (statusVal && stageId) ? 'none' : '';
        });
    };

    document.getElementById('leadStatus')?.addEventListener('change', syncStatusSpecificFields);
    syncStatusSpecificFields();


    // Dynamic field filter inputs
    document.querySelector('[data-dynamic-filter-fields]')?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(triggerReactiveFilter, 300);
    });
    document.querySelector('[data-dynamic-filter-fields]')?.addEventListener('change', triggerReactiveFilter);

    // Browser History (Back / Forward)
    window.addEventListener('popstate', () => {
        fetchFilteredLeads(window.location.href, false);
    });

    // Intercept Pagination Links
    const wirePaginationAndCallLinks = () => {
        document.querySelectorAll('.pagination a, .table-card .pagination a').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                fetchFilteredLeads(a.href, true);
                window.scrollTo({ top: document.querySelector('.filter-panel')?.offsetTop || 0, behavior: 'smooth' });
            });
        });

        // Wire softphone links
        document.querySelectorAll('.js-call-followup').forEach(link => {
            link.addEventListener('click', () => {
                const callHref = link.dataset.callHref;
                if (!callHref || link.target !== '_blank') return;
                window.setTimeout(() => { window.location.href = callHref; }, 120);
            });
        });
    };

    // Column Picker Toggle and Actions
    const toggleFieldPicker = (e) => {
        if (e) e.stopPropagation();
        if (!fieldPickerMenu) return;
        const isHidden = fieldPickerMenu.hidden || fieldPickerMenu.style.display === 'none';
        if (isHidden) {
            fieldPickerMenu.hidden = false;
            fieldPickerMenu.style.display = 'block';
            fieldPickerToggle?.setAttribute('aria-expanded', 'true');
        } else {
            fieldPickerMenu.hidden = true;
            fieldPickerMenu.style.display = 'none';
            fieldPickerToggle?.setAttribute('aria-expanded', 'false');
        }
    };
    fieldPickerToggle?.addEventListener('click', toggleFieldPicker);

    document.addEventListener('click', (e) => {
        if (fieldPickerMenu && !fieldPickerMenu.hidden && fieldPickerMenu.style.display !== 'none') {
            if (!fieldPickerToggle?.contains(e.target) && !fieldPickerMenu.contains(e.target)) {
                fieldPickerMenu.hidden = true;
                fieldPickerMenu.style.display = 'none';
                fieldPickerToggle?.setAttribute('aria-expanded', 'false');
            }
        }
    });

    // Bulk Selection Handling
    const getSelectedLeadIds = () => {
        return Array.from(document.querySelectorAll('.lead-select-checkbox:checked')).map(cb => cb.value);
    };

    const updateBulkToolbar = () => {
        const selectedIds = getSelectedLeadIds();
        const toolbar = document.getElementById('leadsBulkToolbar');
        const counter = document.getElementById('selectedLeadsCounter');
        const selectAll = document.getElementById('selectAllLeads');
        const checkboxes = Array.from(document.querySelectorAll('.lead-select-checkbox'));
        const total = checkboxes.length;

        if (selectAll) {
            selectAll.checked = total > 0 && selectedIds.length === total;
            selectAll.indeterminate = selectedIds.length > 0 && selectedIds.length < total;
        }

        if (counter) counter.textContent = String(selectedIds.length);
        if (toolbar) {
            toolbar.style.display = selectedIds.length > 0 ? 'flex' : 'none';
        }
    };

    window.handleRowSelectionChange = () => {
        updateBulkToolbar();
    };

    window.openBulkAssignModal = () => {
        const ids = getSelectedLeadIds();
        if (ids.length === 0) return;
        const countEl = document.getElementById('bulkAssignCountText');
        if (countEl) countEl.textContent = ids.length;
        const container = document.getElementById('bulkAssignInputs');
        if (container) {
            container.innerHTML = '';
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'lead_ids[]';
                inp.value = id;
                container.appendChild(inp);
            });
        }
        const modal = document.getElementById('bulkAssignModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                const selectEl = document.getElementById('bulk_target_user_id');
                const triggerBtn = selectEl?.closest('.field')?.querySelector('.crm-dropdown-trigger');
                if (triggerBtn) {
                    triggerBtn.focus();
                } else if (selectEl) {
                    selectEl.focus();
                }
            }, 60);
        }
    };

    window.closeBulkAssignModal = () => {
        const modal = document.getElementById('bulkAssignModal');
        if (modal) modal.style.display = 'none';
    };

    document.getElementById('bulkAssignModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'bulkAssignModal') {
            window.closeBulkAssignModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeBulkAssignModal();
        }
    });

    window.submitBulkExport = () => {
        const ids = getSelectedLeadIds();
        if (ids.length === 0) return;
        const container = document.getElementById('bulkExportInputs');
        if (!container) return;
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'lead_ids[]';
            inp.value = id;
            container.appendChild(inp);
        });
        document.getElementById('bulkExportForm')?.submit();
    };

    window.confirmBulkDelete = () => {
        const ids = getSelectedLeadIds();
        if (ids.length === 0) return;
        const msg = `سيتم نقل ${ids.length} عملاء إلى سلة المهملات. هل أنت متأكد من الحذف؟`;
        if (!confirm(msg)) return;
        const container = document.getElementById('bulkDeleteInputs');
        if (!container) return;
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'lead_ids[]';
            inp.value = id;
            container.appendChild(inp);
        });
        document.getElementById('bulkDeleteForm')?.submit();
    };

    document.addEventListener('change', (e) => {
        if (e.target && e.target.id === 'selectAllLeads') {
            document.querySelectorAll('.lead-select-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
            updateBulkToolbar();
        } else if (e.target && e.target.classList.contains('lead-select-checkbox')) {
            updateBulkToolbar();
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target && (e.target.id === 'clearLeadsSelectionBtn' || e.target.closest('#clearLeadsSelectionBtn'))) {
            document.querySelectorAll('.lead-select-checkbox').forEach(cb => { cb.checked = false; });
            updateBulkToolbar();
        }
    });
    // Column Checkbox Changed
    document.querySelectorAll('[data-col-toggle]').forEach(cb => {
        cb.addEventListener('change', () => {
            const checked = Array.from(document.querySelectorAll('[data-col-toggle]:checked')).map(el => el.dataset.colToggle);
            saveColumns(checked);
            applyColumnVisibility();
            triggerReactiveFilter();
        });
    });

    // Clear Optional Columns
    clearOptionalColsBtn?.addEventListener('click', () => {
        saveColumns(MANDATORY_COLS);
        applyColumnVisibility();
        triggerReactiveFilter();
    });

    // Select All Dynamic Stage Fields
    selectAllColsBtn?.addEventListener('click', () => {
        const dynamicCols = Array.from(document.querySelectorAll('[data-dynamic-field-option]'))
            .map(cb => cb.dataset.colToggle)
            .filter(col => col && col.startsWith('stage_field_'));
        saveColumns([...MANDATORY_COLS, ...dynamicCols]);
        applyColumnVisibility();
        triggerReactiveFilter();
    });

    // Restore Default Columns
    restoreDefaultColsBtn?.addEventListener('click', () => {
        saveColumns(DEFAULT_COLS);
        applyColumnVisibility();
        triggerReactiveFilter();
    });

    // Reset Filters button (resets filter values without clearing column choices!)
    resetFiltersBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        if (!form) return;
        form.querySelectorAll('input:not([type="hidden"]), select').forEach(el => {
            el.value = '';
        });
        document.querySelectorAll('[name^="field_filters["]').forEach(el => {
            el.value = '';
        });
        triggerReactiveFilter();
    });

    // Prevent native form submission
    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        triggerReactiveFilter();
    });

    // Initial setup
    applyColumnVisibility();
    wirePaginationAndCallLinks();
    updateBulkToolbar();
})();
</script>
<script>
(() => {
  document.querySelectorAll('.dash-pipeline-strip').forEach((strip) => {
    strip.addEventListener('wheel', (event) => {
      if (
        event.defaultPrevented
        || event.shiftKey
        || Math.abs(event.deltaY) <= Math.abs(event.deltaX)
        || strip.scrollWidth <= strip.clientWidth
      ) {
        return;
      }

      const beforeScrollLeft = strip.scrollLeft;
      const direction = getComputedStyle(strip).direction === 'rtl' ? -1 : 1;

      strip.scrollBy({
        left: event.deltaY * direction,
        behavior: 'auto',
      });

      if (strip.scrollLeft === beforeScrollLeft) {
        return;
      }

      event.preventDefault();
    }, { passive: false });
  });
})();
</script>
<script src="{{ asset('crm-notifications.js') }}?v=1.0.0"></script>
<script src="{{ asset('crm-dropdown.js') }}?v={{ time() }}"></script>
</body>
</html>
