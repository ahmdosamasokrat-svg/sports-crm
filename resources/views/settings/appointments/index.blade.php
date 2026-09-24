@extends('settings.layout')

@section('title', __('crm.appointments_settings'))
@section('heading', __('crm.appointments_settings'))
@section('subheading', __('crm.appointments_settings_desc'))
@section('page-icon', 'bi-calendar2-check')
@section('back-url', route('v2.settings'))
@section('back-title', __('crm.settings'))

@section('content')
<style>
.apt-settings-shell { display: flex; flex-direction: column; gap: 20px; }
.apt-card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow); }
.apt-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--line); flex-wrap: wrap; gap: 10px; }
.apt-card-title { margin: 0; font-size: 16px; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 8px; }
.apt-toggle-box { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: 12px; background: var(--bg); border: 1px solid var(--line); }
.apt-toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.apt-toggle-switch input { opacity: 0; width: 0; height: 0; }
.apt-slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .2s; border-radius: 26px; }
.apt-slider:before { position: absolute; content: ""; height: 20px; width: 20px; inset-inline-start: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
input:checked + .apt-slider { background-color: #0284c7; }
input:checked + .apt-slider:before { transform: translateX(22px); }
[dir="rtl"] input:checked + .apt-slider:before { transform: translateX(-22px); }

.apt-stage-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); margin-bottom: 8px; transition: background .12s ease; }
.apt-stage-item:hover { background: #f0f9ff; }
.apt-stage-item.is-selected { background: #f0f9ff; border-color: #bae6fd; }
</style>

<form action="{{ route('v2.settings.appointments.update') }}" method="POST">
    @csrf

    <div class="apt-settings-shell">
        <!-- 1. MASTER ON/OFF TOGGLE -->
        <div class="apt-card">
            <div class="apt-card-header">
                <h3 class="apt-card-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    {{ __('crm.appointments_module_status') }}
                </h3>
                <span class="badge" style="background: {{ $setting->is_enabled ? '#f0f9ff; color:#0369a1; border:1px solid #bae6fd' : '#fef2f2; color:#991b1b; border:1px solid #fecaca' }}; padding: 6px 12px; font-size: 12px; font-weight: 800;">
                    {{ $setting->is_enabled ? __('crm.active_running') : __('crm.disabled_closed') }}
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <label class="apt-toggle-box" style="cursor: pointer;">
                    <div class="apt-toggle-switch">
                        <input type="checkbox" name="is_enabled" value="1" {{ $setting->is_enabled ? 'checked' : '' }}>
                        <span class="apt-slider"></span>
                    </div>
                    <div>
                        <b style="font-size: 13.5px; color: var(--dark); display: block;">{{ __('crm.enable_appointments_system') }}</b>
                        <span style="font-size: 12px; color: var(--muted); display: block; margin-top: 2px;">
                            {{ __('crm.enable_appointments_desc') }}
                        </span>
                    </div>
                </label>

                <label class="apt-toggle-box" style="cursor: pointer;">
                    <div class="apt-toggle-switch">
                        <input type="checkbox" name="allow_quick_actions" value="1" {{ $setting->allow_quick_actions ? 'checked' : '' }}>
                        <span class="apt-slider"></span>
                    </div>
                    <div>
                        <b style="font-size: 13.5px; color: var(--dark); display: block;">{{ __('crm.enable_quick_actions') }}</b>
                        <span style="font-size: 12px; color: var(--muted); display: block; margin-top: 2px;">
                            {{ __('crm.enable_quick_actions_desc') }}
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. STAGES MAPPING -->
        <div class="apt-card">
            <div class="apt-card-header">
                <div>
                    <h3 class="apt-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        {{ __('crm.mapped_appointment_stages') }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('crm.mapped_appointment_stages_desc') }}
                    </p>
                </div>
            </div>

            @php
                $configuredStages = is_array($setting->stage_ids) ? array_map('intval', $setting->stage_ids) : [];
            @endphp

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach ($categories as $cat)
                    <div style="border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: var(--bg);">
                        <div style="padding: 10px 14px; background: rgba(0,0,0,0.02); font-weight: 800; font-size: 13px; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between;">
                            <span>{{ $cat->localizedName() }}</span>
                            <span class="badge" style="font-size: 11px;">{{ __('crm.stages_count', ['count' => $cat->stages->count()]) }}</span>
                        </div>
                        <div style="padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;">
                            @foreach ($cat->stages as $stage)
                                @php
                                    $isSelected = in_array((int) $stage->id, $configuredStages, true);
                                @endphp
                                <label class="apt-stage-item {{ $isSelected ? 'is-selected' : '' }}" style="cursor: pointer; margin: 0;">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="stage_ids[]" value="{{ $stage->id }}" {{ $isSelected ? 'checked' : '' }}
                                               style="width: 17px; height: 17px; accent-color: #0284c7; cursor: pointer;">
                                        <b style="font-size: 13px; color: var(--dark);">{{ $stage->localizedName() }}</b>
                                    </span>
                                    <span class="badge" style="font-size: 11px; background: var(--card); border: 1px solid var(--line);">
                                        {{ __('crm.fields_count', ['count' => $stage->activeFields->count()]) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. FIELD MAPPINGS -->
        <div class="apt-card">
            <div class="apt-card-header">
                <div>
                    <h3 class="apt-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        {{ __('crm.field_key_bindings') }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('crm.field_key_bindings_desc') }}
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('crm.date_key_label') }} <span style="color:var(--red);">*</span>
                    </label>
                    <input type="text" name="date_field_key" value="{{ old('date_field_key', $setting->date_field_key ?: 'trial_date') }}" required
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; font-family: monospace; background: var(--bg); color: var(--dark);">
                    <small style="color: var(--muted); font-size: 11px; margin-top: 3px; display: block;">الافتراضي: trial_date (أو موعد المتابعة)</small>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('crm.time_key_label') }}
                    </label>
                    <input type="text" name="time_field_key" value="{{ old('time_field_key', $setting->time_field_key ?: 'trial_time') }}"
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; font-family: monospace; background: var(--bg); color: var(--dark);">
                    <small style="color: var(--muted); font-size: 11px; margin-top: 3px; display: block;">الافتراضي: trial_time</small>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('crm.coach_key_label') }}
                    </label>
                    <input type="text" name="coach_field_key" value="{{ old('coach_field_key', $setting->coach_field_key ?: 'coach') }}"
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; font-family: monospace; background: var(--bg); color: var(--dark);">
                    <small style="color: var(--muted); font-size: 11px; margin-top: 3px; display: block;">الافتراضي: coach</small>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('crm.status_key_label') }}
                    </label>
                    <input type="text" name="status_field_key" value="{{ old('status_field_key', $setting->status_field_key ?: 'trial_status') }}"
                           style="width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; font-family: monospace; background: var(--bg); color: var(--dark);">
                    <small style="color: var(--muted); font-size: 11px; margin-top: 3px; display: block;">الافتراضي: trial_status</small>
                </div>
            </div>
        </div>

        <!-- 4. SUBMIT ACTION BAR -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 16px 0; border-top: 1px solid var(--line);">
            <a href="{{ route('v2.settings') }}" class="btn soft" style="min-height: 42px; padding: 0 20px;">
                {{ __('crm.cancel') }}
            </a>
            <button type="submit" class="btn primary" style="min-height: 42px; padding: 0 26px; background: #0284c7; border-color: #0284c7; font-weight: 800; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="background:transparent; border:none;" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                {{ __('crm.save_appointment_settings') }}
            </button>
        </div>
    </div>
</form>
@endsection
