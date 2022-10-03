<?php
$show_form = count($reports) > 0;
$form_show = $show_form ? 'show' : '';
?>
@extends('admin.layouts.main')

@section('page-title', __('admin/app_reports.page-title'))

@section('content')

<div class="d-flex flex-wrap text-nowrap mb-1">
	<div class="details-nav-left mr-auto mb-1">
		<a href="{{ route('admin.app_reports.index') }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back_to_list') }}</a>
	</div>
	<div class="details-nav-right ml-auto mb-1">
		<a href="{{ route('admin.app_reports.verdicts', ['app' => $app->id]) }}" class="btn btn-sm bg-purple">
			<span class="fas fa-list mr-1"></span>
			{{ __('admin/app_reports.verdicts_history') }}
		</a>
	</div>
</div>

@include('admin.app.detail-card', ['app' => $app, 'hide_changes' => true, 'is_snippet' => true, 'show_pending_changes' => false, 'section_id' => 'ori'])

<hr>

@if(count($reports) == 0)
<h4 class="text-center mt-4 mb-5">
	<span class="icon-text-pair align-items-center">
		<span class="icon mr-4">✨</span>
		<span>
			@lang('admin/app_reports.this_app_is_clean')
			<br><small>@lang('admin/app_reports.no_reports_for_this_app')</small>
			<br>
			<button type="button" class="btn btn-warning btn-sm mt-2" data-toggle="collapse" data-target="#reportReviewForm">
				<span class="icon-text-pair">
					<span class="fas fa-caret-down mr-1"></span>
					@lang('admin/app_reports.review_anyway?')
					<span class="fas fa-caret-down ml-l"></span>
				</span>
			</button>
		</span>
		<span class="icon ml-4">✨</span>
	</span>
</h4>
@endif

