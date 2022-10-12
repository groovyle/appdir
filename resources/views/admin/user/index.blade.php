<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$show_type_col = $show_type_col ?? false;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/users.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total]) }}</span>
@endsection

@section('content')
	<div class="mt-2 mb-3">
		@can('create', App\User::class)
		<a href="{{ route('admin.users.create') }}" class="btn btn-primary">{{ __('admin/users.add_new_user') }}</a>
		@endcan
	</div>

	<!-- Filters -->
	<form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.users.index') }}">
		<div class="card-header">
			<h3 class="card-title cursor-pointer" data-card-widget="collapse">{{ __('admin/common.filters') }}</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
					<i class="fas @if($hide_filters) fa-plus @else fa-minus @endif"></i></button>
			</div>
		</div>
		<div class="card-body">
			<div class="form-horizontal">
				<div class="form-group row">
					<label for="fKeyword" class="col-sm-3 col-lg-2">{{ __('admin/common.keyword') }}</label>
					<div class="col-sm-8 col-lg-5">
						<input type="text" class="form-control" name="keyword" id="fKeyword" value="{{ $filters['keyword'] }}" placeholder="{{ __('admin/common.keyword') }}">
					</div>
				</div>
				<div class="form-group row">
					<label for="fProdi" class="col-sm-3 col-lg-2">{{ __('admin/users.fields.prodi') }}</label>
					<div class="col-sm-8 col-lg-5">
						<select class="form-control" name="prodi_id" id="fProdi">
							<option value="">&ndash; {{ __('admin/common.all') }} &ndash;</option>
							@foreach($prodis as $prodi)
							<option value="{{ $prodi->id }}" {!! old_selected('', $filters['prodi_id'], $prodi->id) !!}>{{ $prodi->complete_name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label for="fType" class="col-sm-3 col-lg-2">{{ __('admin/users.fields.entity_type') }}</label>
					<div class="col-sm-8 col-lg-5">
						<select class="form-control" name="type" id="fType">
							<option value="all" {!! old_selected('', $filters['type'], 'all') !!}>&ndash; {{ __('admin/common.all') }} &ndash;</option>
							<option value="user" {!! old_selected('', $filters['type'], 'user') !!}>{{ __('users.entity.user') }} <span class="text-muted">({{ __('admin/common.default') }})</span></option>
							<option value="system" {!! old_selected('', $filters['type'], 'system') !!}>{{ __('users.entity.system') }}</option>
						</select>
					</div>
				</div>
				<div class="form-group row">
					<label for="fSort" class="col-sm-3 col-lg-2">{{ __('admin/common.sort_by') }}</label>
					<div class="col-sm-8 col-lg-5">
						<select class="form-control" name="sort_by" id="fSort">
							<option value="name" disabled>&ndash; {{ __('admin/common.sort_by') }} &ndash;</option>
							<option value="name" {!! old_selected('', $filters['sort_by'], 'name') !!}>{{ __('admin/common.fields.name') }} <span class="text-muted">({{ __('admin/common.default') }})</span></option>
							<option value="apps" {!! old_selected('', $filters['sort_by'], 'apps') !!}>{{ __('admin/common.fields.number_of_apps') }}</option>
						</select>
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="offset-sm-3 offset-lg-2 col">
						<button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.users.index') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content @if($show_filters || request()->has('page')) scroll-to-me @endif">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/users.users_list') }} ({{ $list->total() }})</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		@if($list->isEmpty())
		<div class="card-body">
			@if($total == 0)
			<h4 class="text-left">&ndash; {{ __('admin/users.no_users_yet') }} &ndash;</h4>
			@else
			<h5 class="text-left">&ndash; {{ __('admin/users.no_users_matches') }} &ndash;</h5>
			@endif
		</div>
		@else
		<div class="card-body p-0 pb-1">
			<div class="table-responsive">
				<table class="table table-head-fixed table-hover table-sm">
					<thead>
						<tr>
							<th style="width: 1%;">{{ __('common.#') }}</th>
							<th class="@if($filters['sort_by'] == 'name') text-primary @endif" style="width: 20%;">{{ __('admin/common.fields.name') }}</th>
							@if($show_type_col)
							<th style="width: 15%;">{{ __('admin/users.fields.entity_type') }}</th>
							@endif
							<th style="width: 15%;">{{ __('admin/users.fields.prodi') }}</th>
							<th style="width: 20%;">{{ __('admin/users.fields.roles') }}</th>
							<th class="text-center @if($filters['sort_by'] == 'apps') text-primary @endif" style="width: 10%;">{{ __('admin/common.fields.number_of_apps') }}</th>
							<th style="width: 1%;">{{ __('common.actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($list as $item)
						<tr class="user-item-{{ $item->id }} @if($item->is_system) text-italic @endif">
							<td class="text-right text-unitalic">{{ $list->firstItem() + $loop->index }}</td>
							<td>
								@if(!$item->is_system)
								<div>{{ $item->name }}</div>
								<div class="mt-n1">
									<abbr class="d-inline-block text-085 pr-1" title="{{ __('admin/users.fields.email') }}" data-toggle="tooltip" data-placement="right">{{ $item->email }}</abbr>
								</div>
								@else
								<abbr title="{{ $item->name }}" data-toggle="tooltip">{{ $item->raw_name }}</abbr>
								@endif
							</td>
							@if($show_type_col)
							<td>{{ $item->entity_type }}</td>
							@endif
							<td>@von($item->prodi->compact_name)</td>
							<td>@von($item->roles_text)</td>
							<td class="text-center @if($item->apps_count == 0) text-muted @endif">
								@if(!$item->is_system)
								{{ $item->apps_count }}
								@else
								@vo_
								@endif
							</td>
							<td class="text-nowrap text-unitalic">
								@can('view', $item)
								<a href="{{ route('admin.users.show', ['user' => $item->id]) }}" class="btn btn-default btn-xs text-nowrap btn-ofa-modal" data-title="{{ __('admin/users.page_title.detail') }}: {{ text_truncate($item->name, 30) }}" data-footer="false">
									<span class="fas fa-search mr-1"></span>
									{{ __('common.view') }}
								</a>
								@endcan
								@can('update', $item)
								<a href="{{ route('admin.users.edit', ['user' => $item->id, 'backto' => 'list']) }}" class="btn btn-primary btn-xs text-nowrap">
									<span class="fas fa-edit mr-1"></span>
									{{ __('common.edit') }}
								</a>
								@endcan
								@can('delete', $item)
								<a href="{{ route('admin.users.destroy', ['user' => $item->id, 'backto' => 'back']) }}" class="btn btn-danger btn-xs text-nowrap btn-ays-modal" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/users._self'), $item->name, __('admin/common.fields.id'), $item->id) }}">
									<span class="fas fa-trash mr-1"></span>
									{{ __('common.delete') }}
								</a>
								@endcan
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		<!-- /.card-body -->
		@if($list->hasPages())
		<div class="card-footer">
			{{ $list->links() }}
		</div>
		@endif
		@endif
	</div>
@endsection

@push('scripts')
<script>
jQuery(document).ready(function($) {

	var $filterForm = $(".filters-wrapper");
	$filterForm.on("expanded.lte.cardwidget", function(e) {
		$filterForm.find("input, textarea, select").trigger("change");
	});

	@isset($goto_item)
	var $scrollTarget = $(@json('.user-item-'.$goto_item));
	if($scrollTarget.length > 0) {
		@if($goto_flash)
		Helpers.scrollAndFlash($scrollTarget, { animate: false }, { variant: "blue" });
		@else
		Helpers.scrollTo($scrollTarget, { animate: false });
		@endif
	}
	@endisset
});
</script>
@endpush
