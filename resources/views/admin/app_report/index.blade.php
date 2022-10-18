<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_reports.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/app_reports.page_title.index') }}
@if($view_mode == 'prodi')
<span class="page-sub-title text-r100">@lang('admin/app_reports.page_title.view_mode.'.$view_mode, ['x' => vo_($prodi->complete_name)])</span>
@elseif($view_mode != 'none')
<span class="page-sub-title text-r100">{{ __('admin/app_reports.page_title.view_mode.'.$view_mode) }}</span>
@endif
@endsection

@section('content')
  <!-- Filters -->
  <form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.app_reports.index') }}">
    <div class="card-header">
      <h3 class="card-title cursor-pointer" data-card-widget="collapse">{{ __('admin/common.filters') }}</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas @if($hide_filters) fa-plus @else fa-minus @endif"></i></button>
      </div>
    </div>
    <div class="card-body">
      <div class="form-horizontal">
        <div class="form-group row">
          <label for="fKeyword" class="col-sm-3 col-lg-2">{{ __('admin/common.keyword') }}</label>
          <div class="col-sm-8 col-lg-5">
            <input type="text" class="form-control" name="keyword" id="fKeyword" value="{{ $filters['keyword'] }}" placeholder="{{ __('admin/common.keyword') }}">
          </div>
        </div>
        <div class="form-group row">
          <label for="fStatus" class="col-sm-3 col-lg-2">{{ __('admin/common.status') }}</label>
          <div class="col-sm-8 col-lg-5">
            <select class="form-control" name="status" id="fStatus" autocomplete="off">
              <option value="all">&ndash; {{ __('admin/common.all') }} &ndash;</option>
              <option value="unresolved" {!! old_selected('', $filters['status'], 'unresolved') !!}>{{ __('admin/app_reports.status_unresolved') }}</option>
              <option value="resolved" {!! old_selected('', $filters['status'], 'resolved') !!}>{{ __('admin/app_reports.status_resolved') }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row mb-0">
          <div class="offset-sm-3 offset-lg-2 col">
            <button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
            <a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.app_reports.index') }}">{{ __('admin/common.reset') }}</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Card -->
  <div class="card main-content @if($show_filters) scroll-to-me @endif">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/app_reports.apps_list') }} ({{ $items->total() }})</h3>
    </div>
    @if($items->isEmpty())
    <div class="card-body">
      @if($total == 0)
      <h4 class="text-left">&ndash; {{ __('admin/app_reports.no_app_reports_yet') }} &ndash;</h4>
      @else
      <h5 class="text-left">&ndash; {{ __('admin/apps.no_apps_matches') }} &ndash;</h5>
      @endif
    </div>
    @else
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-head-fixed">
          <thead>
            <tr>
              <th style="width: 50px;">{{ __('common.#') }}</th>
              <th>{{ __('admin/apps.fields.name') }}</th>
              <th>{{ __('admin/app_reports.fields.reports') }}</th>
              <th>{{ __('admin/app_reports.verdicts_history') }}</th>
              <th style="width: 1%;">{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $app)
            <tr>
              <td class="text-right">{{ $items->firstItem() + $loop->index }}</td>
              <td>
                <div>
                  {{ $app->complete_name }}
                  <a href="{{ $app->public_url }}" target="_blank" class="ml-2 text-090" title="{{ __('admin/apps.view_public_page') }}" data-toggle="tooltip"><span class="fas fa-external-link-alt"></span></a>
                </div>
                @include('components.app-logo', ['logo' => $app->logo, 'exact' => '40x40', 'none' => false, 'img_class' => 'mini-app-logo'])
              </td>
              <td>
                @if($app->num_unresolved_reports > 0)
                @lang('admin/app_reports.x_unresolved_reports', ['x' => $app->num_unresolved_reports])
                <!-- TODO: excessive reports info here -->
                @else
                @von
                @endif
              </td>
              <td>
                @if($app->num_verdicts > 0)
                @lang('admin/app_reports.x_past_verdicts', ['x' => $app->num_verdicts])
                @else
                @von
                @endif
              </td>
              <td>
                @if($app->num_unresolved_reports > 0)
                @can('create', App\Models\AppReport::class)
                <a href="{{ route('admin.app_reports.review', ['app' => $app->id]) }}" class="btn btn-primary btn-sm text-nowrap mb-1">
                  <span class="fas fa-clipboard-check mr-1"></span>
                  {{ __('admin/app_reports.review') }}
                </a>
                @endcan
                @endif
                @if($app->num_verdicts > 0)
                @can('view-any', App\Models\AppReport::class)
                <a href="{{ route('admin.app_reports.verdicts', ['app' => $app->id]) }}" class="btn btn-sm bg-purple text-nowrap mb-1">
                  <span class="fas fa-list mr-1"></span>
                  {{ __('admin/app_reports.view_verdicts') }}
                </a>
                @endcan
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @if($items->hasPages())
    <div class="card-footer">
      {{ $items->links() }}
    </div>
    @endif
    @endif
    <!-- /.card-body -->
  </div>
  <!-- /.card -->

@endsection
