@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_verifications.tab_title') }} - @parent
@endsection

@section('page-title', __('admin/app_verifications.page_title.index'))

@section('content')
  <!-- Card -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin/app_verifications.submissions') }}</h3>
    </div>
    @if (empty($unverified))
    <div class="card-body">
      <h4 class="text-left">{{ __('admin/app_verifications.no_app_submissions_yet') }}</h4>
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
              <td>{{ $app->name }}</td>
              <td>
                @include('components.app-verification-status', ['app' => $app])
              </td>
              <td class="text-nowrap">
                <a href="{{ route('admin.app_verifications.review', ['app' => $app->id]) }}" class="btn btn-primary btn-sm">
                  <span class="fas fa-clipboard-check mr-1"></span>
                  {{ __('admin/app_verifications.verify') }}
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

  @if (!empty($verified))
  <!-- Card -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ __('admin.app.verified_apps') }}</h3>
    </div>
    <div class="card-body">
      <div class="row">
        @foreach ($verified as $app)
        <div class="card mx-2 mb-2" style="width: 14rem; line-height: 1.2;">
          <div class="card-img-top" style="background-color: #868e96; min-height: 3rem; max-height: 7rem; overflow: hidden;">
            @if ($app->visual_count)
            <img class="d-block mw-100 mh-100 m-auto" src="{{ $app->visuals[0]->url }}" alt="Thumbnail">
            @else
            <span>{{ __('admin.app.message.no_visuals') }}</span>
            @endif
          </div>
          <div class="card-body p-2">
            <h5><a href="javascript:void(0)" class="stretched-link">{{ $app->name }}</a></h5>
            <p class="small mb-0">
              {!! description_text(Str::limit($app->description, 30)) !!}
            </p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
  @endif

@endsection
