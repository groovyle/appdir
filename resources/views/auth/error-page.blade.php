<?php
list($theme, $counter_theme) = theme_timely();

$title = __('frontend.auth.error_accessing_account');
$message = __('frontend.auth.message_error_login');

if($errors->any()) {
	$message = implode('<br>', $errors->all());
}
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
					<h3 class="text-center text-danger">{{ $title }}</h3>
					<p class="text-pre-wrap lh-125">{!! $message !!}</p>
					<div class="clearfix mt-2">
						<a href="{{ route('login') }}" class="btn btn-sm btn-secondary mb-1 float-left">&laquo; {{ __('frontend.auth.back_to_login_page') }}</a>
						<a href="{{ route('index') }}" class="btn btn-sm btn-primary mb-1 float-right">{{ __('frontend.navs.home') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
