<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
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

<div class="jumbotron bg-transparent text-center" id="afterPublishPage">
  <span class="fas fa-check text-success" style="font-size: 5em;"></span>
  <h1 class="display-4">@lang('admin/apps.messages.congrats!')</h1>
  <p class="lead">@lang('admin/apps.messages.your_changes_have_been_published!')</p>
  <hr class="my-4">
  <div class="d-flex justify-content-center" style="gap: 2em;">
    <a class="btn btn-lg btn-secondary" href="{{ route('admin.apps.show', ['app' => $app->id]) }}">&laquo; @lang('common.go_back')</a>
    <a class="btn btn-lg btn-primary" href="{{ $app->public_url }}" target="_blank"><span class="fas fa-globe-americas mr-1"></span> @lang('admin/apps.view_your_app') &raquo;</a>
  </div>
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
