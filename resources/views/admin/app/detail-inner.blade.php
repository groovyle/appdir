<?php
if(!isset($is_snippet))
  $is_snippet = false;
?>

@push('head-additional')
<style>
  .table-horizontal th,
  .table-horizontal td {
    vertical-align: top;
  }
</style>
@endpush

@section('content')
  @if(!$is_snippet)
  <div class="mb-2 d-flex">
    <div class="details-nav-left mr-auto">
      <a href="{{ route('admin.apps.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
      <a href="{{ route('admin.apps.edit', ['app' => $app->id]) }}" class="btn btn-sm btn-primary">
        <span class="fas fa-edit"></span>
        {{ __('common.edit') }}
      </a>
      <a href="{{ route('admin.apps.visuals', ['app' => $app->id]) }}" class="btn btn-sm btn-default">
        <span class="fas fa-photo-video"></span>
        {{ __('admin.app.edit_visuals') }}
      </a>
    </div>
    <div class="details-nav-right ml-auto">
      <a href="{{ route('admin.apps.changes', ['app' => $app->id, 'current' => '']) }}" class="btn btn-sm btn-secondary">
        <span class="fas fa-tasks"></span>
        {{ __('admin.app.changelog') }}
      </a>
    </div>
  </div>
  @endif
  <!-- Card -->
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3 col-xl-2">{{ __('admin/app.field.name') }}</dt>
        <dd class="col-sm-9 col-xl-10">{{ $app->name }}</dd>

        <dt class="col-sm-3 col-xl-2">{{ __('admin/app.field.short_name') }}</dt>
        <dd class="col-sm-9 col-xl-10">@von($app->short_name)</dd>

        <dt class="col-sm-3 col-xl-2">{{ __('admin/app.field.logo') }}</dt>
        <dd class="col-sm-9 col-xl-10">
          @if($app->logo)
          <a href="{{ $app->logo->url }}" target="_blank"><img rel="logo" src="{{ $app->logo->url }}" class="img-responsive" style="max-width: 150px; max-height: 150px;"></a>
          @else
          @von($app->logo)
          @endif
        </dd>

        <dt class="col-md-3 col-xl-2">{{ __('admin/app.field.description') }}</dt>
        <dd class="col-md-9 col-xl-10 text-pre-wrap">@von($app->description)</dd>

        <dt class="col-sm-3 col-xl-2">
          {{ __('admin/app.field.categories') }}
          ({{ $app->categories->count() }})
        </dt>
        <dd class="col-sm-9 col-xl-10">
          @if($app->categories->isNotEmpty())
          @each('components.app-category', $app->categories, 'category')
          @else
          &ndash;
          @endif
        </dd>

        <dt class="col-sm-3 col-xl-2">
          {{ __('admin/app.field.tags') }}
          ({{ $app->tags->count() }})
        </dt>
        <dd class="col-sm-9 col-xl-10">
          @if($app->tags->isNotEmpty())
          @each('components.app-tag', $app->tags, 'tag')
          @else
          &ndash;
          @endif
        </dd>

        <dt class="col-12">
          {{ __('admin/app.field.visuals') }}
          ({{ $app->visuals->count() }})
        </dt>
        <dd class="col-12">
          <div class="thumb-cards d-flex flex-row flex-nowrap justify-content-start align-items-start mb-2 ofx-auto">
          @foreach ($app->visuals as $visual)
          @php
          $i = $loop->iteration;
          @endphp
          <div class="thumb-item mr-2 mb-2">
            <div class="card m-0">
              <a class="card-img-top" href="{{ $visual->url }}" target="_blank">
                <img src="{{ $visual->thumbnail_url }}" alt="{{ __('common.visual').' '.$i }}">
              </a>
              <div class="card-body p-1">
                <p class="card-text text-center text-secondary mb-1">
                  <a href="#" class="cursor-pointer text-reset" role="button" tabindex="0" title="{{ __('admin/app.field.caption') }}" data-content="@voe($visual->caption, false)" data-toggle="popover" data-trigger="focus" data-placement="right">({{ $i }})</a>
                  @if($visual->type == 'image')
                  @elseif($visual->complete_type == 'video.youtube')
                  &nbsp;<a href="{{ $visual->url }}" target="_blank">{{ $visual->url }}</a>
                  @endif
                </p>
              </div>
            </div>
          </div>
          @endforeach
          </div>
        </dd>

        <dt class="col-sm-3 col-xl-2">{{ __('admin/app.field.status') }}</dt>
        <dd class="col-sm-9 col-xl-10">@include('components.app-verification-status', ['app' => $app])</dd>

        <h3>TODO: show pending changes</h3>
      </dl>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
@endsection

@push('scripts')
<script>
jQuery(document).ready(function($) {
  $('[data-toggle="popover"]').popover({
    container: "body",
  });
});
</script>
@endpush
