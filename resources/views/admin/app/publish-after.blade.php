<?php
$_title = !$app->is_unverified_new ? 'review_changes' : 'publish_app';
$page_title = __('admin/apps.page_title.'.$_title);
$tab_title = 'admin/apps.tab_title.'.$_title;
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
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
    @can('view', $app)
    <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
    @endcan
  </div>
</div>

<div class="jumbotron bg-transparent text-center" id="afterPublishPage">
  <span class="fas fa-check text-success" style="font-size: 5em;"></span>
  <h1 class="display-4">@lang('admin/apps.messages.congrats!')</h1>
  @if($app->is_published)
  <p class="lead">@lang('admin/apps.messages.your_changes_have_been_published!')</p>
  <hr class="my-4">
  <div class="d-flex justify-content-center" style="gap: 2em;">
    @can('view', $app)
    <a class="btn btn-lg btn-secondary" href="{{ route('admin.apps.show', ['app' => $app->id]) }}">&laquo; @lang('common.go_back')</a>
    @endcan
    <a class="btn btn-lg btn-primary" href="{{ $app->public_url }}" target="_blank"><span class="fas fa-globe-americas mr-1"></span> @lang('admin/apps.view_your_app') &raquo;</a>
  </div>
  @else
  <p class="lead">@lang('admin/apps.messages.your_changes_have_been_applied!')</p>
  <hr class="my-4">
  <div class="d-flex justify-content-center" style="gap: 2em;">
    @can('view', $app)
    <a class="btn btn-lg btn-primary" href="{{ route('admin.apps.show', ['app' => $app->id]) }}">@lang('admin/apps.back_to_app_details')</a>
    @endcan
  </div>
  @endif
</div>

@endsection

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
  Helpers.scrollTo("#afterPublishPage", {
    offset: 50,
  });
});
</script>
@endpush
