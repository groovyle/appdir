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
      'text'    => text_truncate($stt->key, 50),
      'url'     => route('admin.settings.show', ['stt' => $stt->key]),
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
{{ __('admin/settings.tab_title.edit', ['x' => text_truncate($stt->key, 20)]) }} - @parent
@else
{{ __('admin/settings.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/settings.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="alert alert-warning">
  <div class="icon-text-pair icon-color-reset">
    <span class="fas fa-exclamation-triangle icon icon-2x mt-2 mr-2"></span>
    <span>@lang('admin/settings.management_warning')</span>
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

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formInputSetting">
  @csrf
  @method($method)

  <input type="hidden" name="backto" value="{{ $backto }}">

  @include('components.page-message', ['show_errors' => true])

  <!-- Card -->
  <div class="card main-content scroll-to-me">
    <div class="card-body">
      <div class="row gutter-lg">
        <div class="col-12 col-md-8 col-xl-6 mx-auto">
          <div class="form-group">
            <label for="inputSttKey">
              {{ __('admin/settings.fields.key') }}
              @include('components.label-mandatory')
              @component('admin.slots.label-hint')
              @lang('admin/settings.fields.key_hint')
              @endcomponent
            </label>
            <input type="text" name="key" class="form-control text-monospace" id="inputSttKey" placeholder="{{ __('admin/settings.fields.key_placeholder') }}" value="{{ old('key', $stt->key) }}" maxlength="200" required>
          </div>

          <div class="form-group">
            <label for="inputSttValue">
              {{ __('admin/settings.fields.value') }}
              @component('admin.slots.label-hint')
              @lang('admin/settings.fields.value_hint')
              @endcomponent
            </label>
            <textarea name="value" class="form-control text-monospace" id="inputSttValue" placeholder="{{ __('admin/settings.fields.value_placeholder') }}" rows="1" maxlength="500" style="max-height: 300px;">{{ old('value', $stt->value) }}</textarea>
          </div>

          <div class="form-group">
            <label for="inputSttDescription">{{ __('admin/common.fields.description') }}</label>
            <textarea name="description" class="form-control" id="inputSttDescription" placeholder="{{ __('admin/settings.fields.description_placeholder') }}" rows="2" maxlength="1000" style="max-height: 300px;">{{ old('description', $stt->description) }}</textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/settings.add_setting') }}</button>
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

  $("#inputSttValue").textareaAutoHeight({
    bypassHeight: true,
  }).trigger("input");
  $("#inputSttDescription").textareaAutoHeight().textareaShowLength();

});
</script>

@endpush
