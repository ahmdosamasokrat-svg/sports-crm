@extends('settings.layout')

@section('title', __('crm.lead_profile_settings') ?? 'تنسيق ملف العميل')
@section('heading', __('crm.lead_profile_settings') ?? 'تنسيق ملف العميل')
@section('subheading', __('crm.lead_profile_settings_desc') ?? 'تخصيص وترتيب تبويبات صفحة العميل واختيار طريقة العرض والتبويب الافتراضي')
@section('page-icon', 'bi-layout-text-window-reverse')
@section('back-url', route('v2.settings'))
@section('back-title', __('crm.settings'))

@section('content')
<style>
.profile-settings-shell { display: flex; flex-direction: column; gap: 20px; }
.profile-card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow); }
.profile-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--line); flex-wrap: wrap; gap: 10px; }
.profile-card-title { margin: 0; font-size: 16px; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 8px; }
.profile-toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.profile-toggle-switch input { opacity: 0; width: 0; height: 0; }
.profile-slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .2s; border-radius: 24px; }
.profile-slider:before { position: absolute; content: ""; height: 18px; width: 18px; inset-inline-start: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
input:checked + .profile-slider { background-color: var(--red); }
input:checked + .profile-slider:before { transform: translateX(20px); }
[dir="rtl"] input:checked + .profile-slider:before { transform: translateX(-20px); }

.layout-picker-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-top: 8px; }
.layout-picker-card {
    border: 2px solid var(--line);
    border-radius: 12px;
    padding: 16px 18px;
    background: var(--bg);
    cursor: pointer;
    transition: all .15s ease;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.layout-picker-card:hover { border-color: color-mix(in srgb, var(--red) 50%, var(--line)); }
.layout-picker-card.is-selected {
    border-color: var(--red);
    background: color-mix(in srgb, var(--red) 4%, var(--card));
}
.layout-picker-card input[type="radio"] { margin-top: 3px; accent-color: var(--red); width: 18px; height: 18px; }

.tab-config-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.tab-config-table th { background: var(--bg); padding: 12px 14px; font-size: 12px; font-weight: 800; color: var(--muted); border-bottom: 1px solid var(--line); }
.tab-config-table td { padding: 12px 14px; border-bottom: 1px solid var(--line); vertical-align: middle; }
.tab-config-table tr:last-child td { border-bottom: none; }
.tab-config-table tr.is-disabled { opacity: 0.6; }
.tab-icon-badge {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--bg);
    border: 1px solid var(--line);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: var(--red);
    flex-shrink: 0;
}
</style>

