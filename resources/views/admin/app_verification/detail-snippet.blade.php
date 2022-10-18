<?php
$is_reported_guilty = $verif->is_reported_guilty;
?>
@include('admin.app_verification.detail-inner', ['is_snippet' => true])

@stack('head-additional')

@section('content')
  <div class="verif-content mb-3">
    <div class="verif-header">
      <div class="icon-text-pair text-bold">
        @if($verif->status->by == 'verifier')
        <span class="fas fa-user-check icon"></span>
        @else
        <span class="fas fa-user-edit icon"></span>
        @endif
        @puser($verif->verifier)
      </div>
      <span class="badge badge-soft cursor-default badge-{{ $verif->status->bg_style }}">
        <span class="icon-text-pair icon-color-reset">
          <span class="fas fa-tag icon"></span>
          <span>{{ $verif->status->name }}</span>
        </span>
      </span>
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
        <div class="verif-value"><span class="init-readmore">@voe($verif->comment ?? $verif->verdict->comments)</span></div>
      </div>
      @else
      <div class="verif-piece">
        <span class="verif-label">@lang('admin/app_verifications.fields.overall_comments'):</span>
        <span class="verif-value"><span class="init-readmore">@voe($verif->comment)</span></span>
      </div>
      @endif
      <?php
      $details_count = count($verif->details ?? []);
      ?>
      <div class="verif-piece">
        <span class="verif-label">
          @lang('admin/app_verifications.other_comments') ({{ $details_count }}):
        </span>
        <span class="ml-1">
          @if($details_count > 0)
          @lang('admin/app_verifications.look_below')
          @else
          @von
          @endif
        </span>
      </div>
    </div>
  </div>

  <h5>@lang('admin/apps.titles.app_info')</h5>
  @yield('detail-content')
@show

@stack('scripts')