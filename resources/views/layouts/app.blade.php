<?php
$theme = $theme ?? 'light';
extract(theme_vars($theme));

$transparent_navs = $transparent_navs ?? false;
if(!$transparent_navs) {
	$body_theme = $body_theme ?? 'bgf-'.$theme_bg;
	$navbar_bg = $navbar_bg ?? 'bg-'.$theme_bg;
	$navbar_theme = $navbar_theme ?? $navbar_bg.' navbar-'.$theme.' shadow-sm';
	$footer_theme = $footer_theme ?? 'bg-'.$theme_bg;
} else {
	$body_theme = $body_theme ?? '';
	$navbar_bg = $navbar_bg ?? 'bg-transparent';
	$navbar_theme = $navbar_theme ?? $navbar_bg.' navbar-'.$theme.' shadow-none';
	$footer_theme = $footer_theme ?? 'bg-transparent shadow-none';
}

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
	<div id="app" class="d-flex flex-column minh-100 theme-{{ $theme }} {{ $body_theme }}">
		<header>
			<nav class="navbar navbar-expand-md {{ $navbar_theme }}" id="navbar">
				<div class="container">
					<a class="navbar-brand" href="{{ route('index') }}" title="{{ app_name() }}">
						<img src="{{ asset('img/fineprint-'.$counter_theme.'.png') }}" alt="{{ app_name() }} logo" width="120" height="30">
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<!-- Left Side Of Navbar -->
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link {{ $menu_active_browse ?? false ? 'active' : '' }}" href="{{ route('apps') }}">{{ __('frontend.navs.browse_apps') }}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link {{ $menu_active_stats ?? false ? 'active' : '' }}" href="{{ route('stats.apps') }}">{{ __('frontend.navs.statistics') }}</a>
							</li>
						</ul>

						<!-- Right Side Of Navbar -->
						<ul class="navbar-nav ml-auto">
							<!-- Language Button -->
							<li class="nav-item mx-2">
								<a class="nav-link" href="#chLangModal" data-toggle="modal" title="{{ __('frontend.lang.click_to_change_language') }}"><span class="py-1 px-2 border-shadow"><span class="text-monospace">{{ strtoupper($lang) }}</span></a>
							</li>

							<!-- Authentication Links -->
							@guest
								<li class="nav-item">
									<a class="nav-link" href="{{ route('login') }}">{{ __('frontend.navs.login') }}</a>
								</li>
								@if (Route::has('register'))
									<li class="nav-item">
										<a class="nav-link" href="{{ route('register') }}">{{ __('frontend.navs.register') }}</a>
									</li>
								@endif
							@else
								<li class="nav-item dropdown">
									<a id="navbarDropdown" class="nav-link dropdown-toggle nav-user" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
										<div class="nav-user-image">
											<img src="{{ Auth::user()->profile_picture }}" alt="User image" width="25" height="25">
										</div>
										<span class="nav-user-text text-truncate">{{ Auth::user()->name }}</span>
										<span class="caret"></span>
									</a>

									<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
										<a class="dropdown-item" href="{{ route('user.profile', ['user' => Auth::id()]) }}">{{ __('frontend.navs.profile') }}</a>
										<a class="dropdown-item" href="{{ route('admin') }}">{{ __('frontend.navs.admin_panel') }}</a>
										<a class="dropdown-item" href="{{ route('admin.profile.index') }}">{{ __('frontend.navs.account_settings') }}</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item btn-logout" href="{{ route('logout') }}">
											<span class="icon-text-pair icon-color-reset align-items-center">
												<span class="fas fa-power-off text-danger"></span>
												<span>{{ __('frontend.navs.logout') }}</span>
											</span>
										</a>
									</div>
								</li>
							@endguest
						</ul>
					</div>
				</div>
			</nav>
		</header>

		@section('outer-content')
		<main class="flex-shrink-0 py-4">
			@yield('content')
		</main>
		@show

		<footer class="footer main-footer mt-auto {{ $footer_theme }}" id="footer">
			<div class="container">
				<div class="text-center">@lang('frontend.footer_text')</div>
			</div>
		</footer>
	</div>

	<div id="to-top" title="@lang('frontend.back_to_top_button')"></div>

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
