<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$scroll_content = !isset($goto_item) && ($show_filters || request()->has('page'));
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_verifications.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/app_verifications.page_title.index') }}
@if($view_mode == 'prodi')
<span class="page-sub-title text-r100">@lang('admin/app_verifications.page_title.view_mode.'.$view_mode, ['x' => vo_($prodi->complete_name)])</span>
@elseif($view_mode != 'none')
<span class="page-sub-title text-r100">{{ __('admin/app_verifications.page_title.view_mode.'.$view_mode) }}</span>
@endif
@endsection

@section('content')
  <!-- Filters -->
  <form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.app_verifications.index') }}">
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
              <option value="unverified" {!! old_selected('', $filters['status'], 'unverified') !!}>{{ __('admin/app_verifications.status_unverified') }}</option>
              <option value="verified" {!! old_selected('', $filters['status'], 'verified') !!}>{{ __('admin/app_verifications.status_verified') }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row mb-0">
          <div class="offset-sm-3 offset-lg-2 col">
            <button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
            <a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.app_verifications.index') }}">{{ __('admin/common.reset') }}</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Card -->
  <div class="card main-content @if($scroll_content) scroll-to-me @endif">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/app_verifications.apps_list') }} ({{ $items->total() }})</h3>
    </div>
    @if($items->isEmpty())
    <div class="card-body">
      @if($total == 0)
      <h4 class="text-left">&ndash; {{ __('admin/app_verifications.no_app_verifications_yet') }} &ndash;</h4>
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
              <th>{{ __('admin/apps.fields.submission_status') }}</th>
              <th style="width: 1%;">{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $app)
            <tr>
              <td class="text-right">{{ $items->firstItem() + $loop->index }}</td>
              <td>
                <div>{{ $app->complete_name }}</div>
                @include('components.app-logo', ['logo' => $app->logo, 'exact' => '40x40', 'none' => false, 'img_class' => 'mini-app-logo'])
              </td>
              <td>
                @include('components.app-verification-status', ['app' => $app])
              </td>
              <td class="text-nowrap">
                @can('review', [App\Models\AppVerification::class, null, $app])
                <a href="{{ route('admin.app_verifications.review', ['app' => $app->id]) }}" class="btn btn-primary btn-sm">
                  <span class="fas fa-clipboard-check mr-1"></span>
                  {{ __('admin/app_verifications.verify') }}
                </a>
                @endcan
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
