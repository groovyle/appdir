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
              <th>{{ __('admin/apps.fields.name') }}</th>
              <th style="width: 20%;">{{ __('admin/apps.fields.categories') }}</th>
              <th style="width: 20%;">{{ __('admin/apps.fields.tags') }}</th>
              <th style="width: 1%;">{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($verified as $app)
            <tr>
              <td class="text-right">{{ $loop->iteration }}</td>
              <td>
                <div>
                  {{ $app->complete_name }}
                  <a href="{{ $app->public_url }}" target="_blank" class="text-success ml-2" title="@lang('admin/apps.app_had_been_verified')" data-toggle="tooltip">
                    <span class="fas fa-check-circle"></span>
                  </a>
                  @if($app->has_floating_changes)
                  <a href="{{ route('admin.apps.show', ['app' => $app->id, 'show_pending' => '']) }}" class="text-warning ml-2" title="@lang('admin/apps.app_has_pending_changes')" data-toggle="tooltip">
                    <span class="fas fa-question-circle"></span>
                  </a>
                  @endif
                </div>
                @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x80', 'none' => false])
              </td>
              <td>
                @if($app->categories->isNotEmpty())
                @each('components.app-category', $app->categories, 'category')
                @else
                @voe()
                @endif
              </td>
              <td>
                @if($app->tags->isNotEmpty())
                @each('components.app-tag', $app->tags, 'tag')
                @else
                @voe()
                @endif
              </td>
              <td class="text-nowrap">
                <a href="{{ route('admin.apps.show', ['app' => $app->id]) }}" class="btn btn-default btn-sm text-nowrap">
                  <span class="fas fa-search mr-1"></span>
                  {{ __('common.view') }}
                </a>
                {{--
                <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-primary btn-sm text-nowrap">
                  <span class="fas fa-edit mr-1"></span>
                  {{ __('common.edit') }}
                </a>
                --}}
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
      <h3 class="card-title">{{ __('admin/apps.app_submissions') }}</h3>

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
              <th>{{ __('admin/apps.fields.name') }}</th>
              <th>{{ __('admin/apps.fields.submission_status') }}</th>
              <th style="width: 1%;">{{ __('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($unverified as $app)
            <tr>
              <td class="text-right">{{ $loop->iteration }}</td>
              <td>
                <div>{{ $app->complete_name }}</div>
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
              <td class="text-nowrap">
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
                  {{ __('admin/apps.verifications') }}
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
