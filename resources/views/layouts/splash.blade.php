<?php
$theme = $theme ?? 'dark';
extract(theme_vars($theme));
?><!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@section('title', '')
	<title>{{ make_title(View::yieldContent('title')) }}</title>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}" />

	<link href="{{ asset('css/base.css') }}" rel="stylesheet">
	<link href="{{ asset('css/login.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">

	<!-- Fonts -->
	<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
	<link rel="dns-prefetch" href="//fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

	@stack('load-styles')

	<!-- Styles -->
	<link href="{{ asset('css/custom-libraries.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/helpers.css') }}" rel="stylesheet">

	@stack('styles')

	@stack('head-additional')
</head>
<body class="h-100">
	<div id="app" class="minh-100 splash-page bg-{{ $theme_bg }} {{ $theme_text }}">
		@section('outer-content')
		<main class="flex-shrink-0 pt-3 pb-4 mt-auto">
			@yield('content')
		</main>
		@show

		<footer class="footer main-footer mt-auto" id="footer">
			@lang('frontend.footer_text')
		</footer>
	</div>

	@include('components.logout-form')

	<!-- Scripts -->
	<script>
	window.AppGlobals = {
		lang: @json(app()->getLocale()),
	}
	</script>
	<script src="{{ asset('js/app.js') }}"></script>
	<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

	@stack('load-scripts')

	<script src="{{ asset('js/helpers.js') }}"></script>
	<script src="{{ asset('js/custom-libraries.js') }}"></script>
	<script src="{{ asset('js/frontend.js') }}"></script>

	@stack('scripts')

</body>
</html>
