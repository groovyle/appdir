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
				<div class="col-12 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-primary elevation-1"><i class="fas fa-cloud"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_apps') }}</span>
							<span class="info-box-number">
								@lang('admin/dashboard.x_of_y_apps_are_public', ['x' => $total_public_apps, 'y' => $total_apps])
							</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-info elevation-1"><i class="fas fa-clipboard-check"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_verifications') }}</span>
							<span class="info-box-number">
								{{ $total_unverifs }}
								<small>{{ __('admin/dashboard.changes_unverified') }}</small>
							</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_reports') }}</span>
							<span class="info-box-number">
								{{ $total_apps_reported }} <small>{{ __('admin/dashboard.apps_reported') }}</small>
							</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
			</div>

			<div class="row mt-4">
				<div class="col-12 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-purple elevation-1"><i class="fas fa-users"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_users') }}</span>
							<span class="info-box-number">
								@lang('admin/dashboard.x_of_y_users_are_blocked', ['x' => $total_blocked_users, 'y' => $total_users])
							</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-olive elevation-1"><i class="fas fa-list"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_categories') }}</span>
							<span class="info-box-number">{{ $total_categories }}</span>
						</div>
						<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>
				<!-- /.col -->
				<div class="col-12 col-sm-6 col-md-4">
					<div class="info-box mb-3">
						<span class="info-box-icon bg-olive elevation-1"><i class="fas fa-tags"></i></span>
						<div class="info-box-content">
							<span class="info-box-text">{{ __('admin/dashboard.total_tags') }}</span>
							<span class="info-box-number">{{ $total_tags }}</span>
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
