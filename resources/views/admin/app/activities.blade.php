<?php
$last_breadcrumb = __('admin/apps.page_title.activities');

$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$scroll_content = !isset($goto_item) && ($show_filters || request()->has('page'));
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.page_title.activities') }} - @parent
@endsection

@section('page-title')
{{ __('admin/apps.page_title.activities') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total_scoped]) }}</span>
@if($view_mode == 'prodi')
<span class="page-sub-title text-r100">@lang('admin/apps.page_title.view_mode.'.$view_mode, ['x' => vo_($prodi->complete_name)])</span>
@else
<span class="page-sub-title text-r100">{{ __('admin/apps.page_title.view_mode.'.$view_mode) }}</span>
@endif
@endsection

@section('content')

  <!-- Filters -->
  <form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.app_activities.index') }}">
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
        @if($view_mode != 'owned')
        <div class="form-group row">
          <label for="fOwned" class="col-sm-3 col-lg-2">{{ __('admin/apps.fields.filter_is_owned') }}</label>
          <div class="col-sm-8 col-lg-5">
            <select class="form-control" name="owned" id="fOwned" autocomplete="off">
              <option value="">&ndash; {{ __('admin/common.all') }} &ndash;</option>
              <option value="mine" {!! old_selected('', $filters['owned'], 'mine') !!}>{{ __('admin/apps.status.my_apps') }}</option>
              <option value="others" {!! old_selected('', $filters['owned'], 'others') !!}>{{ __('admin/apps.status.other_apps') }}</option>
            </select>
          </div>
        </div>
        @endif
        <div class="form-group row mb-0">
          <div class="offset-sm-3 offset-lg-2 col">
            <button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
            <a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.app_activities.index') }}">{{ __('admin/common.reset') }}</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Card -->
  <div class="card main-content @if($scroll_content) scroll-to-me @endif">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/apps.activities') }} ({{ $list->total() }})</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas fa-minus"></i></button>
      </div>
    </div>
    @if($list->isEmpty())
    <div class="card-body">
      @if($total_scoped == 0)
      <h4 class="text-left">&ndash; {{ __('admin/apps.no_app_activities_yet') }} &ndash;</h4>
      @else
      <h5 class="text-left">&ndash; {{ __('admin/apps.no_apps_matches') }} &ndash;</h5>
      @endif
    </div>
    @else
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-head-fixed table-hover table-sm">
          <thead>
            <tr>
              <th class="text-right pr-2" style="width: 50px;">{{ __('common.#') }}</th>
              <th>{{ __('admin/apps.fields.activity') }}</th>
              <th style="width: 15%;">{{ __('admin/common.fields.at') }}</th>
              <th style="width: 1%;">{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($list as $item)
            <tr>
              <td class="text-right pr-2">{{ $list->firstItem() + $loop->index }}</td>
              <td>
                <span title="{{ $item->app->name }}">
                  @if($view_mode != 'owned')
                  @include('admin.app.components.owned-icon', ['app' => $item->app, 'margin' => 'mr-1'])
                  @endif
                  {{ text_truncate($item->app->name, 25) }}
                </span>
                <span class="ml-2">
                  @if($item->concern == 'new')
                    <span class="badge badge-soft cursor-default badge-info">{{ ucfirst(__('admin/common.new')) }}</span>
                  @else
                    @if($item->concern == 'edit')
                      <span class="badge badge-soft cursor-default badge-primary">{{ ucfirst(__('admin/common.edit')) }}</span>
                    @else
                      <span class="badge badge-soft cursor-default badge-{{ $item->status->bg_style }}">{{ $item->status->name }}</span>
                    @endif
                    @if($item->consecutive_prev > 0)
                      <span class="text-090 text-secondary">
                        (x{{ $item->consecutive_prev + 1 }})
                      </span>
                    @endif
                  @endif
                  @if($item->consecutive_prev == 0
                    && ($item->status->by == 'verifier' || !$item->verifier->is_me) )
                  <span class="text-090 ml-2">
                    @lang('admin/common.by')
                    @if($item->verifier->is_me)
                    @lang('admin/common.you')
                    @else
                    @puser($item->verifier)
                    @endif
                  </span>
                  @endif
                </span>
              </td>
              <td>
                <span class="text-090 text-secondary">@include('components.date-with-tooltip', ['date' => $item->action_at, 'reverse' => true])</span>
              </td>
              <td class="text-nowrap">
                @if($item->view_url)
                <a href="{{ $item->view_url }}" class="btn btn-xs btn-default" title="{{ __('common.view') }}" data-toggle="tooltip"><span class="fas fa-search"></span></a>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!-- /.card-body -->
    @if($list->hasPages())
    <div class="card-footer">
      {{ $list->links() }}
    </div>
    @endif
    @endif
  </div>
@endsection

@include('libraries.select2')

@push('scripts')
<script>
jQuery(document).ready(function($) {

  var $filterForm = $(".filters-wrapper");
  $filterForm.on("expanded.lte.cardwidget", function(e) {
    $filterForm.find("input, textarea, select").trigger("change");
  });

});
</script>
@endpush
