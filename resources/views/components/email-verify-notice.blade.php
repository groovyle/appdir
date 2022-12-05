@auth
<?php
$user = \Auth::user();
$can_verify = config('auth.verify_email') && \Route::has('verification.notice');
$email_verify_notice = $email_verify_notice ?? true;
$no_margin = $no_margin ?? false;
$margin = $no_margin ? 'm-0' : 'my-1 mx-1';
$floating = $floating ?? false;
?>
@if($can_verify && $email_verify_notice && !$user->is_verified)
@if(!$floating)
<div class="email-verify-notice alert alert-warning text-center py-1 {{ $margin }}">
	<a href="{{ route('verification.notice') }}">{{ __('frontend.auth.activate_your_account') }}</a> {{ __('frontend.auth.to_get_full_access') }}
</div>
@else
<a class="floating-nav-btn text-warning" href="{{ route('verification.notice') }}" title="{{ __('frontend.auth.activate_your_account') }} {{ __('frontend.auth.to_get_full_access') }}"><span class="fas fa-user-cog fa-fw"></span></a>
@endif
@endif
@endauth