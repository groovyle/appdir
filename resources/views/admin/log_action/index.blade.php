<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
$scroll_content = !isset($goto_item) && ($show_filters || request()->has('page'));
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/log_actions.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/log_actions.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total]) }}</span>
@endsection

@section('content')
	<!-- Filters -->
	<form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.log_actions.index') }}">
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
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.log_actions.index') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content @if($scroll_content) scroll-to-me @endif">
		<div class="card-header">
			<h3 class="card-title">
				{{ __('admin/log_actions.logs_list') }} ({{ $list->total() }})
				&mdash;
				<em class="text-muted text-080">{{ __('admin/log_actions.logs_sort_desc') }}</em>
			</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		@if($list->isEmpty())
		<div class="card-body">
			@if($total == 0)
			<h4 class="text-left">&ndash; {{ __('admin/log_actions.no_log_actions_yet') }} &ndash;</h4>
			@else
			<h5 class="text-left">&ndash; {{ __('admin/log_actions.no_log_actions_matches') }} &ndash;</h5>
			@endif
		</div>
		@else
		<div class="card-body p-0 pb-1">
			<div class="table-responsive">
				<table class="table table-head-fixed table-hover table-sm text-090">
					<thead>
						<tr>
							<th class="text-right" style="width: 50px;">{{ __('common.#') }}</th>
							<th class="text-center" style="width: 50px;">{{ __('admin/common.fields.id') }}</th>
							<th class="">Entity Type</th>
							<th class="">Entity ID</th>
							<th class="">Action</th>
							<th class="">Actor Name</th>
							<th class="">Actor ID</th>
							<th class="">Related Type</th>
							<th class="">Related ID</th>
							<th class="">{{ __('admin/common.fields.description') }}</th>
							<th class="">At</th>
							<th style="width: 1%;"></th>
						</tr>
					</thead>
					<tbody>
						@foreach($list as $item)
						<tr class="log-action-item-{{ $item->id }}">
							<td class="text-right">{{ $list->firstItem() + $loop->index }}</td>
							<td class="text-center">{{ $item->id }}</td>
							<td>@vo_($item->entity_type)</td>
							<td>@vo_($item->entity_id)</td>
							<td>@vo_($item->action)</td>
							<td>@vo_($item->actor_name)</td>
							<td>@vo_($item->actor_id)</td>
							<td>@vo_($item->related_type)</td>
							<td>@vo_($item->related_id)</td>
							<td><span class="init-readmore">@vo_($item->actor_description)</span></td>
							<td>
								@if($item->at)
								@include('components.date-with-tooltip', ['date' => $item->at, 'format' => 'j F Y, H:i:s'])
								@else
								@vo_
								@endif
							</td>
							<td class="text-nowrap">
								@can('view', $item)
								<a href="{{ route('admin.log_actions.show', ['log' => $item->id]) }}" class="btn btn-default btn-xs text-nowrap btn-ofa-modal" data-title="{{ __('admin/log_actions.page_title.detail') }}: {{ __('admin/log_actions.log_x', ['x' => $item->id]) }}" data-footer="false">
									<span class="fas fa-search mr-1"></span>
									{{ __('common.view') }}
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
	var $scrollTarget = $(@json('.log-action-item-'.$goto_item));
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
