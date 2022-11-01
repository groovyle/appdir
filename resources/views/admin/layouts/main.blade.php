@if(!request()->ajax())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@section('title', config('app.admin_name'))
	<title>@yield('title')</title>

	<!-- Pace progress indicator -->
	<link rel="stylesheet" href="{{ asset('plugins/pace-progress/themes/custom-green/pace-theme-flash.css') }}">
	<script data-ajax="true" src="{{ asset('plugins/pace-progress/pace.min.js') }}"></script>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="{{ asset('img/favicon-dark.png') }}" />

	<!-- Fonts -->
	<!-- Font Awesome -->
	<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">

	@stack('load-styles')

	<!-- Theme style -->
	<link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/login.css') }}">
	<link rel="stylesheet" href="{{ asset('css/custom-libraries.css') }}">
	<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
	<link rel="stylesheet" href="{{ asset('css/helpers.css') }}">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

	@stack('styles')

	@stack('head-additional')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper" id="app">
	@include('admin.layouts.main-navbar')

	@include('admin.layouts.main-sidebar')

	<main class="content-wrapper pb-3">
		@section('content-header')
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<div class="container-fluid">
				<div class="row gutter-lg content-header-row mb-2">
					<div class="col">
						<h1 class="page-title">@yield('page-title')</h1>
					</div>
					<div class="col ml-auto">
						<div class="clearfix mt-2">
							@include('admin.layouts.main-breadcrumb')
						</div>
					</div>
				</div>
			</div><!-- /.container-fluid -->
		</section>
		@show

		@section('content-outer')
		<!-- Main content -->
		<section class="content">
			<div class="container-fluid">
				@yield('content')
			</div>
		</section>
		<!-- /.content -->
		@show
	</main>

	<footer class="main-footer">
		<div class="float-right d-none d-sm-block">
			<a href="http://adminlte.io" class="text-secondary" target="_blank">Theme: AdminLTE 3.0.4</a>
		</div>
		<span class="text-bold">@lang('admin/common.footer_text')</span>
	</footer>

</div>

@include('admin.layouts.one-for-all-modal')
@include('admin.layouts.confirmator-modal')
@stack('hidden-contents')

<!-- Scripts -->
<script>
window.AppGlobals = {
	lang: @json(app()->getLocale()),
}
</script>
<script src="{{ asset('js/app.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
@include('admin.layouts.toast-notification')
<script src="{{ asset('js/adminlte.min.js') }}"></script>

@stack('load-scripts')

<script src="{{ asset('js/helpers.js') }}"></script>
<script src="{{ asset('js/custom-libraries.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>

@stack('scripts')

</body>
</html>
@else
	@stack('styles')
	@section('content-outer')
		@yield('content')
	@show
	@stack('scripts')
@endif