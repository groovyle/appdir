<?php
$show_pending_changes = $show_pending_changes ?? true;
$hide_header_extras = $hide_header_extras ?? false;

$section = 'detail-content';
if(isset($section_id))
  $section = $section.'-'.$section_id;
?>
@include('admin.app.detail-inner')
@includeWhen($show_pending_changes, 'admin.app.changes.pending')
<div class="card collapsed-card">
  <div class="card-header">
    <div class="d-flex flex-wrap align-items-center">
      <div class="mr-auto">
        <h4 class="mb-0 text-primary d-inline-block">
          {{ $app->complete_name }}
          <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="@lang('common.show/hide')"><i class="fas fa-search"></i></button>
        </h4>
        <br>
        @if(!$hide_header_extras)
          @if($app->is_published)
            @if($app->is_public)
            <span class="badge badge-soft badge-success align-middle text-080">
              <span class="fas fa-check-circle mr-1"></span>
              @lang('admin/apps.app_is_public')
            </span>
            @else
            <span class="badge badge-soft badge-warning align-middle text-080">
              <span class="fas fa-exclamation-circle mr-1"></span>
              @lang('admin/apps.app_is_not_public')
            </span>
            @endif
          @endif
          @if($app->is_reported)
            <span class="badge badge-soft badge-danger align-middle text-080">
              <span class="fas fa-exclamation-triangle text-090 mr-1"></span>
              @lang('admin/apps.app_was_reported')
            </span>
          @endif
        @endif
        <a href="{{ $app->get_public_url(['version' => $app->version_number]) }}" class="btn btn-xs btn-secondary px-2" target="_blank">
          @lang('admin/app_verifications.review_public_page')
          <span class="fas fa-globe-americas ml-1"></span>
        </a>
      </div>
      <div class="text-right ml-auto">
        @if($app->has_committed)
        <span class="text-bold">
          @lang('admin/apps.changes.version_x', ['x' => $app->version_number])
        </span>
        @else
        <span class="text-bold">
          @lang('admin/apps.this_new_item_is_waiting_verification')
        </span>
        @endif
        @if($show_pending_changes && $app->has_floating_changes)
        <br>
        <button class="btn btn-xs btn-warning btn-pending-changes-show" data-app-id="{{ $app->id }}" data-current-version="{{ $app->version_number }}" data-accumulate-changes="false">
          <span class="fas fa-clock"></span>
          @lang('admin/apps.show_pending_changes')
        </button>
        @endif
      </div>
    </div>
  </div>
  <div class="card-body">
    @yield($section)
  </div>
</div>