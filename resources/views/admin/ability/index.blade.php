<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/abilities.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/abilities.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total]) }}</span>
@endsection

@section('content')

	<div class="alert alert-warning">
		@lang('admin/abilities.management_warning')
	</div>

	<div class="mt-2 mb-3">
		<a href="{{ route('admin.abilities.create') }}" class="btn btn-primary">{{ __('admin/abilities.add_new_ability') }}</a>
	</div>

	<!-- Filters -->
	<form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.abilities.index') }}">
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
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.abilities.index') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content @if($show_filters || request()->has('page')) scroll-to-me @endif">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/abilities.abilities_list') }} ({{ $list->total() }})</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		@if($list->isEmpty())
		<div class="card-body">
			@if($total == 0)
			<h4 class="text-left">&ndash; {{ __('admin/abilities.no_abilities_yet') }} &ndash;</h4>
			@else
			<h5 class="text-left">&ndash; {{ __('admin/abilities.no_abilities_matches') }} &ndash;</h5>
			@endif
		</div>
		@else
		<div class="card-body p-0 pb-1">
			<div class="table-responsive">
				<table class="table table-head-fixed table-hover table-sm">
					<thead>
						<tr>
							<th style="width: 50px;">{{ __('common.#') }}</th>
							<th>{{ __('admin/abilities.fields.title') }}</th>
							<th>{{ __('admin/abilities.fields.name') }}</th>
							<th style="width: 10%;">{{ __('admin/abilities.fields.entity_type') }}</th>
							<th style="width: 5%;">{{ __('admin/abilities.fields.entity_id') }}</th>
							<th style="width: 5%;">{{ __('admin/abilities.fields.only_owned') }}</th>
							<th style="width: 1%;">{{ __('common.actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($list as $item)
						<tr class="ability-item-{{ $item->id }}">
							<td class="text-right">{{ $list->firstItem() + $loop->index }}</td>
							<td>@voe($item->title)</td>
							<td>{{ $item->name }}</td>
							<td class="text-wrap-word text-085">@vo_($item->entity_type)</td>
							<td class="text-085">@vo_($item->entity_id)</td>
							<td>@include('admin.components.yesno', ['value' => $item->only_owned, 'lower' => true, 'color_yes' => 'text-primary text-bold'])</td>
							<td class="text-nowrap">
								<a href="{{ route('admin.abilities.show', ['abl' => $item->id]) }}" class="btn btn-default btn-xs text-nowrap btn-ofa-modal" data-title="{{ __('admin/abilities.page_title.detail') }}: {{ text_truncate($item->title, 30) }}" data-footer="false" data-size="lg">
									<span class="fas fa-search mr-1"></span>
									{{ __('common.view') }}
								</a>
								<a href="{{ route('admin.abilities.edit', ['abl' => $item->id, 'backto' => 'list']) }}" class="btn btn-primary btn-xs text-nowrap">
									<span class="fas fa-edit mr-1"></span>
									{{ __('common.edit') }}
								</a>
								<a href="{{ route('admin.abilities.destroy', array_merge(['abl' => $item->id])) }}" class="btn btn-danger btn-xs text-nowrap btn-ays-modal" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s (%s: %s)', __('admin/abilities._self'), $item->name, __('admin/common.fields.id'), $item->id) }}">
									<span class="fas fa-trash mr-1"></span>
									{{ __('common.delete') }}
								</a>
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
	var $scrollTarget = $(@json('.ability-item-'.$goto_item));
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
