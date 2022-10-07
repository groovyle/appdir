<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin/apps.page_title.changes'),
  ]
];
$goto_version = $goto_version ?? false;
$page_title = __('admin/apps.page_title.changes');
if(($count = $app->changelogs()->count()) > 0) {
  $page_title .= ' ('. $count .')';
}
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.tab_title.changes', ['x' => text_truncate($app->name, 20)]) }} - @parent
@endsection

@section('page-title')
{{ $page_title }}
<br><small class="text-primary">{{ $app->name }}</small>
@endsection

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
</div>

@if($page > 1)
<div class="mb-3">
{{ $changelogs->links() }}
</div>
@endif

<div class="row">
  <div class="col-12">
    @forelse($changelogs as $cl)
    @include('admin.app.changes.list-item', compact('cl', 'app'))
    @empty
    <h4>@lang('admin/apps.changes.there_are_no_changes_yet')</h4>
    @endforelse
  </div>
</div>

<div class="mt-3">
{{ $changelogs->links() }}
</div>

@endsection

@include('libraries.splide')
@include('admin.app.changes.btn-view-version')
@include('admin.app.changes.visuals-comparison')

@push('scripts')
<script>
jQuery(document).ready(function($) {

  var gotoVersion = function() {
    $(document).on("click", ".btn-goto-version", function(e) {
      var $btn = $(this);
      var gotoVersion = $btn.data("gotoVersion");
      if(!gotoVersion)
        return;

      var $target = $(".changes-item-v"+ gotoVersion);
      if($target.length > 0) {
        // Is in current page
        e.preventDefault();
        Helpers.scrollTo($target, {
          animate: true,
          offset: -10,
        });
      }
    });
  }
  gotoVersion();

  @if($goto_version)
  Helpers.scrollTo(@json('#changes-item-'.$goto_version), {
    animate: true,
    offset: 30,
  });
  @endif
});
</script>
@endpush
