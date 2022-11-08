<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($model->name, 50),
    'url'     => route('admin.users.show', ['user' => $model->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/users.page_title.block_user'),
  ]
];
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.tab_title.block_user', ['x' => text_truncate($model->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/users.page_title.block_user'))

@section('content')

<div class="d-flex flex-wrap text-nowrap mb-1">
  <div class="details-nav-left mr-auto mb-1">
    @if($back)
    <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
    @endif
  </div>
  <div class="details-nav-right ml-auto mb-1">
    @if($model->all_blocks_count > 0)
    @can('view', $model)
    <a href="{{ route('admin.users.block_history', ['user' => $model->id]) }}" class="btn btn-sm bg-purple"><span class="fas fa-user-slash mr-1"></span> {{ __('admin/users.blocks_history') }}</a>
    @endcan
    @endif
  </div>
</div>

<div class="main-content">
<form method="POST" action="{{ route('admin.users.block.save', ['user' => $model->id]) }}" class="no-enter-submit" id="formBlockUser">
  @csrf
  @method('POST')

  <input type="hidden" name="backto" value="{{ $backto }}">

  @include('components.page-message', ['show_errors' => true])

  <!-- Card -->
  <div class="card main-content scroll-to-me">
    <div class="card-body">
      <div class="row gutter-lg">
        <div class="col mx-auto" style="max-width: 500px;">
          <div class="form-group">
            <label>{{ __('admin/users.fields.user') }}</label>
            <div class="user-panel d-flex align-items-center pb-2">
              <div class="image">
                <img src="{{ $model->profile_picture }}" class="img-circle elevation-2" alt="User Image" style="width: 3rem;">
              </div>
              <div class="info">
                <span class="d-block maxw-100 text-truncate">{{ $model->name_email }}</span>
                @if($model->prodi)
                <span class="d-block maxw-100 text-truncate text-secondary">{{ $model->prodi->name }}</span>
                @endif
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="inputBlockReason" class="text-danger">{{ __('admin/users.fields.block_reason') }} @include('components.label-mandatory')</label>
            <textarea name="reason" class="form-control" id="inputBlockReason" placeholder="{{ __('admin/users.fields.block_reason_placeholder') }}" rows="2" minlength="20" maxlength="200" style="max-height: 300px;" required>{{ old('reason') }}</textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="mt-4 text-center">
            <button type="submit" class="btn btn-dark btn-min-100">{{ __('admin/users.block_user') }}</button>
            @if($back && !$ajax)
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
</div>
@endsection

@push('scripts')

<script>
jQuery(document).ready(function($) {

  $("#inputBlockReason").textareaAutoHeight().textareaShowLength();


});
</script>

@endpush
