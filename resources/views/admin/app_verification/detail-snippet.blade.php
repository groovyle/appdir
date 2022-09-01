
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
      <div class="icon-text-pair">
        <span class="fas fa-tag icon"></span>
        {!! status_badge($verif->status->name, $verif->status->bg_style.' badge-soft') !!}
      </div>
    </div>
    <div class="verif-metas">
      <div class="icon-text-pair text-muted">
        @include('components.date-with-tooltip', ['date' => $verif->created_at])
      </div>
      <div class="icon-text-pair">
        <span class="far fa-copy icon"></span>
        @lang('admin/app_verifications.reviewed_versions'): {{ $verif->changelog_range }}
      </div>
    </div>
    <div class="verif-body">
      <div class="verif-piece">
        <span class="verif-label">@lang('admin/app_verifications.fields.overall_comments'):</span>
        <span class="verif-value">@voe($verif->comment)</span>
      </div>
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