<?php
list($theme, $counter_theme) = theme_timely();
// $theme = 'dark';
// $counter_theme = counter_theme($theme);
?>
@extends('layouts.splash')

@section('content')
<div class="container px-4">
	<div class="text-center mb-4">
		<a href="{{ route('index') }}" class="d-inline-block">
			<img src="{{ asset('img/logo-'.$counter_theme.'.png') }}" class="logo d-block mx-auto" alt="{{ app_name() }} logo" style="max-width: 200px;">
			<img src="{{ asset('img/fineprint-'.$counter_theme.'.png') }}" class="logo d-block mx-auto" alt="{{ app_name() }} logo" style="max-width: 150px;">
		</a>
	</div>
	<div class="text-center">
		<div class="text-r110">
			<a href="{{ route('apps') }}" class="btn btn-outline-{{ $counter_theme }} btn-lg rounded-0 text-110">{{ __('frontend.navs.browse_apps') }} &raquo;</a>
			@if($total_apps > 0)
			<p class="mt-2 mb-2">
				@lang('frontend.splash.browse_through_x_amazing_apps', ['x' => $total_apps])
				<br>
				@lang('frontend.splash.looking_for_something_specific_x?', ['x' => route('apps', ['show_filter' => 1])])
				<br>
				<span class="text-085 d-inline-block mt-2">@lang('frontend.splash.or_look_at_site_wide_stats', ['x' => route('stats.apps')]).</span>
			</p>
			@else
			<p class="mt-2 mb-0">
				@lang('frontend.splash.there_are_no_apps_yet')
			</p>
			@endif
		</div>

		<hr class="follow-color my-4">

		<div class="text-r100">
			@guest
			<p class="mt-0 mb-1">@lang('frontend.splash.you_are_not_logged_in_yet')</p>
			<a href="{{ route('login') }}" class="btn btn-link btn-lg d-block d-sm-inline-block">{{ __('frontend.navs.login') }}</a>
			<span class="d-none d-sm-inline-block">|</span>
			<a href="{{ route('register') }}" class="btn btn-link btn-lg d-block d-sm-inline-block">{{ __('frontend.navs.register') }}</a>
			@else
			<p class="mt-0 mb-1">@lang('frontend.splash.hey_x_you_are_logged_in', ['x' => $user->name])</p>
			<a href="{{ route('admin.apps.create') }}" class="btn btn-link btn-lg d-block d-sm-inline-block"><span class="fas fa-plus text-070 mr-1" style="vertical-align: 2px;"></span> {{ __('frontend.navs.submit_an_app') }}</a>
			<span class="d-none d-sm-inline-block">|</span>
			<a href="{{ route('admin') }}" class="btn btn-link btn-lg d-block d-sm-inline-block">{{ __('frontend.navs.admin_panel') }}</a>
			<span class="d-none d-sm-inline-block">|</span>
			<a href="{{ route('admin.apps.index', ['whose' => Auth::user()->isA('mahasiswa') ? null : 'own']) }}" class="btn btn-link btn-lg d-block d-sm-inline-block">{{ __('frontend.splash.your_apps_x', ['x' => $user->apps->count()]) }}</a>
			<br>
			<a href="{{ route('logout') }}" class="btn btn-link mt-2 btn-logout">{{ __('frontend.navs.logout') }}</a>
			@endguest
		</div>
	</div>
</div>
@endsection
