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
      'text'    => text_truncate($prodi->name, 50),
      'url'     => route('admin.prodi.show', ['prodi' => $prodi->id]),
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
{{ __('admin/prodi.tab_title.edit', ['x' => text_truncate($prodi->name, 20)]) }} - @parent
@else
{{ __('admin/prodi.page_title.add') }} - @parent
@endif
@endsection

@section('page-title', __('admin/prodi.page_title.'. ($is_edit ? 'edit' : 'add')) )

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

<form method="POST" action="{{ $action }}" id="formInputProdi">
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
            <label for="inputProdiName">{{ __('admin/common.fields.name') }} @include('components.label-mandatory')</label>
            <input type="text" name="name" class="form-control" id="inputProdiName" placeholder="{{ __('admin/prodi.fields.name_placeholder') }}" value="{{ old('name', $prodi->name) }}" maxlength="100" required>
          </div>

          <div class="form-group">
            <label for="inputProdiShortName">{{ __('admin/common.fields.short_name') }}</label>
            <input type="text" name="short_name" class="form-control" id="inputProdiShortName" placeholder="{{ __('admin/prodi.fields.short_name_placeholder') }}" value="{{ old('short_name', $prodi->short_name) }}" maxlength="20">
          </div>

          <div class="form-group">
            <label for="inputProdiDescription">{{ __('admin/common.fields.description') }}</label>
            <textarea name="description" class="form-control" id="inputProdiDescription" placeholder="{{ __('admin/prodi.fields.description_placeholder') }}" rows="3" maxlength="500" style="max-height: 300px;">{{ old('description', $prodi->description) }}</textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            @if($is_edit)
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
            @else
            <button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/prodi.add_prodi') }}</button>
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

  $("#inputProdiDescription").textareaAutoHeight().textareaShowLength();

  $("#formInputProdi").noEnterSubmit();
});
</script>

@endpush
