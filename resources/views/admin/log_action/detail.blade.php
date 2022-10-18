<?php
$append_breadcrumb = [
  [
    'text'    => $log->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/log_actions.tab_title.detail', ['x' => text_truncate($log->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/log_actions.page_title.detail'))

@section('content')
<div class="mb-2">
  @can('view-any', App\Models\LogAction::class)
  <a href="{{ route('admin.log_actions.index', ['goto_item' => $log->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
      <dl class="details-dl">
        <dt>@lang('admin/common.fields.id')</dt>
        <dd>{{ $log->id }}</dd>

        <div class="row gutter-lg d-table">
          <div class="col d-table-cell w-auto">
            <dt>Entity Type</dt>
            <dd>@vo_($log->entity_type)</dd>
          </div>
          <div class="col d-table-cell w-auto">
            <dt>Entity ID</dt>
            <dd>@vo_($log->entity_id)</dd>
          </div>
        </div>

        <div class="row gutter-lg d-table">
          <div class="col d-table-cell w-auto">
            <dt>Related Type</dt>
            <dd>@vo_($log->related_type)</dd>
          </div>
          <div class="col d-table-cell w-auto">
            <dt>Related ID</dt>
            <dd>@vo_($log->related_id)</dd>
          </div>
        </div>

        <dt>Action</dt>
        <dd>@vo_($log->action)</dd>

        <div class="row gutter-lg d-table">
          <div class="col d-table-cell w-auto">
            <dt>Actor</dt>
            <dd>@vo_($log->actor_name)</dd>
          </div>
          <div class="col d-table-cell w-auto">
            <dt>Actor ID</dt>
            <dd>@vo_($log->actor_id)</dd>
          </div>
        </div>

        <dt>@lang('admin/common.fields.description')</dt>
        <dd><span class="text-pre-wrap">@vo_($log->description)</span></dd>

        <dt>At</dt>
        <dd>
          @if($log->at)
          @include('components.date-with-tooltip', ['date' => $log->at, 'format' => 'j F Y, H:i:s'])
          @else
          @vo_
          @endif
        </dd>
      </dl>
    </div>
  </div>
</div>
@endsection