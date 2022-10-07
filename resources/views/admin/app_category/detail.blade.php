<?php
$append_breadcrumb = [
  [
    'text'    => $cat->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/app_categories.tab_title.detail', ['x' => text_truncate($cat->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/app_categories.page_title.detail'))

@section('content')
<div class="mb-2">
  <a href="{{ route('admin.app_categories.index', ['goto_item' => $cat->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  <a href="{{ route('admin.app_categories.edit', ['cat' => $cat->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/app_categories.edit_category') }}
  </a>
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
      <dl class="details-dl">
        <dt>@lang('admin/common.fields.id')</dt>
        <dd>{{ $cat->id }}</dd>

        <dt>@lang('admin/common.fields.name')</dt>
        <dd>{{ $cat->name }}</dd>

        <dt>@lang('admin/common.fields.number_of_apps')</dt>
        <dd class="@if($cat->apps_count == 0) text-secondary @endif">
          {{ $cat->apps_count }}
          @if($cat->apps_count > 0)
          <a href="{{ route('admin.apps.index', ['categories' => $cat->id]) }}" class="text-secondary px-1 py-1 ml-2" title="@lang('admin/app_categories.see_apps_in_this_category')" data-toggle="tooltip"><span class="fas fa-folder-open"></span></a>
          @endif
        </dd>

        <dt>@lang('admin/common.fields.description')</dt>
        <dd><span class="text-pre-wrap">@voe($cat->description)</span></dd>

        <dt>@lang('admin/common.fields.last_updated')</dt>
        <dd>@include('components.date-with-tooltip', ['date' => $cat->updated_at])</dd>
      </dl>
      @if($ajax)
      <div class="mt-2">
        <a href="{{ route('admin.app_categories.edit', ['cat' => $cat->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
          <span class="fas fa-edit"></span>
          {{ __('admin/app_categories.edit_category') }}
        </a>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection