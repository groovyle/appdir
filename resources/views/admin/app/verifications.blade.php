<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/apps.page_title.verifications'),
  ]
];
$goto_version = $goto_version ?? false;
$page_title = __('admin/apps.page_title.verifications');
if(($count = $app->verifications->count()) > 0) {
  $page_title .= ' ('. $count .')';
}
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.tab_title.verifications', ['x' => text_truncate($app->name, 20)]) }} - @parent
@endsection

@section('page-title')
{{ $page_title }}
<br><small class="text-primary">{{ $app->name }}</small>
@endsection

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
</div>

<!-- Card -->
<div class="card">
  <div class="card-body">
    <h2>{{ $app->name }}</h2>

    @if($app->verifications->count())
      <div class="mb-1 text-secondary"><em>(@lang('common.sorted_from_newest_to_oldest'))</em></div>
      <div class="verif-list verif-conversation">
      @foreach($app->verifications->reverse() as $verif)
        @include('admin.app_verification.components.verif-list-item', ['other_comments' => true, 'item_side' => 'reversed'])
      @endforeach
      </div>
    @else
    <p>{{ __('admin.app.message.no_verifications_yet') }}</p>
    @endif
  </div>
  <!-- /.card-body -->
</div>
<!-- /.card -->
@endsection

@include('admin.app_verification.btn-view-verif')

@push('scripts')
<script>
jQuery(document).ready(function($) {
  $('[data-toggle="popover"]').popover({
    container: "body",
  });
});
</script>
@endpush
