<?php
$last_breadcrumb = __('admin/profile.change_profile_picture');
?>

@extends('admin.layouts.main')

@section('title')
{{ __('admin/profile.tab_title.picture') }} - @parent
@endsection

@section('page-title', __('admin/profile.page_title.picture'))

@section('content')

<div class="mb-2">
	@if($back)
	<a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
	@endif
</div>

<form method="POST" action="{{ $action }}" class="no-enter-submit" id="formEditPictureProfile" enctype="multipart/form-data">
	@csrf
	@method($method)

	@include('components.page-message', ['show_errors' => true])

	<!-- Card -->
	<div class="card main-content scroll-to-me">
		<div class="card-body">
			<div class="row gutter-lg">
				<div class="col-12 col-md-8 col-xl-6 mx-auto">
					<div class="form-group">
						<label>{{ __('admin/profile.fields.current_picture') }}</label>
						<div class="user-panel d-flex align-items-center pb-2">
							<div class="image">
								<img src="{{ $user->profile_picture }}" class="img-circle elevation-2" alt="User Image" style="width: 3rem;">
							</div>
							<div class="info">
								<span class="d-block maxw-100 text-truncate">{{ $model->name }}</span>
							</div>
						</div>
						@if(!$user->pictureExists())
						<div class="text-secondary text-090 text-italic">({{ __('admin/profile.this_is_default_user_picture') }})</div>
						@endif
					</div>

					<div class="form-group">
						<label>{{ __('admin/profile.fields.what_to_change?') }}</label>
						<div class="mb-2">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								@if($user->pictureExists())
								<label class="btn btn-sm btn-outline-danger">
									<input type="radio" name="pic_todo" value="remove" class="input-pic-todo" {!! old_checked('pic_todo', null, 'remove') !!}>
									{{ __('admin/profile.fields.remove_picture') }}
								</label>
								@endif
								<label class="btn btn-sm btn-outline-info">
									<input type="radio" name="pic_todo" value="change" class="input-pic-todo" {!! old_checked('pic_todo', null, 'change') !!}>
									{{ __('admin/profile.fields.change_picture') }}
								</label>
							</div>
						</div>
						<div class="picture-todo-wrapper picture-todo-change py-3 d-none">
							<div>
								<label for="input-new-pic">
									{{ __('admin/profile.fields.new_picture') }}
									@component('admin.slots.label-hint')
									@lang('admin/profile.fields.picture_hint')
									@endcomponent
								</label>
								<input type="file" name="new_pic" id="input-new-pic">
							</div>
						</div>
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

@include('admin.libraries.filepond')

@push('scripts')

<script>
jQuery(document).ready(function($) {
	var $form = $("#formEditPictureProfile");

	$form.find(".input-pic-todo").on("change", function(e) {
		if(!this.checked) return;

		var value = $(this).val();
		$(".picture-todo-wrapper").addClass("d-none");
		$(".picture-todo-"+ value).removeClass("d-none");
	}).trigger("change");


	var $newPic = $("#input-new-pic");
	$newPic.filepond({
		//
		dropOnPage: false,
		dropOnElement: true,
		allowImagePreview: true,
		imagePreviewMaxHeight: 150,
		dropValidation: false,
		allowFileTypeValidation: true,
		acceptedFileTypes: ['image/*'],
		fileValidateTypeLabelExpectedTypes: 'Expects {allTypes}',
		fileValidateTypeLabelExpectedTypesMap: {'image/*': 'images'},
		allowFileSizeValidation: true,
		maxFileSize: '2MB',
		files: [
			@if($old_upload = old('new_pic'))
			{
				source: @json($old_upload),
				options: { type: 'limbo' },
			},
			@endif
		],
	});

});
</script>

@endpush
