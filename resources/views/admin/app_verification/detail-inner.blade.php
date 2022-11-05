<?php
if(!isset($is_snippet))
  $is_snippet = false;

$hide_changes = !!($hide_changes ?? false);
if($hide_changes) {
  $old_attributes = optional();
  $diff_relations = optional();
} else {
  $old_attributes = optional($version->display_diffs['attributes']['old'] ?? null);
  $diff_relations = optional($version->display_diffs['relations'] ?? null);
}

$comments = optional($verif->details ?? null);

$rand = random_alpha(5);
?>

@section('detail-content')
      <dl class="row">
        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.fields.owner') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          {{ $app->owner->name_email }}
          @include('admin.app.components.owned-icon')
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.fields.name') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @von($app->name)

          @component('admin.app.components.detail-old-value')
            @isset($old_attributes['name'])
              @voe($old_attributes['name'])
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['name'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.fields.short_name') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @von($app->short_name)

          @component('admin.app.components.detail-old-value')
            @isset($old_attributes['short_name'])
              @voe($old_attributes['short_name'])
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['short_name'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.fields.url') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @if($app->url)
          <a href="{{ $app->url }}" target="_blank">{{ $app->url }} <span class="fas fa-external-link-alt text-080 ml-1"></span></a>
          @else
          @von
          @endif

          @component('admin.app.components.detail-old-value')
            @isset($old_attributes['url'])
              @if($old_attributes['url'])
              <a href="{{ $old_attributes['url'] }}" target="_blank">{{ $old_attributes['url'] }} <span class="fas fa-external-link-alt text-080 ml-1"></span></a>
              @else
              @von
              @endif
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['url'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">{{ __('admin/apps.fields.logo') }}</dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @include('components.app-logo', ['logo' => $app->logo, 'size' => '150x150'])

          @component('admin.app.components.detail-old-value')
            @if(is_array($diff_relations['logo']) && array_key_exists('old', $diff_relations['logo']))
              @include('components.app-logo', ['logo' => $diff_relations['logo']['old'], 'size' => '80x80'])
            @endif
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['logo'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-md-3 col-xl-2">{{ __('admin/apps.fields.description') }}</dt>
        <dd class="col-12 col-md-9 col-xl-10">
          <span class="init-readmore">@von($app->description)</span>

          @component('admin.app.components.detail-old-value')
            @isset($old_attributes['description'])
              <span class="init-readmore">@voe($old_attributes['description'])</span>
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['description'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">
          {{ __('admin/apps.fields.categories') }}
          ({{ $app->categories->count() }})
        </dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @if($app->categories->isNotEmpty())
          @each('components.app-category', $app->categories, 'category')
          @else
          @voe
          @endif

          @component('admin.app.components.detail-old-value')
            @if(is_array($diff_relations['categories']) && array_key_exists('old', $diff_relations['categories']))
              @if(($count = count($diff_relations['categories']['old'])) > 0)
              (@lang('common.total_x', ['x' => $count]))
              @each('components.app-category', $diff_relations['categories']['old'], 'category')
              @else
              @voe
              @endif
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['categories'] }}
          @endcomponent
        </dd>

        <dt class="col-12 col-sm-3 col-xl-2">
          {{ __('admin/apps.fields.tags') }}
          ({{ $app->tags->count() }})
        </dt>
        <dd class="col-12 col-sm-9 col-xl-10">
          @if($app->tags->isNotEmpty())
          @each('components.app-tag', $app->tags, 'tag')
          @else
          @voe
          @endif

          @component('admin.app.components.detail-old-value')
            @if(is_array($diff_relations['tags']) && array_key_exists('old', $diff_relations['tags']))
              @if(($count = count($diff_relations['tags']['old'])) > 0)
              (@lang('common.total_x', ['x' => $count]))
              @each('components.app-tag', $diff_relations['tags']['old'], 'tag')
              @else
              @voe
              @endif
            @endisset
          @endcomponent

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['tags'] }}
          @endcomponent
        </dd>

        <dt class="col-12">
          {{ __('admin/apps.fields.visuals') }}
          ({{ $app->visuals->count() }})

          @if(is_array($diff_relations['visuals']) && array_key_exists('old', $diff_relations['visuals']))
            <a href="#visuals-old-{{ $rand }}" class="fas fa-history text-warning text-090 ml-2" title="@lang('admin/apps.visuals.visual_comparison_detail')" data-toggle="collapse" role="button"></a>
          @endisset

          @component('admin.app_verification.components.btn-pop-comment')
          {{ $comments['visuals'] }}
          @endcomponent
        </dt>
        <dd class="col-12">
          @if(count($app->visuals) > 0)
          @include('admin.app.components.detail-visuals-list', ['visuals' => $app->visuals])
          @else
          @von
          @endif
        </dd>
        @if(is_array($diff_relations['visuals']) && array_key_exists('old', $diff_relations['visuals']))
        <dd class="col-12 collapse" id="visuals-old-{{ $rand }}">
          <div class="text-090 text-bold">@lang('admin/apps.visuals.old_visuals') ({{ count($diff_relations['visuals']['old']) }})</div>
          @include('admin.app.components.detail-visuals-list', ['visuals' => $diff_relations['visuals']['old'], 'smaller' => true])
        </dd>
        @endisset
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
