<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	@section('title', '')
	<title>{{ make_title(View::yieldContent('title')) }}</title>

	<link href="{{ asset('css/base.css') }}" rel="stylesheet">
	<link href="{{ asset('css/login.css') }}" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
	<link href="{{ asset('plugins/ekko-lightbox/ekko-lightbox.css') }}" rel="stylesheet">

	<!-- Fonts -->
	<!-- TODO: use CDN instead -->
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
	<div id="app" class="d-flex flex-column minh-100 bgf-fragrant-clouds">
		<header>
			<nav class="navbar navbar-expand-md bg-fragrant-clouds navbar-light shadow-sm" id="navbar">
				<div class="container">
					<a class="navbar-brand" href="{{ route('index') }}" title="{{ app_name() }}">
						<img src="{{ asset('img/fineprint-dark.png') }}" >
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<!-- Left Side Of Navbar -->
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="{{ route('apps') }}">{{ __('frontend.navs.browse_apps') }}</a>
							</li>
						</ul>

						<!-- Right Side Of Navbar -->
						<ul class="navbar-nav ml-auto">
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
									<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
										{{ Auth::user()->name }} <span class="caret"></span>
									</a>

									<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
										<a class="dropdown-item" href="{{ url('/admin') }}">{{ __('frontend.navs.admin_panel') }}</a>
										<a class="dropdown-item" href="{{ url('/admin') }}">TODO: {{ __('frontend.navs.account_settings') }}</a>
										<div class="dropdown-divider"></div>
										<a class="dropdown-item btn-logout" href="{{ route('logout') }}">
											{{ __('frontend.navs.logout') }}
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

		<footer class="footer main-footer mt-auto bg-fragrant-clouds" id="footer">
			<div class="container">
				<div class="text-center">@lang('frontend.footer_text')</div>
			</div>
		</footer>
	</div>

	<div id="to-top" title="@lang('frontend.back_to_top_button')"></div>

	@include('components.frontend-logout-form')

	<!-- Scripts -->
	<script>
	window.AppGlobals = {
		lang: @json(app()->getLocale()),
	}
	</script>
	<script src="{{ asset('js/app.js') }}"></script>
	<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
	<script src="{{ asset('plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>

	@stack('load-scripts')

	<script src="{{ asset('js/helpers.js') }}"></script>
	<script src="{{ asset('js/custom-libraries.js') }}"></script>
	<script src="{{ asset('js/frontend.js') }}"></script>

	@stack('scripts')

</body>
</html>
