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
      'text'    => text_truncate($tag->name, 50),
      'url'     => route('admin.app_tags.show', ['tag' => $tag->name]),
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
{{ __('admin/app_tags.tab_title.edit', ['x' => text_truncate($tag->name, 20)]) }} - @parent
@else
{{ __('admin/app_tags.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/app_tags.page_title.'. ($is_edit ? 'edit' : 'add')) )

@section('content')

<div class="mb-2">
  @if($is_edit)
  <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  @else
  <a href="{{ route('admin.app_tags.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endif
</div>

<form method="POST" action="{{ $action }}" id="formInputTag">
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
            <label for="inputCatName">{{ __('admin/common.fields.name') }}</label>
            <input type="text" name="name" class="form-control" id="inputCatName" placeholder="{{ __('admin/app_tags.fields.name_placeholder') }}" value="{{ old('name', $tag->name) }}" maxlength="100" required>
          </div>

          <div class="form-group">
            <label for="inputCatDescription">{{ __('admin/common.fields.description') }}</label>
            <textarea name="description" class="form-control" id="inputCatDescription" placeholder="{{ __('admin/app_tags.fields.description_placeholder') }}" rows="3" maxlength="500" style="max-height: 300px;">{{ old('description', $tag->description) }}</textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/app_tags.add_tag') }}</button>
            @endif
            <br>
            <a href="{{ $back }}" class="btn btn-default btn-sm mt-3">{{ __('common.cancel') }}</a>
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

  $("#inputCatDescription").textareaAutoHeight().textareaShowLength();

  $("#formInputTag").noEnterSubmit();
});
</script>

@endpush
