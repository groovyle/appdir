@extends('admin.layouts.main')

@section('title')
{{ __('admin/profile.page_title.index') }} - @parent
@endsection

@section('page-title')
{{ __('admin/profile.page_title.index') }}
@endsection

@section('content')
<div class="row">
	<div class="col-md-3">

		<!-- Profile Image -->
		<div class="card card-primary card-outline">
			<div class="card-body box-profile">
				<div class="text-center">
					<div class="d-inline-block position-relative">
						<a href="{{ $user->profile_picture }}" target="_blank">
							<img class="profile-user-img img-fluid img-circle" src="{{ $user->profile_picture }}" alt="User profile picture">
						</a>
						<a href="{{ route('admin.profile.picture') }}" class="profile-user-img-edit text-info" title="{{ __('admin/profile.change_profile_picture') }}" data-toggle="tooltip"><span class="fas fa-edit"></span></a>
					</div>
				</div>

				<h3 class="profile-username text-center">
					{{ $user->name }}
					@if(!$user->is_system && $user->email)
					<br>
					<small class="text-secondary">{{ $user->email }}</small>
					@endif
				</h3>

				@if($user->roles_text)
				<p class="text-secondary text-center mb-1">{{ $user->roles_text }}</p>
				@endif

				@if(!$user->is_system && $user->prodi)
				<p class="text-center mb-1">{{ $user->prodi->complete_name }}</p>
				@endif

				<a href="{{ route('user.profile', ['user' => $user->id]) }}" class="btn btn-primary btn-block btn-sm mt-4" target="_blank">{{ __('admin/profile.see_public_profile') }}</a>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->

		<!-- Settings Box -->
		<div class="card card-primary settings-box">
			<div class="card-header">
				<h3 class="card-title">{{ __('admin/profile.settings') }}</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">

				<a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-block btn-sm">
					<span class="icon-text-pair icon-color-reset">
						<span class="fas fa-pen icon text-090"></span>
						<span>{{ __('admin/profile.change_my_profile') }}</span>
					</span>
				</a>
				<a href="{{ route('admin.profile.picture') }}" class="btn btn-primary btn-block btn-sm">
					<span class="icon-text-pair icon-color-reset">
						<span class="fas fa-camera icon text-090"></span>
						<span>{{ __('admin/profile.change_profile_picture') }}</span>
					</span>
				</a>

				<hr>

				<a href="{{ route('admin.profile.password') }}" class="btn btn-warning btn-block btn-sm">
					<span class="icon-text-pair icon-color-reset">
						<span class="fas fa-key icon text-090"></span>
						<span>{{ __('admin/profile.change_password') }}</span>
					</span>
				</a>

				<hr>

				<a href="{{ route('logout') }}" class="btn btn-danger btn-block btn-sm btn-logout">{{ __('admin/common.logout_button') }}</a>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
	<!-- /.col -->
	<div class="col-md-9">
		<div class="card">
			<div class="card-body">
				@if($user->apps_count > 0)
				{{ __('admin/profile.you_have_x_apps', ['x' => $user->apps_count]) }}.
				@can('view-any', App\Models\App::class)
				<a href="{{ route('admin.apps.index', ['whose' => 'own']) }}" class="text-primary">{{ __('admin/profile.check_your_apps') }} &raquo;</a>
				@endcan
				@else
				{{ __('admin/profile.you_dont_have_any_apps_yet') }}!
				@can('create', App\Models\App::class)
				<br>
				<a href="{{ route('admin.apps.create') }}" class="btn btn-link">{{ __('admin/profile.make_your_first_app') }} &raquo;</a>
				@endcan
				@endif
			</div><!-- /.card-body -->
		</div>
		<!-- /.nav-tabs-custom -->
	</div>
	<!-- /.col -->
</div>
@endsection

@push('scripts')
<script>
jQuery(document).ready(function($) {

	@if(request('settings') == 1)
	Helpers.scrollTo(".settings-box", { animate: true });
	@endif

});
</script>
@endpush
