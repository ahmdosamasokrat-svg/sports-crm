@extends('settings.layout')

@section('title', 'مصادر العملاء (Lead Sources)')
@section('heading', 'مصادر العملاء')
@section('subheading', 'إدارة وتخصيص مصادر ورود العملاء ديناميكيًا عبر واجهة النظام')
@section('page-icon', 'bi-funnel-fill')
@section('back-url', route('v2.settings'))
@section('back-title', __('crm.settings'))

@section('top-actions')
    <a class="btn primary" href="#sourceEditor">
        <i class="bi bi-plus-lg"></i> إضافة مصدر جديد
    </a>
@endsection

@section('content')
<style>
.sources-layout{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr);gap:20px;align-items:start}
.sources-list{display:flex;flex-direction:column;gap:10px}
.source-card{display:flex;align-items:center;gap:14px;padding:15px 16px;border:1px solid var(--line);border-radius:14px;background:var(--card);transition:border-color .16s ease,opacity .16s ease}
.source-card:hover{border-color:var(--muted)}
.source-card.inactive{opacity:.58;background:var(--bg)}
.source-icon-wrap{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;flex:0 0 auto;color:#fff;font-size:18px}
.source-copy{min-width:0;flex:1}
.source-copy h3{margin:0 0 5px;color:var(--dark);font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px}
.source-copy p{margin:0;color:var(--muted);font-size:12px}
.source-actions{display:flex;align-items:center;gap:6px}
.source-editor{position:sticky;top:18px}
.source-editor .panel{padding:0;overflow:hidden}
.editor-head{padding:18px 20px;border-bottom:1px solid var(--line);background:var(--bg)}
.editor-head h2{margin:0 0 4px;font-size:15px;color:var(--dark)}
.editor-head p{margin:0;color:var(--muted);font-size:11px}
.editor-body{padding:20px}
.editor-grid{display:grid;gap:14px}
.editor-field{display:flex;flex-direction:column;gap:6px}
.editor-field label{font-size:11px;font-weight:800;color:var(--dark)}
.editor-field input,.editor-field select{width:100%}
.editor-actions{display:flex;gap:8px;padding-top:16px;margin-top:4px;border-top:1px solid var(--line)}
@media(max-width:1050px){.sources-layout{grid-template-columns:1fr}.source-editor{position:static}}
</style>

@php
    $editing = $editSource !== null;
@endphp

<div class="sources-layout">
    <div class="sources-list">
        @forelse($sources as $source)
            <div class="source-card {{ $source->is_active ? '' : 'inactive' }}">
                <div class="source-icon-wrap" style="background: {{ $source->color ?: '#3b82f6' }}">
                    <i class="bi {{ $source->icon ?: 'bi-funnel' }}"></i>
                </div>

                <div class="source-copy">
                    <h3>
                        <span>{{ $source->name_ar }}</span>
                        @if($source->name_en)
                            <span style="font-size:12px;color:var(--muted);font-weight:600">({{ $source->name_en }})</span>
                        @endif
                        @if(! $source->is_active)
                            <span class="badge inactive">معطل</span>
                        @endif
                    </h3>
                    <p>اللون: <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $source->color }};vertical-align:middle;margin:0 3px"></span> {{ $source->color }} | الأيقونة: <code>{{ $source->icon }}</code></p>
                </div>

                <div class="source-actions">
                    <form method="POST" action="{{ route('v2.settings.lead-sources.move', $source) }}">
                        @csrf
                        <input type="hidden" name="direction" value="up">
                        <button class="btn small soft" type="submit" title="نقل لأعلى"><i class="bi bi-arrow-up"></i></button>
                    </form>
                    <form method="POST" action="{{ route('v2.settings.lead-sources.move', $source) }}">
                        @csrf
                        <input type="hidden" name="direction" value="down">
                        <button class="btn small soft" type="submit" title="نقل لأسفل"><i class="bi bi-arrow-down"></i></button>
                    </form>
                    <a class="btn small soft" href="{{ route('v2.settings.lead-sources.index', ['edit' => $source->id]) }}#sourceEditor" title="تعديل"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('v2.settings.lead-sources.toggle', $source) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn small {{ $source->is_active ? 'soft' : 'primary' }}" type="submit" title="تفعيل / تعطيل">
                            <i class="bi bi-power"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('v2.settings.lead-sources.destroy', $source) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn small soft" type="submit" style="color:var(--red)" title="حذف"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="panel" style="text-align:center;padding:40px;">
                <i class="bi bi-funnel" style="font-size:32px;color:var(--muted);"></i>
                <p style="margin-top:12px;color:var(--muted);">لا توجد مصادر مضافة بعد. أضف مصادر من النموذج الجانبي.</p>
            </div>
        @endforelse
    </div>

    <!-- SIDEBAR FORM -->
    <div class="source-editor" id="sourceEditor">
        <div class="panel">
            <div class="editor-head">
                <h2><i class="bi {{ $editing ? 'bi-pencil-square' : 'bi-plus-circle-fill' }}" style="color:var(--red)"></i> {{ $editing ? 'تعديل المصدر' : 'إضافة مصدر جديد' }}</h2>
                <p>{{ $editing ? 'تعديل إعدادات مصدر عملاء مسجل' : 'إضافة مصدر جديد يظهر في جميع قوائم التسجيل والفلترة' }}</p>
            </div>
            <div class="editor-body">
                <form method="POST" action="{{ $editing ? route('v2.settings.lead-sources.update', $editSource) : route('v2.settings.lead-sources.store') }}">
                    @csrf
                    @if($editing)
                        @method('PATCH')
                    @endif

                    <div class="editor-grid">
                        <div class="editor-field">
                            <label for="name_ar">اسم المصدر (بالعربية) <span style="color:var(--red)">*</span></label>
                            <input class="control" type="text" id="name_ar" name="name_ar" required value="{{ old('name_ar', $editSource?->name_ar) }}" placeholder="مثال: Meta / فيسبوك">
                        </div>

                        <div class="editor-field">
                            <label for="name_en">اسم المصدر (بالإنجليزية)</label>
                            <input class="control" type="text" id="name_en" name="name_en" value="{{ old('name_en', $editSource?->name_en) }}" placeholder="مثال: Meta / Facebook">
                        </div>

                        <div class="editor-field">
                            <label for="icon">أيقونة المصدر (Bootstrap Icon)</label>
                            <select class="control" id="icon" name="icon">
                                @php
                                    $icons = [
                                        'bi-funnel' => 'قمع / عام (Funnel)',
                                        'bi-facebook' => 'Facebook / Meta',
                                        'bi-instagram' => 'Instagram',
                                        'bi-whatsapp' => 'WhatsApp',
                                        'bi-people' => 'إحالة / ترشيح (Referral)',
                                        'bi-door-open' => 'زيارة مباشرة (Walk-in)',
                                        'bi-file-earmark-excel' => 'إكسيل / استيراد (Excel)',
                                        'bi-google' => 'إعلانات جوجل (Google Ads)',
                                        'bi-globe' => 'موقع إلكتروني (Website)',
                                        'bi-telephone' => 'اتصال هاتفي (Phone Call)',
                                        'bi-tiktok' => 'تيك توك (TikTok)',
                                        'bi-snapchat' => 'سناب شات (Snapchat)',
                                    ];
                                    $currentIcon = old('icon', $editSource?->icon ?? 'bi-funnel');
                                @endphp
                                @foreach($icons as $iconKey => $iconLabel)
                                    <option value="{{ $iconKey }}" @selected($currentIcon === $iconKey)>{{ $iconLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="editor-field">
                            <label for="color">لون المصدر المميز</label>
                            <div style="display:flex;gap:8px;align-items:center">
                                <input type="color" id="colorPicker" style="width:45px;height:40px;padding:2px;border-radius:8px;cursor:pointer" value="{{ old('color', $editSource?->color ?? '#3b82f6') }}" onchange="document.getElementById('color').value = this.value">
                                <input class="control" type="text" id="color" name="color" value="{{ old('color', $editSource?->color ?? '#3b82f6') }}" onchange="document.getElementById('colorPicker').value = this.value">
                            </div>
                        </div>

                        <div class="editor-actions">
                            <button class="btn primary" type="submit"><i class="bi bi-check-lg"></i> {{ $editing ? 'حفظ التعديلات' : 'إضافة المصدر' }}</button>
                            @if($editing)
                                <a class="btn soft" href="{{ route('v2.settings.lead-sources.index') }}">إلغاء</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
