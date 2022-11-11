<?php
list($theme, $counter_theme) = theme_timely();

$url_prev = url()->previous();
$was_on_admin = in_admin_panel( $url_prev );
$was_on_site = in_site( $url_prev );

$url_index = $was_on_admin ? route('admin') : route('index');

?>
@extends('layouts.splash')

@section('content')
<div class="container px-4">
	<div class="row">
		<div class="col-12 col-md-10 col-xl-8 mx-auto">
			<div class="text-center mb-2">
				<a href="{{ route('index') }}" title="{{ __('frontend.navs.home') }}">
					<img src="{{ asset('img/logo-'.$counter_theme.'.png') }}" class="logo" rel="{{ app_name() }}" style="max-width: 120px;">
					<br>
					<img src="{{ asset('img/fineprint-'.$counter_theme.'.png') }}" class="logo" rel="{{ app_name() }}" style="max-width: 96px;">
				</a>
			</div>
			<div class="card text-dark rounded-0">
				<div class="card-body text-center text-r120 lh-120">
					<div class="text-danger">
						<div class="">{{ __('errors.error') }}:</div>
						<div class="text-monospace lh-100 mt-n3" style="font-size: 6rem;">@yield('code')</div>
						<div class="mt-n2">@yield('code-title')</div>
					</div>
					<p class="text-pre-wrap text-150 mt-3">@yield('message')</p>

					<div class="mt-4">
						<button type="button" class="btn btn-dark rounded-0" onclick="window.history.back()">&laquo; {{ __('common.go_back') }}</button>
					</div>

					<div class="login-footer mt-5">
						<p class="text-secondary m-0">(@lang('errors.error_persists_info'))</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
