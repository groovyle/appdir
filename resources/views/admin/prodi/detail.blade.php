<?php
$append_breadcrumb = [
  [
    'text'    => $prodi->name,
  ]
];
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/prodi.tab_title.detail', ['x' => text_truncate($prodi->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/prodi.page_title.detail'))

@section('content')
<div class="mb-2">
  @can('view-any', App\Models\Prodi::class)
  <a href="{{ route('admin.prodi.index', ['goto_item' => $prodi->id]) }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
  @endcan
  @can('update', $prodi)
  <a href="{{ route('admin.prodi.edit', ['prodi' => $prodi->id]) }}" class="btn btn-sm btn-primary">
    <span class="fas fa-edit"></span>
    {{ __('admin/prodi.edit_prodi') }}
  </a>
  @endcan
  @can('delete', $prodi)
  <a href="{{ route('admin.prodi.destroy', ['prodi' => $prodi->id]) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/prodi._self'), $prodi->complete_name, __('admin/common.fields.id'), $prodi->id) }}">
    <span class="fas fa-trash mr-1"></span>
    {{ __('common.delete') }}
  </a>
  @endcan
</div>
<div class="card">
  <div class="card-body">
    <div class="main-content">
      <dl class="details-dl">
        <dt>@lang('admin/common.fields.id')</dt>
        <dd>{{ $prodi->id }}</dd>

        <dt>@lang('admin/common.fields.name')</dt>
        <dd>{{ $prodi->name }}</dd>

        <dt>@lang('admin/common.fields.short_name')</dt>
        <dd>@von($prodi->short_name)</dd>

        <dt>@lang('admin/common.fields.number_of_users')</dt>
        <dd class="@if($prodi->users_count == 0) text-secondary @endif">
          {{ $prodi->users_count }}
          @can('view-any', App\User::class)
          @if($prodi->users_count > 0)
          <a href="{{ route('admin.users.index', ['prodi_id' => $prodi->id]) }}" class="text-secondary px-1 py-1 ml-2" title="@lang('admin/prodi.see_users_in_this_prodi')" data-toggle="tooltip"><span class="fas fa-folder-open"></span></a>
          @endif
          @endcan
        </dd>

        <dt>@lang('admin/common.fields.description')</dt>
        <dd><span class="init-readmore">@voe($prodi->description)</span></dd>

        <dt>@lang('admin/common.fields.last_updated')</dt>
        <dd>@include('components.date-with-tooltip', ['date' => $prodi->updated_at])</dd>
      </dl>
      @if($ajax)
      <div class="mt-2">
        @can('update', $prodi)
        <a href="{{ route('admin.prodi.edit', ['prodi' => $prodi->id, 'backto' => 'list']) }}" class="btn btn-sm btn-primary">
          <span class="fas fa-edit"></span>
          {{ __('admin/prodi.edit_prodi') }}
        </a>
        @endcan
        @can('delete', $prodi)
        <a href="{{ route('admin.prodi.destroy', ['prodi' => $prodi->id, 'backto' => 'back']) }}" class="btn btn-danger btn-sm text-nowrap btn-ays-modal ml-3" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/prodi._self'), $prodi->complete_name, __('admin/common.fields.id'), $prodi->id) }}">
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