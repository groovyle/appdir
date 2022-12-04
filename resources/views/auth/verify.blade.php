<?php
list($theme, $counter_theme) = theme_timely();
// $email_verify_notice = false;
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
					<h3 class="text-center">{{ __('frontend.auth.verify_your_email_address') }}</h3>
					<p class="lh-150">
						{{ __('frontend.auth.to_activate_account_please_check_email_for_verification_link') }}
						<br>{{ __('frontend.auth.please_check_spam_if_not_in_inbox') }}
					</p>
					<form class="" method="POST" action="{{ route('verification.resend') }}">
						@csrf
						{{ __('frontend.auth.did_not_receive_email?') }}
						<button type="submit" class="btn btn-link persist-color p-0 m-0 align-baseline">{{ __('frontend.auth.click_here_to_send_verification_email_again') }}</button>.
					</form>
					@if (session('resent'))
						<div class="alert alert-success mb-0 mt-2" role="alert">
							{{ __('frontend.auth.verification_link_has_been_sent_to_email') }}
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection