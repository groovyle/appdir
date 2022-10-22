<?php
$user = Auth::user();
?>
	<!-- Navbar -->
	<nav class="main-header navbar navbar-expand navbar-white navbar-light" id="top-navbar">
		<!-- Left navbar links -->
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
			</li>
			<li class="nav-item">
				<a class="btn btn-sm btn-default btn-flat my-1 ml-1" href="{{ route('index') }}" target="_blank">{{ __('admin/common.portal_button') }}</a>
			</li>
		</ul>

		<!-- Right navbar links -->
		<ul class="navbar-nav ml-auto">
			{{--
			<!-- Notifications Dropdown Menu -->
			<li class="nav-item dropdown">
				<a class="nav-link" data-toggle="dropdown" href="#">
					<i class="far fa-bell"></i>
					<span class="badge badge-warning navbar-badge"></span>
				</a>
				<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
					<span class="dropdown-item dropdown-header">No notifications</span>
				</div>
			</li>
			--}}
			<!-- User menu -->
			<li class="nav-item dropdown user-menu">
				<a href="{{ route('admin.profile.index') }}" class="nav-link dropdown-toggle" data-toggle="dropdown">
					<img src="{{ $user->profile_picture }}" class="user-image img-circle elevation-1" alt="User Image">
					<span class="d-none d-md-inline-block text-truncate" style="max-width: 200px;">{{ $user->name }}</span>
				</a>
				<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
					<!-- User image -->
					<li class="user-header bg-primary">
						<img src="{{ $user->profile_picture }}" class="img-circle elevation-2" alt="User Image">
						<p class="lh-125">
							{{ $user->name }}
							@if(!$user->is_system)
								@if($user->email)
								<small class="text-080 text-light-gray">{{ $user->email }}</small>
								@endif
								@if($user->prodi)
								<span class="text-100 mt-1">{{ $user->prodi->compact_name }}</span>
								@endif
							@else
							@endif
						</p>
					</li>
					<!-- Menu Footer-->
					<li class="user-footer">
						<a href="{{ route('admin.profile.index') }}" class="btn btn-primary btn-flat">{{ __('admin/common.user_profile_button') }}</a>
						<a class="btn btn-outline-danger btn-flat float-right btn-logout" href="{{ route('logout') }}">{{ __('admin/common.logout_button') }}</a>
						@include('components.logout-form')
					</li>
				</ul>
			</li>
		</ul>
	</nav>
	<!-- /.navbar -->