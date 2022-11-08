<?php
$last_breadcrumb = __('admin/profile.change_my_profile');
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/profile.tab_title.edit') }} - @parent
@endsection

@section('page-title', __('admin/profile.page_title.edit'))

@section('content')

<div class="mb-2">
	@if($back)
	<a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
	@endif
</div>

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formEditProfile">
	@csrf
	@method($method)

	@include('components.page-message', ['show_errors' => true])

	<!-- Card -->
	<div class="card main-content">
		<div class="card-body">
			<div class="row gutter-lg">
				<div class="col-12 col-md-8 col-xl-6 mx-auto">
					<div class="form-group">
						<label>
							{{ __('admin/users.fields.email') }}
							@component('admin.slots.label-hint')
							@lang('admin/profile.fields.email_hint')
							@endcomponent
						</label>
						<p class="form-control-plaintext">@vo_($model->email)</p>
					</div>

					<div class="form-group">
						<label>
							{{ __('admin/users.fields.prodi') }}
							@component('admin.slots.label-hint')
							@lang('admin/profile.fields.prodi_hint')
							@endcomponent
						</label>
						<p class="form-control-plaintext">@vo_(optional($model->prodi)->name)</p>
					</div>

					<div class="form-group">
						<label for="inputProfileName">{{ __('admin/common.fields.name') }} @include('components.label-mandatory')</label>
						<input type="text" name="name" class="form-control" id="inputProfileName" placeholder="{{ __('admin/profile.fields.name_placeholder') }}" value="{{ old('name', $model->name) }}" maxlength="100" required>
					</div>

					<div class="form-group">
						<label for="inputProfileLanguage">{{ __('admin/users.fields.language') }} @include('components.label-mandatory')</label>
						<select name="language" id="inputProfileLanguage" class="form-control">
							<option value="">&ndash; {{ __('admin/profile.use_default_language') }} ({{ $app_locale_text }}) &ndash;</option>
							@foreach($lang_list as $l => $text)
							<option value="{{ $l }}" {!! old_selected('language', $model->lang, $l) !!}>{{ $text }} ({{ $l }})</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-12">
					<div class="mt-4 text-center">
						<button type="submit" class="btn btn-primary btn-min-100">{{ __('common.save') }}</button>
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
