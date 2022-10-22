<?php
$is_self = $is_self ?? false;
?>
@extends('layouts.app')

@section('content')
<div class="container user-profile">

	@if($user->is_blocked)
	<div class="alert alert-danger">
		@lang('frontend.users.this_user_is_blocked')
	</div>
	@endif

	<div class="row">
		<div class="col-12 col-md-4 col-xl-3">
			<div class="card">
				<div class="card-body">
					<div class="user-logo-wrapper">
						<img src="{{ $user->profile_picture }}" rel="User image">
					</div>
					<h4>{{ $user->name }} @if($is_self) <span class="fas fa-user text-primary text-070 ml-1" title="{{ __('frontend.users.this_is_your_public_profile') }}" data-toggle="tooltip"></span> @endif</h4>
					@if($user->prodi)
					<p class="text-secondary">{{ $user->prodi->complete_name }}</p>
					@endif
				</div>
			</div>
		</div>
		<div class="col-12 col-md-8 col-xl-9">
			<section class="user-apps-section mb-4">
				<h3>@lang('frontend.users.this_users_apps_x_total', ['x' => $apps_total])</h3>

				<form class="mb-1" id="searchForm" method="GET" action="{{ route('user.profile', ['user' => $user->id]) }}">
					<input type="hidden" name="f" value="apps" readonly>

					<div class="form-inline">
						<div class="interactable-inputs">
							<div class="input-group-with-icon icon-append mb-2 mr-sm-2">
								<input type="text" class="form-control" name="s" value="{{ request('s') }}" placeholder="@lang('frontend.apps.search_placeholder')" >
								<button type="button" class="fas fa-times icon interactable btn-clear-input" title="@lang('common.clear')"></button>
							</div>
						</div>
						<div class="ml-0">
							<button type="submit" class="btn btn-primary mb-2 mr-sm-2">
								<span class="icon-text-pair icon-color-reset">
									<span class="fas fa-search icon"></span>
									<span>@lang('common.search')</span>
								</span>
							</button>
						</div>
					</div>
				</form>
				@if($apps->isNotEmpty())
				@if($apps_filter_count > 0)
				<h4>search resutls ({{ count($apps) }})</h4>
				@endif
				<div class="app-list mt-2">
					@foreach ($apps as $app)
					<div class="app-item app-item-sm">
						<a class="card" href="{{ $app->public_url }}">
							<div class="card-img-top">
								<img src="{{ $app->small_thumbnail_url }}" alt="thumbnail">
							</div>
							<div class="app-number">#{{ $loop->iteration }}</div>
							<div class="card-body app-item-body text-wrap-word">
								<div class="app-header">
									@include('components.app-logo', ['logo' => $app->logo, 'exact' => '32x32', 'img_class' => 'app-logo', 'default' => false, 'none' => false, 'as_link' => false])
									<div class="app-title">
										<span class="text-primary">{{ $app->name }}</span>
										@if($app->short_name)
										<div class=""><span class="text-085 text-black-50">@lang('frontend.apps.aka')</span> <span class="text-090" title="@lang('frontend.apps.short_name')">{{ $app->short_name }}</span></div>
										@endif
									</div>
								</div>
								<p class="mt-1 mb-3">
									@lang('frontend.apps.by') {{ $app->owner }}
								</p>
								<?php
								$cats_text = [];
								foreach($app->categories as $i => $cat) {
									if($i < 2)  {
										$cats_text[] = $cat->name;
									} else {
										$cats_text[] = __('frontend.apps.and_x_more', ['x' => count($app->categories) - $i]);
										break;
									}
								}
								$cats_text = implode(', ', $cats_text);
								?>
								<p class="text-090 mt-auto mb-0 text-truncate" title="{{ $cats_text }}">@lang('frontend.apps.fields.categories'): @von($cats_text)</p>
								<?php
								$tags_text = [];
								foreach($app->tags as $i => $tag) {
									if($i < 3)  {
										$tags_text[] = $tag->name;
									} else {
										$tags_text[] = __('frontend.apps.and_x_more', ['x' => count($app->tags) - $i]);
										break;
									}
								}
								$tags_text = implode(', ', $tags_text);
								?>
								<p class="text-090 mt-1 mb-0" title="{{ $tags_text }}">@lang('frontend.apps.fields.tags'): @von($tags_text)</p>
							</div>
						</a>
					</div>
					@endforeach
				</div>
				@else
				@if($apps_filter_count == 0)
				<h4>{{ __('frontend.apps.message.no_apps_yet') }}</h4>
				@else
				<h4>{{ __('frontend.apps.message.no_matches') }}</h4>
				@endif
				@endif
			</section>

			<div class="card">
				<div class="card-body">
					TODO: more content...? or not if there's nothing else
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
jQuery(document).ready(function($) {

	var $searchForm = $("#searchForm");

	$searchForm.on("click", ".btn-clear-input", function(e) {
		e.preventDefault();

		var $input;
		var $formGroup = $(this).closest(".form-group");
		if($formGroup.length == 0)
			$formGroup = $(this).parent();

		$input = $formGroup.find("input, textarea, select");
		if($input.length > 0) {
			$input.val(null).focus();
		}
	}).on("click", ".btn-reset-form", function(e) {
		e.preventDefault();

		var $inputs = $searchForm.find(".interactable-inputs").find("input, textarea, select");
		$inputs.each(function() {
			$(this).val(null).trigger("change");
		});
		$searchForm.submit();
	});
});
</script>
@endpush
