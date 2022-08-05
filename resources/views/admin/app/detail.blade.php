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

@include('admin.app.detail-inner')

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
    @if($app->has_history)
    <a href="{{ route('admin.apps.changes', ['app' => $app->id, 'current' => '']) }}" class="btn btn-sm bg-purple">
      <span class="fas fa-tasks"></span>
      {{ __('admin/apps.changelog') }}
    </a>
    @endif
  </div>
</div>
<!-- Card -->
<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap align-items-center">
      <h4 class="text-primary mb-0 mr-auto">{{ $app->name }}</h4>
      <div class="text-right ml-auto">
        @if($app->has_history)
        <span class="text-bold">
          @lang('admin/apps.changes.version_x', ['x' => $app->version_number])
        </span>
        @endif
        @if($app->has_pending_changes)
        <br>
        <button class="btn btn-xs btn-warning btn-pending-changes-show" data-app-id="{{ $app->id }}">
          <span class="fas fa-clock"></span>
          {{ __('admin/apps.show_pending_changes') }}
        </button>
        @endif
      </div>
    </div>
  </div>
  <div class="card-body">
    @yield('detail-content')
  </div>
  <!-- /.card-body -->
</div>
<!-- /.card -->
@endsection
