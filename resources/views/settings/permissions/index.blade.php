@extends('settings.layout')

@section('title', __('crm.permissions'))
@section('heading', __('crm.permissions_matrix'))
@section('subheading', __('crm.permissions_inheritance'))
@section('page-icon', 'bi-shield-lock')

@if (auth()->user()->can('groups.assign_permissions'))
    @section('top-actions')
        <button type="submit" form="permissionsForm" class="btn primary">
            <i class="bi bi-check2"></i> {{ __('crm.save_permissions') }}
        </button>
    @endsection
@endif

@section('content')
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>{{ __('crm.permissions_by_group') }}</h2>
            <p>{{ __('crm.permissions_notice') ?? (app()->getLocale() === 'en' ? 'Super Admin permissions are protected. Configure group permissions via this matrix. When Leads View is granted without All or Group scope, access is automatically scoped to user-assigned leads.' : 'صلاحيات مدير النظام كاملة ومحمية. ويمكن ضبط صلاحيات بقية المجموعات من هذه المصفوفة. عند منح «عرض العملاء» دون نطاق «جميع العملاء» أو «مجموعات المستخدم»، يقتصر العرض تلقائيًا على العملاء المسندة للمستخدم أو المنشأة بواسطته.') }}</p>
        </div>
    </div>

    @php
        $canEditPermissions = auth()->user()->can('groups.assign_permissions');
        $moduleIcons = [
            'dashboard' => 'bi-speedometer2',
            'settings' => 'bi-gear-wide-connected',
            'notifications' => 'bi-bell-fill',
            'leads' => 'bi-people-fill',
            'pipeline_stages' => 'bi-diagram-3-fill',
            'tasks' => 'bi-check2-square',
            'quotations' => 'bi-file-earmark-text-fill',
            'campaigns' => 'bi-megaphone-fill',
            'reports' => 'bi-bar-chart-line-fill',
            'users' => 'bi-person-badge-fill',
            'groups' => 'bi-shield-lock-fill',
            'voip' => 'bi-telephone-fill',
            'calendar' => 'bi-calendar-event-fill',
            'technical_support' => 'bi-headset',
        ];
    @endphp
    <form method="POST" action="{{ route('v2.settings.permissions.update') }}" id="permissionsForm">
        @csrf
        @method('PUT')
        <div class="table-wrap">
            <table class="permission-table">
                <thead>
                    <tr>
                        <th>{{ __('crm.permission') ?? (app()->getLocale() === 'en' ? 'Permission' : 'الصلاحية') }}</th>
                        @foreach ($groups as $group)
                            <th>
                                {{ $group->name }}
                                @if ($group->is_system)<span class="badge system">{{ __('crm.protected') }}</span>@endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissionsByModule as $module => $permissions)
                        <tr class="module-row">
                            <td colspan="{{ $groups->count() + 1 }}">
                                <div class="module-header-wrap">
                                    <div class="module-header-main">
                                        <span class="module-icon-box">
                                            <i class="bi {{ $moduleIcons[$module] ?? 'bi-folder2-open' }}"></i>
                                        </span>
                                        <span class="module-title-text">{{ $moduleLabels[$module] ?? $module }}</span>
                                    </div>
                                    <span class="module-count-badge">{{ $permissions->count() }} {{ __('crm.permissions') ?? 'صلاحيات' }}</span>
                                </div>
                            </td>
                        </tr>
                        @foreach ($permissions as $permission)
                            <tr>
                                <td>
                                    <span class="permission-bullet"></span>
                                    <strong>{{ $permission->name_ar }}</strong>
                                </td>
                                @foreach ($groups as $group)
                                    @php($checked = $group->isSuperAdmin() || $group->permissions->contains('code', $permission->code))
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="permissions[{{ $group->id }}][]"
                                            value="{{ $permission->code }}"
                                            @checked($checked)
                                            @disabled(!$canEditPermissions || $group->isSuperAdmin())
                                            aria-label="{{ $permission->name_ar }} - {{ $group->name }}"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canEditPermissions)
            <div class="actions" style="margin-top:20px">
                <button class="btn primary">{{ __('crm.save_permissions') }}</button>
            </div>
        @endif
    </form>
</section>
@endsection
