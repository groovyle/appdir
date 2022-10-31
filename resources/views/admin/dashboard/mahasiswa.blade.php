@extends('admin.layouts.main')

@section('title')
{{ __('admin/dashboard.page_title') }} - @parent
@endsection

@section('page-title')
{{ __('admin/dashboard.page_title') }}
@endsection

@section('content')
<section class="content">
	<section class="content-header">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12 col-md-3">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-primary elevation-1"><i class="fas fa-cloud"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.your_apps') }}</span>
							<span class="info-box-number">
								@lang('admin/dashboard.x_of_y_apps_are_public', ['x' => $total_public_apps, 'y' => $total_apps])
							</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-3">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-warning elevation-1"><i class="far fa-clock"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.pending_changes') }}</span>
							<span class="info-box-number">@lang('admin/dashboard.x_pending_changes', ['x' => $total_changes_pending])</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-3">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-success elevation-1"><i class="fas fa-clipboard-check"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.approved_changes') }}</span>
							<span class="info-box-number">@lang('admin/dashboard.x_approved_changes', ['x' => $total_changes_approved])</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-3">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.reported_apps') }}</span>
							<span class="info-box-number">{{ $total_apps_reported }}</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
			</div>

			<div class="row mt-4">
				<div class="col-12 col-sm-8 col-xl-6">
					@include('admin.dashboard.components.app_activities', ['activities' => $app_activities])
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</section>
</section>
@endsection
