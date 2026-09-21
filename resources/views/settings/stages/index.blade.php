@extends('settings.layout')

@section('title', __('crm.stages_settings_title'))
@section('heading', __('crm.stages_settings_heading'))
@section('subheading', __('crm.stages_settings_subheading'))
@section('page-icon', 'bi-diagram-3')

@section('top-actions')
    <div style="display:flex; align-items:center; gap:8px;">
        <button type="button" class="btn soft" onclick="openAddCategoryModal()" style="border-color:#cbd5e1; background:#fff;">
            <i class="bi bi-collection-fill" style="color:#0284c7;"></i> {{ __('crm.add_stage_category') ?: 'إنشاء مسار جديد' }}
        </button>
        <button type="button" class="btn primary" onclick="openAddStageModal()">
            <i class="bi bi-plus-lg"></i> {{ __('crm.add_additional_stage') ?: 'إضافة مرحلة' }}
        </button>
    </div>
@endsection

@section('content')
<section class="grid stats-grid" style="margin-bottom: 20px;">
    <article class="stat-card">
        <span>{{ __('crm.total_current_stages') }}</span>
        <b>{{ $totalStagesCount }}</b>
    </article>
    <article class="stat-card">
        <span>{{ __('crm.primary_stages_count') }}</span>
        <b>{{ $primaryStagesCount }}</b>
    </article>
    <article class="stat-card">
        <span>{{ __('crm.custom_stages_count') }}</span>
        <b>{{ $customStagesCount }}</b>
    </article>
</section>