<form action="{{ route('v2.settings.lead_profile.update') }}" method="POST">
    @csrf

    <div class="profile-settings-shell">
        <!-- 1. GENERAL LAYOUT & DEFAULT TAB -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h3 class="profile-card-title">
                        <i class="bi bi-grid-1x2" style="color:var(--red);"></i>
                        {{ __('crm.lead_profile_layout_mode') ?? 'نمط عرض ملف العميل' }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('crm.lead_profile_layout_desc') ?? 'اختر شكل التبويبات وطريقة توزيع البيانات الأساسية في صفحة العميل' }}
                    </p>
                </div>
            </div>

            <div class="layout-picker-grid">
                <label class="layout-picker-card {{ $setting->layout_mode === 'hybrid' ? 'is-selected' : '' }}">
                    <input type="radio" name="layout_mode" value="hybrid" {{ $setting->layout_mode === 'hybrid' ? 'checked' : '' }} onchange="updateLayoutHighlight(this)">
                    <div>
                        <strong style="display:block; font-size:14px; color:var(--dark);">
                            <i class="bi bi-layout-sidebar-inset"></i>
                            {{ __('crm.layout_hybrid_title') ?? 'الوضع المختلط (بطاقة عميل جانبية + تبويبات)' }}
                        </strong>
                        <span style="display:block; font-size:12px; color:var(--muted); margin-top:4px; line-height:1.5;">
                            {{ __('crm.layout_hybrid_desc') ?? 'تثبيت بيانات العميل الرئيسية وأرقام التواصل في بطاقة جانبية دائمة، وعرض باقي الأقسام داخل تبويبات متسعة.' }}
                        </span>
                    </div>
                </label>

                <label class="layout-picker-card {{ $setting->layout_mode === 'full_width' ? 'is-selected' : '' }}">
                    <input type="radio" name="layout_mode" value="full_width" {{ $setting->layout_mode === 'full_width' ? 'checked' : '' }} onchange="updateLayoutHighlight(this)">
                    <div>
                        <strong style="display:block; font-size:14px; color:var(--dark);">
                            <i class="bi bi-window-fullscreen"></i>
                            {{ __('crm.layout_full_width_title') ?? 'التبويبات الكاملة (عرض كامل للشاشة)' }}
                        </strong>
                        <span style="display:block; font-size:12px; color:var(--muted); margin-top:4px; line-height:1.5;">
                            {{ __('crm.layout_full_width_desc') ?? 'عرض شريط التبويبات بالكامل أسفل الرأس، وتحويل جميع الأقسام بما فيها بيانات العميل إلى تبويبات مستقلة.' }}
                        </span>
                    </div>
                </label>
            </div>

            <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--line); display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div style="flex: 1 1 240px;">
                    <label for="default_tab_select" style="font-weight: 800; font-size: 13px; color: var(--dark); margin-bottom: 4px;">
                        <i class="bi bi-star"></i> {{ __('crm.default_active_tab') ?? 'التبويب الافتراضي عند فتح العميل' }}
                    </label>
                    <span style="font-size: 11.5px; color: var(--muted); display: block;">
                        {{ __('crm.default_active_tab_desc') ?? 'التبويب الذي يفتح تلقائياً عند الدخول إلى ملف أي عميل' }}
                    </span>
                </div>
                <div style="flex: 1 1 240px; max-width: 360px;">
                    <select id="default_tab_select" name="default_tab" style="font-weight: 700;">
                        @foreach($tabs as $tab)
                            <option value="{{ $tab['key'] }}" {{ $setting->default_tab === $tab['key'] ? 'selected' : '' }}>
                                {{ $tab['label'] }} ({{ $tab['key'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. TAB MANAGEMENT & REORDERING -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h3 class="profile-card-title">
                        <i class="bi bi-sliders" style="color:var(--red);"></i>
                        {{ __('crm.lead_profile_tabs_config') ?? 'إدارة وترتيب تبويبات ملف العميل' }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('crm.lead_profile_tabs_desc') ?? 'يمكنك تفعيل أو إخفاء أي تبويب، وتحديد الترتيب (1 للأول)، وتخصيص المسمى بالعربية والإنجليزية' }}
                    </p>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="tab-config-table">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">{{ __('crm.active') }}</th>
                            <th style="width: 80px; text-align: center;">{{ __('crm.order') ?? 'الترتيب' }}</th>
                            <th style="min-width: 160px;">{{ __('crm.tab_identifier') ?? 'التبويب' }}</th>
                            <th style="min-width: 220px;">{{ __('crm.label_ar') ?? 'الاسم في الواجهة (عربي)' }}</th>
                            <th style="min-width: 220px;">{{ __('crm.label_en') ?? 'الاسم في الواجهة (English)' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tabs as $index => $tab)
                            <tr id="tab-row-{{ $tab['key'] }}" class="{{ !$tab['is_enabled'] ? 'is-disabled' : '' }}">
                                <td style="text-align: center;">
                                    <input type="hidden" name="tabs[{{ $index }}][key]" value="{{ $tab['key'] }}">
                                    <label class="profile-toggle-switch" style="display: inline-block; cursor: pointer;">
                                        <input
                                            type="checkbox"
                                            name="tabs[{{ $index }}][is_enabled]"
                                            value="1"
                                            {{ $tab['is_enabled'] ? 'checked' : '' }}
                                            onchange="toggleTabRow('{{ $tab['key'] }}', this.checked)"
                                        >
                                        <span class="profile-slider"></span>
                                    </label>
                                </td>

                                <td style="text-align: center;">
                                    <input
                                        type="number"
                                        name="tabs[{{ $index }}][position]"
                                        value="{{ $tab['position'] }}"
                                        min="1"
                                        max="50"
                                        style="width: 60px; text-align: center; font-weight: 800; min-height: 38px; padding: 6px 4px;"
                                    >
                                </td>

                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="tab-icon-badge">
                                            <i class="bi {{ $tab['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <strong style="display: block; font-size: 13.5px; color: var(--dark);">
                                                {{ $tab['label'] }}
                                            </strong>
                                            <span style="font-size: 11px; color: var(--muted); font-family: monospace;">
                                                #{{ $tab['key'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="tabs[{{ $index }}][label_ar]"
                                        value="{{ $tab['label_ar'] }}"
                                        placeholder="{{ $tab['label_ar'] }}"
                                        style="min-height: 38px;"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="tabs[{{ $index }}][label_en]"
                                        value="{{ $tab['label_en'] }}"
                                        placeholder="{{ $tab['label_en'] }}"
                                        style="min-height: 38px;"
                                        dir="ltr"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- 3. LEAD FILTERS PANEL CONFIGURATION -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h3 class="profile-card-title">
                        <i class="bi bi-funnel" style="color:var(--red);"></i>
                        {{ __('crm.lead_filters_panel_settings') ?? 'إعدادات وترتيب فلاتر العملاء (Filter Panel)' }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('crm.lead_filters_panel_desc') ?? 'تخصيص ظهور وترتيب فلاتر شريط البحث، وتحديد الفلاتر التي تدعم الاختيار المتعدد (Multiselect)' }}
                    </p>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="tab-config-table">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">{{ __('crm.status') ?? 'التفعيل' }}</th>
                            <th style="width: 80px; text-align: center;">{{ __('crm.order') ?? 'الترتيب' }}</th>
                            <th style="width: 100px; text-align: center;">{{ __('crm.multiselect') ?? 'متعدد (Multi)' }}</th>
                            <th style="width: 200px;">{{ __('crm.filter_key') ?? 'رمز الفلتر' }}</th>
                            <th>{{ __('crm.label_ar') ?? 'الاسم بالعربية' }}</th>
                            <th>{{ __('crm.label_en') ?? 'الاسم بالإنجليزية' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filters as $fIndex => $filter)
                            <tr id="filter-row-{{ $filter['key'] }}" class="{{ $filter['is_enabled'] ? '' : 'is-disabled' }}">
                                <td style="text-align: center;">
                                    <label class="profile-toggle-switch" style="display:inline-block;">
                                        <input type="hidden" name="filters[{{ $fIndex }}][key]" value="{{ $filter['key'] }}">
                                        <input
                                            type="checkbox"
                                            name="filters[{{ $fIndex }}][is_enabled]"
                                            value="1"
                                            {{ $filter['is_enabled'] ? 'checked' : '' }}
                                            onchange="toggleFilterRow('{{ $filter['key'] }}', this.checked)"
                                        >
                                        <span class="profile-slider"></span>
                                    </label>
                                </td>
                                <td style="text-align: center;">
                                    <input
                                        type="number"
                                        name="filters[{{ $fIndex }}][position]"
                                        value="{{ $filter['position'] }}"
                                        min="1"
                                        max="50"
                                        style="width: 60px; text-align: center; min-height: 38px;"
                                    >
                                </td>
                                <td style="text-align: center;">
                                    @if(in_array($filter['key'], ['status', 'source', 'employee'], true))
                                        <label class="profile-toggle-switch" style="display:inline-block;" title="{{ __('crm.enable_multiselect') ?? 'تفعيل الاختيار المتعدد' }}">
                                            <input
                                                type="checkbox"
                                                name="filters[{{ $fIndex }}][is_multiselect]"
                                                value="1"
                                                {{ $filter['is_multiselect'] ? 'checked' : '' }}
                                            >
                                            <span class="profile-slider"></span>
                                        </label>
                                    @else
                                        <span style="color:var(--muted); font-size:11px;">—</span>
                                        <input type="hidden" name="filters[{{ $fIndex }}][is_multiselect]" value="0">
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="tab-icon-badge">
                                            <i class="bi {{ $filter['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <strong style="display:block; font-size: 13px; color: var(--dark);">
                                                {{ $filter['label'] }}
                                            </strong>
                                            <code style="font-size: 11px; color: var(--muted); background: var(--bg); padding: 2px 6px; border-radius: 4px;">
                                                {{ $filter['key'] }}
                                            </code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="filters[{{ $fIndex }}][label_ar]"
                                        value="{{ $filter['label_ar'] }}"
                                        placeholder="{{ $filter['label_ar'] }}"
                                        style="min-height: 38px;"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="filters[{{ $fIndex }}][label_en]"
                                        value="{{ $filter['label_en'] }}"
                                        placeholder="{{ $filter['label_en'] }}"
                                        style="min-height: 38px;"
                                        dir="ltr"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 24px; display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('v2.settings') }}" class="btn soft">
                    {{ __('crm.cancel') }}
                </a>
                <button type="submit" class="btn primary" style="min-width: 140px;">
                    <i class="bi bi-check-lg"></i> {{ __('crm.save_settings') ?? 'حفظ الإعدادات' }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function updateLayoutHighlight(radio) {
    document.querySelectorAll('.layout-picker-card').forEach(card => card.classList.remove('is-selected'));
    if (radio.checked) {
        radio.closest('.layout-picker-card').classList.add('is-selected');
    }
}

function toggleTabRow(key, isChecked) {
    const row = document.getElementById('tab-row-' + key);
    if (row) {
        if (isChecked) {
            row.classList.remove('is-disabled');
        } else {
            row.classList.add('is-disabled');
        }
    }
}

function toggleFilterRow(key, isChecked) {
    const row = document.getElementById('filter-row-' + key);
    if (row) {
        if (isChecked) {
            row.classList.remove('is-disabled');
        } else {
            row.classList.add('is-disabled');
        }
    }
}
</script>
@endsection
