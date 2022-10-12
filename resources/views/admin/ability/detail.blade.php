<?php
$append_breadcrumb = [
  [
    'text'    => $abl->title,
  ]
];
$rand = random_string(5);
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/abilities.tab_title.detail', ['x' => text_truncate($abl->title, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/abilities.page_title.detail'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.abilities.index', ['goto_item' => $abl->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  <a href="{{ route('admin.abilities.edit', ['abl' => $abl->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/abilities.edit_ability') }}
  </a>
</div>
<div class="main-content">
<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" id="ability-tablist-{{ $rand }}" ability="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="#ability-info-{{ $rand }}" data-toggle="tab" ability="tab">{{ __('admin/abilities.ability_data') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#ability-roles-{{ $rand }}" data-toggle="tab" ability="tab">{{ __('admin/abilities.ability_roles') }} ({{ count($abl->roles) }})</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#ability-users-{{ $rand }}" data-toggle="tab" ability="tab">{{ __('admin/abilities.ability_users') }} ({{ count($abl->users) }})</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content" id="ability-tabs-{{ $rand }}">
      <div class="tab-pane fade show active" id="ability-info-{{ $rand }}" ability="tabpanel">
        <dl class="details-dl">
          <dt>@lang('admin/common.fields.id')</dt>
          <dd>{{ $abl->id }}</dd>

          <div class="row gutter-lg d-table">
            <div class="col d-table-cell w-auto">
              <dt>@lang('admin/abilities.fields.title')</dt>
              <dd>{{ $abl->title }}</dd>
            </div>
            <div class="col d-table-cell w-auto">
              <dt>@lang('admin/abilities.fields.name')</dt>
              <dd>{{ $abl->name }}</dd>
            </div>
          </div>

          <div class="row gutter-lg d-table">
            <div class="col d-table-cell w-auto">
              <dt>@lang('admin/abilities.fields.entity_type')</dt>
              <dd>@vo_($abl->entity_type)</dd>
            </div>
            <div class="col d-table-cell w-auto">
              <dt>@lang('admin/abilities.fields.entity_id')</dt>
              <dd>@vo_($abl->entity_id)</dd>
            </div>
          </div>

          <dt>@lang('admin/abilities.fields.only_owned')</dt>
          <dd>@include('admin.components.yesno', ['value' => $abl->only_owned, 'color_yes' => 'text-primary text-bold'])</dd>

          <dt>@lang('admin/abilities.fields.scope')</dt>
          <dd>@vo_($abl->scope)</dd>

          <dt>@lang('admin/abilities.fields.options')</dt>
          <dd>@vo_($abl->option)</dd>

          <dt>@lang('admin/common.fields.last_updated')</dt>
          <dd>@include('components.date-with-tooltip', ['date' => $abl->updated_at])</dd>
        </dl>
        @if($ajax)
        <div class="mt-2">
          <a href="{{ route('admin.abilities.edit', ['abl' => $abl->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
            <span class="fas fa-edit"></span>
            {{ __('admin/abilities.edit_ability') }}
          </a>
        </div>
        @endif
      </div>
      <div class="tab-pane fade" id="ability-roles-{{ $rand }}" ability="tabpanel">
        @if(count($abl->roles) > 0)
        <ol>
          @foreach($abl->roles as $role)
          <li>
            <span>{{ $role->title }} ({{ $role->name }})</span>
            @include('admin.ability.components.item-pivot-icons', ['pivot' => $role->pivot])
          </li>
          @endforeach
        </ol>
        @else
        @von
        @endif
      </div>
      <div class="tab-pane fade" id="ability-users-{{ $rand }}" ability="tabpanel">
        @if(count($abl->users) > 0)
        <ol>
          @foreach($abl->users as $user)
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