<!-- STAGES GROUPED BY PIPELINE CATEGORY -->
<div style="display:flex; flex-direction:column; gap:24px;">
    @foreach ($groupedStages as $group)
        @php
            $cat = $group['category'];
            $catStages = $group['stages'];
            $catColor = $cat ? ($cat->color ?: '#64748b') : '#94a3b8';
        @endphp
        <section class="panel" style="border-top: 4px solid {{ $catColor }};">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:rgba(248, 250, 252, 0.5); padding:16px 20px; border-bottom:1px solid var(--line);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:{{ $catColor }}1a; color:{{ $catColor }}; font-size:18px; border:1px solid {{ $catColor }}33;">
                        <i class="bi {{ $cat ? ($cat->icon ?: 'bi-diagram-3') : 'bi-folder' }}"></i>
                    </span>
                    <div>
                        <h2 style="margin:0; font-size:17px; font-weight:800; display:flex; align-items:center; gap:8px;">
                            {{ $cat ? $cat->name_ar : 'مراحل غير مصنفة تحت مسار' }}
                            @if ($cat && $cat->name_en)
                                <small style="color:var(--muted); font-size:13px; font-weight:600;">({{ $cat->name_en }})</small>
                            @endif
                            <span class="badge" style="background:#fff; color:var(--dark); border:1px solid var(--line); font-size:11px; padding:2px 8px;">
                                {{ $catStages->count() }} مراحل
                            </span>
                        </h2>
                        @if ($cat && $cat->description_ar)
                            <p style="margin:4px 0 0; font-size:12px; color:var(--muted);">{{ $cat->description_ar }}</p>
                        @endif
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    @if ($cat)
                        <button type="button" class="btn small soft" style="font-size:12px; padding:4px 10px; color:#0284c7; border-color:#bae6fd; background:#f0f9ff;" onclick="openAddStageModal({{ $cat->id }})" title="إضافة مرحلة لهذا المسار">
                            <i class="bi bi-plus-lg"></i> إضافة مرحلة
                        </button>
                        <button type="button" class="btn small soft" style="font-size:12px; padding:4px 10px;" onclick='openEditCategoryModal(@json($cat))' title="تعديل إعدادات المسار">
                            <i class="bi bi-pencil"></i> تعديل المسار
                        </button>
                        @if ($catStages->isEmpty())
                            <form action="{{ route('v2.settings.stage_categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المسار؟');" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn small danger" style="padding:4px 8px;" title="حذف المسار">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            <div class="table-wrap">
                @if ($catStages->isNotEmpty())
                    <table>
                        <thead>
                            <tr>
                                <th style="width:60px; text-align:center;">#</th>
                                <th>{{ __('crm.stage_name_col') }}</th>
                                <th>{{ __('crm.type_col') }}</th>
                                <th>{{ __('crm.has_followups_col') }}</th>
                                <th>{{ __('crm.color_col') }}</th>
                                <th>{{ __('crm.leads_count_col') }}</th>
                                <th>{{ __('crm.status_th') }}</th>
                                <th style="width:160px">{{ __('crm.actions_th') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($catStages as $index => $stage)
                                <tr>
                                    <td style="text-align:center;">
                                        <span class="badge" style="font-size:13px; font-weight:bold; background:var(--bg); border:1px solid var(--line);">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            @if ($stage->icon)
                                                <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:#f1f5f9; color:#475569; font-size:15px;">
                                                    <i class="bi {{ $stage->icon }}"></i>
                                                </span>
                                            @endif
                                            <div>
                                                <strong style="font-size:14.5px">{{ $stage->localizedName() }}</strong>
                                                @if ($stage->description_ar)
                                                    <small style="display:block; color:var(--muted); margin-top:2px">{{ $stage->description_ar }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($stage->isPrimary())
                                            <span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd">
                                                <i class="bi bi-shield-check"></i> {{ __('crm.primary_stage_badge') }}
                                            </span>
                                        @else
                                            <span class="badge" style="background:#f3e8ff; color:#7e22ce; border:1px solid #e9d5ff">
                                                <i class="bi bi-plus-circle"></i> {{ __('crm.additional_stage_badge') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($stage->has_followups)
                                            <span class="badge" style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;" title="{{ __('crm.has_followups_yes_badge') }}">
                                                <i class="bi bi-calendar-check"></i> {{ __('crm.has_followups_yes_badge') }}
                                            </span>
                                        @else
                                            <span class="badge" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;" title="{{ __('crm.has_followups_no_badge') }}">
                                                <i class="bi bi-slash-circle"></i> {{ __('crm.has_followups_no_badge') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:8px">
                                            <span style="display:inline-block; width:20px; height:20px; border-radius:6px; background:{{ $stage->color ?? '#64748b' }}; border:1px solid rgba(0,0,0,0.1)"></span>
                                            <code style="font-size:12px">{{ $stage->color ?? '—' }}</code>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $stage->leads_count > 0 ? 'active' : '' }}" style="font-size:12px">
                                            {{ number_format($stage->leads_count) }} {{ __('crm.lead_unit') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($stage->is_active)
                                            <span class="badge active">{{ __('crm.stage_active_badge') }}</span>
                                        @else
                                            <span class="badge inactive">{{ __('crm.stage_inactive_badge') }}</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap; width:130px;">
                                        <div class="actions" style="display:inline-flex; gap:6px; align-items:center; flex-wrap:nowrap; white-space:nowrap;">
                                            <a href="{{ route('v2.settings.stages.fields.index', $stage) }}" class="btn small soft" style="color:#4f46e5; border-color:#c7d2fe; background:#eef2ff; width:34px; height:34px; min-height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" title="{{ __('crm.manage_stage_fields') }}" aria-label="{{ __('crm.manage_stage_fields') }}">
                                                <i class="bi bi-ui-checks" style="font-size:14px;"></i>
                                            </a>
                                            <button type="button" class="btn small soft" style="width:34px; height:34px; min-height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" onclick='openEditModal(@json($stage))' title="{{ __('crm.edit') }}" aria-label="{{ __('crm.edit') }}">
                                                <i class="bi bi-pencil-square" style="font-size:14px;"></i>
                                            </button>

                                            @if ($stage->leads_count === 0)
                                                <form method="POST" action="{{ route('v2.settings.stages.destroy', $stage) }}" onsubmit="return confirm(@json(__('crm.confirm_delete_stage')))" style="margin:0; display:inline-flex; flex-shrink:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn small danger" style="width:34px; height:34px; min-height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" title="{{ __('crm.delete') }}" aria-label="{{ __('crm.delete') }}">
                                                        <i class="bi bi-trash" style="font-size:14px;"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn small danger" style="width:34px; height:34px; min-height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" onclick='openSafeDeleteStageModal(@json($stage), {{ (int) $stage->leads_count }})' title="حذف المرحلة ونقل/أرشفة العملاء" aria-label="{{ __('crm.delete') }}">
                                                    <i class="bi bi-trash" style="font-size:14px;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align:center; padding:30px 16px; color:var(--muted); font-size:13px;">
                        <i class="bi bi-diagram-3" style="font-size:24px; display:block; margin-bottom:6px; opacity:0.5;"></i>
                        لا توجد مراحل مسجلة تحت هذا المسار حتى الآن.
                    </div>
                @endif
            </div>
        </section>
    @endforeach
</div>

@php
$crmStageIcons = [
    ['icon' => 'bi-person', 'name' => 'عميل (شخص)'],
    ['icon' => 'bi-person-plus', 'name' => 'عميل جديد'],
    ['icon' => 'bi-person-check', 'name' => 'عميل مؤكد'],
    ['icon' => 'bi-person-x', 'name' => 'عميل ملغى'],
    ['icon' => 'bi-people', 'name' => 'مجموعة عملاء'],
    ['icon' => 'bi-telephone', 'name' => 'هاتف'],
    ['icon' => 'bi-telephone-outbound', 'name' => 'اتصال صادر'],
    ['icon' => 'bi-telephone-inbound', 'name' => 'اتصال وارد'],
    ['icon' => 'bi-telephone-x', 'name' => 'لم يتم الرد'],
    ['icon' => 'bi-chat-dots', 'name' => 'محادثة'],
    ['icon' => 'bi-whatsapp', 'name' => 'واتساب'],
    ['icon' => 'bi-envelope', 'name' => 'بريد إلكتروني'],
    ['icon' => 'bi-check-circle', 'name' => 'مكتمل ومؤكد'],
    ['icon' => 'bi-x-circle', 'name' => 'غير مهتم'],
    ['icon' => 'bi-clock-history', 'name' => 'متابعة لاحقة'],
    ['icon' => 'bi-calendar-check', 'name' => 'موعد محدد'],
    ['icon' => 'bi-calendar-event', 'name' => 'حدث ومقابلة'],
    ['icon' => 'bi-arrow-repeat', 'name' => 'متابعة دورية'],
    ['icon' => 'bi-hourglass-split', 'name' => 'قيد الانتظار'],
    ['icon' => 'bi-star', 'name' => 'مميز'],
    ['icon' => 'bi-star-fill', 'name' => 'نجم'],
    ['icon' => 'bi-flag', 'name' => 'راية ومرحلة'],
    ['icon' => 'bi-bookmark', 'name' => 'علامة'],
    ['icon' => 'bi-pin-angle', 'name' => 'تثبيت'],
    ['icon' => 'bi-bell', 'name' => 'تنبيه'],
    ['icon' => 'bi-hand-thumbs-up', 'name' => 'موافقة'],
    ['icon' => 'bi-briefcase', 'name' => 'أعمال وشركات'],
    ['icon' => 'bi-building', 'name' => 'مؤسسة'],
    ['icon' => 'bi-patch-check', 'name' => 'معتمد'],
    ['icon' => 'bi-tag', 'name' => 'تصنيف'],
    ['icon' => 'bi-sliders', 'name' => 'تخصيص'],
];
@endphp

<!-- MODAL: ADD STAGE -->
<div id="addStageModal" class="crm-body-modal-shell" style="display:none;" onclick="if(event.target===this && (Date.now() - (this._openedAt || 0) > 300)) this.style.display='none'">
    <div class="crm-body-modal-dialog" style="max-width:520px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="margin:0; font-size:18px;">{{ __('crm.add_stage_modal_title') }}</h3>
            <button type="button" onclick="document.getElementById('addStageModal').style.display='none'" style="border:0; background:transparent; font-size:22px; cursor:pointer">&times;</button>
        </div>
        <form method="POST" action="{{ route('v2.settings.stages.store') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_name_label') }} <span style="color:var(--red)">*</span></label>
                <input type="text" name="name_ar" required placeholder="{{ __('crm.stage_name_label') }}" autofocus>
            </div>
            <div class="form-grid" style="margin-bottom:14px;">
                <div>
                    <label>{{ __('crm.stage_color_hex') }}</label>
                    <div style="display:flex; gap:8px;">
                        <input type="color" id="stageColorPicker" value="#7b61df" style="width:48px; height:42px; padding:2px;" onchange="document.getElementById('stageColorInput').value=this.value">
                        <input type="text" id="stageColorInput" name="color" value="#7b61df" placeholder="#7b61df" pattern="^#[0-9a-fA-F]{6}$" onchange="document.getElementById('stageColorPicker').value=this.value">
                    </div>
                </div>
                <div>
                    <label>{{ __('crm.icon_optional') }}</label>
                    <div class="icon-picker-wrap" id="addStageIconWrap" style="position:relative;">
                        <input type="hidden" id="addStageIconInput" name="icon" value="">
                        <button type="button" class="icon-picker-btn" id="addStageIconBtn" onclick="toggleIconDropdown('addStage')" style="width:100%; height:42px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1px solid #dbe1e9; border-radius:10px; padding:0 12px; background:#fff; color:var(--ink); cursor:pointer; text-align:start;">
                            <span style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden;">
                                <span class="icon-preview-box" id="addStageIconPreview" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; background:#f1f5f9; color:#475569; font-size:15px; flex-shrink:0;">
                                    <i class="bi bi-slash-circle" style="font-size:13px; color:#94a3b8;"></i>
                                </span>
                                <span class="icon-selected-label" id="addStageIconLabel" style="font-size:13px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ __('crm.choose_icon_optional') }}
                                </span>
                            </span>
                            <span style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                                <span class="icon-clear-btn" id="addStageIconClear" onclick="event.stopPropagation(); clearIconSelection('addStage')" style="display:none; color:#94a3b8; font-size:14px; padding:2px 4px; border-radius:4px; cursor:pointer;" title="{{ __('crm.no_icon') }}">&times;</span>
                                <i class="bi bi-chevron-down" style="font-size:12px; color:#94a3b8;"></i>
                            </span>
                        </button>
                        <div class="icon-picker-dropdown" id="addStageIconDropdown" style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:auto; width:280px; max-width:min(280px, calc(100vw - 32px)); background:#fff; border:1px solid #dbe1e9; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.15); padding:10px; z-index:1000;">
                            <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:6px; max-height:210px; overflow-y:auto; padding:2px;">
                                @foreach($crmStageIcons as $ico)
                                    <button type="button" class="icon-option-btn" onclick="selectIcon('addStage', '{{ $ico['icon'] }}', '{{ $ico['name'] }}')" title="{{ $ico['name'] }}" style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; color:#334155; font-size:16px; cursor:pointer; transition:all 0.15s ease;">
                                        <i class="bi {{ $ico['icon'] }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_category') }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small></label>
                <select name="pipeline_stage_category_id" id="addStageCategoryId" style="width:100%; padding:10px 14px; border:1px solid #dbe1e9; border-radius:10px; font-size:14px; background:#fff; color:var(--dark);">
                    <option value="">-- {{ __('crm.no_category_direct') }} --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_has_followups_label') }} <span style="color:var(--red)">*</span></label>
                <select name="has_followups" id="addStageHasFollowups" style="width:100%; padding:10px 14px; border:1px solid #dbe1e9; border-radius:10px; font-size:14px; background:#fff; color:var(--dark);">
                    <option value="1" selected>{{ __('crm.stage_has_followups_yes') }}</option>
                    <option value="0">{{ __('crm.stage_has_followups_no') }}</option>
                </select>
                <small style="color:var(--muted); display:block; margin-top:4px;">{{ __('crm.stage_has_followups_hint') }}</small>
            </div>
            <div style="margin-bottom:18px;">
                <label>{{ __('crm.description_optional') }}</label>
                <textarea name="description_ar" rows="2" placeholder="{{ __('crm.description_optional') }}"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn soft" onclick="document.getElementById('addStageModal').style.display='none'">{{ __('crm.cancel') }}</button>
                <button type="submit" class="btn primary">{{ __('crm.save_stage') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT STAGE -->
<div id="editStageModal" class="crm-body-modal-shell" style="display:none;" onclick="if(event.target===this && (Date.now() - (this._openedAt || 0) > 300)) this.style.display='none'">
    <div class="crm-body-modal-dialog" style="max-width:520px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="margin:0; font-size:18px;">{{ __('crm.edit_stage_modal_title') }}</h3>
            <button type="button" onclick="document.getElementById('editStageModal').style.display='none'" style="border:0; background:transparent; font-size:22px; cursor:pointer">&times;</button>
        </div>
        <form id="editStageForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_name_label') }} <span style="color:var(--red)">*</span></label>
                <input type="text" id="editStageName" name="name_ar" required>
            </div>
            <div class="form-grid" style="margin-bottom:14px;">
                <div>
                    <label>{{ __('crm.order_col') }} <span style="color:var(--red)">*</span></label>
                    <input type="number" id="editStagePosition" name="position" min="1" max="255" required>
                </div>
                <div>
                    <label>{{ __('crm.stage_color_hex') }}</label>
                    <div style="display:flex; gap:8px;">
                        <input type="color" id="editStageColorPicker" value="#7b61df" style="width:48px; height:42px; padding:2px;" onchange="document.getElementById('editStageColorInput').value=this.value">
                        <input type="text" id="editStageColorInput" name="color" placeholder="#7b61df" pattern="^#[0-9a-fA-F]{6}$" onchange="document.getElementById('editStageColorPicker').value=this.value">
                    </div>
                </div>
            </div>
            <div class="form-grid" style="margin-bottom:14px;">
                <div>
                    <label>{{ __('crm.icon_optional') }}</label>
                    <div class="icon-picker-wrap" id="editStageIconWrap" style="position:relative;">
                        <input type="hidden" id="editStageIconInput" name="icon" value="">
                        <button type="button" class="icon-picker-btn" id="editStageIconBtn" onclick="toggleIconDropdown('editStage')" style="width:100%; height:42px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1px solid #dbe1e9; border-radius:10px; padding:0 12px; background:#fff; color:var(--ink); cursor:pointer; text-align:start;">
                            <span style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden;">
                                <span class="icon-preview-box" id="editStageIconPreview" style="width:24px; height:24px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; background:#f1f5f9; color:#475569; font-size:15px; flex-shrink:0;">
                                    <i class="bi bi-slash-circle" style="font-size:13px; color:#94a3b8;"></i>
                                </span>
                                <span class="icon-selected-label" id="editStageIconLabel" style="font-size:13px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ __('crm.choose_icon_optional') }}
                                </span>
                            </span>
                            <span style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                                <span class="icon-clear-btn" id="editStageIconClear" onclick="event.stopPropagation(); clearIconSelection('editStage')" style="display:none; color:#94a3b8; font-size:14px; padding:2px 4px; border-radius:4px; cursor:pointer;" title="{{ __('crm.no_icon') }}">&times;</span>
                                <i class="bi bi-chevron-down" style="font-size:12px; color:#94a3b8;"></i>
                            </span>
                        </button>
                        <div class="icon-picker-dropdown" id="editStageIconDropdown" style="display:none; position:absolute; top:calc(100% + 4px); right:0; left:auto; width:280px; max-width:min(280px, calc(100vw - 32px)); background:#fff; border:1px solid #dbe1e9; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.15); padding:10px; z-index:1000;">
                            <div style="display:grid; grid-template-columns:repeat(6, 1fr); gap:6px; max-height:210px; overflow-y:auto; padding:2px;">
                                @foreach($crmStageIcons as $ico)
                                    <button type="button" class="icon-option-btn" onclick="selectIcon('editStage', '{{ $ico['icon'] }}', '{{ $ico['name'] }}')" title="{{ $ico['name'] }}" style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; color:#334155; font-size:16px; cursor:pointer; transition:all 0.15s ease;">
                                        <i class="bi {{ $ico['icon'] }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div id="editStageActiveWrap" style="display:flex; align-items:center; margin-top:24px;">
                    <label class="check-card" style="margin:0; width:100%; cursor:pointer;">
                        <input type="checkbox" id="editStageIsActive" name="is_active" value="1">
                        <div>
                            <strong>{{ __('crm.stage_active_label') }}</strong>
                            <small>{{ __('crm.stage_active_desc') }}</small>
                        </div>
                    </label>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_category') }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small></label>
                <select name="pipeline_stage_category_id" id="editStageCategoryId" style="width:100%; padding:10px 14px; border:1px solid #dbe1e9; border-radius:10px; font-size:14px; background:#fff; color:var(--dark);">
                    <option value="">-- {{ __('crm.no_category_direct') }} --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <label>{{ __('crm.stage_has_followups_label') }} <span style="color:var(--red)">*</span></label>
                <select name="has_followups" id="editStageHasFollowups" style="width:100%; padding:10px 14px; border:1px solid #dbe1e9; border-radius:10px; font-size:14px; background:#fff; color:var(--dark);">
                    <option value="1">{{ __('crm.stage_has_followups_yes') }}</option>
                    <option value="0">{{ __('crm.stage_has_followups_no') }}</option>
                </select>
                <small style="color:var(--muted); display:block; margin-top:4px;">{{ __('crm.stage_has_followups_hint') }}</small>
            </div>
            <div style="margin-bottom:18px;">
                <label>{{ __('crm.description_optional') }}</label>
                <textarea id="editStageDescription" name="description_ar" rows="2"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn soft" onclick="document.getElementById('editStageModal').style.display='none'">{{ __('crm.cancel') }}</button>
                <button type="submit" class="btn primary">{{ __('crm.save_changes') }}</button>
            </div>
        </form>
    </div>
</div>
<!-- MODAL: SAFE DELETE STAGE WITH LEADS -->
<div id="safeDeleteStageModal" class="crm-body-modal-shell" style="display:none;" onclick="if(event.target===this && (Date.now() - (this._openedAt || 0) > 300)) closeSafeDeleteStageModal()">
    <div class="crm-body-modal-dialog" style="max-width:520px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="margin:0; font-size:18px; color:var(--red);"><i class="bi bi-exclamation-triangle"></i> حذف المرحلة: <span id="safeDeleteStageName"></span></h3>
            <button type="button" onclick="closeSafeDeleteStageModal()" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <form id="safeDeleteStageForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:14px; margin-bottom:16px; color:#92400e; font-size:13px; line-height:1.6;">
                تحتوي هذه المرحلة حالياً على <strong><span id="safeDeleteLeadsCount"></span> عميل</strong>. لحذف المرحلة بأمان دون فقدان البيانات، يرجى اختيار الإجراء المطلوب:
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; margin-bottom:8px;">
                    <input type="radio" name="lead_action" value="move" checked onchange="onSafeDeleteActionChange(this)">
                    <span>أ) نقل العملاء إلى مرحلة نشطة أخرى</span>
                </label>
                <div id="safeDeleteMoveGroup" style="margin-inline-start:24px; margin-top:6px;">
                    <select name="destination_stage_id" id="safeDeleteDestinationStage" style="width:100%; height:38px; border-radius:8px; border:1px solid var(--line); padding:0 10px;">
                        @foreach ($stages as $stgOption)
                            @if (! $stgOption->isPrimary())
                                <option value="{{ $stgOption->id }}">{{ $stgOption->localizedName() }} ({{ $stgOption->leads_count }} عميل)</option>
                            @else
                                <option value="{{ $stgOption->id }}">{{ $stgOption->localizedName() }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700;">
                    <input type="radio" name="lead_action" value="trash" onchange="onSafeDeleteActionChange(this)">
                    <span style="color:#b91c1c;">ب) نقل جميع عملاء المرحلة إلى سلة المهملات (Trash)</span>
                </label>
                <small style="display:block; margin-inline-start:24px; color:var(--muted); font-size:11px;">يمكنك استعادة العملاء لاحقاً من صفحة سلة المهملات.</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--line); padding-top:14px;">
                <button type="button" class="btn soft" onclick="closeSafeDeleteStageModal()">{{ __('crm.cancel') }}</button>
                <button type="submit" class="btn danger" onclick="return confirm('هل أنت متأكد من تنفيذ هذا الإجراء وحذف المرحلة؟')">تأكيد الحذف</button>
            </div>
        </form>
    </div>
</div>

<script>
const crmIconsMap = @json(collect($crmStageIcons)->keyBy('icon'));

function toggleIconDropdown(prefix) {
    const dd = document.getElementById(prefix + 'IconDropdown');
    if (!dd) return;
    const isShown = dd.style.display === 'block';
    document.querySelectorAll('.icon-picker-dropdown').forEach(d => d.style.display = 'none');
    if (!isShown) {
        dd.style.display = 'block';
        requestAnimationFrame(() => {
            const modal = dd.closest('.crm-body-modal-dialog') || document.body;
            const mR = modal.getBoundingClientRect();
            const dR = dd.getBoundingClientRect();
            if (dR.right > mR.right - 10) {
                dd.style.left = 'auto';
                dd.style.right = '0px';
            } else if (dR.left < mR.left + 10) {
                dd.style.left = '0px';
                dd.style.right = 'auto';
            }
        });
    } else {
        dd.style.display = 'none';
    }
}
function selectIcon(prefix, iconClass, iconName) {
    const input = document.getElementById(prefix + 'IconInput');
    const preview = document.getElementById(prefix + 'IconPreview');
    const label = document.getElementById(prefix + 'IconLabel');
    const clear = document.getElementById(prefix + 'IconClear');
    const dd = document.getElementById(prefix + 'IconDropdown');

    if (input) input.value = iconClass;
    if (preview) preview.innerHTML = `<i class="bi ${iconClass}" style="color:var(--red);"></i>`;
    if (label) {
        label.textContent = iconName || iconClass;
        label.style.color = 'var(--ink)';
        label.style.fontWeight = 'bold';
    }
    if (clear) clear.style.display = 'inline-block';
    if (dd) dd.style.display = 'none';
}

function clearIconSelection(prefix) {
    const input = document.getElementById(prefix + 'IconInput');
    const preview = document.getElementById(prefix + 'IconPreview');
    const label = document.getElementById(prefix + 'IconLabel');
    const clear = document.getElementById(prefix + 'IconClear');

    if (input) input.value = '';
    if (preview) preview.innerHTML = '<i class="bi bi-slash-circle" style="font-size:13px; color:#94a3b8;"></i>';
    if (label) {
        label.textContent = @json(__('crm.choose_icon_optional'));
        label.style.color = '#64748b';
        label.style.fontWeight = 'normal';
    }
    if (clear) clear.style.display = 'none';
}

// Close icon picker dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.icon-picker-wrap')) {
        document.querySelectorAll('.icon-picker-dropdown').forEach(d => d.style.display = 'none');
    }
});

function openAddStageModal(preselectedCategoryId = null) {
    clearIconSelection('addStage');
    document.getElementById('stageColorPicker').value = '#7b61df';
    document.getElementById('stageColorInput').value = '#7b61df';
    const addHasFollowups = document.getElementById('addStageHasFollowups');
    if (addHasFollowups) {
        addHasFollowups.value = '1';
    }
    const catSelect = document.getElementById('addStageCategoryId');
    if (catSelect && preselectedCategoryId) {
        catSelect.value = preselectedCategoryId;
    }
    const modal = document.getElementById('addStageModal');
    modal._openedAt = Date.now();
    modal.style.display = 'flex';
}

function openEditModal(stage) {
    const form = document.getElementById('editStageForm');
    form.action = `/settings/stages/${stage.id}`;
    document.getElementById('editStageName').value = stage.name_ar || '';
    document.getElementById('editStagePosition').value = stage.position || 1;
    document.getElementById('editStageColorInput').value = stage.color || '#3478f6';
    document.getElementById('editStageColorPicker').value = stage.color || '#3478f6';
    document.getElementById('editStageDescription').value = stage.description_ar || '';
    document.getElementById('editStageCategoryId').value = stage.pipeline_stage_category_id || '';

    const editHasFollowups = document.getElementById('editStageHasFollowups');
    if (editHasFollowups) {
        editHasFollowups.value = (stage.has_followups !== false && stage.has_followups !== 0 && stage.has_followups !== '0') ? '1' : '0';
    }

    // Handle Icon in edit modal
    if (stage.icon && stage.icon.trim() !== '') {
        const icoInfo = crmIconsMap[stage.icon];
        selectIcon('editStage', stage.icon, icoInfo ? icoInfo.name : stage.icon);
    } else {
        clearIconSelection('editStage');
    }

    const activeWrap = document.getElementById('editStageActiveWrap');
    const activeCb = document.getElementById('editStageIsActive');

    if (stage.is_primary) {
        activeWrap.style.display = 'none';
        activeCb.checked = true;
    } else {
        activeWrap.style.display = 'flex';
        activeCb.checked = !!stage.is_active;
    }

    const modal = document.getElementById('editStageModal');
    modal._openedAt = Date.now();
    modal.style.display = 'flex';
}

function openSafeDeleteStageModal(stage, count) {
    const form = document.getElementById('safeDeleteStageForm');
    form.action = `/settings/stages/${stage.id}`;
    document.getElementById('safeDeleteStageName').textContent = stage.name_ar;
    document.getElementById('safeDeleteLeadsCount').textContent = count;

    const select = document.getElementById('safeDeleteDestinationStage');
    if (select) {
        Array.from(select.options).forEach(opt => {
            const isSelf = String(opt.value) === String(stage.id);
            opt.disabled = isSelf;
            opt.hidden = isSelf;
        });
        const firstValid = Array.from(select.options).find(opt => !opt.disabled);
        if (firstValid) select.value = firstValid.value;
    }

    const modal = document.getElementById('safeDeleteStageModal');
    modal._openedAt = Date.now();
    modal.style.display = 'flex';
}
function onSafeDeleteActionChange(radio) {
    const moveGroup = document.getElementById('safeDeleteMoveGroup');
    if (radio.value === 'move') {
        moveGroup.style.display = 'block';
        document.getElementById('safeDeleteDestinationStage').required = true;
    } else {
        moveGroup.style.display = 'none';
        document.getElementById('safeDeleteDestinationStage').required = false;
    }
}

function openAddCategoryModal() {
    const modal = document.getElementById('addCategoryModal');
    modal._openedAt = Date.now();
    modal.style.display = 'flex';
}

function openEditCategoryModal(category) {
    const form = document.getElementById('editCategoryForm');
    form.action = `/settings/stage-categories/${category.id}`;
    document.getElementById('editCategoryNameAr').value = category.name_ar || '';
    document.getElementById('editCategoryNameEn').value = category.name_en || '';
    document.getElementById('editCategoryDescriptionAr').value = category.description_ar || '';
    document.getElementById('editCategoryColorInput').value = category.color || '#3478f6';
    document.getElementById('editCategoryColorPicker').value = category.color || '#3478f6';

    const iconSelect = document.getElementById('editCategoryIconSelect');
    if (iconSelect && category.icon) {
        iconSelect.value = category.icon;
    }

    const autoTransferCb = document.getElementById('editAutoTransferCb');
    const autoTransferWrap = document.getElementById('editAutoTransferFields');
    if (autoTransferCb) {
        autoTransferCb.checked = !!category.auto_transfer_enabled;
        if (autoTransferWrap) {
            autoTransferWrap.style.display = category.auto_transfer_enabled ? 'block' : 'none';
        }
    }

    const actionSelect = document.getElementById('editAutoTransferAction');
    if (actionSelect && category.auto_transfer_action) {
        actionSelect.value = category.auto_transfer_action;
    }

    const triggerStageSelect = document.getElementById('editTriggerStageSelect');
    if (triggerStageSelect) {
        triggerStageSelect.value = category.trigger_stage_id || '';
        syncStatusesForTrigger('editTriggerStageSelect', 'editTriggerStatusSelect', category.trigger_status_id);
    }

    const targetStageSelect = document.getElementById('editTargetStageSelect');
    if (targetStageSelect) {
        targetStageSelect.value = category.target_stage_id || '';
    }

    const modal = document.getElementById('editCategoryModal');
    modal._openedAt = Date.now();
    modal.style.display = 'flex';
}

const allStagesData = @json($allStages ?? []);

function syncStatusesForTrigger(stageSelectId, statusSelectId, selectedStatusId = null) {
    const stageSelect = document.getElementById(stageSelectId);
    const statusSelect = document.getElementById(statusSelectId);
    if (!stageSelect || !statusSelect) return;

    const stageId = parseInt(stageSelect.value, 10);
    statusSelect.innerHTML = '<option value="">-- أي حالة في هذه المرحلة --</option>';

    if (!stageId) return;

    const stage = allStagesData.find(s => s.id === stageId);
    if (stage && stage.statuses && stage.statuses.length > 0) {
        stage.statuses.forEach(st => {
            const opt = document.createElement('option');
            opt.value = st.id;
            opt.textContent = st.name_ar || st.name_en || st.code;
            if (selectedStatusId && String(st.id) === String(selectedStatusId)) {
                opt.selected = true;
            }
            statusSelect.appendChild(opt);
        });
    }
}
</script>

<!-- ADD CATEGORY MODAL -->
<div id="addCategoryModal" class="crm-body-modal-shell" style="display:none;" onclick="handleBackdropClick(event, 'addCategoryModal')">
    <div class="crm-body-modal-dialog" style="max-width: 620px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--line); padding-bottom:12px;">
            <h3 style="margin:0; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-collection-fill" style="color:var(--red);"></i> {{ __('crm.add_stage_category') ?: 'إنشاء مسار عمل جديد' }}
            </h3>
            <button type="button" onclick="closeModal('addCategoryModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--muted);">&times;</button>
        </div>

        <form action="{{ route('v2.settings.stage_categories.store') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.category_name_ar') ?: 'اسم المسار بالعربية' }} <span style="color:var(--red)">*</span>
                    </label>
                    <input type="text" name="name_ar" required placeholder="مثال: مسار الاشتراكات" dir="rtl"
                           style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark); text-align:right;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.category_name_en') ?: 'اسم المسار بالإنجليزية' }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small>
                    </label>
                    <input type="text" name="name_en" placeholder="e.g. Subscriptions Pipeline"
                           style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                    {{ __('crm.description') ?: 'الوصف' }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small>
                </label>
                <textarea name="description_ar" rows="2" placeholder="اكتب وصفاً موجزاً لطبيعة هذا المسار..." dir="rtl"
                          style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark); resize:vertical; text-align:right;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.color_col') ?: 'لون المسار' }}
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="addCategoryColorPicker" value="#3478f6"
                               onchange="document.getElementById('addCategoryColorInput').value = this.value;"
                               style="width:44px; height:42px; border:1px solid var(--line); border-radius:8px; cursor:pointer; padding:2px; background:none;">
                        <input type="text" name="color" id="addCategoryColorInput" value="#3478f6"
                               oninput="document.getElementById('addCategoryColorPicker').value = this.value;"
                               placeholder="#3478f6"
                               style="flex:1; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                    </div>
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.icon_class') ?: 'أيقونة المسار' }}
                    </label>
                    <select name="icon" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                        <option value="bi-arrow-repeat">تجديد ومتابعة دورية (bi-arrow-repeat)</option>
                        <option value="bi-repeat">تكرار واشتراكات (bi-repeat)</option>
                        <option value="bi-person-check">مشترك مؤكد (bi-person-check)</option>
                        <option value="bi-telephone-outbound">مبيعات وتواصل (bi-telephone-outbound)</option>
                        <option value="bi-telephone">هاتف وتواصل (bi-telephone)</option>
                        <option value="bi-collection">مجموعة مسارات (bi-collection)</option>
                        <option value="bi-folder2-open">مجلد أعمال (bi-folder2-open)</option>
                        <option value="bi-tags">وسوم وفئات (bi-tags)</option>
                        <option value="bi-funnel">قمع بيع (bi-funnel)</option>
                        <option value="bi-briefcase">حقيبة أعمال (bi-briefcase)</option>
                        <option value="bi-diagram-3">مخطط مراحل (bi-diagram-3)</option>
                        <option value="bi-kanban">لوحة كانبان (bi-kanban)</option>
                        <option value="bi-check2-all">اكتمال وإنجاز (bi-check2-all)</option>
                    </select>
                </div>
            </div>

            <!-- AUTOMATIC TRANSFER CONFIGURATION -->
            <div style="margin-bottom:20px; border:1px solid var(--line); border-radius:10px; padding:14px; background:var(--bg);">
                <label style="display:flex; align-items:center; gap:8px; font-weight:800; font-size:13px; cursor:pointer; margin-bottom:10px;">
                    <input type="checkbox" name="auto_transfer_enabled" value="1" onchange="document.getElementById('addAutoTransferFields').style.display = this.checked ? 'block' : 'none';">
                    <i class="bi bi-lightning-charge-fill" style="color:#4f46e5;"></i>
                    <span>{{ __('ترحيل / نسخ العميل تلقائيًا إلى هذا المسار عند وصوله لمرحلة محددة') }}</span>
                </label>

                <div id="addAutoTransferFields" style="display:none; margin-top:12px; border-top:1px dashed var(--line); padding-top:12px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('نوع الإجراء') }}
                            </label>
                            <select name="auto_transfer_action" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="clone">{{ __('استنساخ عميل جديد في هذا المسار (Cloned Lead)') }}</option>
                                <option value="move">{{ __('نقل نفس العميل بالكامل إلى هذا المسار (Move)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('مرحلة الإطلاق والتحويل (المسار المصدر)') }}
                            </label>
                            <select name="trigger_stage_id" id="addTriggerStageSelect" onchange="syncStatusesForTrigger('addTriggerStageSelect', 'addTriggerStatusSelect')" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('اختر المرحلة المحفزة') }} --</option>
                                @foreach ($allStages as $stg)
                                    <option value="{{ $stg->id }}">{{ $stg->localizedName() }} @if ($stg->category) ({{ $stg->category->name_ar }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('حالة محددة للإطلاق (اختياري - أي حالة افتراضيًا)') }}
                            </label>
                            <select name="trigger_status_id" id="addTriggerStatusSelect" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('أي حالة في هذه المرحلة') }} --</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('المرحلة الابتدائية في هذا المسار المستهدف') }}
                            </label>
                            <select name="target_stage_id" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('أول مرحلة في هذا المسار تلقائيًا') }} --</option>
                                @foreach ($allStages as $stg)
                                    <option value="{{ $stg->id }}">{{ $stg->localizedName() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--line); padding-top:16px;">
                <button type="button" class="btn light" onclick="closeModal('addCategoryModal')">{{ __('crm.cancel') }}</button>
                <button type="submit" class="btn primary">{{ __('crm.save_category') ?: 'حفظ المسار' }}</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div id="editCategoryModal" class="crm-body-modal-shell" style="display:none;" onclick="handleBackdropClick(event, 'editCategoryModal')">
    <div class="crm-body-modal-dialog" style="max-width: 620px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--line); padding-bottom:12px;">
            <h3 style="margin:0; font-size:18px; font-weight:800; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-pencil-square" style="color:var(--red);"></i> {{ __('crm.edit_stage_category') ?: 'تعديل مسار العمل' }}
            </h3>
            <button type="button" onclick="closeModal('editCategoryModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--muted);">&times;</button>
        </div>

        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PATCH')
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.category_name_ar') ?: 'اسم المسار بالعربية' }} <span style="color:var(--red)">*</span>
                    </label>
                    <input type="text" name="name_ar" id="editCategoryNameAr" required dir="rtl"
                           style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark); text-align:right;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.category_name_en') ?: 'اسم المسار بالإنجليزية' }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small>
                    </label>
                    <input type="text" name="name_en" id="editCategoryNameEn"
                           style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                    {{ __('crm.description') ?: 'الوصف' }} <small style="color:var(--muted)">({{ __('crm.optional') }})</small>
                </label>
                <textarea name="description_ar" id="editCategoryDescriptionAr" rows="2" dir="rtl"
                          style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark); resize:vertical; text-align:right;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.color_col') ?: 'لون المسار' }}
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="editCategoryColorPicker" value="#3478f6"
                               onchange="document.getElementById('editCategoryColorInput').value = this.value;"
                               style="width:44px; height:42px; border:1px solid var(--line); border-radius:8px; cursor:pointer; padding:2px; background:none;">
                        <input type="text" name="color" id="editCategoryColorInput" value="#3478f6"
                               oninput="document.getElementById('editCategoryColorPicker').value = this.value;"
                               placeholder="#3478f6"
                               style="flex:1; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                    </div>
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">
                        {{ __('crm.icon_class') ?: 'أيقونة المسار' }}
                    </label>
                    <select name="icon" id="editCategoryIconSelect" style="width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:14px; background:var(--bg); color:var(--dark);">
                        <option value="bi-arrow-repeat">تجديد ومتابعة دورية (bi-arrow-repeat)</option>
                        <option value="bi-repeat">تكرار واشتراكات (bi-repeat)</option>
                        <option value="bi-person-check">مشترك مؤكد (bi-person-check)</option>
                        <option value="bi-telephone-outbound">مبيعات وتواصل (bi-telephone-outbound)</option>
                        <option value="bi-telephone">هاتف وتواصل (bi-telephone)</option>
                        <option value="bi-collection">مجموعة مسارات (bi-collection)</option>
                        <option value="bi-folder2-open">مجلد أعمال (bi-folder2-open)</option>
                        <option value="bi-tags">وسوم وفئات (bi-tags)</option>
                        <option value="bi-funnel">قمع بيع (bi-funnel)</option>
                        <option value="bi-briefcase">حقيبة أعمال (bi-briefcase)</option>
                        <option value="bi-diagram-3">مخطط مراحل (bi-diagram-3)</option>
                        <option value="bi-kanban">لوحة كانبان (bi-kanban)</option>
                        <option value="bi-check2-all">اكتمال وإنجاز (bi-check2-all)</option>
                    </select>
                </div>
            </div>

            <!-- AUTOMATIC TRANSFER CONFIGURATION -->
            <div style="margin-bottom:20px; border:1px solid var(--line); border-radius:10px; padding:14px; background:var(--bg);">
                <label style="display:flex; align-items:center; gap:8px; font-weight:800; font-size:13px; cursor:pointer; margin-bottom:10px;">
                    <input type="checkbox" name="auto_transfer_enabled" id="editAutoTransferCb" value="1" onchange="document.getElementById('editAutoTransferFields').style.display = this.checked ? 'block' : 'none';">
                    <i class="bi bi-lightning-charge-fill" style="color:#4f46e5;"></i>
                    <span>{{ __('ترحيل / نسخ العميل تلقائيًا إلى هذا المسار عند وصوله لمرحلة محددة') }}</span>
                </label>

                <div id="editAutoTransferFields" style="display:none; margin-top:12px; border-top:1px dashed var(--line); padding-top:12px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('نوع الإجراء') }}
                            </label>
                            <select name="auto_transfer_action" id="editAutoTransferAction" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="clone">{{ __('استنساخ عميل جديد في هذا المسار (Cloned Lead)') }}</option>
                                <option value="move">{{ __('نقل نفس العميل بالكامل إلى هذا المسار (Move)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('مرحلة الإطلاق والتحويل (المسار المصدر)') }}
                            </label>
                            <select name="trigger_stage_id" id="editTriggerStageSelect" onchange="syncStatusesForTrigger('editTriggerStageSelect', 'editTriggerStatusSelect')" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('اختر المرحلة المحفزة') }} --</option>
                                @foreach ($allStages as $stg)
                                    <option value="{{ $stg->id }}">{{ $stg->localizedName() }} @if ($stg->category) ({{ $stg->category->name_ar }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('حالة محددة للإطلاق (اختياري - أي حالة افتراضيًا)') }}
                            </label>
                            <select name="trigger_status_id" id="editTriggerStatusSelect" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('أي حالة في هذه المرحلة') }} --</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700;">
                                {{ __('المرحلة الابتدائية في هذا المسار المستهدف') }}
                            </label>
                            <select name="target_stage_id" id="editTargetStageSelect" style="width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:#fff;">
                                <option value="">-- {{ __('أول مرحلة في هذا المسار تلقائيًا') }} --</option>
                                @foreach ($allStages as $stg)
                                    <option value="{{ $stg->id }}">{{ $stg->localizedName() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--line); padding-top:16px;">
                <button type="button" class="btn light" onclick="closeModal('editCategoryModal')">{{ __('crm.cancel') }}</button>
                <button type="submit" class="btn primary">{{ __('crm.update_category') ?: 'حفظ التعديلات' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
