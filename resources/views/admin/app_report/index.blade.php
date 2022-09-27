@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_reports.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/app_reports.page_title.index'))

@section('content')
  <!-- Filters -->
  <form class="card card-primary card-outline filters-wrapper" method="GET" action="{{ route('admin.app_reports.index') }}">
    <div class="card-header">
      <h3 class="card-title cursor-pointer" data-card-widget="collapse">{{ __('admin/common.filters') }}</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas fa-minus"></i></button>
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
              <option value="unverified" {!! old_selected('', $filters['status'], 'unverified') !!}>{{ __('admin/app_reports.status_unverified') }}</option>
              <option value="verified" {!! old_selected('', $filters['status'], 'verified') !!}>{{ __('admin/app_reports.status_verified') }}</option>
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
  <div class="card main-content scroll-to-me">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/app_reports.submissions') }}</h3>
    </div>
    @if($items->isEmpty())
    <div class="card-body">
      <h4 class="text-left">{{ __('admin/app_reports.no_app_submissions_yet') }}</h4>
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
                reported {{ $app->num_reports }} times
                <br>
                with {{ $app->num_resolved_reports }} reports resolved
                <br>
                <br>
                TODO: excessive reports info here
              </td>
              <td class="text-nowrap">
                <a href="{{ route('admin.app_reports.review', ['app' => $app->id]) }}" class="btn btn-primary btn-sm">
                  <span class="fas fa-clipboard-check mr-1"></span>
                  {{ __('admin/app_reports.review') }}
                </a>
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
