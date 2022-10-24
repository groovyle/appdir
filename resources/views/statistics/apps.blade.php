<?php
$menu_active_stats = true;
?>
@extends('layouts.app')

@section('title', __('frontend.statistics.apps._title'))

@section('content')
<div class="stats-apps">

<div class="container">
	<div class="mb-4">
		<h1 class="mb-1">{{ __('frontend.statistics.apps._title') }}</h1>
		<p class="text-110 text-secondary mb-0">{{ __('frontend.statistics.apps._description') }}</p>
	</div>

	<div class="row gutter-lg">
		<div class="col-12 col-sm-4 col-lg-3 col-xl-2">
			<div class="nav flex-column nav-pills" id="app-stats-tab" role="tablist" aria-orientation="vertical">
				<a class="nav-link active" id="app-stats-by-cat-tab" data-toggle="pill" href="#app-stats-by-cat" role="tab" aria-controls="app-stats-by-cat" aria-selected="true">{{ __('frontend.statistics.apps.by_category') }}</a>
				<a class="nav-link" id="app-stats-by-tag-tab" data-toggle="pill" href="#app-stats-by-tag" role="tab" aria-controls="app-stats-by-tag" aria-selected="false">{{ __('frontend.statistics.apps.by_tag') }}</a>
			</div>
		</div>
		<div class="col-12 col-sm-8 col-lg-9 col-xl-10">
			<div class="card">
				<div class="card-body">
					<div class="tab-content" id="app-stats-tab-content">
						<div class="tab-pane fade show active" id="app-stats-by-cat" role="tabpanel" aria-labelledby="app-stats-by-cat-tab">
							@if(count($categories) == 0)
							<h4 class="m-4">{{ __('frontend.statistics.apps.no_categories_yet') }}</h4>
							@else
							<h3 class="text-center mb-2">{{ __('frontend.statistics.apps.apps_by_category') }}</h3>
							<div class="mb-2">
								<div class="maxw-100 position-relative mx-auto" style="width: 400px; height: 300px;">
									<canvas id="app-stats-by-cat-pie" width="300" height="200"></canvas>
								</div>
							</div>
							<div>
								<h5 class="text-center mb-2">{{ __('frontend.statistics.apps.list_of_categories') }} ({{ count($categories) }})</h5>
								<div class="table-responsive">
									<table class="table table-hover border w-fit-content mx-auto lh-120">
										<thead>
											<tr class="bg-light">
												<th class="text-right pr-3" style="width: 1%;">#</th>
												<th>{{ __('frontend.statistics.apps.category') }}</th>
												<th class="text-nowrap" colspan="2" style="width: 10%;">{{ __('frontend.statistics.apps.fields.total_apps') }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($categories as $cat)
											@if($cat->id == '__others') @continue @endif
											<tr>
												<td class="text-right text-secondary pr-2">{{ $loop->iteration }}</td>
												<td class="pr-4">{{ $cat->name }}</td>
												<td class="text-right pr-3">{{ $cat->apps_count }}</td>
												<td class="text-right pr-3">{{ $cat->percentage }}%</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
							@endif
						</div>
						<div class="tab-pane fade" id="app-stats-by-tag" role="tabpanel" aria-labelledby="app-stats-by-tag-tab">
							@if(count($tags) == 0)
							<h4 class="m-4">{{ __('frontend.statistics.apps.no_tags_yet') }}</h4>
							@else
							<h3 class="text-center mb-2">{{ __('frontend.statistics.apps.apps_by_tag') }}</h3>
							<div class="mb-2">
								<div class="maxw-100 position-relative mx-auto" style="width: 400px; height: 300px;">
									<canvas id="app-stats-by-tag-pie" width="300" height="200"></canvas>
								</div>
							</div>
							<div>
								<h5 class="text-center mb-2">{{ __('frontend.statistics.apps.list_of_tags') }} ({{ count($tags) }})</h5>
								<div class="table-responsive">
									<table class="table table-hover border w-fit-content mx-auto lh-120">
										<thead>
											<tr class="bg-light">
												<th class="text-right pr-3" style="width: 1%;">#</th>
												<th>{{ __('frontend.statistics.apps.tag') }}</th>
												<th class="text-nowrap" colspan="2" style="width: 10%;">{{ __('frontend.statistics.apps.fields.total_apps') }}</th>
											</tr>
										</thead>
										<tbody>
											@foreach($tags as $tag)
											@if($tag->id == '__others') @continue @endif
											<tr>
												<td class="text-right text-secondary pr-2">{{ $loop->iteration }}</td>
												<td class="pr-4">{{ $tag->name }}</td>
												<td class="text-right pr-3">{{ $tag->apps_count }}</td>
												<td class="text-right pr-3">{{ $tag->percentage }}%</td>
											</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</div>
@endsection

@include('libraries.chartjs')

<?php
$cat_pie_data = [];
$cat_pie_labels = [];
$cat_pie_percents = [];
foreach($pie_categories as $c) {
	$cat_pie_data[] = $c->apps_count;
	$cat_pie_labels[] = $c->name;
	$cat_pie_percents[] = $c->percentage;
}

$tag_pie_data = [];
$tag_pie_labels = [];
$tag_pie_percents = [];
foreach($pie_tags as $t) {
	$tag_pie_data[] = $t->apps_count;
	$tag_pie_labels[] = $t->name;
	$tag_pie_percents[] = $t->percentage;
}
?>

@push('scripts')
<script>
jQuery(document).ready(function($) {

	@if(count($categories) > 0)
	var ctx = $("#app-stats-by-cat-pie");
	var catPieData = @json($cat_pie_data);
	var catPieLabels = @json($cat_pie_labels);
	var catPiePercents = @json($cat_pie_percents);
	var catPieChart = new Chart(ctx, {
		type: "pie",
		data: {
			datasets: [{
				data: catPieData,
				backgroundColor: Helpers.chartColors(catPieData.length),
			}],
			labels: catPieLabels,
			percentages: catPiePercents,
		},
		options: {
			tooltips: {
				enabled: true,
				callbacks: {
					label: function(tip, data) {
						var label = data.labels[tip.index];
						var value = data.datasets[tip.datasetIndex].data[tip.index];
						var percent = data.percentages[tip.index];
						return " "+ label +": "+ value +" ("+ percent +"%)";
					},
				},
			},
			legend	: {
				enabled: true,
			},
		},
	});
	@endif

	@if(count($tags) > 0)
	var ctx = $("#app-stats-by-tag-pie");
	var tagPieData = @json($tag_pie_data);
	var tagPieLabels = @json($tag_pie_labels);
	var tagPiePercents = @json($tag_pie_percents);
	var tagPieChart = new Chart(ctx, {
		type: "pie",
		data: {
			datasets: [{
				data: tagPieData,
				backgroundColor: Helpers.chartColors(tagPieData.length),
			}],
			labels: tagPieLabels,
			percentages: tagPiePercents,
		},
		options: {
			tooltips: {
				enabled: true,
				callbacks: {
					label: function(tip, data) {
						var label = data.labels[tip.index];
						var value = data.datasets[tip.datasetIndex].data[tip.index];
						var percent = data.percentages[tip.index];
						return " "+ label +": "+ value +" ("+ percent +"%)";
					},
				},
			},
			legend	: {
				enabled: true,
			},
		},
	});
	@endif
});
</script>
@endpush