@extends('settings.layout')

@section('title', __('crm.referrals_settings') ?? 'إعدادات الإحالات')
@section('heading', __('crm.referrals_settings') ?? 'إعدادات الإحالات')
@section('subheading', 'التحكم في تشغيل نظام الإحالات وتحديد حقول النموذج المستندة إلى أسئلة المراحل')
@section('page-icon', 'bi-gift')
@section('back-url', route('v2.settings'))
@section('back-title', __('crm.settings'))

@section('content')
<style>
.referral-settings-shell { display: flex; flex-direction: column; gap: 20px; }
.ref-card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 22px; box-shadow: var(--shadow); }
.ref-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--line); flex-wrap: wrap; gap: 10px; }
.ref-card-title { margin: 0; font-size: 16px; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 8px; }
.ref-toggle-box { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: 12px; background: var(--bg); border: 1px solid var(--line); }
.ref-toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.ref-toggle-switch input { opacity: 0; width: 0; height: 0; }
.ref-slider { position: absolute; cursor: pointer; inset: 0; background-color: #cbd5e1; transition: .2s; border-radius: 26px; }
.ref-slider:before { position: absolute; content: ""; height: 20px; width: 20px; inset-inline-start: 3px; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
input:checked + .ref-slider { background-color: #059669; }
input:checked + .ref-slider:before { transform: translateX(22px); }
[dir="rtl"] input:checked + .ref-slider:before { transform: translateX(-22px); }

.ref-stage-group { border: 1px solid var(--line); border-radius: 12px; margin-bottom: 14px; overflow: hidden; background: var(--card); transition: all .15s ease; }
.ref-stage-head { background: var(--bg); padding: 12px 16px; font-weight: 800; font-size: 13.5px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); }
.ref-field-row { display: grid; grid-template-columns: 2fr 1fr 1fr; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--line); transition: background .12s ease; }
.ref-field-row:last-child { border-bottom: none; }
.ref-field-row:hover { background: rgba(248, 250, 252, 0.7); }
.ref-field-row.is-selected { background: #f0fdf4; }
.ref-field-label { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 13px; color: var(--dark); }
.ref-field-badges { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 4px; font-size: 11px; }

@media (max-width: 768px) {
    .ref-field-row { grid-template-columns: 1fr; gap: 8px; }
}
</style>

<form action="{{ route('v2.settings.referrals.update') }}" method="POST">
    @csrf

    <div class="referral-settings-shell">
        <!-- 1. MASTER TOGGLE & BASIC CONTROLS -->
        <div class="ref-card">
            <div class="ref-card-header">
                <h3 class="ref-card-title">
                    <i class="bi bi-power" style="color: #059669;"></i>
                    {{ __('crm.referral_system_status') ?? 'حالة نظام الإحالات العام' }}
                </h3>
                <span class="badge" style="background: {{ $settings->is_enabled ? '#ecfdf5; color:#065f46; border:1px solid #a7f3d0' : '#fef2f2; color:#991b1b; border:1px solid #fecaca' }}; padding: 6px 12px; font-size: 12px; font-weight: 800;">
                    <i class="bi bi-circle-fill" style="font-size: 8px; margin-inline-end: 4px;"></i>
                    {{ $settings->is_enabled ? 'مفعل ويعمل حالياً' : 'معطل ومغلق' }}
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <label class="ref-toggle-box" style="cursor: pointer;">
                    <div class="ref-toggle-switch">
                        <input type="checkbox" name="is_enabled" value="1" {{ $settings->is_enabled ? 'checked' : '' }}>
                        <span class="ref-slider"></span>
                    </div>
                    <div>
                        <b style="font-size: 13.5px; color: var(--dark); display: block;">{{ __('تفعيل ميزة إحالة صديق في كافة صفحات المشتركين') }}</b>
                        <span style="font-size: 12px; color: var(--muted); display: block; margin-top: 2px;">
                            {{ __('عند التعطيل، يختفي زر "إحالة صديق" من تفاصيل العملاء والمشتركين، ويتم حظر إرسال أي إحالات برمجياً.') }}
                        </span>
                    </div>
                </label>

                <label class="ref-toggle-box" style="cursor: pointer;">
                    <div class="ref-toggle-switch">
                        <input type="checkbox" name="allow_notes" value="1" {{ $settings->allow_notes ? 'checked' : '' }}>
                        <span class="ref-slider"></span>
                    </div>
                    <div>
                        <b style="font-size: 13.5px; color: var(--dark); display: block;">{{ __('إتاحة حقل الملاحظات النصية للإحالة (Notes)') }}</b>
                        <span style="font-size: 12px; color: var(--muted); display: block; margin-top: 2px;">
                            {{ __('إظهار مربع نص اختياري لإضافة تفاصيل إضافية عن معرفة اللاعب بالمشترك أو أي ملاحظات أخرى.') }}
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. TARGET PIPELINE & STAGE (ROUTING) -->
        <div class="ref-card">
            <div class="ref-card-header">
                <div>
                    <h3 class="ref-card-title">
                        <i class="bi bi-diagram-3" style="color: #3b82f6;"></i>
                        {{ __('crm.target_pipeline_and_stage') ?? 'المسار والمرحلة المستهدفة للعميل المُحال' }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('اختر المسار المستهدف أولاً، وستظهر قائمة المراحل التابعة له لاختيار المرحلة المحددة.') }}
                    </p>
                </div>
            </div>

            @php
                $currentTargetStage = $stages->firstWhere('id', (int) $settings->target_pipeline_stage_id);
                $currentTargetCatId = $currentTargetStage?->pipeline_stage_category_id;
            @endphp

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; align-items: end;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('المسار المستهدف (Target Pipeline)') }}
                    </label>
                    <select id="targetPipelineSelect" onchange="onTargetPipelineChange()" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: var(--dark); font-size: 13px;">
                        <option value="">-- {{ __('اختر المسار المستهدف أولاً') }} --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) $currentTargetCatId === (string) $cat->id ? 'selected' : '' }}>
                                {{ $cat->localizedName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="targetStageWrap" style="{{ $currentTargetCatId ? 'display: block;' : 'display: none;' }}">
                    <label style="display: block; font-weight: 700; margin-bottom: 6px; font-size: 13px;">
                        {{ __('المرحلة المستهدفة (Target Stage)') }}
                    </label>
                    <select name="target_pipeline_stage_id" id="targetStageSelect" style="width: 100%; height: 40px; padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: var(--dark); font-size: 13px;">
                        <option value="">-- {{ __('أول مرحلة تلقائياً في هذا المسار') }} --</option>
                        @if ($currentTargetCatId && isset($categoriesWithStages[$currentTargetCatId]))
                            @foreach ($categoriesWithStages[$currentTargetCatId] as $stg)
                                <option value="{{ $stg['id'] }}" {{ (int) $settings->target_pipeline_stage_id === (int) $stg['id'] ? 'selected' : '' }}>
                                    {{ $stg['name'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. DYNAMIC FIELDS BUILDER (CHOOSE PIPELINE -> STAGE APPEARS -> QUESTIONS) -->
        <div class="ref-card">
            <div class="ref-card-header">
                <div>
                    <h3 class="ref-card-title">
                        <i class="bi bi-ui-checks" style="color: #dc2626;"></i>
                        {{ __('crm.customize_referral_fields') ?? 'تخصيص حقول نموذج الإحالة' }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('اختر المسار أولاً، وستظهر قائمة المراحل التابعة له لاختيار المرحلة وتحديد أسئلتها وحقولها.') }}
                    </p>
                </div>
                <div id="selectedCountBadge" class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-size:12.5px; font-weight:800; padding:6px 12px;">
                    <i class="bi bi-check-circle-fill"></i>
                    <span id="selectedCountNumber">0</span> {{ __('حقل مخصص محدد') }}
                </div>
            </div>

            <!-- BASELINE PERMANENT FIELDS NOTICE -->
            <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 12.5px; color: #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-lock-fill" style="color: #64748b; font-size: 14px;"></i>
                    <b>{{ __('الحقول الأساسية الثابتة:') }}</b>
                    <span>1. {{ __('اسم العميل/اللاعب المُحال') }} (إلزامي)</span> • 
                    <span>2. {{ __('رقم الهاتف / واتساب') }} (إلزامي)</span>
                </div>
                <span class="badge" style="background: #e2e8f0; color: #475569; font-weight: 700; font-size: 11px;">مدمجة في كل إحالة</span>
            </div>

            <!-- DYNAMIC SELECTION CONTROLS: PIPELINE DROPDOWN -> STAGE DROPDOWN (APPEARS ONLY AFTER CHOOSING PIPELINE) -->
            <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; align-items: end;">
                    <div>
                        <label style="display: block; font-weight: 800; margin-bottom: 6px; font-size: 12.5px; color: var(--dark);">
                            <i class="bi bi-diagram-2" style="color: #4f46e5;"></i>
                            {{ __('1. اختر المسار (Pipeline)') }}
                        </label>
                        <select id="fieldPipelineSelect" onchange="onFieldPipelineChange()" style="width: 100%; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: var(--card); color: var(--dark);">
                            <option value="">-- {{ __('اختر المسار لعرض مراحله...') }} --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->localizedName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="fieldStageWrap" style="display: none;">
                        <label style="display: block; font-weight: 800; margin-bottom: 6px; font-size: 12.5px; color: var(--dark);">
                            <i class="bi bi-ui-radios-grid" style="color: #059669;"></i>
                            {{ __('2. اختر المرحلة التابعة للمسار (Stage)') }}
                        </label>
                        <select id="fieldStageSelect" onchange="onFieldStageChange()" style="width: 100%; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: var(--card); color: var(--dark);">
                            <option value="all">-- {{ __('جميع المراحل في هذا المسار') }} --</option>
                        </select>
                    </div>

                    <div id="fieldSearchWrap" style="display: none;">
                        <label style="display: block; font-weight: 800; margin-bottom: 6px; font-size: 12.5px; color: var(--dark);">
                            <i class="bi bi-search" style="color: #d97706;"></i>
                            {{ __('بحث سريع في أسئلة المرحلة') }}
                        </label>
                        <input type="text" id="fieldSearchInput" onkeyup="filterQuestionsList()" placeholder="🔍 ابحث بالاسم أو الـ key..."
                               style="width: 100%; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 12.5px; background: var(--card); color: var(--dark);">
                    </div>
                </div>
            </div>

            <!-- PROMPT WHEN NO PIPELINE CHOSEN -->
            <div id="noPipelineChosenPrompt" style="text-align: center; padding: 36px 20px; border: 2px dashed var(--line); border-radius: 12px; background: var(--card); color: var(--muted); margin-bottom: 16px;">
                <i class="bi bi-diagram-2" style="font-size: 32px; color: #94a3b8; display: block; margin-bottom: 8px;"></i>
                <b style="font-size: 14px; color: var(--dark); display: block; margin-bottom: 4px;">{{ __('اختر مسار العمل أولاً لعرض المراحل وأسئلتها') }}</b>
                <span style="font-size: 12px;">{{ __('حدد المسار من القائمة أعلاه (مثل المبيعات والتواصل أو الاشتراكات) لتخصيص الحقول التابعة له.') }}</span>
            </div>

            <!-- QUESTIONS CONTAINERS GROUPED BY STAGE -->
            <div id="questionsContainer">
                @php $hasAnyFields = false; @endphp
                @foreach ($categories as $cat)
                    @foreach ($cat->stages as $stage)
                        @if ($stage->activeFields->isNotEmpty())
                            @php $hasAnyFields = true; @endphp
                            <div class="ref-stage-group question-stage-block"
                                 id="stage_block_{{ $stage->id }}"
                                 data-pipeline-id="{{ $cat->id }}"
                                 data-stage-id="{{ $stage->id }}"
                                 data-stage-name="{{ $stage->localizedName() }}"
                                 style="display: none;">
                                <div class="ref-stage-head">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-folder2-open" style="color: {{ $cat->color ?: '#dc2626' }}"></i>
                                        <strong>{{ $cat->localizedName() }}</strong> » {{ $stage->localizedName() }}
                                    </span>
                                    <span class="badge" style="background: var(--card); border: 1px solid var(--line); font-size: 11px;">
                                        {{ $stage->activeFields->count() }} {{ __('أسئلة / حقول') }}
                                    </span>
                                </div>

                                <div>
                                    @foreach ($stage->activeFields as $sf)
                                        @php
                                            $isConfigured = isset($configuredFields[$sf->id]);
                                            $isSelected = $isConfigured && $configuredFields[$sf->id]->is_active;
                                            $isRequired = $isConfigured ? $configuredFields[$sf->id]->is_required : $sf->is_required;
                                            $pos = $isConfigured ? $configuredFields[$sf->id]->position : $sf->position;
                                        @endphp
                                        <div class="ref-field-row question-item-row {{ $isSelected ? 'is-selected' : '' }}"
                                             data-field-name="{{ $sf->localizedLabel() }} {{ $sf->key }}"
                                             data-field-id="{{ $sf->id }}">
                                            <div>
                                                <label class="ref-field-label" style="cursor: pointer;">
                                                    <input type="checkbox" name="selected_fields[]" value="{{ $sf->id }}" {{ $isSelected ? 'checked' : '' }}
                                                           onchange="onFieldCheckboxChange(this)"
                                                           style="width: 17px; height: 17px; accent-color: #059669; cursor: pointer;">
                                                    <span>{{ $sf->localizedLabel() }}</span>
                                                </label>
                                                <div class="ref-field-badges">
                                                    <span style="font-family: monospace; color: var(--muted); background: var(--bg); padding: 1px 6px; border-radius: 4px; border: 1px solid var(--line);">
                                                        key: {{ $sf->key }}
                                                    </span>
                                                    <span class="badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                                        {{ \App\Models\PipelineStageField::TYPES[$sf->type] ?? $sf->type }}
                                                    </span>
                                                    @if ($sf->binding_type === 'canonical')
                                                        <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                                            {{ __('مرتبط ببيانات العميل') }}: {{ $sf->binding_target }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div>
                                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; cursor: pointer; color: var(--dark);">
                                                    <input type="checkbox" name="required_fields[]" value="{{ $sf->id }}" {{ $isRequired ? 'checked' : '' }}
                                                           style="width: 15px; height: 15px; accent-color: #dc2626; cursor: pointer;">
                                                    <span style="color: #dc2626;">{{ __('حقل إلزامي') }}</span>
                                                </label>
                                            </div>

                                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                                <span style="font-size: 11px; color: var(--muted); font-weight: 700;">{{ __('الترتيب') }}:</span>
                                                <input type="number" name="field_positions[{{ $sf->id }}]" value="{{ $pos }}" min="1" max="999"
                                                       style="width: 60px; height: 32px; text-align: center; border: 1px solid var(--line); border-radius: 6px; font-size: 12px; background: var(--bg); color: var(--dark);">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach

                @if (! $hasAnyFields)
                    <div style="text-align: center; padding: 30px; color: var(--muted); font-size: 13px;">
                        <i class="bi bi-info-circle" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                        {{ __('لا توجد أسئلة مراحل مخصصة حالياً. يمكنك إضافة أسئلة من شاشة المسارات والمراحل.') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. STAGE QUESTION AUTO-TRIGGERS -->
        <div class="ref-card">
            <div class="ref-card-header">
                <div>
                    <h3 class="ref-card-title">
                        <i class="bi bi-lightning-charge" style="color: #f59e0b;"></i>
                        {{ __('ربط الإحالات بأسئلة المراحل (Trigger Questions)') }}
                    </h3>
                    <p style="margin: 4px 0 0; color: var(--muted); font-size: 12px;">
                        {{ __('يمكنك تحديد أسئلة مراحل محددة (مثل أسئلة NPS أو طلب الترشيح) لتحفيز واقتراح تسجيل الإحالة أثناء ملء نموذج متابعة المرحلة.') }}
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @php
                    $triggerCandidates = $allStageFields->filter(function ($f) {
                        return str_contains($f->key, 'referral') || str_contains($f->key, 'nps') || str_contains($f->key, 'recommend');
                    });
                    $currentTriggers = is_array($settings->trigger_stage_field_ids) ? $settings->trigger_stage_field_ids : [];
                @endphp

                @if ($triggerCandidates->isNotEmpty())
                    @foreach ($triggerCandidates as $tc)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); cursor: pointer;">
                            <input type="checkbox" name="trigger_stage_field_ids[]" value="{{ $tc->id }}" {{ in_array($tc->id, $currentTriggers, true) ? 'checked' : '' }}
                                   style="width: 17px; height: 17px; accent-color: #f59e0b; cursor: pointer;">
                            <div>
                                <b style="font-size: 13px; color: var(--dark); display: block;">{{ $tc->localizedLabel() }}</b>
                                <span style="font-size: 11px; color: var(--muted);">مرحلة: {{ $tc->stage?->localizedName() ?? '—' }} ({{ $tc->key }})</span>
                            </div>
                        </label>
                    @endforeach
                @else
                    <p style="color: var(--muted); font-size: 12px; margin: 0;">
                        {{ __('لم يتم العثور تلقائياً على أسئلة تحتوي على كلمات إحالة أو NPS. يمكنك استخدام الحقول الديناميكية أعلاه في أي وقت.') }}
                    </p>
                @endif
            </div>
        </div>

        <!-- 5. SAVE SUBMIT BAR -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 16px 0; border-top: 1px solid var(--line);">
            <a href="{{ route('v2.settings') }}" class="btn soft" style="min-height: 42px; padding: 0 20px;">
                {{ __('إلغاء') }}
            </a>
            <button type="submit" class="btn primary" style="min-height: 42px; padding: 0 26px; background: #059669; border-color: #059669; font-weight: 800; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-check2-circle" style="font-size: 18px;"></i>
                {{ __('حفظ إعدادات الإحالات') }}
            </button>
        </div>
    </div>
</form>

<script>
const pipelineStagesMap = @json($categoriesWithStages ?? []);
const initialTargetStageId = {{ (int) ($settings->target_pipeline_stage_id ?? 0) }};

// --- 1. Target Routing (Pipeline -> Stage dropdown appears) ---
function onTargetPipelineChange(presetStageId = null) {
    const pipelineSelect = document.getElementById('targetPipelineSelect');
    const stageWrap = document.getElementById('targetStageWrap');
    const stageSelect = document.getElementById('targetStageSelect');
    if (!pipelineSelect || !stageWrap || !stageSelect) return;

    const pipelineId = pipelineSelect.value ? String(pipelineSelect.value) : '';

    if (!pipelineId || !pipelineStagesMap[pipelineId] || pipelineStagesMap[pipelineId].length === 0) {
        stageWrap.style.display = 'none';
        stageSelect.innerHTML = '<option value="">-- أول مرحلة تلقائياً في هذا المسار --</option>';
        stageSelect.value = '';
        stageSelect.dispatchEvent(new CustomEvent('crm-dropdown:update'));
        return;
    }

    // Pipeline chosen: make stage dropdown appear!
    stageWrap.style.display = 'block';
    stageSelect.innerHTML = '<option value="">-- أول مرحلة تلقائياً في هذا المسار --</option>';

    const stages = pipelineStagesMap[pipelineId] || [];
    stages.forEach(s => {
        const opt = document.createElement('option');
        opt.value = String(s.id);
        opt.textContent = s.name;
        if (presetStageId && String(s.id) === String(presetStageId)) {
            opt.selected = true;
        }
        stageSelect.appendChild(opt);
    });

    if (presetStageId && String(presetStageId) !== '') {
        stageSelect.value = String(presetStageId);
    } else {
        stageSelect.value = '';
    }

    // Crucial: notify crm-dropdown.js to rebuild the dropdown UI list!
    stageSelect.dispatchEvent(new CustomEvent('crm-dropdown:update'));
}

// --- 2. Referral Fields Selector (Pipeline -> Stage dropdown appears) ---
function onFieldPipelineChange() {
    const pipelineSelect = document.getElementById('fieldPipelineSelect');
    const stageWrap = document.getElementById('fieldStageWrap');
    const searchWrap = document.getElementById('fieldSearchWrap');
    const stageSelect = document.getElementById('fieldStageSelect');
    const emptyPrompt = document.getElementById('noPipelineChosenPrompt');
    if (!pipelineSelect || !stageWrap || !stageSelect) return;

    const pipelineId = pipelineSelect.value ? String(pipelineSelect.value) : '';

    if (!pipelineId || !pipelineStagesMap[pipelineId] || pipelineStagesMap[pipelineId].length === 0) {
        stageWrap.style.display = 'none';
        if (searchWrap) searchWrap.style.display = 'none';
        if (emptyPrompt) emptyPrompt.style.display = 'block';

        stageSelect.innerHTML = '<option value="all">-- جميع المراحل في هذا المسار --</option>';
        stageSelect.value = 'all';
        stageSelect.dispatchEvent(new CustomEvent('crm-dropdown:update'));

        // Hide all stage question blocks
        document.querySelectorAll('.question-stage-block').forEach(b => b.style.display = 'none');
        return;
    }

    // Pipeline chosen: make stage dropdown and search wrap appear!
    stageWrap.style.display = 'block';
    if (searchWrap) searchWrap.style.display = 'block';
    if (emptyPrompt) emptyPrompt.style.display = 'none';

    stageSelect.innerHTML = '<option value="all">-- جميع المراحل في هذا المسار --</option>';

    const stages = pipelineStagesMap[pipelineId] || [];
    stages.forEach(s => {
        const opt = document.createElement('option');
        opt.value = String(s.id);
        opt.textContent = s.name + (s.fields_count > 0 ? ` (${s.fields_count} سؤال)` : ' (بدون أسئلة)');
        stageSelect.appendChild(opt);
    });

    stageSelect.value = 'all';

    // Crucial: notify crm-dropdown.js to rebuild the dropdown UI list!
    stageSelect.dispatchEvent(new CustomEvent('crm-dropdown:update'));

    onFieldStageChange();
}

function onFieldStageChange() {
    const pipelineSelect = document.getElementById('fieldPipelineSelect');
    const stageSelect = document.getElementById('fieldStageSelect');

    const pipelineId = pipelineSelect ? String(pipelineSelect.value) : '';
    const stageId = stageSelect ? String(stageSelect.value) : 'all';

    const stageBlocks = document.querySelectorAll('.question-stage-block');

    stageBlocks.forEach(block => {
        const bPipeId = String(block.getAttribute('data-pipeline-id'));
        const bStageId = String(block.getAttribute('data-stage-id'));

        let show = true;
        if (!pipelineId || bPipeId !== pipelineId) {
            show = false;
        } else if (stageId && stageId !== 'all' && bStageId !== stageId) {
            show = false;
        }

        block.style.display = show ? 'block' : 'none';
    });

    filterQuestionsList();
}

function filterQuestionsList() {
    const query = (document.getElementById('fieldSearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.question-item-row');

    rows.forEach(row => {
        const text = (row.getAttribute('data-field-name') || '').toLowerCase();
        if (!query || text.includes(query)) {
            row.style.display = 'grid';
        } else {
            row.style.display = 'none';
        }
    });
}

function onFieldCheckboxChange(checkbox) {
    const row = checkbox.closest('.question-item-row');
    if (checkbox.checked) {
        row.classList.add('is-selected');
    } else {
        row.classList.remove('is-selected');
    }
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('input[name="selected_fields[]"]:checked').length;
    const badge = document.getElementById('selectedCountNumber');
    if (badge) {
        badge.textContent = count;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const targetPipelineSelect = document.getElementById('targetPipelineSelect');
    if (targetPipelineSelect) {
        targetPipelineSelect.addEventListener('change', () => onTargetPipelineChange());
        if (targetPipelineSelect.value) {
            onTargetPipelineChange(initialTargetStageId);
        }
    }

    const fieldPipelineSelect = document.getElementById('fieldPipelineSelect');
    if (fieldPipelineSelect) {
        fieldPipelineSelect.addEventListener('change', () => onFieldPipelineChange());
    }

    const fieldStageSelect = document.getElementById('fieldStageSelect');
    if (fieldStageSelect) {
        fieldStageSelect.addEventListener('change', () => onFieldStageChange());
    }

    updateSelectedCount();
});
</script>
@endsection
