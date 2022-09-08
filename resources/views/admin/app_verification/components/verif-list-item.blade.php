<?php
$item_class = $item_class ?? '';
$other_comments = !!($other_comments ?? false);
$hide_navs = !!($hide_navs ?? false);
$hide_edit = !!($hide_edit ?? true);
?>
<div class="verif-content verif-item verif-item-{{ $verif->id }} {{ $item_class }}">
  <div class="verif-header">
    <div class="icon-text-pair text-bold">
      @if($verif->status->by == 'verifier')
      <span class="fas fa-user-check icon"></span>
      @else
      <span class="fas fa-user-edit icon"></span>
      @endif
      @puser($verif->verifier)
    </div>
    @if($verif->concern != 'new')
    <div class="icon-text-pair">
      <span class="fas fa-tag icon"></span>
      {!! status_badge($verif->status->name, $verif->status->bg_style.' badge-soft') !!}
    </div>
    @endif
    @if(!$hide_navs)
    <button type="button" class="btn btn-tool btn-view-verif ml-n1" data-toggle="tooltip" title="@lang('admin/app_verifications.view_this_item')" data-app-id="{{ $app->id }}" data-verif-id="{{ $verif->id }}"><span class="fas fa-expand"></span></button>
    @if($verif->can_edit && !$hide_edit)
    <a href="{{ route('admin.app_verifications.review', ['app' => $verif->app_id, 'verif' => $verif->id]) }}" class="btn btn-tool ml-n2" data-toggle="tooltip" title="@lang('admin/app_verifications.edit_this_item')"><span class="fas fa-pencil-alt"></span></a>
    @endif
    @endif
  </div>
  <div class="verif-metas">
    <div class="icon-text-pair text-muted">
      @include('components.date-with-tooltip', ['date' => $verif->updated_at])
    </div>
    <div class="icon-text-pair">
      @if($verif->status->by == 'verifier')
      <span class="fas fa-spell-check icon"></span>
      <span>@lang('admin/app_verifications.versions_verified'): @vo_((string) $verif->changelog_range)</span>
      @else
      <span class="far fa-copy icon"></span>
      <span>@lang('admin/app_verifications.related_versions'): @vo_((string) $verif->changelog_range)</span>
      @endif
    </div>
  </div>
  <div class="verif-body">
  @if($verif->status->by == 'verifier')
    <div class="verif-value-group">
      <div class="verif-label">@lang('admin/app_verifications.fields.overall_comments'):</div>
      <div class="verif-value">@voe($verif->comment)</div>
    </div>
    @if($other_comments && $verif->details)
    <div class="verif-value-group">
      <div class="verif-label">@lang('admin/app_verifications.other_comments') ({{ count($verif->details ?? []) }}):</div>
      <div class="verif-pieces">
        @foreach($verif->ordered_details as $field => $comment)
        <div class="icon-text-pair">
          <span class="fas fa-comment text-light-gray text-090 icon"></span>
          <span>
            {{ __('admin/apps.fields.'.$field) }}:
            <div class="verif-value">@voe($comment)</div>
          </span>
        </div>
        @endforeach
      </div>
    </div>
    @endif
  @elseif($verif->concern == 'new')
    @lang('admin/app_verifications.new_item_submitted')
  @elseif($verif->concern == 'edit')
    @lang('admin/app_verifications.item_edited')
  @elseif($verif->concern == 'commit')
    @lang('admin/app_verifications.pending_changes_applied')
  @elseif($verif->concern == 'publish')
    @lang('admin/app_verifications.item_published')
  @else
    @vo_($verif->comment)
  @endif
  </div>
</div>