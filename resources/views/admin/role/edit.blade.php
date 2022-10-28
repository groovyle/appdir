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
      'text'    => text_truncate($role->name, 50),
      'url'     => route('admin.roles.show', ['role' => $role->id]),
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
{{ __('admin/roles.tab_title.edit', ['x' => text_truncate($role->name, 20)]) }} - @parent
@else
{{ __('admin/roles.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/roles.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="alert alert-warning">
  <div class="icon-text-pair icon-color-reset">
    <span class="fas fa-exclamation-triangle icon icon-2x mt-2 mr-2"></span>
    <span>@lang('admin/roles.management_warning')</span>
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
            <label for="inputRoleId">{{ __('admin/common.fields.id') }}</label>
            <p class="form-control-plaintext" id="inputRoleId">{{ $role->id }}</p>
          </div>
          @endif

          <div class="form-group">
            <label for="inputRoleName">
              {{ __('admin/roles.fields.name') }}
              @if($is_edit)
                @component('admin.slots.label-hint', ['icon' => 'fas fa-info-circle'])
                @lang('admin/roles.fields.name_hint_edit')
                @endcomponent
              @else
                @component('admin.slots.label-hint')
                @lang('admin/roles.fields.name_hint')
                @endcomponent
              @endif
            </label>
            @if(!$is_edit)
            <input type="text" name="name" class="form-control" id="inputRoleName" placeholder="{{ __('admin/roles.fields.name_placeholder') }}" value="{{ old('name', $role->name) }}" maxlength="100" required>
            @else
            <p class="form-control-plaintext" id="inputRoleName">{{ $role->name }}</p>
            @endif
          </div>

          <div class="form-group">
            <label for="inputRoleTitle">
              {{ __('admin/roles.fields.title') }}
              @component('admin.slots.label-hint')
              @lang('admin/roles.fields.title_hint')
              @endcomponent
            </label>
            <input type="text" name="title" class="form-control" id="inputRoleTitle" placeholder="{{ __('admin/roles.fields.title_placeholder') }}" value="{{ old('title', $role->title) }}" maxlength="200">
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="form-group">
            <label for="inputRoleAbilities">{{ __('admin/roles.fields.abilities') }}</label>
            <div class="d-inline-block form-check ml-3">
              <input type="checkbox" class="form-check-input" id="input-abl-all" value="1" autocomplete="off">
              <label class="form-check-label" for="input-abl-all">{{ __('admin/common.check_all') }}</label>
            </div>
            <div class="ofy-auto list-group role-abilities" id="inputRoleAbilities" style="max-height: 250px;">
              @foreach($abilities as $abl)
              <label class="list-group-item list-group-item-action ability-item cursor-pointer py-1 pr-2 pl-3 m-0 w-auto" for="input-abl-check-{{ $abl->id }}">
                <input type="hidden" name="abilities[{{ $abl->id }}][id]" class="input-abl-id" value="{{ $abl->id }}" id="input-abl-id-{{ $abl->id }}">
                <div class="form-check">
                  <span class="icon-text-pair">
                    <span class="icon d-inline-block">
                      <input type="checkbox" name="abilities[{{ $abl->id }}][check]" class="form-check-input input-abl-check" value="{{ $abl->id }}" id="input-abl-check-{{ $abl->id }}" {!! old_checked('abilities.'.$abl->id.'.check', $role->abilities_ids, $abl->id) !!}>
                      <select name="abilities[{{ $abl->id }}][mode]" class="form-control form-control-xs d-inline-block w-auto input-abl-mode mr-1">
                        <option value="allow" {!! old_selected('abilities.'.$abl->id.'.mode', $role->abilities_modes[$abl->id] ?? null, 'allow') !!}>{{ __('admin/abilities.details.mode_allow') }}</option>
                        <option value="forbid" {!! old_selected('abilities.'.$abl->id.'.mode', $role->abilities_modes[$abl->id] ?? null, 'forbid') !!}>{{ __('admin/abilities.details.mode_forbid') }}</option>
                      </select>
                    </span>
                    <span class="d-inline-block form-check-label input-abl-text text-090 text-unbold">
                      @vo_($abl->entity_type) | @vo_($abl->entity_id) | @vo_($abl->name) (@vo_($abl->title))
                      <!-- @include('admin.ability.components.item-text', ['item' => $abl]) -->
                    </span>
                  </span>
                </div>
              </label>
              @endforeach
            </div>
          </div>

          <div class="form-group">
            <label for="inputRoleUsers">{{ __('admin/roles.fields.users') }}</label>
            <select class="form-control" name="users[]" id="inputRoleUsers" multiple></select>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/roles.add_role') }}</button>
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

  var $inputRoleUsers = $("#inputRoleUsers");
  $inputRoleUsers.select2({
    multiple: true,
    closeOnSelect: true,
    selectOnClose: false,
    allowClear: true,
    placeholder: @json(__('admin/roles.fields.users_placeholder')),
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

  var $roleAbilities = $(".role-abilities");
  $roleAbilities.on("change", ".input-abl-check, .input-abl-mode", function(e) {
    var $check = $(this).closest(".ability-item").find(".input-abl-check");
    var $mode = $(this).closest(".ability-item").find(".input-abl-mode");
    var $text = $(this).closest(".ability-item").find(".input-abl-text");
    $text.toggleClass("text-danger", $check.prop("checked") && $mode.val() == "forbid");
  });

  var $checkAllAbilities = $("#input-abl-all");
  $checkAllAbilities.on("change", function(e) {
    $(".input-abl-check").prop("checked", this.checked).trigger("change");
  });


  // === Initial states
  $(".role-abilities").find(".input-abl-check, .input-abl-mode").trigger("change");

  // Pre-populate users
  var oldUsers = @json(old('users', $role->users_ids));
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
          $inputRoleUsers.append(opt);
        });
        $inputRoleUsers.trigger("change");
      },
    });
  }
});
</script>
@endpush
