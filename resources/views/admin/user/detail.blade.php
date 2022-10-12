<?php
$append_breadcrumb = [
  [
    'text'    => $user->name,
  ]
];
$rand = random_string(5);
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.tab_title.detail', ['x' => text_truncate($user->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/users.page_title.detail'))

@section('content')
<div class="mb-2">
  @can('view-any', App\User::class)
  <a href="{{ route('admin.users.index', ['goto_item' => $user->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
  @can('update', $user)
  <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/users.edit_user') }}
  </a>
  @endcan
  @can('delete', $user)
  <a href="{{ route('admin.users.destroy', ['user' => $user->id]) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/users._self'), $user->name, __('admin/common.fields.id'), $user->id) }}">
    <span class="fas fa-trash mr-1"></span>
    {{ __('common.delete') }}
  </a>
  @endcan
</div>
<div class="main-content">
<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" id="user-tablist-{{ $rand }}" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="#user-info-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/users.user_data') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#user-roles-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/users.user_roles') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#user-abilities-{{ $rand }}" data-toggle="tab" role="tab">{{ __('admin/users.user_abilities') }}</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content" id="user-tabs-{{ $rand }}">
      <div class="tab-pane fade show active" id="user-info-{{ $rand }}" role="tabpanel">
        <dl class="details-dl">
          <div class="table-responsive">
            <table class="valign-top">
              <tbody>
                <tr>
                  <td class="pr-5">
                    <dt>@lang('admin/common.fields.id')</dt>
                    <dd>{{ $user->id }}</dd>
                  </td>
                  <td class="pr-5">
                    <dt>@lang('admin/users.fields.entity_type')</dt>
                    <dd class="@if($user->is_system) text-italic @endif">{{ $user->entity_type }}</dd>
                  </td>
                </tr>
                <tr>
                  <td class="pr-5">
                    <dt>@lang('admin/common.fields.name')</dt>
                    @if($user->is_system)
                    <dd>
                      <abbr title="{{ $user->name }}" data-toggle="tooltip">{{ $user->raw_name }}</abbr>
                    </dd>
                    @else
                    <dd>{{ $user->name }}</dd>
                    @endif
                  </td>
                  <td class="pr-5">
                    <dt>@lang('admin/users.fields.email')</dt>
                    <dd>{{ $user->email }}</dd>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <dt>@lang('admin/users.fields.prodi')</dt>
          <dd>@von($user->prodi->complete_name)</dd>

          <dt>@lang('admin/common.fields.number_of_apps')</dt>
          <dd class="@if($user->apps_count == 0) text-secondary @endif">
            @if(!$user->is_system)
            {{ $user->apps_count }}
            @if($user->apps_count > 0)
            <a href="{{ route('admin.apps.index', ['user' => $user->id]) }}" class="text-secondary px-1 py-1 ml-2" title="@lang('admin/users.see_apps_by_this_user')" data-toggle="tooltip"><span class="fas fa-folder-open"></span> TODO</a>
            @endif
            @else
            @vo_
            @endif
          </dd>

          <dt>@lang('admin/common.fields.last_updated')</dt>
          <dd>@include('components.date-with-tooltip', ['date' => $user->updated_at])</dd>
        </dl>
        @if($ajax)
        <div class="mt-2">
          @can('update', $user)
          <a href="{{ route('admin.users.edit', ['user' => $user->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
            <span class="fas fa-edit"></span>
            {{ __('admin/users.edit_user') }}
          </a>
          @endcan
          @can('delete', $user)
          <a href="{{ route('admin.users.destroy', ['user' => $user->id, 'backto' => 'back']) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/users._self'), $user->name, __('admin/common.fields.id'), $user->id) }}">
            <span class="fas fa-trash mr-1"></span>
            {{ __('common.delete') }}
          </a>
          @endcan
        </div>
        @endif
      </div>
      <div class="tab-pane fade" id="user-roles-{{ $rand }}" role="tabpanel">
        @if(count($user->roles) > 0)
        <ol>
          @foreach($user->roles as $role)
          <li>{{ $role->title }} ({{ $role->name }})</li>
          @endforeach
        </ol>
        @else
        @von
        @endif
      </div>
      <div class="tab-pane fade" id="user-abilities-{{ $rand }}" role="tabpanel">
        <div>
          <h5>{{ __('admin/users.user_roles_abilities') }} ({{ count($user->roles_abilities) }})</h5>
          @if(count($user->roles_abilities) > 0)
          <ul>
            @foreach($user->roles_abilities as $abl)
            <li>
              @include('admin.ability.components.item-text', ['item' => $abl])
            </li>
            @endforeach
          </ul>
          @else
          @von
          @endif
        </div>
        @if(count($user->abilities) > 0)
        <hr>
        <div>
          <h5>{{ __('admin/users.user_direct_abilities') }} ({{ count($user->abilities) }})</h5>
          <ul>
            @foreach($user->abilities as $abl)
            <li>
              @include('admin.ability.components.item-text', ['item' => $abl])
            </li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
</div>
@endsection