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
      'text'    => text_truncate($model->name, 50),
      'url'     => route('admin.users.show', ['user' => $model->id]),
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
{{ __('admin/users.tab_title.edit', ['x' => text_truncate($model->name, 20)]) }} - @parent
@else
{{ __('admin/users.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/users.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="mb-2">
  @if($back)
  @if($is_edit)
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  @else
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endif
  @endif
</div>

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formInputUser">
  @csrf
  @method($method)

  <input type="hidden" name="backto" value="{{ $backto }}">

  @include('components.page-message', ['show_errors' => true])

  <!-- Card -->
  <div class="card main-content scroll-to-me">
    <div class="card-body">
      <div class="row gutter-lg">
        <div class="col-12 col-md-6">
          <div class="form-group">
            <label for="inputUserName">
              {{ __('admin/common.fields.name') }}
              @include('admin.user.components.is-me-icon', ['user' => $model])
            </label>
            <input type="text" name="name" class="form-control" id="inputUserName" placeholder="{{ __('admin/users.fields.name_placeholder') }}" value="{{ old('name', $model->name) }}" maxlength="100" required>
          </div>

          <div class="form-group">
            <label for="inputUserEmail">{{ __('admin/users.fields.email') }}</label>
            <input type="text" name="email" class="form-control" id="inputUserEmail" placeholder="{{ __('admin/users.fields.email_placeholder') }}" value="{{ old('email', $model->email) }}" maxlength="200" required>
          </div>

          <div class="form-group">
            <label for="inputUserProdi">{{ __('admin/users.fields.prodi') }}</label>
            @if($view_mode == 'all')
            <select name="prodi_id" class="form-control" id="inputUserProdi">
              <option value="" class="text-muted">&ndash; {{ __('admin/users.fields.prodi_placeholder') }} &ndash;</option>
              @forelse($prodis as $prodi)
              <option value="{{ $prodi->id }}" {!! old_selected('prodi_id', $model->prodi_id, $prodi->id) !!}>{{ $prodi->complete_name }}</option>
              @empty
              <option value="" class="text-muted" disabled>&ndash; {{ __('admin/prodi.no_prodi_yet') }} &ndash;</option>
              @endforelse
            </select>
            @else
            <p class="form-control-plaintext" id="inputUserProdi">@vo_($prodis)</p>
            @endif
          </div>

          @if(!$is_edit)
          <div class="form-group">
            <label for="inputUserPassword">
              {{ __('admin/users.fields.password') }}
              @component('admin.slots.label-hint')
              @lang('admin/users.fields.password_hint', ['min' => 5, 'max' => 50])
              @endcomponent
            </label>
            <div class="input-group password-wrapper">
              <input type="password" name="password" class="form-control text-monospace" id="inputUserPassword" placeholder="{{ __('admin/users.fields.password_placeholder') }}" value="" minlength="5" maxlength="50" autocomplete="off" required>
              <div class="input-group-append">
                <a href="#" class="input-group-text plain btn-see-password" data-targets="#inputUserPassword, #inputUserPasswordConfirmation"><span class="far fa-eye"></span></a>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="inputUserPasswordConfirmation">{{ __('admin/users.fields.password_confirmation') }}</label>
            <input type="password" name="password_confirmation" class="form-control text-monospace" id="inputUserPasswordConfirmation" placeholder="{{ __('admin/users.fields.password_confirmation_placeholder') }}" value="" autocomplete="off" required>
          </div>
          @endif
        </div>
        <div class="col-12 col-md-6">
          <div class="form-group">
            <label for="inputUserRoles">{{ __('admin/users.fields.roles') }}</label>
            @if($allow_role)
            <div class="ofy-auto list-group user-roles" id="inputUserRoles" style="max-height: 250px;">
              @foreach($roles as $role)
              <label class="list-group-item list-group-item-action role-item cursor-pointer py-1 pr-2 pl-3 m-0" for="input-role-check-{{ $role->id }}">
                <input type="hidden" name="roles[{{ $role->id }}][id]" class="input-role-id" value="{{ $role->id }}" id="input-role-id-{{ $role->id }}">
                <div class="form-check">
                  <input type="checkbox" name="roles[{{ $role->id }}][check]" class="form-check-input input-role-check" value="{{ $role->id }}" id="input-role-check-{{ $role->id }}" {!! old_checked('roles.'.$role->id.'.check', $model->roles_ids, $role->id) !!}>
                  <span class="d-inline-block form-check-label input-role-text text-unbold">{{ $role->title }} ({{ $role->name }})</span>
                </div>
              </label>
              @endforeach
            </div>
            @else
            <p class="form-control-plaintext" id="inputUserRole">@vo_($model->roles_text)</p>
            @endif
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/users.add_user') }}</button>
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

@push('scripts')

<script>
jQuery(document).ready(function($) {

  var $checkAllAbilities = $("#input-role-all");
  $checkAllAbilities.on("change", function(e) {
    $(".input-role-check").prop("checked", this.checked);
  });


  // === Initial states
  $(".user-roles").find(".input-role-check").trigger("change");

});
</script>

@endpush
