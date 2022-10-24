<?php
list($theme, $counter_theme) = theme_timely();
extract(theme_vars($theme));
$body_theme = 'bg-'.$theme_bg;
$transparent_navs = true;
?>
@extends('layouts.app')

@section('title', __('frontend.navs.login'))

@section('outer-content')
<div class="flex-grow-1 d-flex flex-column">

<div class="login-container">
	<div class="login-box">
		<div class="login-header">
			<img src="{{ asset('img/logo-dark.png') }}" class="logo" rel="{{ app_name() }}">
		</div>
		<form method="POST" action="{{ route('login') }}" class="login-form">
			@csrf

			@if($errors->any())
			<div class="text-center text-danger lh-110 mb-2">
				{!! nl2br(implode("\n", $errors->all())) !!}
			</div>
			@endif

			<div class="form-group">
				<label for="loginEmail" class="sr-only">@lang('frontend.users.fields.email')</label>
				<input id="loginEmail" type="text" class="login-form-control" name="email" value="{{ old('email') }}" placeholder="{{ __('frontend.users.fields.email') }}" required autocomplete="email" autofocus>

				@error('email')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="form-group">
				<label for="loginPassword" class="sr-only">@lang('frontend.users.fields.password')</label>
				<input id="loginPassword" type="password" class="login-form-control" name="password" placeholder="{{ __('frontend.users.fields.password') }}" required autocomplete="current-password">

				@error('password')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>

			<div class="form-group">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="remember" id="loginRemember" {!! old_checked('remember') !!}>

					<label class="form-check-label" for="loginRemember">@lang('frontend.users.fields.remember_login')</label>
				</div>
			</div>

			<div class="form-group mb-0">
				<button type="submit" class="btn-login">@lang('frontend.users.login_btn')</button>
			</div>
		</form>
		<?php?>
		<div class="login-footer">
			<ul class="login-footer-links">
				@if (Route::has('password.request'))
				<li>
					<a href="{{ route('password.request') }}">@lang('frontend.users.fields.forgot_your_password')</a>
				</li>
				@endif
				@if (Route::has('register'))
				<li>
					<a href="{{ route('register') }}">@lang('frontend.users.fields.register_an_account')</a>
				</li>
				@endif
			</ul>
		</div>
	</div>
</div>

</div>
@endsection

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {
	var $email = $("#loginEmail");

	Helpers.scrollTo($(".login-box"));
	Helpers.focusAndSelectText($email);
});
</script>
@endpush