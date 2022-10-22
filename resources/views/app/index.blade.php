<?php
// dd(explode(',', request('t', '')));
$selected_in_compiled = function($value, $data_key) {
	return in_array($value, explode(',', request($data_key))) ? 'selected="selected"' : '';
};

$filter_btn_class = $filter_count == 0 ? 'btn-light bordered' : 'btn-success';
?>

@extends('layouts.app')

@section('content')
<div class="container">
	<h1>@lang('frontend.apps.browse_apps')</h1>
	<form class="mb-4" method="GET" action="{{ route('apps') }}" id="searchForm">
		<div class="mb-2">
			<button type="button" class="btn {{ $filter_btn_class }} btn-sm text-100 rounded-pill px-3" data-toggle="collapse" data-target="#searchFormInner" style="min-width: 80px;">
				@lang('frontend.apps.filters')
				@if($filter_count > 0) ({{ $filter_count }}) @endif
			</button>
			@if($filter_count > 0)
			<button type="button" class="btn btn-secondary btn-sm rounded-pill btn-reset-form ml-1" title="@lang('frontend.apps.reset_btn')" data-toggle="tooltip">
				<span class="fas fa-times icon"></span>
			</button>
			@endif
		</div>
		<div class="collapse @if($show_filter) show @endif" id="searchFormInner">
			<input type="hidden" name="f" value="1" readonly>

			<div class="form-inline interactable-inputs">
				<div class="input-group-with-icon icon-append mb-2 mr-sm-3">
					<input type="text" class="form-control" name="s" value="{{ request('s') }}" placeholder="@lang('frontend.apps.search_placeholder')" >
					<button type="button" class="fas fa-times icon interactable btn-clear-input" title="@lang('common.clear')"></button>
				</div>
				<div class="mb-2 mr-sm-3" style="min-width: 200px;">
					<input type="hidden" name="c" id="inputCategories" value="{{ request('c') }}">
					<select class="w-100 compile-values" id="searchCategories" data-compile-to="#inputCategories" autocomplete="off" multiple>
						@foreach($categories as $category)
						<option value="{{ $category->id }}" {{ $selected_in_compiled($category->id, 'c') }}>{{ $category->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="mb-2 mr-sm-3" style="min-width: 200px;">
					<input type="hidden" name="t" id="inputTags" value="{{ request('t') }}">
					<select class="w-100 compile-values" id="searchTags" data-compile-to="#inputTags" autocomplete="off" multiple>
						@foreach($tags as $tag)
						<option value="{{ $tag->name }}" {{ $selected_in_compiled($tag->name, 't') }}>{{ $tag->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="mt-0">
				<button type="submit" class="btn btn-success btn-sm px-2">
					<span class="icon-text-pair icon-color-reset">
						<span class="fas fa-search icon"></span>
						<span>@lang('frontend.apps.search_btn')</span>
					</span>
				</button>
				<button type="button" class="btn btn-danger btn-sm px-2 btn-reset-form">
					<span class="icon-text-pair icon-color-reset">
						<span class="fas fa-times icon"></span>
						<span>@lang('frontend.apps.reset_btn')</span>
					</span>
				</button>
			</div>
		</div>
	</form>
	<div class="row justify-content-center">
		<div class="col">
			@if ($apps->isNotEmpty())
			@if($filter_count > 0)
			<h4>@lang('frontend.apps.showing_search_results_from_x_to_y_of_z', ['x' => $apps->firstItem(), 'y' => $apps->lastItem(), 'z' => $apps->total()])</h4>
			@else
			<h3>@lang('frontend.apps.all_apps') ({{ $total_all }})</h3>
			@endif
			@if(!$apps->onFirstPage())
			<div class="mt-2 mb-3">
				{{ $apps->links() }}
			</div>
			@endif
			<div class="app-list">
				@foreach ($apps as $app)
				<div class="app-item">
					<a class="card" href="{{ $app->public_url }}">
						<div class="card-img-top">
							<img src="{{ $app->small_thumbnail_url }}" alt="thumbnail">
						</div>
						<div class="app-number">#{{ $loop->index + $apps->firstItem() }}</div>
						<div class="card-body app-item-body text-wrap-word">
							<div class="app-header">
								@include('components.app-logo', ['logo' => $app->logo, 'exact' => '32x32', 'img_class' => 'app-logo', 'default' => false, 'none' => false, 'as_link' => false])
								<div class="app-title">
									<span class="text-primary">{{ $app->name }}</span>
									@if($app->short_name)
									<div class=""><span class="text-085 text-black-50">@lang('frontend.apps.aka')</span> <span class="text-090" title="@lang('frontend.apps.short_name')">{{ $app->short_name }}</span></div>
									@endif
								</div>
							</div>
							<p class="mt-1 mb-2">
								@lang('frontend.apps.by') {{ $app->owner }}
							</p>
							<?php
							$cats_text = [];
							foreach($app->categories as $i => $cat) {
								if($i < 2)  {
									$cats_text[] = $cat->name;
								} else {
									$cats_text[] = __('frontend.apps.and_x_more', ['x' => count($app->categories) - $i]);
									break;
								}
							}
							$cats_text = implode(', ', $cats_text);
							?>
							<p class="text-090 mt-auto mb-0 text-truncate" title="{{ $cats_text }}">@lang('frontend.apps.fields.categories'): @von($cats_text)</p>
							<?php
							$tags_text = [];
							foreach($app->tags as $i => $tag) {
								if($i < 3)  {
									$tags_text[] = $tag->name;
								} else {
									$tags_text[] = __('frontend.apps.and_x_more', ['x' => count($app->tags) - $i]);
									break;
								}
							}
							$tags_text = implode(', ', $tags_text);
							?>
							<p class="text-090 mt-1 mb-0" title="{{ $tags_text }}">@lang('frontend.apps.fields.tags'): @von($tags_text)</p>
						</div>
					</a>
				</div>
				@endforeach
			</div>
			<div class="mt-2">
				{{ $apps->links() }}
			</div>
			@else
			@if($filter_count == 0)
			<h3>{{ __('frontend.apps.message.no_apps_yet') }}</h3>
			@else
			<h3>{{ __('frontend.apps.message.no_matches') }}</h3>
			@endif
			@endif
		</div>
	</div>
</div>
@endsection

@include('libraries.select2')

@push('scripts')
<script>
jQuery(document).ready(function($) {

	var $searchForm = $("#searchForm"),
			$searchFormInner = $("#searchFormInner")
	;

	$searchForm.on("click", ".btn-clear-input", function(e) {
		e.preventDefault();

		var $input;
		var $formGroup = $(this).closest(".form-group");
		if($formGroup.length == 0)
			$formGroup = $(this).parent();

		$input = $formGroup.find("input, textarea, select");
		if($input.length > 0) {
			$input.val(null).focus();
		}
	}).on("click", ".btn-reset-form", function(e) {
		e.preventDefault();

		var $inputs = $searchForm.find(".interactable-inputs").find("input, textarea, select");
		$inputs.each(function() {
			$(this).val(null).trigger("change");
		});
		$searchForm.submit();
	});

	$searchFormInner.on("shown.bs.collapse", function(e) {
		if(e.target != e.currentTarget)
			return;

		$searchForm.find(".interactable-inputs").find("input, textarea, select").trigger("change");
	});

	var $inputCategories = $("#searchCategories");
	$inputCategories.select2({
		width: "100%",
		multiple: true,
		allowClear: true,
		closeOnSelect: true,
		placeholder: @json(__('frontend.apps.fields.categories')),
		maximumSelectionLength: 3,
	});

	var $inputTags = $("#searchTags");
	$inputTags.select2({
		width: "100%",
		multiple: true,
		allowClear: true,
		closeOnSelect: false,
		placeholder: @json(__('frontend.apps.fields.tags')),
		maximumSelectionLength: 5,
	});

	@if($filter_count > 0)
	Helpers.scrollTo(".app-list", {
		animate: true,
		duration: 150,
		offset: -10,
	});
	@endif

});
</script>
@endpush
