<?php
$append_breadcrumb = [
  [
    'text'    => $app->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.detail'))

@include('admin.app.detail-inner', ['hide_status' => false])
@include('admin.app.changes.pending')

@section('content')
<div class="d-flex flex-wrap text-nowrap mb-1">
  <div class="details-nav-left mr-auto mb-1">
    <a href="{{ route('admin.apps.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
    <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-sm btn-primary">
      <span class="fas fa-edit"></span>
      {{ __('admin/apps.edit_app_info') }}
    </a>
    <a href="{{ route('admin.apps.visuals', ['app' => $app->id]) }}" class="btn btn-sm btn-info">
      <span class="fas fa-photo-video"></span>
      {{ __('admin/apps.edit_visuals') }}
    </a>
  </div>
  <div class="details-nav-right ml-auto mb-1">
  </div>
</div>
<!-- Card -->
<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap align-items-center">
      <div class="mr-auto">
        <h4 class="mb-0 text-primary d-inline-block">{{ $app->complete_name }}</h4>
        @if($app->is_verified)
        <br>
        <a href="{{ $app->public_url }}" class="btn btn-xs btn-default px-2" target="_blank">
          @lang('admin/apps.view_public_page')
          <span class="fas fa-globe-americas ml-1"></span>
        </a>
        @endif
      </div>
      <div class="text-right ml-auto">
        @if($app->has_committed)
        <span class="text-bold">
          @lang('admin/apps.changes.version_x', ['x' => $app->version_number])
        </span>
        @else
        <span class="text-bold">
          @lang('admin/apps.this_new_item_is_waiting_verification')
        </span>
        @endif
        @if($app->has_floating_changes)
        <br>
        <button class="btn btn-xs btn-warning btn-pending-changes-show" data-app-id="{{ $app->id }}" data-current-version="{{ $app->version_number }}" data-accumulate-changes="false">
          <span class="fas fa-clock"></span>
          @lang('admin/apps.show_pending_changes')
        </button>
        @endif
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="row gutter-lg app-detail-panels">
      <div class="col-12 col-md-4 side-panel right">
        <div class="mb-2 text-center">
          @if($app->has_verifications)
          <a href="{{ route('admin.apps.verifications', ['app' => $app->id]) }}" class="btn btn-app text-dark">
            <span class="fas fa-clipboard-check"></span>
            {{ __('admin/apps.verifications') }}
          </a>
          @endif
          @if($app->has_history)
          <a href="{{ route('admin.apps.changes', ['app' => $app->id, 'current' => '']) }}" class="btn btn-app text-dark">
            <span class="fas fa-history"></span>
            {{ __('admin/apps.history') }}
          </a>
          @endif
        </div>

        @if($app->last_verification->status->by == 'verifier')
        <div class="last-verif-info">
          <div class="lead font-weight-normal">@lang('admin/app_verifications.last_verification')</div>
          @include('admin.app_verification.components.verif-list-item', ['verif' => $app->last_verification, 'other_comments' => true, 'item_class' => 'text-090'])
        </div>
        @endif
      </div>

      <div class="col-12 col-md-8 content-panel">
        @if($app->last_verification->status->by == 'verifier')
        @if($app->last_verification->status_id == 'revision-needed')
        <div class="alert alert-warning">
          <div class="icon-text-pair icon-2x icon-color-reset">
            <span class="fas fa-exclamation-circle icon"></span>
            <div>
              @nl2br(__('admin/apps.messages.last_verification_revision-needed'))
              <br>
              <a href="#" class="text-black btn-flash-elm" data-flash-target=".last-verif-info">@lang('admin/apps.messages.check_verification_details')</a>
            </div>
          </div>
        </div>
        @elseif($app->last_verification->status_id == 'rejected')
        <div class="alert alert-danger">
          <div class="icon-text-pair icon-2x icon-color-reset">
            <span class="fas fa-exclamation-circle icon"></span>
            <div>
              @nl2br(__('admin/apps.messages.last_verification_rejected'))
              <br>
              <a href="#" class="text-white btn-flash-elm" data-flash-target=".last-verif-info">@lang('admin/apps.messages.check_verification_details')</a>
            </div>
          </div>
        </div>
        @elseif($app->last_verification->status_id == 'approved' && $app->has_approved_changes)
        <div class="callout callout-success py-2">
          @lang('admin/apps.your_app\'s_edits_version_x_has_been_approved', ['x' => $app->approved_changes->last()->version])
          <br>
          <a href="{{ route('admin.apps.publish', ['app' => $app->id]) }}" class="btn btn-success text-white btn-sm mt-1">@lang('admin/apps.publish_edits') &raquo;</a>
        </div>
        @endif
        @endif

        @yield('detail-content')
      </div>
    </div>
  </div>
  <!-- /.card-body -->
</div>
<!-- /.card -->
@endsection

@include('admin.app_verification.btn-view-verif')

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
  @if(request()->has('show_verification'))
  // Put in a slight timeout so that everything else finishes first
  setTimeout(function() {
    Helpers.scrollAndFlash($(".last-verif-info"), { animate: true });
  }, 10);
  @elseif(request()->has('show_pending'))
  // Put in a slight timeout so that everything else finishes first
  setTimeout(function() {
    $(".btn-pending-changes-show").trigger("click");
  }, 10);
  @endif
});
</script>
@endpush
