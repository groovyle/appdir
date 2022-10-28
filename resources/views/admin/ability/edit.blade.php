<?php
if(!$is_edit) {
  $append_breadcrumb = [
    [
      'text'    => __('common.add'),
    ]
  ];
} else {
  $append_breadcrumb = [
    [
      'text'    => text_truncate($abl->title, 50),
      'url'     => route('admin.abilities.show', ['abl' => $abl->id]),
      'active'  => false,
    ],
    [
      'text'    => __('common.edit'),
    ]
  ];
}
?>

@extends('admin.layouts.main')

@section('title')
@if($is_edit)
{{ __('admin/abilities.tab_title.edit', ['x' => text_truncate($abl->title, 20)]) }} - @parent
@else
{{ __('admin/abilities.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/abilities.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="alert alert-warning">
  <div class="icon-text-pair icon-color-reset">
    <span class="fas fa-exclamation-triangle icon icon-2x mt-2 mr-2"></span>
    <span>@lang('admin/abilities.management_warning')</span>
  </div>
</div>

<div class="mb-2">
  @if($back)
  @if($is_edit)
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  @else
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endif
  @endif
</div>

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formInputRole">
  @csrf
  @method($method)

  <input type="hidden" name="backto" value="{{ $backto }}">

  @include('components.page-message', ['show_errors' => true])

  <!-- Card -->
  <div class="card main-content scroll-to-me">
    <div class="card-body">
      <div class="row gutter-lg">
        <div class="col-12 col-md-8 col-xl-6">
          @if($is_edit)
          <div class="form-group">
            <label for="inputAbilityId">{{ __('admin/common.fields.id') }}</label>
            <p class="form-control-plaintext" id="inputAbilityId">{{ $abl->id }}</p>
          </div>
          @endif

          <div class="form-group">
            <label for="inputAbilityEntityType">{{ __('admin/abilities.fields.entity_type') }}</label>
            <input type="text" name="entity_type" class="form-control" id="inputAbilityEntityType" placeholder="{{ __('admin/abilities.fields.entity_type') }}" value="{{ old('entity_type', $abl->entity_type) }}" maxlength="100">
          </div>

          <div class="form-group">
            <label for="inputAbilityEntityId">{{ __('admin/abilities.fields.entity_id') }}</label>
            <input type="text" name="entity_id" class="form-control" id="inputAbilityEntityId" placeholder="{{ __('admin/abilities.fields.entity_id') }}" value="{{ old('entity_id', $abl->entity_id) }}" maxlength="100">
          </div>

          <div class="form-group">
            <label for="inputAbilityName">
              {{ __('admin/abilities.fields.name') }}
              @component('admin.slots.label-hint')
              @lang('admin/abilities.fields.name_hint')
              @endcomponent
            </label>
            <input type="text" name="name" class="form-control" id="inputAbilityName" placeholder="{{ __('admin/abilities.fields.name_placeholder') }}" value="{{ old('name', $abl->name) }}" maxlength="100" required>
          </div>

          {{--
          <div class="form-group">
            <label for="inputAbilityName">
              {{ __('admin/abilities.fields.name') }}
              @if($is_edit)
                @component('admin.slots.label-hint', ['icon' => 'fas fa-info-circle'])
                @lang('admin/abilities.fields.name_hint_edit')
                @endcomponent
              @else
                @component('admin.slots.label-hint')
                @lang('admin/abilities.fields.name_hint')
                @endcomponent
              @endif
            </label>
            @if(!$is_edit)
            <input type="text" name="name" class="form-control" id="inputAbilityName" placeholder="{{ __('admin/abilities.fields.name_placeholder') }}" value="{{ old('name', $abl->name) }}" maxlength="100" required>
            @else
            <p class="form-control-plaintext" id="inputAbilityName">{{ $abl->name }}</p>
            @endif
          </div>
          --}}

          <div class="form-group">
            <label for="inputAbilityTitle">
              {{ __('admin/abilities.fields.title') }}
              @component('admin.slots.label-hint')
              @lang('admin/abilities.fields.title_hint')
              @endcomponent
            </label>
            <input type="text" name="title" class="form-control" id="inputAbilityTitle" placeholder="{{ __('admin/abilities.fields.title_placeholder') }}" value="{{ old('title', $abl->title) }}" maxlength="200">
          </div>

          <div class="form-group">
            <label for="inputAbilityOnlyOwned">{{ __('admin/abilities.fields.only_owned') }}</label>
            <input type="checkbox" name="only_owned" value="1" class="ml-2" id="inputAbilityOnlyOwned" {!! old_checked('only_owned', $abl->only_owned) !!}>
          </div>

        </div>
        <div class="col-12 col-md-6">
          <div class="form-group">
            <label for="inputAbilityRoles">{{ __('admin/abilities.fields.roles') }}</label>
            {{--
            <div class="d-inline-block form-check ml-3">
              <input type="checkbox" class="form-check-input" id="input-role-all" value="1" autocomplete="off">
              <label class="form-check-label" for="input-role-all">{{ __('admin/common.check_all') }}</label>
            </div>
            --}}
            <div class="ofy-auto list-group ability-roles" id="inputAbilityRoles" style="max-height: 250px;">
              @foreach($roles as $role)
              <label class="list-group-item list-group-item-action role-item cursor-pointer py-1 pr-2 pl-3 m-0" for="input-role-check-{{ $role->id }}">
                <input type="hidden" name="roles[{{ $role->id }}][id]" class="input-role-id" value="{{ $role->id }}" id="input-role-id-{{ $role->id }}">
                <div class="form-check">
                  <span class="icon-text-pair">
                    <span class="icon d-inline-block">
                      <input type="checkbox" name="roles[{{ $role->id }}][check]" class="form-check-input input-role-check" value="{{ $role->id }}" id="input-role-check-{{ $role->id }}" {!! old_checked('roles.'.$role->id.'.check', $abl->roles_ids, $role->id) !!}>
                      <select name="roles[{{ $role->id }}][mode]" class="form-control form-control-xs d-inline-block w-auto input-role-mode mr-1">
                        <option value="allow" {!! old_selected('roles.'.$role->id.'.mode', $abl->roles_modes[$role->id] ?? null, 'allow') !!}>{{ __('admin/abilities.details.mode_allow') }}</option>
                        <option value="forbid" {!! old_selected('roles.'.$role->id.'.mode', $abl->roles_modes[$role->id] ?? null, 'forbid') !!}>{{ __('admin/abilities.details.mode_forbid') }}</option>
                      </select>
                    </span>
                    <span class="d-inline-block form-check-label input-role-text text-unbold">{{ $role->title }} ({{ $role->name }})</span>
                  </span>
                </div>
              </label>
              @endforeach
            </div>
          </div>

          <div class="form-group">
            <label for="inputAbilityUsers">
              {{ __('admin/abilities.fields.users') }}
              @component('admin.slots.label-hint', ['icon' => 'fas fa-exclamation-circle', 'color' => 'text-danger'])
              @lang('admin/abilities.fields.users_hint')
              @endcomponent
            </label>
            <select class="form-control" name="users[]" id="inputAbilityUsers" multiple></select>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/abilities.add_ability') }}</button>
            @endif
            @if($back)
            <br>
            <a href="{{ $back }}" class="btn btn-default btn-sm mt-3">{{ __('common.cancel') }}</a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.card -->
</form>
@endsection

@include('libraries.select2')

@push('scripts')
<script>
jQuery(document).ready(function($) {

  var $form = $("#formInputRole");

  var $inputAbilityUsers = $("#inputAbilityUsers");
  $inputAbilityUsers.select2({
    multiple: true,
    closeOnSelect: true,
    selectOnClose: false,
    allowClear: true,
    placeholder: @json(__('admin/abilities.fields.users_placeholder')),
    ajax: {
      url: @json(route('admin.users.lookup')),
      delay: 500,
      dataType: "json",
      cache: true,
      method: "GET",
      data: function(params) {
        var query = {
          keyword: params.term,
          page: params.page || 1,
        };
        return query;
      },
      processResults: function(data, params) {
        return {
          results: data.data,
          total: data.total,
          pagination: {
            more: data.more,
          },
        };
      },
    },
  });

  var $ablAbilities = $(".ability-roles");
  $ablAbilities.on("change", ".input-role-check, .input-role-mode", function(e) {
    var $check = $(this).closest(".role-item").find(".input-role-check");
    var $mode = $(this).closest(".role-item").find(".input-role-mode");
    var $text = $(this).closest(".role-item").find(".input-role-text");
    $text.toggleClass("text-danger", $check.prop("checked") && $mode.val() == "forbid");
  });

  var $checkAllAbilities = $("#input-role-all");
  $checkAllAbilities.on("change", function(e) {
    $(".input-role-check").prop("checked", this.checked);
  });


  // === Initial states
  $(".ability-roles").find(".input-role-check, .input-role-mode").trigger("change");

  // Pre-populate users
  var oldUsers = @json(old('users', $abl->users_ids));
  if(oldUsers.length > 0) {
    $.ajax({
      url: @json(route('admin.users.lookup')),
      dataType: "json",
      cache: true,
      method: "GET",
      data: {
        ids: oldUsers.join(","),
      },
      success: function(data, status, xhr) {
        if(!data.success) return;
        data.data.forEach(function(item) {
          var opt = new Option(item.text, item.id, true, true);
          $inputAbilityUsers.append(opt);
        });
        $inputAbilityUsers.trigger("change");
      },
    });
  }
});
</script>
@endpush
