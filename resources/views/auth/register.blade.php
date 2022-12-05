<?php
list($theme, $counter_theme) = theme_timely();
extract(theme_vars($theme));
$body_theme = 'bg-'.$theme_bg;
$transparent_navs = true;
$recaptcha_enabled = recaptcha_enabled();
?>
@extends('layouts.app')

@section('title', __('frontend.navs.register'))

@section('outer-content')
<div class="flex-grow-1 d-flex flex-column">

<div class="login-container text-dark">
	<div class="row flex-grow-1">
		<div class="col-12 col-md-8 col-xl-6 mx-auto">
			<div class="login-box w-100">
				<div class="login-header">
					<h3>{{ __('frontend.auth.register_header') }}</h3>
				</div>

				<form method="POST" action="{{ route('register') }}" class="login-form text-left">
					@csrf

					<div class="form-group row">
						<label for="name" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.name') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<input id="name" type="text" class="login-form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>

							@error('name')
								<span class="invalid-feedback d-block" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="email" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.email') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<input id="email" type="email" class="login-form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>

							@error('email')
								<span class="invalid-feedback d-block" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="prodi" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.prodi') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<select id="prodi" name="prodi" class="login-form-select custom-select" required>
								<option value="">&ndash; {{ __('frontend.auth.fields.choose_prodi') }} &ndash;</option>
								@foreach($prodis as $p)
								<option value="{{ $p->id }}" {!! old_selected('prodi', null, $p->id) !!}>{{ $p->complete_name }}</option>
								@endforeach
							</select>

							@error('prodi')
								<span class="invalid-feedback d-block" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="password" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.password') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<div class="input-group password-wrapper">
								<input id="password" type="password" class="login-form-control form-control @error('password') is-invalid @enderror" name="password" required>
								<div class="input-group-append">
									<button type="button" class="input-group-text plain text-decoration-none rounded-0 btn-see-password" data-targets="#password, #password-confirm" title="{{ __('common.show/hide_password') }}" data-toggle="tooltip"><span class="far fa-eye fa-fw"></span></button>
								</div>
							</div>

							@error('password')
								<span class="invalid-feedback d-block" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.password_confirm') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<input id="password-confirm" type="password" class="login-form-control" name="password_confirmation" required>
						</div>
					</div>

					<div class="form-group row">
						<label for="language" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.language') }} @include('components.label-mandatory', ['flying' => true])</label>

						<div class="col-md-7">
							<select id="language" name="language" class="login-form-select custom-select" required autocomplete="off">
								<option value="">&ndash; {{ __('frontend.auth.fields.choose_language') }} &ndash;</option>
								@foreach($lang_list as $l => $text)
								<option value="{{ $l }}" {!! old_selected('language', $lang, $l) !!}>{{ $text }}</option>
								@endforeach
							</select>

							@error('language')
								<span class="invalid-feedback d-block" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="mt-4">
						@if($recaptcha_enabled)
						<div class="form-group row mb-3">
							<div class="col d-flex justify-content-center">
								<div class="w-auto text-center">
									<div id="recaptcha_register" class="w-fit-content mx-auto"></div>

									@error('g-recaptcha-response')
										<span class="invalid-feedback d-block" role="alert">
											<strong>{{ $message }}</strong>
										</span>
									@enderror
								</div>
							</div>
						</div>
						@endif

						<div class="form-group row mb-0">
							<div class="col-12 text-center">
								<button type="submit" class="btn-login">{{ __('frontend.auth.register_btn') }}</button>
							</div>
						</div>
					</div>
				</form>

				<div class="login-footer">
					<ul class="login-footer-links">
						<li>
							<a href="{{ route('login') }}">@lang('frontend.auth.fields.already_have_an_account_log_in')</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

</div>
@endsection

@push('scripts')
@if($recaptcha_enabled)
{!!  GoogleReCaptchaV2::render('recaptcha_register') !!}
@endif

<script type="text/javascript">
jQuery(document).ready(function($) {

});
</script>
@endpush
