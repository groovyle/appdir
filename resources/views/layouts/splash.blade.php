<?php
$theme = $theme ?? 'dark';
extract(theme_vars($theme));

$lang = app()->getLocale();
$lang_text = langtext();
?><!doctype html>
<html lang="{{ str_replace('_', '-', $lang) }}" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	@section('meta')
	@show

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@section('title', '')
	<title>{{ make_title(View::yieldContent('title')) }}</title>

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}" />

	<link href="{{ asset('css/base.css') }}" rel="stylesheet">
	<link href="{{ asset('css/login.css') }}" rel="stylesheet">

	@stack('head-additional')
</head>
<body class="h-100">
	<div id="app" class="minh-100 splash-page theme-{{ $theme }} bg-{{ $theme_bg }} {{ $theme_text }}">
		<div class="floating-nav-btn-wrapper">
			<a class="floating-nav-btn" href="#chLangModal" data-toggle="modal" title="{{ __('frontend.lang.click_to_change_language') }}"><span class="text-monospace">{{ strtoupper($lang) }}</span></a>
			@include('components.email-verify-notice', ['no_margin' => true, 'floating' => true])
		</div>

		@section('outer-content')
		<main class="flex-shrink-0 pt-5 pb-4 mt-auto">
			@yield('content')
		</main>
		@show

		<footer class="footer main-footer mt-auto" id="footer">
			@lang('frontend.footer_text')
		</footer>
	</div>

	@include('components.language-modal')
	@include('components.logout-form')


	<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">

	<!-- Fonts -->
	<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Nunito&family=Rubik&display=swap" rel="stylesheet">

	@stack('load-styles')

	<!-- Styles -->
	<link href="{{ asset('css/custom-libraries.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/helpers.css') }}" rel="stylesheet">

	@stack('styles')

	<!-- Scripts -->
	<script>
	window.AppGlobals = {
		lang: @json($lang),
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
