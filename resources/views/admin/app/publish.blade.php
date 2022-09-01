<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($ori->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/apps.page_title.review_changes'),
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.review_changes'))

@section('content')
<div class="d-flex flex-wrap text-nowrap mb-1">
  <div class="details-nav-left mr-auto mb-1">
    <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
  </div>
</div>
<form method="POST" action="{{ route('admin.apps.publish.save', ['app' => $app->id]) }}" id="formPublishChanges">

@include('components.page-message', ['show_errors' => true])

@csrf
@method('POST')

<input type="hidden" name="verif_ids" value="{{ $verifs->pluck('id')->implode(',') }}" readonly>

<!-- Card -->
<div class="card card-primary card-outline card-outline-tabs">
  <div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" role="tablist">
      <li class="pt-2 px-3 mt-1"><h3 class="card-title">@lang('admin/apps.compare'):</h3></li>
      <li class="nav-item">
        <a class="nav-link active" href="#app-comparison-new" id="app-comparison-new-tab" data-toggle="pill" role="tab">@lang('common.new')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#app-comparison-old" id="app-comparison-old-tab" data-toggle="pill" role="tab">@lang('common.old')</a>
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
          <h4 class="mb-0 text-primary">{{ $app->complete_name }}</h4>
          <span class="text-success">@lang('admin/apps.changes.version_x', ['x' => $app->version_number])</span>
        </div>
        @include('admin.app.detail-inner', ['section_id' => 'new', 'is_snippet' => true, 'app' => $app, 'ori' => null, 'hide_status' => true])
        @yield('detail-content-new')
      </div>
    </div>
    <div class="tab-pane fade" role="tabpanel" id="app-comparison-old">
      <div class="card-body">
        <div class="mb-2">
          <h4 class="mb-0 text-primary">{{ $ori->complete_name }}</h4>
          <span class="text-danger">@lang('admin/apps.changes.version_x', ['x' => $ori->version_number])</span>
        </div>
        @include('admin.app.detail-inner', ['section_id' => 'old', 'is_snippet' => true, 'app' => $ori, 'ori' => null, 'hide_status' => true])
        @yield('detail-content-old')
      </div>
    </div>
    <div class="tab-pane fade" role="tabpanel" id="app-comparison-changes">
      <div class="card-body">
        <h4>@lang('admin/apps.changes.summary_of_changes')</h4>
        <div class="changes-item">
          <div class="changes-content">
            @include('admin.app.changes.list-item-body', ['cl' => $summary, 'app' => $ori])
          </div>
        </div>
      </div>
      @if(!empty($changes))
      <div class="card-body">
        <h4 class="m-0">
            @lang('admin/apps.changes.detailed_information_on_the_changes')
            <button type="button" class="btn btn-default btn-xs ml-2" data-toggle="collapse" data-target="#detailed-changes">@lang('common.show/hide')</button>
        </h4>
      </div>
      <ul class="list-group list-group-flush mt-n3 collapse collapse-scrollto" id="detailed-changes" data-scroll-offset="80">
        @foreach($verifs as $i => $vf)
        <li class="list-group-item">
          <p class="lead mb-1">@lang('admin/apps.verification') #{{ $i + 1 }}</p>
          @include('admin.app_verification.components.verif-list-item', ['verif' => $vf, 'hide_navs' => true, 'other_comments' => true])
          @php
          $vrand = random_alpha(5);
          @endphp
          <div class="verif-content">
            <div class="verif-body">
              <div class="verif-value-group">
                <div class="verif-label">
                  @lang('admin/app_verifications.changes_verified') ({{ $vf->changelogs->count() }})
                  <button type="button" class="btn btn-default btn-xs ml-2" data-toggle="collapse" data-target="#verif-changes-{{ $vrand }}">@lang('common.show/hide')</button>
                </div>
              </div>
              <div class="collapse collapse-scrollto text-090 pt-3 pb-2 px-3" id="verif-changes-{{ $vrand }}" data-scroll-offset="80">
                @forelse($vf->changelogs as $cl)
                @include('admin.app.changes.list-item', ['cl' => $cl, 'app' => $ori, 'show_status' => false])
                @empty
                @von
                @endforelse
              </div>
            </div>
          </div>
        </li>
        @endforeach
      </ul>
      @endif
    </div>
  </div>
  <div class="card-footer">
    <div class="text-center">
      <button type="submit" class="btn btn-primary btn-min-100">@lang('admin/apps.changes.publish_changes_now')</button>
    </div>
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
});
</script>
@endpush
