<?php
$append_breadcrumb = [
  [
    'text'    => $stt->key,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/settings.tab_title.detail', ['x' => text_truncate($stt->key, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/settings.page_title.detail'))

@section('content')
<div class="mb-2">
  @can('view-any', App\Models\AppCategory::class)
  <a href="{{ route('admin.settings.index', ['goto_item' => $stt->key]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
  @can('update', $stt)
  <a href="{{ route('admin.settings.edit', ['stt' => $stt->key]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/settings.edit_setting') }}
  </a>
  @endcan
  @can('delete', $stt)
  <a href="{{ route('admin.settings.destroy', ['stt' => $stt->key]) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s = %s', __('admin/settings._self'), $stt->key, voe($stt->value)) }}">
    <span class="fas fa-trash mr-1"></span>
    {{ __('common.delete') }}
  </a>
  @endcan
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
      <dl class="details-dl">
        <dt>@lang('admin/settings.fields.key')</dt>
        <dd class="text-monospace">{{ $stt->key }}</dd>

        <dt>@lang('admin/settings.fields.value')</dt>
        <dd class="text-monospace">@voe($stt->value)</dd>

        <dt>@lang('admin/common.fields.description')</dt>
        <dd><span class="init-readmore text-pre-wrap">@voe($stt->description)</span></dd>
      </dl>
      @if($ajax)
      <div class="mt-2">
        @can('update', $stt)
        <a href="{{ route('admin.settings.edit', ['stt' => $stt->key, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
          <span class="fas fa-edit"></span>
          {{ __('admin/settings.edit_setting') }}
        </a>
        @endcan
        @can('delete', $stt)
        <a href="{{ route('admin.settings.destroy', ['stt' => $stt->key, 'backto' => 'back']) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s = %s', __('admin/settings._self'), $stt->key, voe($stt->value)) }}">
          <span class="fas fa-trash mr-1"></span>
          {{ __('common.delete') }}
        </a>
        @endcan
      </div>
      @endif
    </div>
  </div>
</div>
@endsection