@extends('settings.layout')

@section('title', __('crm.audit_logs'))

@section('content')
<div style="display:flex; flex-direction:column; gap:20px;">

    <!-- TOP HEADER WITH COUNTERS -->
    <section class="panel" style="margin-bottom:0;">
        <div class="panel-head" style="margin-bottom:0;">
            <div>
                <h2><i class="bi bi-clock-history" style="color:var(--red);"></i> {{ __('crm.audit_logs') }}</h2>
                <p>{{ __('crm.audit_logs_subtitle') }}</p>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <div style="text-align:center; padding:6px 14px; background:var(--bg); border:1px solid var(--line); border-radius:10px;">
                    <span style="font-size:11px; color:var(--muted); font-weight:700; display:block;">{{ __('crm.total_logs') }}</span>
                    <b style="font-size:17px; color:var(--dark);">{{ number_format($totalLogs) }}</b>
                </div>
                <div style="text-align:center; padding:6px 14px; background:color-mix(in srgb, #16a34a 8%, var(--card)); border:1px solid #bbf7d0; border-radius:10px;">
                    <span style="font-size:11px; color:#166534; font-weight:700; display:block;">{{ __('crm.today_activities') }}</span>
                    <b style="font-size:17px; color:#16a34a;">{{ number_format($todayLogs) }}</b>
                </div>
            </div>
        </div>
    </section>

    <!-- FILTER FORM -->
    <section class="panel">
        <form method="GET" action="{{ route('v2.settings.audit-logs.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px; align-items:end;">
            
            <!-- Search Text -->
            <div>
                <label for="searchQuery" style="font-size:12px; margin-bottom:5px; font-weight:700; color:var(--dark);">
                    <i class="bi bi-search"></i> {{ __('crm.quick_search') }}
                </label>
                <input
                    type="text"
                    id="searchQuery"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="{{ __('crm.search_logs_placeholder') }}"
                    style="height:40px; font-size:12.5px;"
                >
            </div>

            <!-- Filter by User -->
            <div>
                <label for="filterUser" style="font-size:12px; margin-bottom:5px; font-weight:700; color:var(--dark);">
                    <i class="bi bi-person"></i> {{ __('crm.user') }}
                </label>
                <select id="filterUser" name="user_id" style="height:40px; font-size:12.5px;">
                    <option value="">{{ __('crm.all_users') }}</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected((string)$filters['user_id'] === (string)$u->id)>
                            {{ $u->name }} ({{ $u->username }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Module -->
            <div>
                <label for="filterModule" style="font-size:12px; margin-bottom:5px; font-weight:700; color:var(--dark);">
                    <i class="bi bi-folder2-open"></i> {{ __('crm.module_section') }}
                </label>
                <select id="filterModule" name="module" style="height:40px; font-size:12.5px;">
                    <option value="">{{ __('crm.all_modules') }}</option>
                    @foreach ($modules as $modKey => $modName)
                        <option value="{{ $modKey }}" @selected($filters['module'] === $modKey)>
                            {{ $modName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="dateFrom" style="font-size:12px; margin-bottom:5px; font-weight:700; color:var(--dark);">
                    <i class="bi bi-calendar"></i> {{ __('crm.date_from') }}
                </label>
                <input
                    type="date"
                    id="dateFrom"
                    name="date_from"
                    value="{{ $filters['date_from'] }}"
                    style="height:40px; font-size:12.5px;"
                >
            </div>

            <!-- Date To -->
            <div>
                <label for="dateTo" style="font-size:12px; margin-bottom:5px; font-weight:700; color:var(--dark);">
                    <i class="bi bi-calendar"></i> {{ __('crm.date_to') }}
                </label>
                <input
                    type="date"
                    id="dateTo"
                    name="date_to"
                    value="{{ $filters['date_to'] }}"
                    style="height:40px; font-size:12.5px;"
                >
            </div>

            <!-- Submit & Reset Buttons -->
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary small" style="flex:1; justify-content:center;">
                    <i class="bi bi-filter"></i> {{ __('crm.filter') }}
                </button>
                @if (array_filter($filters))
                    <a href="{{ route('v2.settings.audit-logs.index') }}" class="btn soft small" style="justify-content:center;" title="{{ __('crm.reset_filter') }}">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>

        </form>
    </section>

    <!-- LOGS TABLE -->
    <section class="panel" style="padding:0; overflow:hidden;">
        <div class="table-wrap" style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse; text-align:start;">
                <thead>
                    <tr style="border-bottom:1px solid var(--line); background:var(--bg);">
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted); width:170px;">{{ __('crm.timestamp') }}</th>
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted); width:180px;">{{ __('crm.user') }}</th>
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted); width:140px;">{{ __('crm.action') }}</th>
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted);">{{ __('crm.details_and_description') }}</th>
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted); width:140px;">{{ __('crm.ip_and_device') }}</th>
                        <th style="padding:14px 16px; font-size:12px; font-weight:800; color:var(--muted); width:100px; text-align:center;">{{ __('crm.changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action, 'create') || str_contains($log->action, 'login') => '#16a34a',
                                str_contains($log->action, 'delete') || str_contains($log->action, 'trash') || str_contains($log->action, 'failed') => '#dc2626',
                                str_contains($log->action, 'update') || str_contains($log->action, 'transition') => '#3b82f6',
                                str_contains($log->action, 'assign') => '#f59e0b',
                                default => '#6b7280',
                            };
                        @endphp
                        <tr style="border-bottom:1px solid var(--line); transition:background 0.1s ease;">
                            <!-- Timestamp -->
                            <td style="padding:12px 16px; font-size:12px; color:var(--dark); white-space:nowrap;">
                                <div style="font-weight:700;">{{ $log->created_at->format('Y-m-d') }}</div>
                                <small style="color:var(--muted); font-size:11px;">{{ $log->created_at->format('h:i:s A') }}</small>
                            </td>

                            <!-- User -->
                            <td style="padding:12px 16px; font-size:13px; color:var(--dark);">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:30px; height:30px; border-radius:50%; background:color-mix(in srgb, var(--red) 12%, var(--card)); color:var(--red); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">
                                        {{ mb_substr($log->user_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        <div style="font-weight:800; line-height:1.2;">{{ $log->user_name ?? __('crm.system') }}</div>
                                        @if($log->user)
                                            <small style="color:var(--muted); font-size:11px;">{{ $log->user->username }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Action Badge -->
                            <td style="padding:12px 16px; font-size:12px; white-space:nowrap;">
                                <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:800; background:color-mix(in srgb, {{ $actionColor }} 12%, var(--card)); color:{{ $actionColor }}; border:1px solid color-mix(in srgb, {{ $actionColor }} 30%, transparent);">
                                    {{ $log->localizedAction() }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td style="padding:12px 16px; font-size:12.5px; color:var(--dark);">
                                <div style="font-weight:600; line-height:1.4;">{{ $log->description }}</div>
                                @if ($log->subject_label)
                                    <div style="margin-top:3px; font-size:11px; color:var(--muted); display:flex; align-items:center; gap:6px;">
                                        <span><i class="bi bi-link-45deg"></i> {{ __('crm.target') }}: <strong>{{ $log->subject_label }}</strong></span>
                                        @if ($targetUrl = $log->targetUrl())
                                            <a href="{{ $targetUrl }}" class="btn small soft" style="padding:2px 8px; font-size:10.5px; min-height:22px; line-height:1; display:inline-flex; align-items:center; gap:4px; text-decoration:none;" title="{{ app()->getLocale() === 'en' ? 'Open' : 'عرض' }}">
                                                <span>{{ app()->getLocale() === 'en' ? 'Open' : 'عرض' }}</span>
                                                <i class="bi bi-arrow-left rtl-flip" style="font-size:10px;"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if (!empty($log->properties['changes']))
                                    <div style="margin-top:6px; display:flex; flex-direction:column; gap:4px;">
                                        @foreach (array_slice($log->properties['changes'], 0, 3) as $field => $change)
                                            <div style="font-size:11px; background:var(--bg); border:1px solid var(--line); border-radius:6px; padding:3px 8px; display:inline-flex; align-items:center; gap:6px; max-width:fit-content;">
                                                <strong style="color:var(--dark);">{{ \App\Models\ActivityLog::fieldLabel($field) }}:</strong>
                                                <span style="color:#dc2626; text-decoration:line-through;">{{ is_array($change['old']) ? json_encode($change['old']) : ($change['old'] ?? '—') }}</span>
                                                <i class="bi bi-arrow-right rtl-flip" style="font-size:10px; color:var(--muted);"></i>
                                                <span style="color:#16a34a; font-weight:700;">{{ is_array($change['new']) ? json_encode($change['new']) : ($change['new'] ?? '—') }}</span>
                                            </div>
                                        @endforeach
                                        @if (count($log->properties['changes']) > 3)
                                            <span style="font-size:10.5px; color:var(--muted); font-style:italic;">
                                                + {{ count($log->properties['changes']) - 3 }} {{ __('crm.more_changes') ?? 'تغييرات إضافية' }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif (!empty($log->properties['from_status']) || !empty($log->properties['to_status']))
                                    <div style="margin-top:6px; display:flex; flex-direction:column; gap:4px;">
                                        <div style="font-size:11px; background:var(--bg); border:1px solid var(--line); border-radius:6px; padding:3px 8px; display:inline-flex; align-items:center; gap:6px; max-width:fit-content;">
                                            <strong style="color:var(--dark);">{{ __('crm.status') }}:</strong>
                                            <span style="color:#dc2626; text-decoration:line-through;">{{ $log->properties['from_status'] ?? '—' }}</span>
                                            <i class="bi bi-arrow-right rtl-flip" style="font-size:10px; color:var(--muted);"></i>
                                            <span style="color:#16a34a; font-weight:700;">{{ $log->properties['to_status'] ?? '—' }}</span>
                                            @if (!empty($log->properties['to_stage']))
                                                <small style="color:var(--muted);">({{ $log->properties['to_stage'] }})</small>
                                            @endif
                                        </div>
                                        @if (!empty($log->properties['context']['outcome']))
                                            <div style="font-size:11.5px; color:var(--muted); margin-top:2px;">
                                                <i class="bi bi-chat-left-text"></i> {{ $log->properties['context']['outcome'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- IP & Agent -->
                            <td style="padding:12px 16px; font-size:11px; color:var(--muted);">
                                <div><code>{{ $log->ip_address ?? '—' }}</code></div>
                            </td>

                            <!-- Changes Modal Trigger -->
                            <td style="padding:12px 16px; text-align:center;">
                                @if (!empty($log->properties))
                                    <button
                                        type="button"
                                        class="btn small soft"
                                        style="font-size:11px; padding:4px 8px; min-height:28px;"
                                        onclick="showLogDetails({{ json_encode($log->properties) }}, '{{ addslashes($log->description) }}')"
                                    >
                                        <i class="bi bi-eye"></i> {{ __('crm.view_details') }}
                                    </button>
                                @else
                                    <span style="color:var(--muted); font-size:12px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px 16px; text-align:center; color:var(--muted);">
                                <i class="bi bi-clock-history" style="font-size:36px; display:block; margin-bottom:12px; opacity:0.5;"></i>
                                <span style="font-size:14px; font-weight:700;">{{ __('crm.no_logs_found') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div style="padding:14px 20px; border-top:1px solid var(--line); background:var(--bg);">
                {{ $logs->links() }}
            </div>
        @endif
    </section>

</div>

<!-- DETAILS MODAL -->
<div id="logDetailsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:999999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(3px);">
    <div style="background:var(--card); border:1px solid var(--line); border-radius:16px; width:100%; max-width:650px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        
        <div style="padding:16px 20px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; background:var(--bg); border-top-left-radius:16px; border-top-right-radius:16px;">
            <h3 style="margin:0; font-size:15px; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:8px;">
                <i class="bi bi-info-circle-fill" style="color:var(--red);"></i> {{ __('crm.changes_details') }}
            </h3>
            <button type="button" onclick="closeLogDetailsModal()" style="background:none; border:none; font-size:22px; line-height:1; color:var(--muted); cursor:pointer; padding:0 4px;">&times;</button>
        </div>

        <div style="padding:20px; overflow-y:auto; flex:1;">
            <div id="modalLogDescription" style="font-size:13px; font-weight:700; margin-bottom:16px; padding:10px 14px; border-radius:10px; background:color-mix(in srgb, var(--red) 8%, var(--card)); border:1px solid color-mix(in srgb, var(--red) 20%, transparent); color:var(--dark);"></div>
            <div id="modalLogContent"></div>
        </div>

        <div style="padding:14px 20px; border-top:1px solid var(--line); background:var(--bg); border-bottom-left-radius:16px; border-bottom-right-radius:16px; display:flex; justify-content:flex-end;">
            <button type="button" class="btn small soft" onclick="closeLogDetailsModal()">{{ __('crm.close') }}</button>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const i18nModifiedFields = @json(__('crm.modified_fields'));
    const i18nField = @json(__('crm.field'));
    const i18nPrevValue = @json(__('crm.previous_value'));
    const i18nNewValue = @json(__('crm.new_value'));

    const fieldMap = {
        'name': @json(__('crm.name') ?? 'Name'),
        'first_name': @json(__('crm.first_name') ?? 'First Name'),
        'last_name': @json(__('crm.last_name') ?? 'Last Name'),
        'phone': @json(__('crm.phone') ?? 'Phone'),
        'email': @json(__('crm.email') ?? 'Email'),
        'company_name': @json(__('crm.company_name') ?? 'Company Name'),
        'governorate': @json(__('crm.governorate') ?? 'Governorate'),
        'address': @json(__('crm.address') ?? 'Address'),
        'activity': @json(__('crm.activity') ?? 'Activity'),
        'job_title': @json(__('crm.job_title') ?? 'Job Title'),
        'source': @json(__('crm.source') ?? 'Source'),
        'assigned_employee': @json(__('crm.assigned_employee') ?? 'Assigned Employee'),
        'assigned_user': @json(__('crm.assigned_employee') ?? 'Assigned User'),
        'status': @json(__('crm.status') ?? 'Status'),
        'lead_status_id': @json(__('crm.status') ?? 'Status'),
        'notes': @json(__('crm.notes') ?? 'Notes'),
        'next_follow_up_at': @json(__('crm.next_followup') ?? 'Next Follow-up'),
        'disinterest_reason': @json(__('crm.reason') ?? 'Disinterest Reason'),
        'solution_type': @json(__('crm.solution_type') ?? 'Solution Type'),
    };

    function showLogDetails(properties, description) {
        document.getElementById('modalLogDescription').textContent = description;
        const container = document.getElementById('modalLogContent');
        container.innerHTML = '';

        if (properties.changes) {
            let html = `<h4 style="font-size:13px; font-weight:800; margin-bottom:10px;">${i18nModifiedFields}</h4>`;
            html += '<table style="width:100%; border-collapse:collapse; font-size:12.5px; margin-bottom:14px;">';
            html += `<thead style="background:var(--bg); border-bottom:1px solid var(--line);"><tr><th style="padding:10px; text-align:start;">${i18nField}</th><th style="padding:10px; text-align:start;">${i18nPrevValue}</th><th style="padding:10px; text-align:start;">${i18nNewValue}</th></tr></thead><tbody>`;

            for (const [field, change] of Object.entries(properties.changes)) {
                const label = fieldMap[field] || field;
                const oldVal = typeof change.old === 'object' ? JSON.stringify(change.old) : (change.old ?? '—');
                const newVal = typeof change.new === 'object' ? JSON.stringify(change.new) : (change.new ?? '—');

                html += `<tr style="border-bottom:1px solid var(--line);">
                    <td style="padding:10px; font-weight:700;">
                        <div>${label}</div>
                        <small style="color:var(--muted); font-size:10.5px; font-family:monospace;">${field}</small>
                    </td>
                    <td style="padding:10px; color:#dc2626; background:rgba(220,38,38,0.05); font-weight:600;">
                        <span style="text-decoration:line-through;">${escapeHtml(oldVal)}</span>
                    </td>
                    <td style="padding:10px; color:#16a34a; background:rgba(22,163,74,0.05); font-weight:700;">
                        ${escapeHtml(newVal)}
                    </td>
                </tr>`;
            }
            html += '</tbody></table>';
            container.innerHTML = html;
        } else if (properties.from_status || properties.to_status || properties.context) {
            let html = '<div style="display:flex; flex-direction:column; gap:12px;">';

            html += '<div style="display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:10px; background:var(--bg); border:1px solid var(--line);">';
            html += `<span style="font-size:12px; font-weight:800; color:var(--muted);">${@json(__('crm.status'))}:</span>`;
            html += `<span style="color:#dc2626; font-weight:700; text-decoration:line-through;">${escapeHtml(properties.from_status || '—')}</span>`;
            html += '<i class="bi bi-arrow-right rtl-flip" style="color:var(--muted); font-size:12px;"></i>';
            html += `<span style="color:#16a34a; font-weight:800; font-size:13.5px;">${escapeHtml(properties.to_status || '—')}</span>`;
            if (properties.to_stage) {
                html += `<span style="font-size:11.5px; color:var(--muted); margin-inline-start:auto;">(${escapeHtml(properties.to_stage)})</span>`;
            }
            html += '</div>';

            if (properties.context && Object.keys(properties.context).length > 0) {
                html += '<div style="padding:12px 14px; border-radius:10px; background:var(--bg); border:1px solid var(--line);">';
                html += `<div style="font-size:12px; font-weight:800; color:var(--dark); margin-bottom:8px;">${@json(__('crm.notes'))}:</div>`;
                for (const [cKey, cVal] of Object.entries(properties.context)) {
                    if (cVal) {
                        const valStr = typeof cVal === 'object' ? JSON.stringify(cVal) : String(cVal);
                        html += `<div style="font-size:12.5px; color:var(--dark); line-height:1.5;">${escapeHtml(valStr)}</div>`;
                    }
                }
                html += '</div>';
            }

            html += '</div>';
            container.innerHTML = html;
        } else {
            let html = '<table style="width:100%; border-collapse:collapse; font-size:12px;">';
            html += `<thead style="background:var(--bg); border-bottom:1px solid var(--line);"><tr><th style="padding:8px; text-align:start;">${i18nField}</th><th style="padding:8px; text-align:start;">${@json(__('crm.value') ?? 'Value')}</th></tr></thead><tbody>`;
            
            for (const [pKey, pVal] of Object.entries(properties)) {
                const label = fieldMap[pKey] || pKey;
                const valStr = typeof pVal === 'object' ? JSON.stringify(pVal, null, 2) : String(pVal ?? '—');
                html += `<tr style="border-bottom:1px solid var(--line);">
                    <td style="padding:8px; font-weight:700;">${label}</td>
                    <td style="padding:8px; font-family:sans-serif;">${escapeHtml(valStr)}</td>
                </tr>`;
            }
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        document.getElementById('logDetailsModal').style.display = 'flex';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function closeLogDetailsModal() {
        document.getElementById('logDetailsModal').style.display = 'none';
    }

    document.getElementById('logDetailsModal')?.addEventListener('click', function(e) {
        if (e.target.id === 'logDetailsModal') {
            closeLogDetailsModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLogDetailsModal();
        }
    });
</script>
@endpush
@endsection
