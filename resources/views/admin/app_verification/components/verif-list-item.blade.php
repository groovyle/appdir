<?php
$is_reported_guilty = $verif->is_reported_guilty;
$item_side = $item_side ?? 'auto';
if($item_side == 'auto' || $item_side == 'reversed') {
  $item_side_state = $verif->status->by == 'editor';
  if($item_side == 'reversed') $item_side_state = !$item_side_state;
  $item_side = $item_side_state ? 'right' : 'left';
}
$item_class = $is_reported_guilty ? 'red' : ($item_class ?? 'auto');
if($item_class == 'auto') {
  $item_class = $verif->status->by == 'editor' ? 'blue' : 'green';
}
$other_comments = !!($other_comments ?? false);
$hide_navs = !!($hide_navs ?? false);
$hide_edit = !!($hide_edit ?? true);
?>
<div class="verif-content verif-item verif-item-{{ $verif->id }} {{ $item_side }} {{ $item_class }}">
  <div class="verif-header">
    <div class="icon-text-pair text-bold">
      @if($verif->status->by == 'verifier')
      <span class="fas fa-user-check icon"></span>
      @else
      <span class="fas fa-pencil-alt icon"></span>
      @endif
      @puser($verif->verifier)
    </div>
    @if($verif->concern != 'new')
    <span class="badge badge-soft cursor-default badge-{{ $verif->status->bg_style }}">
      <span class="icon-text-pair icon-color-reset">
        <span class="fas fa-tag icon"></span>
        <span>{{ $verif->status->name }}</span>
      </span>
    </span>
    @endif
    @if(!$hide_navs)
    <button type="button" class="btn btn-tool btn-view-verif ml-n1" data-toggle="tooltip" title="@lang('admin/app_verifications.view_this_item')" data-app-id="{{ $app->id }}" data-verif-id="{{ $verif->id }}"><span class="fas fa-expand"></span></button>
    @if(!$hide_edit)
    @can('update', $verif)
    <a href="{{ route('admin.app_verifications.review', ['app' => $verif->app_id, 'verif' => $verif->id]) }}" class="btn btn-tool ml-n2" data-toggle="tooltip" title="@lang('admin/app_verifications.edit_this_item')"><span class="fas fa-pencil-alt"></span></a>
    @endcan
    @endif
    @endif
  </div>
  <div class="verif-metas">
    <div class="icon-text-pair text-muted">
      @include('components.date-with-tooltip', ['date' => $verif->updated_at])
    </div>
    <div class="icon-text-pair">
      @if($is_reported_guilty)
      <span class="fas fa-spell-check icon"></span>
      <span>@lang('admin/app_verifications.related_version'): @vo_((string) optional($verif->verdict->version)->version)</span>
      @elseif($verif->status->by == 'verifier')
      <span class="fas fa-spell-check icon"></span>
      <span>@lang('admin/app_verifications.versions_verified'): @vo_((string) $verif->changelog_range)</span>
      @else
      <span class="far fa-copy icon"></span>
      <span>@lang('admin/app_verifications.related_versions'): @vo_((string) $verif->changelog_range)</span>
      @endif
    </div>
  </div>
  <div class="verif-body">
  @if($is_reported_guilty)
    <div class="lh-130">
      @lang('admin/app_verifications.this_app_was_unlisted_because_of_reports')
    </div>
  @endif
  @if($verif->status->by == 'verifier')
    @if($is_reported_guilty)
    <div class="verif-value-group">
      <span class="verif-label">@lang('admin/app_reports.fields.violation_types'):</span>
      <span class="d-inline-block ml-1">
        @forelse($verif->verdict->categories as $rc)
        <span class="btn btn-xs btn-default rounded-pill cursor-default" data-toggle="tooltip" title="{{ $rc->description }}" data-custom-class="tooltip-wider">{{ $rc->name }}</span>
        @empty
        @vo_
        @endforelse
      </span>
    </div>
    <div class="verif-value-group">
      <div class="verif-label">@lang('admin/app_reports.fields.ban_reasons'):</div>
      <div class="verif-value">@voe($verif->comment ?? $verif->verdict->comments)</div>
    </div>
    @else
    <div class="verif-value-group">
      <div class="verif-label">@lang('admin/app_verifications.fields.overall_comments'):</div>
      <div class="verif-value">@voe($verif->comment)</div>
    </div>
    @endif
    @if($other_comments && $verif->details)
    <div class="verif-value-group">
      <div class="verif-label">@lang('admin/app_verifications.other_comments') ({{ count($verif->details ?? []) }}):</div>
      <div class="verif-pieces">
        @foreach($verif->ordered_details as $field => $comment)
        @if($field[0] == '_')
        @continue
        @endif
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