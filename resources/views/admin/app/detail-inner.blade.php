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

@section('detail-content')
      <dl class="row">
        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.field.name') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">{{ $app->name }}</dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.field.short_name') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">@von($app->short_name)</dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.field.logo') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x150'])
        </dd>

        <dt class="col-12 col-md-3 col-xl-2">{{ __('admin/apps.field.description') }}</dt>
        <dd class="col-12 col-md-9 col-xl-10 text-pre-wrap">@von($app->description)</dd>

        <dt class="col-12 col-sm-3 col-xl-2">
          {{ __('admin/apps.field.categories') }}
          ({{ $app->categories->count() }})
        </dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @if($app->categories->isNotEmpty())
          @each('components.app-category', $app->categories, 'category')
          @else
          @voe()
          @endif
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">
          {{ __('admin/apps.field.tags') }}
          ({{ $app->tags->count() }})
        </dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @if($app->tags->isNotEmpty())
          @each('components.app-tag', $app->tags, 'tag')
          @else
          @voe()
          @endif
        </dd>

        <dt class="col-12">
          {{ __('admin/apps.field.visuals') }}
          ({{ $app->visuals->count() }})
          @if(!$is_snippet)
          <a href="{{ route('admin.apps.visuals', ['app' => $app->id]) }}" class="text-info ml-2" title="@lang('admin/apps.edit_visuals')" data-toggle="tooltip"><span class="fas fa-edit"></span></a>
          @endif
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
              <div class="card-body py-1 px-2">
                <p class="card-text text-center text-secondary mb-1">
                  <a href="#" class="cursor-pointer text-reset" role="button" tabindex="0" title="{{ __('admin/apps.field.caption') }}" data-content="@voe($visual->caption, false)" data-toggle="popover" data-trigger="hover focus" data-placement="right">({{ $i }})</a>
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

        <dt class="col-sm-3 col-xl-2">{{ __('admin/apps.field.status') }}</dt>
        <dd class="col-sm-9 col-xl-10">@include('components.app-verification-status', ['app' => $app])</dd>
      </dl>
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
