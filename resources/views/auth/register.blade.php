<?php
list($theme, $counter_theme) = theme_timely();
extract(theme_vars($theme));
$body_theme = 'bg-'.$theme_bg;
$transparent_navs = true;
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

				<form method="POST" action="{{ route('register') }}">
					@csrf

					<div class="form-group row">
						<label for="name" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.name') }}</label>

						<div class="col-md-7">
							<input id="name" type="text" class="login-form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

							@error('name')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="email" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.email') }}</label>

						<div class="col-md-7">
							<input id="email" type="email" class="login-form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

							@error('email')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="prodi" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.prodi') }}</label>

						<div class="col-md-7">
							<select id="prodi" name="prodi" class="login-form-select custom-select" required autocomplete="prodi">
								<option value="">&ndash; {{ __('frontend.auth.fields.choose_prodi') }} &ndash;</option>
								@foreach($prodis as $p)
								<option value="{{ $p->id }}" {!! old_selected('prodi', null, $p->id) !!}>{{ $p->complete_name }}</option>
								@endforeach
							</select>

							@error('prodi')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="password" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.password') }}</label>

						<div class="col-md-7">
							<input id="password" type="password" class="login-form-control form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

							@error('password')
								<span class="invalid-feedback" role="alert">
									<strong>{{ $message }}</strong>
								</span>
							@enderror
						</div>
					</div>

					<div class="form-group row">
						<label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('frontend.auth.fields.password_confirm') }}</label>

						<div class="col-md-7">
							<input id="password-confirm" type="password" class="login-form-control" name="password_confirmation" required autocomplete="new-password">
						</div>
					</div>

					<div class="form-group row mt-4 mb-0">
						<div class="col-12 text-center">
							<button type="submit" class="btn-login">{{ __('frontend.auth.register_btn') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

</div>
@endsection

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {

});
</script>
@endpush