<form id="reportReviewForm" method="POST" action="{{ route('admin.app_reports.verify', ['app' => $app->id]) }}" class="report-management collapse {{ $form_show }} collapse-scrollto">
	@csrf
	@method('POST')

	@include('components.page-message', ['show_errors' => true])

	<div class="row">
		<div class="col-md-4">
			<div class="card filters-panel mb-0">
				<div class="card-header">
					<h4 class="card-title">@lang('admin/common.filters')</h4>
				</div>
				<div class="card-body">
					<div class="list-filters mb-2">
						<div class="form-inline">
							<input type="text" class="form-control form-control-sm input-filter filter-search mr-1" data-filter-key="search" value="" autocomplete="off" placeholder="{{ __('admin/common.search') }}">
							<button type="button" class="btn btn-info btn-sm btn-apply-filters">@lang('admin/common.search')</button>
						</div>
					</div>
					<div class="list-filters mb-2">
						<label>@lang('admin/app_reports.fields.categories')</label>
						<div>
							<button type="button" class="btn btn-sm btn-default rounded-pill btn-filter filter-category-_all mb-1" data-filter="{{ json_encode(['category' => '_all']) }}" data-filter-mode="replace">@lang('admin/app_reports.all_categories')</button>
							@forelse($all_categories as $rc)
							<button type="button" class="btn btn-sm btn-default rounded-pill btn-filter filter-category-{{ $rc->id }} mb-1" data-filter="{{ json_encode(['category' => $rc->id]) }}" data-toggle="tooltip" title="{{ $rc->description }}" data-custom-class="tooltip-wider">{{ $rc->name }} ({{ $rc->reports_count }})</button>
							@empty
							@endforelse
						</div>
					</div>
					<div class="list-filters mb-2">
						<label>@lang('admin/app_reports.fields.reported_versions')</label>
						<div>
							<button type="button" class="btn btn-sm btn-default rounded-pill btn-filter filter-version-_all mb-1" data-filter="{{ json_encode(['version' => '_all']) }}" data-filter-mode="replace">@lang('admin/app_reports.all_app_versions')</button>
							@forelse($all_versions as $rv)
							<button type="button" class="btn btn-sm btn-default rounded-pill btn-filter filter-version-{{ $rv->version }} mb-1" data-filter="{{ json_encode(['version' => $rv->version]) }}">{{ $rv->display_name }} ({{ $rv->reports_count }})</button>
							@empty
							@endforelse
						</div>
					</div>
					<button type="button" class="btn btn-default btn-sm rounded-pill btn-filter mt-3" data-filter="{}" data-filter-mode="replace_all">
						<span class="fas fa-times mr-1"></span>
						@lang('admin/common.clear_all_filters')
					</button>
				</div>
			</div>
			<button type="button" class="btn btn-block btn-primary btn-scrollto-verdict mt-3">
				<span class="icon-text-pair">
					<span class="fas fa-caret-down mr-1"></span>
					@lang('admin/app_reports.go_to_verdict_section')
					<span class="fas fa-caret-down ml-l"></span>
				</span>
			</button>
		</div>
		<div class="col-md-8 d-flex align-items-stretch">
			<div class="card mb-0 w-100">
				<div class="card-header">
					<h4 class="card-title">@lang('admin/app_reports.reports_list') (@lang('admin/common.x_items', ['x' => count($reports)]))</h4>
				</div>
				<div class="card-body navigation-panel pb-1 flex-grow-0">
					<div class="float-left maxw-100 mb-2 mr-3">
						<h6 class="report-list-count-text mb-1">@lang('admin/common.showing_x_items_of_y_total')</h6>
						<h6 class="report-list-resolved-text mb-0">@lang('admin/app_reports.x_of_y_reports_have_been_validated')</h6>
					</div>
					<div class="float-right maxw-100 mb-2">
						<div class="form-check d-inline-block">
							<input type="checkbox" class="form-check-input btn-check-all" id="fnav-check-all" value="1" autocomplete="off">
							<label class="form-check-label" for="fnav-check-all">@lang('admin/common.select_all')</label>
						</div>
						<div class="btn-group ml-2">
							<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">@lang('admin/common.for_selected_items')</button>
							<div class="dropdown-menu dropdown-menu-right">
								<button type="button" class="dropdown-item btn-all-status" data-value="valid">{{ __('admin/app_reports.mark_as_valid') }}</button>
								<button type="button" class="dropdown-item btn-all-status" data-value="invalid">{{ __('admin/app_reports.mark_as_invalid') }}</button>
								<button type="button" class="dropdown-item btn-all-status" data-value="_clear">{{ __('admin/app_reports.clear_mark') }}</button>
							</div>
						</div>
					</div>
				</div>
				@if(count($reports) > 0)
				<div class="card-body pt-1">
					<div class="report-list">
					@foreach($all_versions as $rv)
					<div class="report-version-wrapper">
						<h6 class="text-r090">@lang('admin/app_reports.reports_on_x', ['x' => $rv->display_name]) (@lang('admin/common.x_items', ['x' => $rv->reports_count]))</h6>
						<div class="report-version-items">
						@foreach($rv->reports as $r)
							<div class="card report-item report-item-{{ $r->id }}" data-report-id="{{ $r->id }}">
								<div class="card-header pb-1 px-3 border-bottom-0">
									<h5 class="card-title">
										@if($r->registered_sender)
										<span class="pr-2" title="{{ __('admin/app_reports.report_from_registered_user') }}" data-toggle="tooltip" data-placement="right">
											<span class="fas fa-user text-lightblue text-080 mr-1"></span>
											@puser($r->user)
										</span>
										@else
										<span class="pr-2" title="{{ __('admin/app_reports.report_from_anonymous_user') }}" data-toggle="tooltip" data-placement="right">
											<span class="fas fa-envelope text-secondary text-080 mr-1"></span>
											{{ $r->email }}
										</span>
										@endif
										<div class="text-080 d-flex">
											<span class="text-secondary mr-2">@include('components.date-with-tooltip', ['date' => $r->updated_at])</span>
											<span class="text-secondary mr-2 d-inline-block">
												@if($rv->version == $app->version_number)
												<strong>{{ $r->version->display_name }}</strong>
												@else
												<span>{{ $r->version->display_name }}</span>
												<span class="far fa-clock text-090 text-warning ml-1" title="@lang('admin/app_reports.take_care_when_reviewing_older_version_reports')" data-toggle="tooltip" data-custom-class="tooltip-wider"></span>
												@endif
												@if($rv->version != '__none')
												<button type="button" class="btn btn-tool btn-tool-inline btn-view-version ml-1" data-toggle="tooltip" title="@lang('admin/apps.changes.view_this_version')" data-app-id="{{ $app->id }}" data-version="{{ $rv->version }}"><span class="fas fa-expand"></span></button>
												@endif
											</span>
										</div>
									</h5>
									<div class="float-right">
										<div class="form-check checkbox-lg">
											<input type="checkbox" class="form-check-input report-checkbox" value="1" autocomplete="off">
										</div>
									</div>
								</div>
								<div class="card-body pt-1 pb-2 px-3 lh-130">
									<input type="hidden" name="report[{{ $r->id }}][id]" value="{{ $r->id }}">
									<div>
										<span class="text-pre-wrap reason-text">@voe($r->reason)</span>
									</div>
									<div class="d-flex flex-row flex-wrap justify-content-between align-items-start mt-2" style="gap: 0.5rem 1rem;">
										<div class="">
											<div class="d-inline-block">
												@forelse($r->categories as $rc)
												<button type="button" class="btn btn-xs btn-default rounded-pill btn-filter filter-category-{{ $rc->id }}" data-filter="{{ json_encode(['category' => $rc->id]) }}" data-toggle="tooltip" title="{{ $rc->description }}" data-custom-class="tooltip-wider">{{ $rc->name }}</button>
												@empty
												@vo_
												@endforelse
											</div>
										</div>
										<div class="text-090 d-flex flex-row flex-wrap align-items-center flex-shrink-1">
											<label class="mr-1 mb-0 btn-reset-status cursor-pointer text-unbold" tabindex="0">@lang('admin/app_reports.was_this_a_valid_report?')</label>
											<div class="btn-group btn-group-toggle report-status-group" data-toggle="buttons">
												<label class="btn btn-outline-info btn-flat btn-xs text-090" data-toggle="tooltip" title="{{ __('admin/app_reports.mark_report_as_valid') }}">
													<input type="radio" name="report[{{ $r->id }}][status]" value="valid" class="report-status" {!! old_checked('report.'.$r->id.'.status', null, 'valid') !!}>
													@lang('admin/app_reports.label_report_is_valid')
												</label>
												<label class="btn btn-outline-secondary btn-flat btn-xs text-090" data-toggle="tooltip" title="{{ __('admin/app_reports.mark_report_as_invalid') }}">
													<input type="radio" name="report[{{ $r->id }}][status]" value="invalid" class="report-status" {!! old_checked('report.'.$r->id.'.status', null, 'invalid') !!}>
													@lang('admin/app_reports.label_report_is_invalid')
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						@endforeach
						</div>
					</div>
					@endforeach
					</div>
				</div>
				@else
				<div class="card-body flex-grow-0 my-auto">
					<h5 class="text-center">&ndash; @lang('admin/app_reports.no_reports_for_this_app') &ndash;</h5>
				</div>
				@endif
			</div>
		</div>
	</div>

	<div class="card report-verdict-wrapper mt-4">
		<div class="card-body">
			<div class="form-group">
				<label for="input-final-comments">@lang('admin/app_reports.fields.final_comments')</label>
				<div class="callout callout-warning py-2 px-3 mb-1 text-090">
					<span class="icon-text-pair icon-2x">
						<span class="fas fa-info-circle icon text-150 text-warning"></span>
						@lang('admin/app_reports.fields.final_comments_hint')
					</span>
				</div>
				<textarea class="form-control" name="final_comments" id="input-final-comments" placeholder="{{ __('admin/app_reports.fields.final_comments_placeholder') }}" rows="2" minlength="50" maxlength="1000" required>{{ old('final_comments') }}</textarea>
			</div>
			<div class="form-group accordion" id="verdict-collapse-group">
				<label for="input-verdict">@lang('admin/app_reports.fields.verdict')</label>
				<div class="verdict-item lh-120 mb-2">
					<div class="form-check">
						<input type="radio" name="verdict" value="innocent" class="form-check-input input-verdict" id="input-verdict-innocent" required {!! old_checked('verdict', null, 'innocent') !!}>
						<label for="input-verdict-innocent" class="form-check-label">
							<div class="text-110 text-info"><strong>@lang('admin/app_reports.verdicts.innocent')</strong></div>
							<div class="text-unbold">@lang('admin/app_reports.verdicts.innocent_explanation')</div>
						</label>
						<div class="verdict-additional collapse" data-parent="#verdict-collapse-group"></div>
					</div>
				</div>
				<div class="verdict-item lh-120 mb-2">
					<div class="form-check">
						<input type="radio" name="verdict" value="guilty" class="form-check-input input-verdict" id="input-verdict-guilty" required {!! old_checked('verdict', null, 'guilty') !!}>
						<label for="input-verdict-guilty" class="form-check-label">
							<div class="text-110 text-danger"><strong>@lang('admin/app_reports.verdicts.guilty')</strong></div>
							<div class="text-unbold">@lang('admin/app_reports.verdicts.guilty_explanation')</div>
						</label>
						<div class="verdict-additional collapse" data-parent="#verdict-collapse-group">
							<div class="form-check mt-1">
								<input type="checkbox" name="block_user" value="1" class="form-check-input" id="input-verdict-guilty-block-user" {!! old_checked('block_user') !!}>
								<label for="input-verdict-guilty-block-user" class="form-check-label">
									<div class=""><strong>@lang('admin/app_reports.verdicts.guilty_block_user')?</strong></div>
									<div class="text-090 text-unbold">@lang('admin/app_reports.verdicts.guilty_block_user_explanation')</div>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group mt-5 mb-0 text-center">
				<button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/app_reports.settle_review') }}</button>
			</div>
		</div>
	</div>
</form>

@endsection

@include('admin.app.changes.btn-view-version')

@push('scripts')
<script>
jQuery(document).ready(function($) {

	/*$(".version-select-list").on("click", ".version-select-item", function(e) {
		e.preventDefault();
	});*/

	$('[data-toggle="popover"]').popover({
		container: "body",
	});

	var $form = $("#reportReviewForm");
	var $checkAll = $("#fnav-check-all");

	var $reportRoot = $(".report-management");
	var $reportList = $(".report-list");
	var $reportItems = $reportList.find(".report-item");
	var $reportCountText = $(".report-list-count-text"),
			reportCountTextTemplate = $reportCountText.text()
	;
	var $reportResolvedText = $(".report-list-resolved-text"),
			reportResolvedTextTemplate = $reportResolvedText.text()
	;

	var reportsData = @json($reports_data->all());
	// Init data
	(function() {
		for(var i in reportsData) {
			var item = reportsData[i];
			item._matches = 0;
			item.$item = $reportItems.filter(".report-item-"+ item.id);
		}
	})();
	console.log(reportsData);

	var emptyFilters = {
		category: [],
		version: null,
		search: null,
	};
	var searchAttributes = ["reason", "name", "email"];
	var activeFilters = {};
	var multipleFiltersMode = "and";
	var getSelectedReports = function() {
		return $reportItems.filter(".report-item-selected:not(.hidden)");
	}
	var _refreshCountText = function() {
		var text = reportCountTextTemplate;
		text = text.replace(":x", $reportItems.filter(":not(.hidden)").length);
		text = text.replace(":y", $reportItems.length);
		$reportCountText.text(text);
	}
	var refreshCountText = Helpers.debounce(_refreshCountText, 100, false);
	var _refreshResolvedText = function() {
		var text = reportResolvedTextTemplate;
		var countResolved = $reportItems.filter(function() {
			return $(this).find(".report-status:checked").length > 0;
		}).length;
		var countAll = $reportItems.length;
		text = text.replace(":x", countResolved);
		text = text.replace(":y", countAll);
		$reportResolvedText.text(text);

		$reportResolvedText.toggleClass("text-success", countResolved == countAll && countAll > 0);
		$reportResolvedText.toggleClass("text-danger", countResolved == 0 && countAll > 0);
		$reportResolvedText.toggleClass("text-info", countResolved > 0 && countResolved != countAll);
	}
	var refreshResolvedText = Helpers.debounce(_refreshResolvedText, 100, false);
	var refreshBtnGroupState = function(group) {
		var $group = $(group);
		if(!$group.is("[data-toggle=buttons]"))
			return;

		setTimeout(function() {
			group.find("input[type=checkbox], input[type=radio]").each(function(i, item) {
				$(item).closest(".btn").toggleClass("active", this.checked);
			});
		}, 10);
	}


	$reportRoot.on("change", ".report-checkbox", function(e) {
		var $item = $(this).closest(".report-item");
		$item.toggleClass("report-item-selected", this.checked);
	}).on("change", ".report-status", function(e) {
		refreshBtnGroupState($(this).closest(".report-status-group"));
		refreshResolvedText();
	}).on("click", ".btn-all-status", function(e) {
		e.preventDefault();

		var value = $(this).data("value");
		if(typeof value === "undefined")
			return;

		var $selected = getSelectedReports();
		if($selected.length == 0) {
			alert(@json(__('admin/app_reports.select_some_reports_to_use_this_feature')));
			return;
		}

		var $inputs = $selected.find(".report-status");
		$inputs.prop("checked", false).trigger("change");
		if(["valid", "invalid"].indexOf(value) !== -1) {
			var $matches = $inputs.filter(function() {
				return $(this).prop("value") == value;
			});
			$matches.prop("checked", true).trigger("change");
		}

		// Clear selection if used check all
		if($checkAll.prop("checked")) {
			$checkAll.prop("checked", false).trigger("change");
		}
	});

	$checkAll.on("change", function(e) {
		var checked = this.checked;
		// $reportItems.toggleClass("report-item-selected", checked);
		$reportItems.find(".report-checkbox").prop("checked", checked).trigger("change");
	});

	$reportRoot.on("click", ".btn-scrollto-verdict", function(e) {
		e.preventDefault();

		Helpers.scrollTo(".report-verdict-wrapper");
	});

	$(".reason-text").readMore({
		maxLines: 3,
	});


	$("#input-final-comments").textareaShowLength().textareaAutoHeight({
		bypassHeight: false,
	});

	$reportRoot.on("click", ".report-item .btn-reset-status", function(e) {
		e.preventDefault();
		var $item = $(this).closest(".report-item");
		$item.find(".report-status").prop("checked", false).trigger("change");
	});

	$reportRoot.on("click", ".btn[data-toggle=tooltip]", function(e) {
		// Hide tooltip on click
		$(this).tooltip("hide");
	});

	$reportRoot.on("click", ".btn-filter", function(e) {
		e.preventDefault();

		var filterData = $(this).data("filter"),
				filterMode = $(this).data("filterMode");
		if(filterData) {
			applyFilters(filterData, filterMode);
		}
	}).on("click", ".btn-apply-filters", function(e) {
		e.preventDefault();

		var $list = $(this).closest(".list-filters");
		applyInputFilters($list);
	}).on("keypress", ".input-filter", function(e) {
		if(e.keyCode == 13) {
			e.preventDefault();
			var $list = $(this).closest(".list-filters");
			applyInputFilters($list);
		}
	});

	// Array.indexOf uses strict comparison (===), this is the loose version (==)
	var findIndex = function(needle, arr) {
		return arr.findIndex((v) => v == needle);
	}

	var arrayFilters = ["category"];
	var prepareFilters = function(filters, state) {
		if(!filters) filters = $.extend(true, {}, emptyFilters);
		if(typeof state === "undefined")
			state = "toggle";

		var filterCount = 0;
		if(state == "replace_all") {
			activeFilters = $.extend(true, {}, emptyFilters);
			state = true;
		}

		// Normalize data values
		for(var key in filters) {
			var value = filters[key];
			if(arrayFilters.indexOf(key) !== -1) {
				if(!activeFilters[key] || state == "replace")
					activeFilters[key] = [];

				if(["_all", "_none"].indexOf(String(value).toLowerCase()) !== -1) {
					// Do nothing
					activeFilters[key] = [];
				} else {
					if(!(value instanceof Array))
						value = [value];

					value.forEach(function(item, i) {
						var found = activeFilters[key] instanceof Array
							&& findIndex(item, activeFilters[key]) !== -1
						;
						if(state == "toggle") {
							state = !found;
						}
						if(state) {
							activeFilters[key].push(item);
						} else {
							activeFilters[key] = Helpers.removeArrayElement(activeFilters[key], item);
						}
					});
				}
			} else if(typeof value !== "undefined") {
				if(!activeFilters.hasOwnProperty(key))
					activeFilters[key] = null;

				if(value !== null) {
					value = (""+ value).trim();
					if(["_all", "_none"].indexOf(value.toLowerCase()) !== -1) {
						value = "";
					}
				}
				var notEmpty = value !== "" && value !== null;

				if(state == "toggle") {
					if(value == activeFilters[key]) {
						// Remove existing
						state = false;
					} else if(notEmpty) {
						// Always replace
						state = true;
					} else {
						state = !activeFilters[key];
					}
				} else {
					state = state && notEmpty;
				}
				if(state) {
					activeFilters[key] = value;
				} else {
					activeFilters[key] = null;
				}
			}
		}

		// Refresh buttons states
		for(var key in activeFilters) {
			if(key[0] == "_") continue;

			var value = activeFilters[key];
			if(arrayFilters.indexOf(key) !== -1) {
				clearFilterHandles(key);
				value.forEach(function(item, i) {
					toggleFilterHandles(key +"-"+ item, true);
				});
				toggleFilterHandles(key +"-_all", value.length == 0);

				if(value.length > 0) {
					filterCount++;
				}
			} else {
				var toState = value !== null;
				toggleFilterHandles(key, false, true);
				toggleFilterHandles(key +"-"+ value, toState);
				toggleFilterHandles(key +"-_all", !toState);
				if(!toState) {
					toggleFilterInputs(key, false);
				}

				if(toState) {
					filterCount++;
				}
			}
		}

		activeFilters._count = filterCount;
		activeFilters._empty = filterCount == 0;

		/*if(activeFilters._empty) {
			clearFilterHandles();
		}
		toggleFilterHandles("_all", activeFilters._empty);*/
	}

	var toggleFilterHandles = function(key, state, wild) {
		if(typeof wild === "undefined") wild = false;

		var $handles;
		if(!wild) {
			$handles = $reportRoot.find(".btn.filter-"+ key);
		} else {
			$handles = $reportRoot.find(".btn[class*='filter-"+key+"']");
		}
		$handles
			.toggleClass("btn-info", state)
			.toggleClass("btn-default", !state)
		;
	}
	var toggleFilterInputs = function(key, state) {
		if(!state) {
			$reportRoot.find(".input-filter[class*='filter-"+key+"']").val(null);
		}
	}
	var clearFilterHandles = function(key) {
		if(!key) key = "";
		$reportRoot.find(".btn[class*='filter-"+key+"']")
			.removeClass("btn-info")
			.addClass("btn-default")
		;
		$reportRoot.find(".input-filter[class*='filter-"+key+"']").val(null);
	}

	var applyFilters = function(filters, state) {
		prepareFilters(filters, state);

		// Start filtering items
		for(var i in reportsData) {
			var item = reportsData[i];

			// Show all if filter is empty
			if(activeFilters._empty) {
				item._matches = 1;
				item._matchesAll = true;
			} else {
				// Reset matches
				item._matches = 0;
				item._matchesAll = false;

				for(var fkey in activeFilters) {
					if(fkey[0] == "_") continue;

					var fvalue = activeFilters[fkey];
					if(fvalue instanceof Array && fvalue.length > 0) {
						var intersect = item[fkey].filter((n) => findIndex(n, fvalue) !== -1);
						// OR
						// if(intersect.length > 0) item._matches++;

						// AND
						if(intersect.length == fvalue.length) item._matches++;
					} else if(fvalue) {
						if(fkey == "search") {
							// do a LIKE search on a lot of attributes
							var pattern = Helpers.escapeRegex(fvalue);
							pattern.replace(/\s+/g, "\\s+");
							var rx = new RegExp(pattern, "i");

							var matches = false;
							for(var i = 0; i < searchAttributes.length; i++) {
								var skey = searchAttributes[i];
								matches = rx.test(item[skey]);
								if(matches) break;
							}
							if(matches) item._matches++;
						} else {
							if(item[fkey] == fvalue) item._matches++;
						}
					}
				}

				item._matchesAll = item._matches == activeFilters._count;
			}

			var orMode = multipleFiltersMode == "or";

			// Show/hide items
			if((orMode && item._matches > 0) || (!orMode && item._matchesAll)) {
				item.$item.removeClass("d-none hidden");
			} else {
				item.$item.addClass("d-none hidden");
				// Deactivate selections
				item.$item.find(".report-checkbox").prop("checked", false).trigger("change");
			}
		}

		refreshCountText();

		// To refresh readMore sections
		windowResize();
	}

	var _windowResize = function() {
		$(window).trigger("resize");
	}
	var windowResize = Helpers.debounce(_windowResize, 200, false);

	var applyInputFilters = function(list) {
		var $list;
		if(typeof list === "undefined") {
			$list = $reportRoot.find(".list-filters");
		} else {
			$list = $(list);
		}

		var $inputs = $list.find("input, select, textarea");
		var filterData = {};
		$inputs.each(function() {
			var key = $(this).data("filterKey") || $(this).prop("name");
			if(key) filterData[key] = $(this).val();
		});

		applyFilters(filterData, true);
	}

	$reportRoot.on("change", ".input-verdict", function(e) {
		var $additional = $(this).closest(".verdict-item").find(".verdict-additional");
		var $otherAdditionals = $(this).closest(".report-verdict-wrapper").find(".verdict-additional").not($additional);

		// $otherAdditionals.collapse("hide");
		$additional.collapse(this.checked ? "show" : "hide");
	});

	// On load states
	$reportRoot.find(".report-checkbox").trigger("change");
	$reportRoot.find(".input-verdict").trigger("change");
	$form.noEnterSubmit();
	applyFilters();
	refreshCountText();
	refreshResolvedText();

	// Scroll to form if there are any errors
	@if($errors->any())
	Helpers.scrollTo($form, { animate: false });
	@endif

});
</script>

@endpush
