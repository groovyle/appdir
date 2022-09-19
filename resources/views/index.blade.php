@extends('layouts.splash')

@section('content')
<div class="container px-4">
	<div class="text-center mb-4">
		<img src="{{ asset('img/logo-light.png') }}" class="logo" rel="{{ app_name() }}" style="max-width: 200px;">
		<br>
		<img src="{{ asset('img/fineprint-light.png') }}" class="logo" rel="{{ app_name() }}" style="max-width: 150px;">
	</div>
	<div class="text-center">
		<div class="text-r110">
			<a href="{{ route('apps') }}" class="btn btn-outline-light btn-lg rounded-0 text-110">{{ __('frontend.navs.browse_apps') }} &raquo;</a>
			<p class="mt-2 mb-0">
				@if($total_apps > 0)
				@lang('frontend.splash.browse_through_x_amazing_apps', ['x' => $total_apps])
				<br>
				@lang('frontend.splash.looking_for_something_specific_x?', ['x' => route('apps', ['show_filter' => 1])])
				@else
				@lang('frontend.splash.there_are_no_apps_yet')
				@endif
			</p>
		</div>

		<hr class="my-4">

		<div class="text-r100">
			@guest
			<p class="mt-0 mb-1">@lang('frontend.splash.you_are_not_logged_in_yet')</p>
			<a href="{{ route('login') }}" class="btn btn-link btn-lg">{{ __('frontend.navs.login') }}</a>
			|
			<a href="{{ route('register') }}" class="btn btn-link btn-lg">{{ __('frontend.navs.register') }}</a>
			@else
			<p class="mt-0 mb-1">@lang('frontend.splash.hey_x_you_are_logged_in', ['x' => $user->name])</p>
			<a href="#" class="btn btn-link btn-lg">{{ __('frontend.navs.submit_an_app') }}</a>
			|
			<a href="{{ url('/admin') }}" class="btn btn-link btn-lg">{{ __('frontend.navs.admin_panel') }}</a>
			|
			<a href="{{ url('/admin/my_apps') }}" class="btn btn-link btn-lg">{{ __('frontend.splash.your_apps_x', ['x' => $user->apps->count()]) }}</a>
			<br>
			<a href="{{ route('logout') }}" class="btn btn-link mt-2 btn-logout">{{ __('frontend.navs.logout') }}</a>
			@endguest
		</div>
	</div>
</div>
@endsection
