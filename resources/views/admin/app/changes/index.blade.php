<?php
$append_breadcrumb = [
  [
    'text'    => text_truncate($app->name, 50),
    'url'     => route('admin.apps.show', ['app' => $app->id]),
    'active'  => false,
  ],
  [
    'text'    => __('admin.app.page_title.changes.index'),
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin.app.changes.tab_title') }} - @parent
@endsection

@section('page-title', __('admin.app.page_title.changes.index'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
</div>

@if(request()->input('page', 1) > 1)
<div class="mb-3">
{{ $changelogs->links() }}
</div>
@endif

@foreach($changelogs as $cl)
@include('admin.app.changes.list-item', compact('cl', 'app'))
@endforeach

<div class="mt-3">
{{ $changelogs->links() }}
</div>

@endsection

@include('libraries.splide')

@push('scripts')
<div class="modal fade" id="versionModal" tabindex="-1" aria-labelledby="versionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="versionModalLabel">@lang('admin/app.changes.version_preview')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="placeholder-content">
          <h4 class="my-5">loading...</h4>
        </div>
        <div class="error-message alert alert-danger d-none">
          <h5 class="my-5">@lang('admin/app.changes.cannot_load_version_preview')</h5>
        </div>
      </div>
      <div class="modal-footer text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('common.close')</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="changesVisualsModal" tabindex="-1" aria-labelledby="changesVisualsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changesVisualsModalLabel">@lang('admin/app.changes.visuals_comparison')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="@lang('common.close')"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="placeholder-content">
          <h4 class="my-5">loading...</h4>
        </div>
        <div class="error-message alert alert-danger d-none">
          <h5 class="my-5">@lang('admin/app.changes.cannot_load_visuals_comparison')</h5>
        </div>
      </div>
      <div class="modal-footer text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('common.close')</button>
      </div>
    </div>
  </div>
</div>

<script>
jQuery(document).ready(function($) {

  var versionView = function() {
    var $modal = $("#versionModal"),
      $modalTitle = $modal.find(".modal-title"),
      $modalBody = $modal.find(".modal-body"),
      $placeholderContent = $modalBody.find(".placeholder-content").remove(),
      $error = $modalBody.find(".error-message").remove().removeClass("d-none");

    function modalTitle(version) {
      $modalTitle.html( @json(__('admin/app.changes.version_preview')) +': '+ version );
    }

    function modalContent(content) {
      $modalBody.empty().append(content);
    }

    function modalShow(version) {
      if(!version) {
        modalContent($error);
        return;
      }

      modalTitle(version);
      modalContent($placeholderContent);
      $modal.modal("show");

      $.ajax({
        url: @json( route('admin.apps.changes.details', ['app' => $cl->app_id]) ),
        method: "GET",
        cache: true,
        data: {
          version: version,
        },
        dataType: "html",
        success: function(response, status, xhr) {
          modalContent(response);
        },
        error: function(xhr, status, message) {
          console.error("Error occurred while trying to load content. "+ status +": "+ message);
          modalContent($error);
        },
      });
    }

    $(document).on("click", ".changes-item .btn-view-version", function(e) {
      e.preventDefault();

      // Show modal containing the item
      var $btn = $(this);
      var $item = $btn.closest(".changes-item");
      var version = $item.data("version");

      modalShow(version);
    });
  }
  versionView();


  var changesVisuals = function() {
    var $modal = $("#changesVisualsModal"),
      $modalBody = $modal.find(".modal-body"),
      $placeholderContent = $modalBody.find(".placeholder-content").remove(),
      $error = $modalBody.find(".error-message").remove().removeClass("d-none");

    function modalContent(content) {
      $modalBody.empty().append(content);
    }

    $(document).on("click", ".btn-compare-visuals", function(e) {
      e.preventDefault();

      // Show modal containing the difference
      var $btn = $(this);
      var version = $btn.data("version") || "";
      var newIds = $btn.data("visualsNew") || "";
      var oldIds = $btn.data("visualsOld") || "";

      modalContent($placeholderContent);
      $modal.modal("show");

      $.ajax({
        url: @json( route('admin.apps.changes.visuals', ['app' => $cl->app_id]) ),
        method: "GET",
        cache: true,
        data: {
          new: newIds,
          old: oldIds,
          version: version,
          // autoplay: 1,
        },
        dataType: "html",
        success: function(response, status, xhr) {
          modalContent(response);
        },
        error: function(xhr, status, message) {
          console.error("Error occurred while trying to load content. "+ status +": "+ message);
          modalContent($error);
        },
      });
    });
  }
  changesVisuals();

  @if(request()->has('current') && $app->version)
  Helpers.scrollTo(@json('#changes-item-'.$app->version->id), {
    animate: true,
    offset: 30,
  });
  @endif
});
</script>
@endpush
