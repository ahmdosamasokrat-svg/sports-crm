@extends('settings.layout')

@section('title', __('crm.branches'))
@section('heading', __('crm.branches'))
@section('subheading', __('crm.branches_subheading') ?: 'إدارة فروع ومواقع الصالات الرياضية وتوزيع الوصول')
@section('page-icon', 'bi-geo-alt-fill')
@section('back-url', route('v2.settings'))
@section('back-title', __('crm.settings'))

@section('top-actions')
    <a class="btn primary" href="#branchEditor">
        <i class="bi bi-plus-lg"></i> {{ __('crm.add_new_branch') ?: 'إضافة فرع جديد' }}
    </a>
@endsection

@section('content')
<style>
.branches-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
    gap: 20px;
    align-items: start;
}
.branches-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.branch-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 18px;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--card);
    transition: border-color .16s ease, opacity .16s ease;
}
.branch-card:hover {
    border-color: var(--muted);
}
.branch-card.inactive {
    opacity: .62;
    background: var(--bg);
}
.branch-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    font-size: 20px;
}
.branch-copy {
    min-width: 0;
    flex: 1;
}
.branch-copy h3 {
    margin: 0 0 6px;
    color: var(--dark);
    font-size: 15px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.branch-meta {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.branch-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.branch-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.branch-editor {
    position: sticky;
    top: 18px;
}
.branch-editor .panel {
    padding: 0;
    overflow: hidden;
}
.editor-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--line);
    background: var(--bg);
}
.editor-head h2 {
    margin: 0 0 4px;
    font-size: 15px;
    color: var(--dark);
}
.editor-head p {
    margin: 0;
    color: var(--muted);
    font-size: 11px;
}
.editor-body {
    padding: 20px;
}
.editor-grid {
    display: grid;
    gap: 14px;
}
.editor-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.editor-field label {
    font-size: 11px;
    font-weight: 800;
    color: var(--dark);
}
.editor-field input, .editor-field select, .editor-field textarea {
    width: 100%;
}
.editor-actions {
    display: flex;
    gap: 8px;
    padding-top: 16px;
    margin-top: 4px;
    border-top: 1px solid var(--line);
}
@media (max-width: 1050px) {
    .branches-layout {
        grid-template-columns: 1fr;
    }
    .branch-editor {
        position: static;
    }
}
</style>

@php
    $editing = $editBranch !== null;
@endphp

