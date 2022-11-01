<?php
$page_title = __('admin/apps.page_title.switch_version');
$tab_title = 'admin/apps.tab_title.switch_version';
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/apps.page_title.changes'),
    'url'     => route('admin.apps.changes', ['app' => $app->id, 'go_version' => $version->version, 'go_flash' => 1]),
    'active'  => false,
  ],
  [
    'text'    => $page_title,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __($tab_title, ['x' => text_truncate($app->name, 20)]) }} - @parent
@endsection

@section('page-title')
{{ $page_title }}
<br><small class="text-primary">{{ $app->name }}</small>
@endsection

@section('content')
<div class="d-flex flex-wrap text-nowrap mb-1">
  <div class="details-nav-left mr-auto mb-1">
    @if($back)
    <a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
    @endcan
  </div>
</div>
<form method="POST" action="{{ route('admin.apps.switch_version.save', ['app' => $app->id, 'version' => $version->version]) }}" id="formSwitchVersion">

@include('components.page-message', ['show_errors' => true])

@csrf
@method('POST')

<input type="hidden" name="version" value="{{ $version->version }}" readonly>

<!-- Card -->
<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" role="tablist">
      <li class="pt-2 px-3 mt-1"><h3 class="card-title">@lang('admin/apps.compare'):</h3></li>
      <li class="nav-item">
        <a class="nav-link active" href="#app-comparison-new" id="app-comparison-new-tab" data-toggle="pill" role="tab"><strong>@lang('admin/apps.changes.target_version')</strong></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#app-comparison-old" id="app-comparison-old-tab" data-toggle="pill" role="tab">@lang('admin/apps.changes.current_version')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#app-comparison-changes" id="app-comparison-changes-tab" data-toggle="pill" role="tab">@lang('admin/apps.changes.summary_of_changes')</a>
      </li>
    </ul>
  </div>
  <div class="tab-content" id="app-comparison-tabpanes">
    <div class="tab-pane fade show active" role="tabpanel" id="app-comparison-new">
      <div class="card-body">
        <div class="mb-2">
          <h4 class="mb-0 text-primary">{{ $target->complete_name }}</h4>
          <span class="text-success text-bold">@lang('admin/apps.changes.version_x', ['x' => $target->version_number])</span>
        </div>
        @include('admin.app.detail-inner', ['section_id' => 'new', 'is_snippet' => true, 'app' => $target, 'ori' => null, 'hide_status' => true, 'hide_changes' => true, 'mark_changes' => 'text-success', 'mark_changes_mode' => 'old', 'version' => $summary])
        @yield('detail-content-new')
      </div>
    </div>
    <div class="tab-pane fade" role="tabpanel" id="app-comparison-old">
      <div class="card-body">
        <div class="mb-2">
          <h4 class="mb-0 text-primary">{{ $app->complete_name }}</h4>
          <span class="text-danger">@lang('admin/apps.changes.version_x', ['x' => $app->version_number])</span>
        </div>
        @include('admin.app.detail-inner', ['section_id' => 'old', 'is_snippet' => true, 'app' => $app, 'ori' => null, 'hide_status' => true, 'hide_changes' => true, 'mark_changes' => 'text-danger', 'mark_changes_mode' => 'new', 'version' => $summary])
        @yield('detail-content-old')
      </div>
    </div>
    <div class="tab-pane fade" role="tabpanel" id="app-comparison-changes">
      <div class="card-body">
        <h4 class="mb-1">@lang('admin/apps.changes.summary_of_changes')</h4>
        <p class="mb-2">@lang('admin/apps.changes.switching_from_version_x_to_y', ['x' => $app->version_number, 'y' => $version->version])</p>
        <div class="changes-item">
          <div class="changes-content">
            @include('admin.app.changes.list-item-body', ['cl' => $summary, 'app' => $app])
          </div>
        </div>
      </div>
      {{--
      @if(!empty($compiled_changes['versions']))
      <div class="card-body">
        <h4 class="m-0">
            @lang('admin/apps.changes.detailed_information_on_the_changes')
            <button type="button" class="btn btn-default btn-xs ml-2" data-toggle="collapse" data-target="#detailed-changes">@lang('common.show/hide')</button>
        </h4>
        <div class="mt-2 collapse collapse-scrollto" id="detailed-changes" data-scroll-offset="80">
          @foreach($compiled_changes['versions'] as $cl)
            @include('admin.app.changes.list-item')
          @endforeach
        </div>
      </div>
      @endif
      --}}
    </div>
  </div>
  <div class="card-footer">
    @if(!empty($compiled_changes['changes']))
    <div class="text-center">
      <p class="mb-0">@lang('admin/apps.changes.data_from_target_version_will_be_copied')</p>
      <p class="text-danger text-bold mb-3">@lang('admin/apps.changes.any_pending_changes_will_be_discarded')</p>
      <button type="submit" class="btn btn-primary btn-min-100">@lang('admin/apps.changes.switch_to_version_x', ['x' => $version->version])</button>
    </div>
    @else
    <div class="text-center">
      <p class="text-purple text-bold my-2">@lang('admin/apps.changes.cannot_switch_version_because_no_changes')</p>
    </div>
    @endif
  </div>
</div>
<!-- /.card -->
</form>
@endsection

@include('libraries.splide')
@include('admin.app.changes.btn-view-version')
@include('admin.app.changes.visuals-comparison')

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
  var $form = $("#formSwitchVersion");

});
</script>
@endpush
