<?php
$append_breadcrumb = [
  [
    'text'    => $user->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.tab_title.detail', ['x' => text_truncate($user->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/users.page_title.detail'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.users.index', ['goto_item' => $user->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/users.edit_user') }}
  </a>
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
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
                  <dd>{{ $user->name }}</dd>
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
        @if(!$user->is_system)
        <a href="{{ route('admin.users.edit', ['user' => $user->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
          <span class="fas fa-edit"></span>
          {{ __('admin/users.edit_user') }}
        </a>
        @endif
      </div>
      @endif
    </div>
  </div>
</div>
@endsection