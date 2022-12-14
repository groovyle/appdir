<?php
list($theme, $counter_theme) = theme_timely();
?>
@extends('layouts.splash')

@section('content')
<div class="container px-4">
	<div class="row">
		<div class="col-12 col-md-6 mx-auto">
			<div class="text-center mt-n5 mb-4">
				<a href="{{ route('index') }}" class="d-inline-block" title="{{ __('frontend.navs.home') }}">
					<img src="{{ asset('img/logo-'.$counter_theme.'.png') }}" class="logo d-block mx-auto" alt="{{ app_name() }} logo" style="max-width: 150px;">
					<img src="{{ asset('img/fineprint-'.$counter_theme.'.png') }}" class="logo d-block mx-auto" alt="{{ app_name() }} logo" style="max-width: 120px;">
				</a>
			</div>
			<div class="card text-dark">
				<div class="card-body">
					<h3 class="text-center text-success">{{ __('frontend.auth.messages.account_verified') }}</h3>
					<p class="text-pre-wrap lh-125 text-center">{{ __('frontend.auth.messages.account_verified_sub', ['app' => app_name()]) }}</p>
					<div class="clearfix mt-4 text-center">
						<a href="{{ route('index') }}" class="btn btn-dark">{{ __('frontend.auth.back_to_home') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
