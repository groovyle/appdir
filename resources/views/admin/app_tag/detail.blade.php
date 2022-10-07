<?php
$append_breadcrumb = [
  [
    'text'    => $tag->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_tags.tab_title.detail', ['x' => text_truncate($tag->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/app_tags.page_title.detail'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.app_tags.index', ['goto_item' => $tag->name]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  <a href="{{ route('admin.app_tags.edit', ['tag' => $tag->name]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/app_tags.edit_tag') }}
  </a>
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
      <dl class="details-dl">
        <dt>@lang('admin/common.fields.name')</dt>
        <dd>{{ $tag->name }}</dd>

        <dt>@lang('admin/common.fields.number_of_apps')</dt>
        <dd class="@if($tag->apps_count == 0) text-secondary @endif">
          {{ $tag->apps_count }}
          @if($tag->apps_count > 0)
          <a href="{{ route('admin.apps.index', ['tags' => $tag->name]) }}" class="text-secondary px-1 py-1 ml-2" title="@lang('admin/app_tags.see_apps_in_this_tag')" data-toggle="tooltip"><span class="fas fa-folder-open"></span></a>
          @endif
        </dd>

        <dt>@lang('admin/common.fields.description')</dt>
        <dd><span class="text-pre-wrap">@voe($tag->description)</span></dd>

        <dt>@lang('admin/common.fields.last_updated')</dt>
        <dd>@include('components.date-with-tooltip', ['date' => $tag->updated_at])</dd>
      </dl>
      @if($ajax)
      <div class="mt-2">
        <a href="{{ route('admin.app_tags.edit', ['tag' => $tag->name, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
          <span class="fas fa-edit"></span>
          {{ __('admin/app_tags.edit_tag') }}
        </a>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection