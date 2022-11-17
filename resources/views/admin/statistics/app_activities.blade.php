<?php
$breadcrumb_paths = [
	[
		'text'	=> __('admin/stats.app_activities.page_title'),
	],
];

$edits_total = $edits->sum('total');
$reports_total = $reports->sum('total');
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/stats.app_activities.page_title') }} - @parent
@endsection

@section('page-title')
{{ __('admin/stats.app_activities.page_title') }}
@endsection

@section('content')
<!-- Filters -->
<form class="card card-primary card-outline filters-wrapper" method="GET" action="{{ route('admin.stats.app_activities') }}">
	<div class="card-header">
		<h3 class="card-title cursor-pointer" data-card-widget="collapse">{{ __('admin/common.filters') }}</h3>
		<div class="card-tools">
			<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
				<i class="fas fa-minus"></i></button>
		</div>
	</div>
	<div class="card-body">
		<div class="form-horizontal">
			<div class="form-group row">
				<label for="fRange" class="col-sm-3 col-lg-2">{{ __('admin/stats.fields.range') }}</label>
				<div class="col-sm-8 col-lg-5">
					<input type="text" class="form-control" name="date_range" id="fRange" value="{{ $filters['date_range'] }}" placeholder="{{ __('admin/common.fields.range') }}">
				</div>
			</div>
			<div class="form-group row">
				<label for="fGroupMode" class="col-sm-3 col-lg-2">{{ __('admin/stats.fields.group_mode') }}</label>
				<div class="col-sm-8 col-lg-5">
					<select class="form-control" name="group_mode" id="fGroupMode" autocomplete="off">
						<option value="month">&ndash; {{ __('admin/common.choose') }} &ndash;</option>
						<option value="month" {!! old_selected('', $filters['group_mode'], 'month') !!}>{{ __('admin/stats.group_mode.month') }} <span class="text-muted">({{ __('admin/common.default') }})</span></option>
						<option value="year" {!! old_selected('', $filters['group_mode'], 'year') !!}>{{ __('admin/stats.group_mode.year') }}</option>
					</select>
				</div>
			</div>
			<div class="form-group row mb-0">
				<div class="offset-sm-3 offset-lg-2 col">
					<button type="submit" class="btn btn-primary">{{ __('admin/common.apply') }}</button>
					<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.stats.app_activities') }}">{{ __('admin/common.reset') }}</a>
				</div>
			</div>
		</div>
	</div>
</form>

<div class="main-content">
	<!-- Card -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/stats.app_activities.new_apps') }}</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					<div class="maxw-100 position-relative" style="height: 200px;">
						<canvas id="new-apps-chart" width="400"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Card -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/stats.app_activities.edits') }}</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		<div class="card-body">
			@if($edits_total == 0)
			<h4 class="text-center">&ndash; {{ __('admin/common.no_data_available') }} &ndash;</h4>
			@else
			<div class="row">
				<div class="col-12 col-lg-4">
					<h5 class="text-center">{{ __('admin/stats.app_activities.number_of_edits') }}</h5>
					<div class="maxw-100 position-relative mx-auto" style="width: 600px; height: 200px;">
						<canvas id="edits-chart" width="600"></canvas>
					</div>
				</div>
				<div class="col-12 col-lg-8 mt-4 mt-lg-0">
					<h5 class="text-center">{{ __('admin/stats.app_activities.status_of_edits') }}</h5>
					<div class="maxw-100 position-relative" style="height: 200px;">
						<canvas id="changes-statuses-chart" width="400"></canvas>
					</div>
				</div>
			</div>
			@endif
		</div>
	</div>

	<!-- Card -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/stats.app_activities.app_reports') }}</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		<div class="card-body">
			@if($reports_total == 0)
			<h4 class="text-center">&ndash; {{ __('admin/common.no_data_available') }} &ndash;</h4>
			@else
			<div class="row">
				<div class="col-12">
					<h5 class="text-center">{{ __('admin/stats.app_activities.number_of_reported_apps') }}</h5>
					<div class="maxw-100 position-relative mx-auto" style="width: 600px; height: 150px;">
						<canvas id="reports-chart" width="600"></canvas>
					</div>
				</div>
			</div>
			<div class="row mt-0 mt-md-4">
				<div class="col-12 col-md-6 mt-4 mt-md-0">
					<h5 class="text-center">{{ __('admin/stats.app_activities.report_categories') }}</h5>
					<div class="maxw-100 position-relative mx-auto" style="height: 200px;">
						<canvas id="report-categories-chart" width="400"></canvas>
					</div>
				</div>
				<div class="col-12 col-md-6 mt-4 mt-md-0">
					<h5 class="text-center">{{ __('admin/stats.app_activities.report_statuses') }}</h5>
					<div class="maxw-100 position-relative mx-auto" style="height: 200px;">
						<canvas id="report-statuses-chart" width="400"></canvas>
					</div>
				</div>
			</div>
			@endif
		</div>
	</div>

</div>
@endsection

@include('libraries.momentjs')
@include('libraries.daterangepicker')
@include('libraries.chartjs')

@push('scripts')
<script>
jQuery(document).ready(function($) {
	$("#fRange").daterangepicker({
		autoUpdateInput: true,
		showDropdowns: true,
		minYear: 2020,
		maxYear: @json((int) date('Y')),
		linkedCalendars: false,
		locale: {
			format: "DD-MM-YYYY",
			separator: " : ",
		},
		drops: "auto"
	});

	var getMaxFromArray = function(arr) {
		return arr.reduce((a, b) => Math.max(a, b), -Infinity);
	}
	var graceMaxValue = function(value) {
		return value == 0 ? undefined : value + Math.pow(10, Math.floor(Math.log10(value)));
	}

	<?php
	$tgroup_labels = [];
	$tgroup_long_labels = [];
	foreach($time_groups as $tg) {
		$tgroup_labels[] = $tg->group_short_text;
		$tgroup_long_labels[] = $tg->group_text;
	}
	?>
	var tGroupLabels = @json($tgroup_labels);
	var tGroupLongLabels = @json($tgroup_long_labels);

	<?php
	$new_apps_data = [];
	$verified_apps_data = [];
	foreach($new_apps as $i => $na) {
		$new_apps_data[] = $na->total;
		$verified_apps_data[] = $verified_apps[$i]->total;
	}
	?>
	var $newAppsChart = $("#new-apps-chart");
	var newAppsData = @json($new_apps_data);
	var verifiedAppsData = @json($verified_apps_data);
	var newAppsChart = new Chart($newAppsChart, {
		type: "bar",
		plugins: [ChartDataLabels],
		data: {
			datasets: [{
				label: @json(__('admin/stats.app_activities.new_apps')),
				data: newAppsData,
				barPercentage: 0.95,
				categoryPercentage: 0.6,
				minBarLength: 1,
			}, {
				label: @json(__('admin/stats.app_activities.verified_apps')),
				data: verifiedAppsData,
				barPercentage: 0.95,
				categoryPercentage: 0.6,
				minBarLength: 1,
			}],
			labels: tGroupLabels,
			longLabels: tGroupLongLabels,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				mode: "index",
				intersect: false,
				callbacks: {
					title: function(tip, data) {
						return data.longLabels[tip[0].index];
					},
				},
			},
			legend: {
				display: true,
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true,
						precision: 0,
						suggestedMax: graceMaxValue(getMaxFromArray(newAppsData.concat(verifiedAppsData))),
					},
				}],
			},
			plugins: {
				colorschemes: {
					scheme: "brewer.SetOne9",
				},
				datalabels: {
					anchor: "end",
					align: "end",
					offset: -2,
				},
			},
		},
	});

	<?php
	$edits_data = [];
	foreach($edits as $i => $ed) {
		$edits_data[] = $ed->total;
	}
	?>
	var $editsChart = $("#edits-chart");
	var editsData = @json($edits_data);
	var editsChart = new Chart($editsChart, {
		type: "line",
		plugins: [ChartDataLabels],
		data: {
			datasets: [{
				label: @json(__('admin/stats.app_activities.edits/changes')),
				data: editsData,
				borderColor: "#7FDBFF",
				pointBackgroundColor: "#7FDBFF",
				fill: false,
				lineTension: 0.2,
			}],
			labels: tGroupLabels,
			longLabels: tGroupLongLabels,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				mode: "index",
				intersect: false,
				callbacks: {
					title: function(tip, data) {
						return data.longLabels[tip[0].index];
					},
				},
			},
			legend: {
				display: false,
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true,
						precision: 0,
						suggestedMax: graceMaxValue(getMaxFromArray(editsData)),
					},
				}],
			},
			plugins: {
				datalabels: {
					anchor: "end",
					align: -45,
					offset: -2,
					clamp: true,
				},
			},
		},
	});

	<?php
	$changes_approved_data = [];
	$changes_rejected_data = [];
	$changes_pending_data = [];
	foreach($changes_statuses as $i => $cs) {
		$changes_approved_data[] = $cs->total_approved;
		$changes_rejected_data[] = $cs->total_rejected;
		$changes_pending_data[] = $cs->total_pending;
	}
	?>
	var $changesStatusesChart = $("#changes-statuses-chart");
	var changesApprovedData = @json($changes_approved_data);
	var changesRejectedData = @json($changes_rejected_data);
	var changesPendingData = @json($changes_pending_data);
	var changesStatusesChart = new Chart($changesStatusesChart, {
		type: "bar",
		plugins: [ChartDataLabels],
		data: {
			datasets: [{
				label: @json(__('admin/stats.app_activities.approved_changes')),
				data: changesApprovedData,
				backgroundColor: "#5ad45a",
				barPercentage: 0.9,
				categoryPercentage: 0.6,
				minBarLength: 1,
			},{
				label: @json(__('admin/stats.app_activities.pending_changes')),
				data: changesPendingData,
				backgroundColor: "#ebdc78",
				barPercentage: 0.9,
				categoryPercentage: 0.6,
				minBarLength: 1,
			},{
				label: @json(__('admin/stats.app_activities.rejected_changes')),
				data: changesRejectedData,
				backgroundColor: "#b30000",
				barPercentage: 0.9,
				categoryPercentage: 0.6,
				minBarLength: 1,
			}],
			labels: tGroupLabels,
			longLabels: tGroupLongLabels,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				mode: "index",
				intersect: false,
				callbacks: {
					title: function(tip, data) {
						return data.longLabels[tip[0].index];
					},
				},
			},
			legend: {
				display: true,
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true,
						precision: 0,
						// suggestedMax: graceMaxValue(getMaxFromArray(changesApprovedData.concat(changesRejectedData).concat(changesPendingData))),
					},
				}],
			},
			plugins: {
				datalabels: {
					anchor: "end",
					align: "end",
					offset: -4,
				},
			},
		},
	});

	@if($reports_total > 0)
	<?php
	$reports_data = [];
	foreach($reports as $i => $rp) {
		$reports_data[] = $rp->total_apps;
	}
	?>
	var $reportsChart = $("#reports-chart");
	var reportsData = @json($reports_data);
	var reportsChart = new Chart($reportsChart, {
		type: "line",
		plugins: [ChartDataLabels],
		data: {
			datasets: [{
				label: @json(__('admin/stats.app_activities.reported_apps')),
				data: reportsData,
				borderColor: "#ea5545",
				pointBackgroundColor: "#ea5545",
				pointBorderColor: "#ea5545",
				fill: false,
				lineTension: 0.2,
			}],
			labels: tGroupLabels,
			longLabels: tGroupLongLabels,
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				mode: "index",
				intersect: false,
				callbacks: {
					title: function(tip, data) {
						return data.longLabels[tip[0].index];
					},
				},
			},
			legend: {
				display: false,
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true,
						precision: 0,
						suggestedMax: graceMaxValue(getMaxFromArray(reportsData)),
					},
				}],
			},
			plugins: {
				datalabels: {
					anchor: "end",
					align: -45,
					offset: -2,
					clamp: true,
				},
			},
		},
	});


	<?php
	$report_categories_pie_data = [];
	$report_categories_pie_labels = [];
	$report_categories_pie_percentages = [];
	$report_categories_pie_total = $report_categories->sum('total');
	foreach($report_categories as $rpc) {
		$report_categories_pie_data[] = $rpc->total;
		$report_categories_pie_labels[] = $rpc->name;
		$report_categories_pie_percentages[] = $report_categories_pie_total > 0 ? round($rpc->total / $report_categories_pie_total * 100, 1) : 0;
	}
	?>
	var $reportCategoriesChart = $("#report-categories-chart");
	var reportCategoriesChart = new Chart($reportCategoriesChart, {
		type: "pie",
		data: {
			datasets: [{
				data: @json($report_categories_pie_data),
			}],
			labels: @json($report_categories_pie_labels),
			percentages: @json($report_categories_pie_percentages),
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				callbacks: {
					label: function(tip, data) {
						var label = data.labels[tip.index];
						var value = data.datasets[tip.datasetIndex].data[tip.index];
						var percent = data.percentages[tip.index];
						return " "+ label +": "+ value +" | "+ percent +"%";
					},
				},
			},
			legend: {
				display: true,
			},
			plugins: {
				colorschemes: {
					scheme: "brewer.SetOne9",
				}
			},
		},
	});

	<?php
	$report_statuses_pie_data = [];
	$report_statuses_pie_labels = [];
	$report_statuses_pie_percentages = [];
	$report_statuses_pie_total = $report_statuses->sum('total');
	foreach($report_statuses as $rpc) {
		$report_statuses_pie_data[] = $rpc->total;
		$report_statuses_pie_labels[] = $rpc->name;
		$report_statuses_pie_percentages[] = $report_statuses_pie_total > 0 ? round($rpc->total / $report_statuses_pie_total * 100, 1) : 0;
	}
	?>
	var $reportStatusesChart = $("#report-statuses-chart");
	var reportStatusesChart = new Chart($reportStatusesChart, {
		type: "pie",
		data: {
			datasets: [{
				data: @json($report_statuses_pie_data),
				backgroundColor: ["#5ad45a", "#b30000", "#ebdc78"],
			}],
			labels: @json($report_statuses_pie_labels),
			percentages: @json($report_statuses_pie_percentages),
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			tooltips: {
				enabled: true,
				callbacks: {
					label: function(tip, data) {
						var label = data.labels[tip.index];
						var value = data.datasets[tip.datasetIndex].data[tip.index];
						var percent = data.percentages[tip.index];
						return " "+ label +": "+ value +" | "+ percent +"%";
					},
				},
			},
			legend: {
				display: true,
			},
		},
	});
	@endif


});
</script>
@endpush
