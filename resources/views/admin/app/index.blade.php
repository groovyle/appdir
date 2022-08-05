@extends('admin.layouts.main')

@section('title')
{{ __('admin/apps.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/apps.page_title.index'))

@section('content')
  <div class="mt-2 mb-3">
    <a href="{{ route('admin.apps.create') }}" class="btn btn-primary">{{ __('admin/apps.submit_an_app') }}</a>
  </div>
  <!-- Card -->
  @if ($verified->isNotEmpty())
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/apps.verified_apps') }}</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas fa-minus"></i></button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-head-fixed">
          <thead>
            <tr>
              <th style="width: 50px;">{{ __('common.#') }}</th>
              <th>{{ __('admin/apps.field.name') }}</th>
              <th>{{ __('admin/apps.submission_status') }}</th>
              <th>{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($verified as $app)
            <tr>
              <td class="text-right">{{ $loop->iteration }}</td>
              <td>
                <div>{{ $app->name }}</div>
                @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x80', 'none' => false])
              </td>
              <td>
                @include('components.app-verification-status', ['app' => $app])
              </td>
              <td>
                <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-default btn-sm text-nowrap">
                  <span class="fas fa-search mr-1"></span>
                  {{ __('common.detail') }}
                </a>
                <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-primary btn-sm text-nowrap">
                  <span class="fas fa-edit mr-1"></span>
                  {{ __('common.edit') }}
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!-- /.card-body -->
  </div>
  <hr>
  @endif

  <!-- /.card -->
  <!-- Card -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/apps.submissions') }}</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
          <i class="fas fa-minus"></i></button>
      </div>
    </div>
    @if ($unverified->isEmpty())
    <div class="card-body">
      <h4 class="text-left">{{ __('admin/apps.no_app_submissions_yet') }}</h4>
    </div>
    @else
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-head-fixed">
          <thead>
            <tr>
              <th style="width: 50px;">{{ __('common.#') }}</th>
              <th>{{ __('admin/apps.field.name') }}</th>
              <th>{{ __('admin/apps.submission_status') }}</th>
              <th>{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($unverified as $app)
            <tr>
              <td class="text-right">{{ $loop->iteration }}</td>
              <td>
                <div>{{ $app->name }}</div>
                @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x80', 'none' => false])
              </td>
              <td>
                {!! status_badge($app->verification_status->name, $app->verification_status->bg_style) !!}
                @if ($app->verifications()->exists())
                <br>
                <div class="d-inline-block small" title="{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}" data-toggle="tooltip" data-placement="right" data-trigger="hover click">
                  <span class="fa-fw far fa-clock"></span>
                 {{ $app->last_verification->updated_at->longRelativeToNowDiffForHumans() }}
                 <span class="sr-only">{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}</span>
               </div>
               @endif
              </td>
              <td>
                <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-default btn-sm text-nowrap">
                  <span class="fas fa-search mr-1"></span>
                  {{ __('common.detail') }}
                </a>
                <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-primary btn-sm text-nowrap">
                  <span class="fas fa-edit mr-1"></span>
                  {{ __('common.edit') }}
                </a>
                <a href="{{ route('admin.apps.verifications', ['app' => $app->id]) }}" class="btn btn-secondary btn-sm text-nowrap">
                  <span class="fas fa-tasks mr-1"></span>
                  {{ __('common.verifications') }}
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
@endsection
