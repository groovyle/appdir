<?php
$show_filters = $filter_count > 0;
$hide_filters = !$show_filters;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/settings.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/settings.page_title.index') }}
<span class="page-sub-title">{{ __('common.total_x', ['x' => $total]) }}</span>
@endsection

@section('content')
	<div class="alert alert-warning">
		<div class="icon-text-pair icon-color-reset">
			<span class="fas fa-exclamation-triangle icon icon-2x mt-2 mr-2"></span>
			<span>@lang('admin/settings.management_warning')</span>
		</div>
	</div>

	<div class="mt-2 mb-3">
		@can('create', App\Models\Setting::class)
		<a href="{{ route('admin.settings.create') }}" class="btn btn-primary">{{ __('admin/settings.add_new_setting') }}</a>
		@endcan
	</div>

	<!-- Filters -->
	<form class="card card-primary card-outline filters-wrapper @if($hide_filters) collapsed-card @endif" method="GET" action="{{ route('admin.settings.index') }}">
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
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.settings.index') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content @if($show_filters || request()->has('page')) scroll-to-me @endif">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/settings.settings_list') }} ({{ count($list) }})</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		@if(count($list) == 0)
		<div class="card-body">
			@if($total == 0)
			<h4 class="text-left">&ndash; {{ __('admin/settings.no_settings_yet') }} &ndash;</h4>
			@else
			<h5 class="text-left">&ndash; {{ __('admin/settings.no_settings_matches') }} &ndash;</h5>
			@endif
		</div>
		@else
		<div class="card-body p-0 pb-1">
			<div class="table-responsive">
				<table class="table table-head-fixed table-hover table-sm">
					<thead>
						<tr>
							<th style="width: 50px;">{{ __('common.#') }}</th>
							<th class="text-primary">{{ __('admin/settings.fields.key') }}</th>
							<th style="width: 20%;">{{ __('admin/settings.fields.value') }}</th>
							<th style="width: 30%;">{{ __('admin/common.fields.description') }}</th>
							<th style="width: 1%;">{{ __('common.actions') }}</th>
						</tr>
					</thead>
					<tbody class="text-090">
						@foreach($list as $item)
						<tr class="setting-item-{{ Str::slug($item->key) }}">
							<td class="text-right">{{ $loop->iteration }}</td>
							<td class="text-monospace text-wrap-word">{{ $item->key }}</td>
							<td class="text-monospace">@voe($item->value)</td>
							<td class=""><span class="init-readmore text-pre-wrap">@voe($item->description)</span></td>
							<td class="text-nowrap">
								@can('view', $item)
								<a href="{{ route('admin.settings.show', ['stt' => $item->key]) }}" class="btn btn-default btn-xs text-nowrap btn-ofa-modal" data-title="{{ __('admin/settings.page_title.detail') }}: {{ text_truncate($item->key, 30) }}" data-footer="false">
									<span class="fas fa-search mr-1"></span>
									{{ __('common.view') }}
								</a>
								@endcan
								@can('update', $item)
								<a href="{{ route('admin.settings.edit', ['stt' => $item->key, 'backto' => 'list']) }}" class="btn btn-primary btn-xs text-nowrap">
									<span class="fas fa-edit mr-1"></span>
									{{ __('common.edit') }}
								</a>
								@endcan
								@can('delete', $item)
								<a href="{{ route('admin.settings.destroy', ['stt' => $item->key, 'backto' => 'back']) }}" class="btn btn-danger btn-xs text-nowrap btn-ays-modal" data-method="DELETE" data-prompt="_delete" data-description="{{ sprintf('<strong>%s</strong>: %s = %s', __('admin/settings._self'), $item->key, voe($item->value)) }}">
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
	var $scrollTarget = $(@json('.setting-item-'.$goto_item));
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
