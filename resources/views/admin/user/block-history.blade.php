<?php
$append_breadcrumb = [
	[
		'text'    => text_truncate($model->name, 50),
		'url'     => route('admin.users.show', ['user' => $model->id]),
		'active'  => false,
	],
	[
		'text'    => __('admin/users.page_title.block_history'),
	]
];
$tab_blocks_active = count($blocks_active) > 0 || $errors->any();
$tab_blocks_inactive = !$tab_blocks_active;
?>
@extends('admin.layouts.main')

@section('title')
{{ __('admin/users.tab_title.block_history', ['x' => text_truncate($model->name, 20)]) }} - @parent
@endsection

@section('page-title', __('admin/users.page_title.block_history'))

@section('content')

<div class="mb-2">
	@if($back)
	<a href="{{ $back }}" class="btn btn-sm btn-default">&laquo; {{ __('common.back') }}</a>
	@endif
</div>

<div class="main-content">
<div class="card card-primary card-outline card-outline-tabs">
	<div class="card-header p-1">
		<div class="user-panel d-flex align-items-center pb-2">
			<div class="image">
				<img src="{{ $model->profile_picture }}" class="img-circle elevation-2" alt="User Image" style="width: 3rem;">
			</div>
			<div class="info">
				<span class="d-block maxw-100 text-truncate">{{ $model->name_email }}</span>
				@if($model->prodi)
				<span class="d-block maxw-100 text-truncate text-secondary">{{ $model->prodi->name }}</span>
				@endif
			</div>
		</div>
	</div>
	<div class="card-header p-0 border-bottom-0">
		<ul class="nav nav-tabs" id="user-tablist-blocks" role="tablist">
			<li class="nav-item">
				<a class="nav-link @if($tab_blocks_active) active @endif" href="#user-blocks-active" data-toggle="tab" role="tab">{{ __('admin/users.active_blocks') }} <span class="badge badge-danger ml-1">{{ count($blocks_active) }}</span></a>
			</li>
			<li class="nav-item">
				<a class="nav-link @if($tab_blocks_inactive) active @endif" href="#user-blocks-inactive" data-toggle="tab" role="tab">{{ __('admin/users.inactive_blocks') }} <span class="badge badge-secondary ml-1">{{ count($blocks_inactive) }}</span></a>
			</li>
		</ul>
	</div>
	<div class="card-body">
		<div class="tab-content" id="user-tabs-blocks">
			<div class="tab-pane fade @if($tab_blocks_active) show active @endif" id="user-blocks-active" role="tabpanel">
				@if(count($blocks_active) > 0)
				<div class="mb-3 text-secondary"><em>(@lang('common.sorted_from_newest_to_oldest'))</em></div>
				@foreach($blocks_active as $ub)
					@include('admin.user.components.block-item', ['collapse' => false])
				@endforeach
				@else
				<h5>&ndash; @lang('admin/users.no_active_blocks') &ndash;</h5>
				@endif

				@can('unblock', $model)
				<div class="mt-4">
					<form method="POST" action="{{ route('admin.users.unblock.save', ['user' => $model->id]) }}" id="userUnblockForm">
						@csrf
						@method('POST')
						<input type="hidden" name="backto" value="{{ $backto }}">
						@include('components.page-message', ['show_errors' => true])
						<button data-url="{{ route('admin.users.unblock.save', ['user' => $model->id]) }}" class="btn btn-success btn-min-100 btn-ays-modal btn-unblock-user" data-description="{{ __('admin/users.lift_all_blocks_message', ['x' => $model->name_email]) }}"><span class="fas fa-check mr-1"></span> {{ __('admin/users.lift_all_blocks') }}</button>
					</form>
				</div>
				@endcan
			</div>
			<div class="tab-pane fade @if($tab_blocks_inactive) show active @endif" id="user-blocks-inactive" role="tabpanel">
				@if(count($blocks_inactive) > 0)
				<div class="mb-3 text-secondary"><em>(@lang('common.sorted_from_newest_to_oldest'))</em></div>
				@foreach($blocks_inactive as $ub)
					@include('admin.user.components.block-item')
				@endforeach
				@else
				<h5>&ndash; @lang('admin/users.no_inactive_blocks') &ndash;</h5>
				@endif
			</div>
		</div>
	</div>
</div>
</div>
@endsection

@push('scripts')

<script>
jQuery(document).ready(function($) {

	var $btnUnblock = $(".btn-unblock-user");
	var $formUnblock = $("#userUnblockForm");
	$btnUnblock.data("onApprove", function() {
		if($formUnblock.length == 0)
			return false;

		$formUnblock.submit();
		return false;
	});

});
</script>

@endpush
