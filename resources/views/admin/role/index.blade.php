<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$scroll_content = !isset($goto_item) && ($show_filters || request()->has('page'));
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/roles.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/roles.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total]) }}</span>
@endsection

@section('content')
	<div class="alert alert-warning">
		<div class="icon-text-pair icon-color-reset">
			<span class="fas fa-exclamation-triangle icon icon-2x mt-2 mr-2"></span>
			<span>@lang('admin/roles.management_warning')</span>
		</div>
	</div>

	<div class="mt-2 mb-3">
		@can('create', App\Models\Role::class)
		<a href="{{ route('admin.roles.create') }}" class="btn btn-primary">{{ __('admin/roles.add_new_role') }}</a>
		@endcan
	</div>

	<!-- Filters -->
	<form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.roles.index') }}">
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
				<div class="form-group row mb-0">
					<div class="offset-sm-3 offset-lg-2 col">
						<button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.roles.index') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content @if($scroll_content) scroll-to-me @endif">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/roles.roles_list') }} ({{ $list->total() }})</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		@if($list->isEmpty())
		<div class="card-body">
			@if($total == 0)
			<h4 class="text-left">&ndash; {{ __('admin/roles.no_roles_yet') }} &ndash;</h4>
			@else
			<h5 class="text-left">&ndash; {{ __('admin/roles.no_roles_matches') }} &ndash;</h5>
			@endif
		</div>
		@else
		<div class="card-body p-0 pb-1">
			<div class="table-responsive">
				<table class="table table-head-fixed table-hover table-sm">
					<thead>
						<tr>
							<th style="width: 50px;">{{ __('common.#') }}</th>
							<th>{{ __('admin/roles.fields.title') }}</th>
							<th>{{ __('admin/roles.fields.name') }}</th>
							<th class="text-center" style="width: 20%;">{{ __('admin/common.fields.number_of_users') }}</th>
							<th style="width: 1%;">{{ __('common.actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($list as $item)
						<tr class="role-item-{{ $item->id }}">
							<td class="text-right">{{ $list->firstItem() + $loop->index }}</td>
							<td>@voe($item->title)</td>
							<td>{{ $item->name }}</td>
							<td class="text-center @if($item->users_count == 0) text-muted @endif">{{ $item->users_count }}</td>
							<td class="text-nowrap">
								@can('view', $item)
								<a href="{{ route('admin.roles.show', ['role' => $item->id]) }}" class="btn btn-default btn-xs text-nowrap btn-ofa-modal" data-title="{{ __('admin/roles.page_title.detail') }}: {{ text_truncate($item->name, 30) }}" data-footer="false" data-size="lg">
									<span class="fas fa-search mr-1"></span>
									{{ __('common.view') }}
								</a>
								@endcan
								@can('update', $item)
								<a href="{{ route('admin.roles.edit', ['role' => $item->id, 'backto' => 'list']) }}" class="btn btn-primary btn-xs text-nowrap">
									<span class="fas fa-edit mr-1"></span>
									{{ __('common.edit') }}
								</a>
								@endcan
								@can('delete', $item)
								<a href="{{ route('admin.roles.destroy', ['role' => $item->id, 'backto' => 'back']) }}" class="btn btn-danger btn-xs text-nowrap btn-ays-modal" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/roles._self'), $item->name, __('admin/common.fields.id'), $item->id) }}">
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
	var $scrollTarget = $(@json('.role-item-'.$goto_item));
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
