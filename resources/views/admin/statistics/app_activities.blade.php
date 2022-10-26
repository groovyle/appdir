@extends('admin.layouts.main')

@section('title')
{{ __('admin/stats.app_activities.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/stats.app_activities.page_title.index') }}
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
					<label for="fRange" class="col-sm-3 col-lg-2">{{ __('admin/common.fields.range') }}</label>
					<div class="col-sm-8 col-lg-5">
						<input type="text" class="form-control" name="range" id="fRange" value="{{ $filters['range'] }}" placeholder="{{ __('admin/common.fields.range') }}">
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="offset-sm-3 offset-lg-2 col">
						<button type="submit" class="btn btn-primary">{{ __('admin/common.search') }}</button>
						<a class="btn btn-secondary btn-sm ml-2" href="{{ route('admin.stats.app_activities') }}">{{ __('admin/common.reset') }}</a>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- Card -->
	<div class="card main-content">
		<div class="card-header">
			<h3 class="card-title">{{ __('admin/stats.app_activities.categories_list') }}</h3>
			<div class="card-tools">
				<button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fas fa-minus"></i></button>
			</div>
		</div>
		<div class="card-body"></div>
	</div>
@endsection

@include('libraries.momentjs')
@include('libraries.daterangepicker')

@push('scripts')
<script>
jQuery(document).ready(function($) {
	$("#fRange").daterangepicker({
		"showDropdowns": true,
		"minYear": 2022,
		"maxYear": 2022,
		"linkedCalendars": false,
		"startDate": "10/18/2022",
		"endDate": "10/24/2022",
		"drops": "auto"
	});
});
</script>
@endpush
