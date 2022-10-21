<?php
$last_breadcrumb = __('admin/profile.change_password');
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/profile.tab_title.password') }} - @parent
@endsection

@section('page-title', __('admin/profile.page_title.password'))

@section('content')

<div class="mb-2">
	@if($back)
	<a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
	@endif
</div>

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formPasswordProfile">
	@csrf
	@method($method)

	@include('components.page-message', ['show_errors' => true])

	<!-- Card -->
	<div class="card main-content scroll-to-me">
		<div class="card-body">
			<div class="row gutter-lg">
				<div class="col-12 col-md-8 col-xl-6 mx-auto">
					<div class="form-group">
						<label for="inputOldPassword">{{ __('admin/profile.fields.old_password') }}</label>

						<div class="input-group password-wrapper">
							<input type="password" name="old_password" class="form-control" id="inputOldPassword" placeholder="{{ __('admin/profile.fields.old_password_placeholder') }}" value="" autocomplete="off" required>
							<div class="input-group-append">
								<a href="#" class="input-group-text plain btn-see-password" data-targets="#inputOldPassword"><span class="far fa-eye"></span></a>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="inputNewPassword">{{ __('admin/profile.fields.new_password') }}</label>
						<div class="input-group password-wrapper">
							<input type="password" name="new_password" class="form-control" id="inputNewPassword" placeholder="{{ __('admin/profile.fields.new_password_placeholder') }}" value="" autocomplete="off" minlength="5" maxlength="50" required>
							<div class="input-group-append">
								<a href="#" class="input-group-text plain btn-see-password" data-targets="#inputNewPassword, #inputNewPassword2"><span class="far fa-eye"></span></a>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="inputNewPassword2">{{ __('admin/profile.fields.new_password2') }}</label>
						<input type="password" name="new_password_confirmation" class="form-control" id="inputNewPassword2" placeholder="{{ __('admin/profile.fields.new_password2_placeholder') }}" value="" autocomplete="off" required>
					</div>
				</div>
				<div class="col-12">
					<div class="mt-4 text-center">
						<button type="submit" class="btn btn-primary btn-min-100">{{ __('admin/profile.change_password') }}</button>
						@if($back)
						<br>
						<a href="{{ $back }}" class="btn btn-default btn-sm mt-3">{{ __('common.cancel') }}</a>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /.card -->
</form>
@endsection

@push('scripts')

<script>
jQuery(document).ready(function($) {

});
</script>

@endpush