<div class="branches-layout">
    <div class="branches-list">
        @forelse($branches as $branch)
            <div class="branch-card {{ $branch->is_active ? '' : 'inactive' }}" id="branch-card-{{ $branch->id }}">
                <div class="branch-icon-wrap">
                    <i class="bi bi-geo-alt"></i>
                </div>

                <div class="branch-copy">
                    <h3>
                        <span>{{ $branch->name_ar }}</span>
                        @if($branch->name_en)
                            <span style="font-size:12px;color:var(--muted);font-weight:600">({{ $branch->name_en }})</span>
                        @endif
                        <code style="font-size:11px;background:var(--bg);padding:2px 6px;border-radius:6px;border:1px solid var(--line)">{{ $branch->code }}</code>
                        @if($branch->code === 'main')
                            <span class="badge" style="background:#e0e7ff;color:#3730a3;font-size:10px">{{ __('crm.default_branch') ?: 'الفرع الافتراضي' }}</span>
                        @endif
                        @if(! $branch->is_active)
                            <span class="badge inactive">{{ __('crm.inactive') ?: 'معطل' }}</span>
                        @endif
                    </h3>
                    <div class="branch-meta">
                        @if($branch->phone)
                            <span><i class="bi bi-telephone"></i> <bdi dir="ltr">{{ $branch->phone }}</bdi></span>
                        @endif
                        @if($branch->address)
                            <span><i class="bi bi-pin-map"></i> {{ $branch->address }}</span>
                        @endif
                        <span><i class="bi bi-people"></i> {{ $branch->users_count }} {{ __('crm.users') ?: 'مستخدمين' }}</span>
                        <span><i class="bi bi-person-badge"></i> {{ $branch->leads_count }} {{ __('crm.leads') ?: 'عملاء' }}</span>
                    </div>
                </div>

                <div class="branch-actions">
                    <a class="btn small soft" href="{{ route('v2.settings.branches.index', ['edit' => $branch->id]) }}#branchEditor" title="{{ __('crm.edit') }}">
                        <i class="bi bi-pencil"></i>
                    </a>

                    @can('branches.manage')
                        <form method="POST" action="{{ route('v2.settings.branches.toggle', $branch) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn small soft" type="submit" title="{{ $branch->is_active ? __('crm.deactivate') : __('crm.activate') }}">
                                <i class="bi {{ $branch->is_active ? 'bi-pause-circle' : 'bi-play-circle text-success' }}"></i>
                            </button>
                        </form>

                        @if($branch->code !== 'main' && $branch->users_count === 0 && $branch->leads_count === 0)
                            <form method="POST" action="{{ route('v2.settings.branches.destroy', $branch) }}" onsubmit="return confirm('{{ __('crm.confirm_delete_branch') ?: 'هل أنت متأكد من حذف هذا الفرع؟' }}');">
                                @csrf
                                @method('DELETE')
                                <button class="btn small danger" type="submit" title="{{ __('crm.delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        @empty
            <div class="panel text-center" style="padding:40px">
                <i class="bi bi-geo-alt" style="font-size:32px;color:var(--muted)"></i>
                <p style="margin-top:10px;color:var(--muted)">{{ __('crm.no_branches_found') ?: 'لا توجد فروع مسجلة حالياً.' }}</p>
            </div>
        @endforelse
    </div>

    @can('branches.manage')
    <div class="branch-editor" id="branchEditor">
        <div class="panel">
            <div class="editor-head">
                <h2>{{ $editing ? (__('crm.edit_branch') ?: 'تعديل بيانات الفرع') : (__('crm.add_new_branch') ?: 'إضافة فرع جديد') }}</h2>
                <p>{{ $editing ? (__('crm.updating_branch') . ': ' . $editBranch->name_ar) : (__('crm.branch_create_desc') ?: 'أدخل تفاصيل الفرع الجديد وحدد حالة التفعيل') }}</p>
            </div>

            <form class="editor-body" method="POST" action="{{ $editing ? route('v2.settings.branches.update', $editBranch) : route('v2.settings.branches.store') }}">
                @csrf
                @if($editing)
                    @method('PATCH')
                @endif

                <div class="editor-grid">
                    <div class="editor-field">
                        <label for="name_ar">{{ __('crm.branch_name_ar') ?: 'اسم الفرع (بالعربية)' }} <span class="text-danger">*</span></label>
                        <input id="name_ar" name="name_ar" type="text" value="{{ old('name_ar', $editBranch->name_ar ?? '') }}" required placeholder="مثال: فرع المعادي">
                    </div>

                    <div class="editor-field">
                        <label for="name_en">{{ __('crm.branch_name_en') ?: 'اسم الفرع (بالإنجليزية)' }}</label>
                        <input id="name_en" name="name_en" type="text" value="{{ old('name_en', $editBranch->name_en ?? '') }}" placeholder="e.g. Maadi Branch">
                    </div>

                    <div class="editor-field">
                        <label for="code">{{ __('crm.branch_code') ?: 'رمز الفرع (Code)' }} <span class="text-danger">*</span></label>
                        <input id="code" name="code" type="text" value="{{ old('code', $editBranch->code ?? '') }}" required placeholder="مثال: maadi" {{ $editing && $editBranch->code === 'main' ? 'readonly' : '' }}>
                        <small style="font-size:10px;color:var(--muted)">{{ __('crm.branch_code_help') ?: 'رمز مختصر فريد بالإنجليزية (حروف وأرقام وشرطة فقط)' }}</small>
                    </div>

                    <div class="editor-field">
                        <label for="phone">{{ __('crm.phone') ?: 'رقم الهاتف' }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $editBranch->phone ?? '') }}" placeholder="010XXXXXXXX">
                    </div>

                    <div class="editor-field">
                        <label for="address">{{ __('crm.address') ?: 'العنوان' }}</label>
                        <textarea id="address" name="address" rows="2" placeholder="العنوان التفصيلي للفرع">{{ old('address', $editBranch->address ?? '') }}</textarea>
                    </div>

                    <div class="editor-field" style="flex-direction:row;align-items:center;gap:8px">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $editBranch->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0;cursor:pointer">{{ __('crm.active_branch') ?: 'فرع نشط ومتاح للعمل' }}</label>
                    </div>
                </div>

                <div class="editor-actions">
                    <button class="btn primary" type="submit">
                        <i class="bi bi-check-lg"></i> {{ $editing ? (__('crm.update') ?: 'تحديث الفرع') : (__('crm.save') ?: 'حفظ الفرع') }}
                    </button>

                    @if($editing)
                        <a class="btn soft" href="{{ route('v2.settings.branches.index') }}">
                            {{ __('crm.cancel') ?: 'إلغاء' }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
