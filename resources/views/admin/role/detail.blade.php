<?php
$append_breadcrumb = [
  [
    'text'    => $role->name,
  ]
];
$rand = random_string(5);
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/roles.tab_title.detail', ['x' => text_truncate($role->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/roles.page_title.detail'))

@section('content')
<div class="mb-2">
  @can('view-any', App\Models\Role::class)
  <a href="{{ route('admin.roles.index', ['goto_item' => $role->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
  @can('update', $role)
  <a href="{{ route('admin.roles.edit', ['role' => $role->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/roles.edit_role') }}
  </a>
  @endcan
  @can('delete', $role)
  <a href="{{ route('admin.roles.destroy', ['role' => $role->id]) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/roles._self'), $role->name, __('admin/common.fields.id'), $role->id) }}">
    <span class="fas fa-trash mr-1"></span>
    {{ __('common.delete') }}
  </a>
  @endcan
</div>
<div class="main-content">
<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" id="role-tablist-{{ $rand }}" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="#role-info-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/roles.role_data') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#role-abilities-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/roles.role_abilities') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#role-users-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/roles.role_users') }} ({{ count($role->users) }})</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content" id="role-tabs-{{ $rand }}">
      <div class="tab-pane fade show active" id="role-info-{{ $rand }}" role="tabpanel">
        <dl class="details-dl">
          <dt>@lang('admin/common.fields.id')</dt>
          <dd>{{ $role->id }}</dd>

          <dt>@lang('admin/roles.fields.name')</dt>
          <dd>{{ $role->name }}</dd>

          <dt>@lang('admin/roles.fields.title')</dt>
          <dd>{{ $role->title }}</dd>

          <dt>@lang('admin/roles.fields.level')</dt>
          <dd>@vo_($role->level)</dd>

          <dt>@lang('admin/roles.fields.scope')</dt>
          <dd>@vo_($role->scope)</dd>

          <dt>@lang('admin/common.fields.last_updated')</dt>
          <dd>@include('components.date-with-tooltip', ['date' => $role->updated_at])</dd>
        </dl>
        @if($ajax)
        <div class="mt-2">
          @can('update', $role)
          <a href="{{ route('admin.roles.edit', ['role' => $role->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
            <span class="fas fa-edit"></span>
            {{ __('admin/roles.edit_role') }}
          </a>
          @endcan
          @can('delete', $role)
          <a href="{{ route('admin.roles.destroy', ['role' => $role->id, 'backto' => 'back']) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/roles._self'), $role->name, __('admin/common.fields.id'), $role->id) }}">
            <span class="fas fa-trash mr-1"></span>
            {{ __('common.delete') }}
          </a>
          @endcan
        </div>
        @endif
      </div>
      <div class="tab-pane fade" id="role-abilities-{{ $rand }}" role="tabpanel">
        @if(count($role->abilities) > 0)
        <ul>
          @foreach($role->abilities as $abl)
          <li>
            @include('admin.ability.components.item-text', ['item' => $abl])
          </li>
          @endforeach
        </ul>
        @else
        @von
        @endif
      </div>
      <div class="tab-pane fade" id="role-users-{{ $rand }}" role="tabpanel">
        @if(count($role->users) > 0)
        <ol>
          @foreach($role->users as $user)
          <li>{{ $user->name }} ({{ $user->email }})</li>
          @endforeach
        </ol>
        @else
        @von
        @endif
      </div>
    </div>
  </div>
</div>
</div>
@endsection