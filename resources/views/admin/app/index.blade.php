<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$scroll_content = !isset($goto_item) && ($show_filters || request()->has('page'));
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/apps.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total_scoped]) }}</span>
@if($view_mode == 'prodi')
<span class="page-sub-title text-r100">@lang('admin/apps.page_title.view_mode.'.$view_mode, ['x' => vo_($prodi->complete_name)])</span>
@else
<span class="page-sub-title text-r100">{{ __('admin/apps.page_title.view_mode.'.$view_mode) }}</span>
@endif
@endsection

@section('content')
  @include('components.page-message', ['dismiss' => true])

  <div class="mt-2 mb-3">
    @can('create', App\Models\App::class)
    <a href="{{ route('admin.apps.create') }}" class="btn btn-primary">{{ __('admin/apps.submit_an_app') }}</a>
    @endcan
  </div>

  <!-- Filters -->
  <form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.apps.index') }}">
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
              <option value="">&ndash; {{ __('admin/common.all') }} &ndash;</option>
              <option value="unverified" {!! old_selected('', $filters['status'], 'unverified') !!}>{{ __('admin/apps.status.is_unverified') }}</option>
              <option value="verified" {!! old_selected('', $filters['status'], 'verified') !!}>{{ __('admin/apps.status.is_verified') }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="fPublished" class="col-sm-3 col-lg-2">{{ __('admin/apps.fields.is_published') }}</label>
          <div class="col-sm-8 col-lg-5">
            <select class="form-control" name="published" id="fPublished" autocomplete="off">
              <option value="">&ndash; {{ __('admin/common.all') }} &ndash;</option>
              <option value="yes" {!! old_selected('', $filters['published'], 'yes') !!}>{{ __('common.yes') }}</option>
              <option value="no" {!! old_selected('', $filters['published'], 'no') !!}>{{ __('common.no') }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="fdCategories" class="col-sm-3 col-lg-2">{{ __('admin/apps.fields.categories') }}</label>
          <div class="col-sm-8 col-lg-5">
            <input type="hidden" name="categories" id="fCategories" value="{{ $filters['categories'] }}">
            <select class="form-control compile-values" id="fdCategories" data-compile-to="#fCategories" autocomplete="off" multiple>
              @foreach($categories as $c)
              <option value="{{ $c->id }}" {!! old_selected('', explode(',', $filters['categories']), $c->id) !!}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="fdTags" class="col-sm-3 col-lg-2">{{ __('admin/apps.fields.tags') }}</label>
          <div class="col-sm-8 col-lg-5">
            <input type="hidden" name="tags" id="fTags" value="{{ $filters['tags'] }}">
            <select class="form-control compile-values" id="fdTags" data-compile-to="#fTags" autocomplete="off" multiple>
              @foreach($tags as $t)
              <option value="{{ $t->name }}" {!! old_selected('', explode(',', $filters['tags']), $t->name) !!}>{{ $t->name }}</option>
              @endforeach
            </select>
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
            <a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.apps.index') }}">{{ __('admin/common.reset') }}</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Card -->
  <div class="card main-content @if($scroll_content) scroll-to-me @endif">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/apps.apps_list') }} ({{ $items->total() }})</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas fa-minus"></i></button>
      </div>
    </div>
    @if($items->isEmpty())
    <div class="card-body">
      @if($total_scoped == 0)
      <h4 class="text-left">&ndash; {{ __('admin/apps.no_app_submissions_yet') }} &ndash;</h4>
      @else
      <h5 class="text-left">&ndash; {{ __('admin/apps.no_apps_matches') }} &ndash;</h5>
      @endif
    </div>
    @else
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-head-fixed table-hover">
          <thead>
            <tr>
              <th style="width: 50px;">{{ __('common.#') }}</th>
              <th>{{ __('admin/apps.fields.name') }}</th>
              <th style="width: 20%;">{{ __('admin/common.status') }}</th>
              <th style="width: 20%;">{{ __('admin/apps.fields.categories') }}</th>
              <th style="width: 20%;">{{ __('admin/apps.fields.tags') }}</th>
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
                  @if($view_mode != 'owned')
                  @include('admin.app.components.owned-icon')
                  @endif
                </div>
                @include('components.app-logo', ['logo' => $app->logo, 'exact' => '60x60', 'none' => false, 'img_class' => 'mini-app-logo'])
              </td>
              <td>
                <div class="d-inline-block">
                @if($app->is_verified)
                <div>
                  @if($app->is_published)
                  {{ __('admin/apps.status.is_published') }}
                  @if($app->is_listed)
                  <a href="{{ $app->public_url }}" target="_blank" class="text-success ml-2" title="@lang('admin/apps.app_had_been_verified')" data-toggle="tooltip">
                    <span class="fas fa-check-circle"></span>
                  </a>
                  @endif
                  @else
                  {{ __('admin/apps.status.is_private') }}
                  @endif
                </div>
                @if($app->is_reported)
                <div class="text-danger">{{ __('admin/apps.status.is_reported') }}</div>
                @endif
                @else
                {{ __('admin/apps.status.is_unverified') }}
                @endif
                </div>
                <div class="d-inline-block">
                  @if(optional($app->last_verification)->status_id == 'revision-needed')
                  <a href="{{ Auth::user()->cannot('view', $app) ? '#' : route('admin.apps.show', ['app' => $app->id, 'show_verification' => '']) }}" class="text-info ml-2" title="@lang('admin/apps.app_changes_needs_revision_to_be_approved')" data-toggle="tooltip">
                    <span class="fas fa-question-circle"></span>
                  </a>
                  @elseif($app->has_pending)
                  <a href="{{ Auth::user()->cannot('view', $app) ? '#' : route('admin.apps.show', ['app' => $app->id, 'show_pending' => '']) }}" class="text-warning ml-2" title="@lang('admin/apps.app_has_pending_changes')" data-toggle="tooltip">
                    <span class="fas fa-question-circle"></span>
                  </a>
                  @endif
                  @if($app->has_approved)
                  <a href="{{ Auth::user()->cannot('view', $app) ? '#' : route('admin.apps.show', ['app' => $app->id, 'show_verification' => '']) }}" class="text-success ml-2" title="@lang('admin/apps.app_has_approved_changes')" data-toggle="tooltip">
                    <span class="fas fa-question-circle"></span>
                  </a>
                  @endif
                  @if(optional($app->last_changes)->is_rejected)
                  <a href="{{ Auth::user()->cannot('view', $app) ? '#' : route('admin.apps.show', ['app' => $app->id, 'show_verification' => '']) }}" class="text-danger ml-2" title="@lang('admin/apps.app_has_rejected_changes')" data-toggle="tooltip">
                    <span class="fas fa-times-circle"></span>
                  </a>
                  @endif
                </div>
              </td>
              <td>
                @if($app->categories->isNotEmpty())
                @each('components.app-category', $app->categories, 'category')
                @else
                @voe()
                @endif
              </td>
              <td>
                @if($app->tags->isNotEmpty())
                @each('components.app-tag', $app->tags, 'tag')
                @else
                @voe()
                @endif
              </td>
              <td class="text-nowrap">
                @can('view', $app)
                <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-default btn-sm text-nowrap">
                  <span class="fas fa-search mr-1"></span>
                  {{ __('common.view') }}
                </a>
                @endcan
                {{--
                @can('update', $app)
                <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-primary btn-sm text-nowrap">
                  <span class="fas fa-edit mr-1"></span>
                  {{ __('common.edit') }}
                </a>
                @endcan
                --}}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!-- /.card-body -->
    @if($items->hasPages())
    <div class="card-footer">
      {{ $items->links() }}
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


  var $filterCategories = $("#fdCategories");
  $filterCategories.select2({
    width: "100%",
    multiple: true,
    allowClear: true,
    closeOnSelect: true,
    placeholder: @json(__('admin/apps.fields.categories')),
    // maximumSelectionLength: 3,
  });

  var $filterTags = $("#fdTags");
  $filterTags.select2({
    width: "100%",
    multiple: true,
    allowClear: true,
    closeOnSelect: false,
    placeholder: @json(__('admin/apps.fields.tags')),
    // maximumSelectionLength: 5,
  });

});
</script>
@endpush
