<?php
$is_report_form = old('is_report_form') ?? request()->has('report');
$show_report_form = $is_report_form ? 'show' : '';

// dd($errors->all());
?>

@extends('layouts.app')

@section('outer-content')
<!-- TODO: meta tags -->
<main class="flex-grow-1 app-page">

	<div class="app-header full-page-tabs">
		<div class="container">
			<div class="app-with-logo">
				@if($app->logo)
				<div class="logo-wrapper">
					@include('components.app-logo', ['logo' => $app->logo, 'exact' => '80x80', 'img_class' => 'app-logo', 'as_link' => false])
				</div>
				@endif
				<div class="logo-complement">
					<h1 class="app-title mb-1">
						{{ $app->name }}
						@if($app->short_name)
						<small title="{{ __('frontend.apps.short_name') }}">({{ $app->short_name }})</small>
						@endif
					</h1>
					<div class="app-subtitle segmented">
						<span>@lang('frontend.apps.by') {{ $app->owner }}</span>
						<span>@lang('frontend.apps.x_views', ['x' => $app->page_views])</span>
						<span>@lang('frontend.apps.version_x', ['x' => vo_($app->version_number)])</span>
						<span class="text-muted">
							@cuf('lcfirst', trans('frontend.apps.fields.published_at'))
							@if($app->published_at)
							@include('components/date-with-tooltip', ['date' => $app->published_at, 'reverse' => true])
							@else
							@vo_
							@endif
						</span>
					</div>
				</div>
			</div>
		</div>
				{{--
		<div class="container container-tabs">
			<div class="nav nav-tabs" id="main-page-tabs" role="tablist">
				<a class="nav-item nav-link active" href="#details-tabpane" id="details-tab" data-toggle="tab" role="tab">@lang('frontend.apps.details')</a>
				<a class="nav-item nav-link" href="#comments-tabpane" id="comments-tab" data-toggle="tab" role="tab">@lang('frontend.apps.comments') <span class="badge badge-secondary ml-1">0</span></a>
			</div>
		</div>
				--}}
	</div>

	<div class="app-content tab-content pt-3 px-3 pb-5" id="main-page-tabpanes">
		<div class="tab-pane fade show active" id="details-tabpane" role="tabpanel">
			<div class="container">
				@if($report_message = session('report_message'))
				@include('components.page-message', ['message' => $report_message['message'], 'status' => $report_message['type'].' scroll-to-me', 'dismiss' => true])
				@endif
				<form class="card mb-4 collapse collapse-scrollto {{ $show_report_form }}" id="reportAppForm" method="POST" action="{{ route('apps.report.save', ['slug' => $app->slug]) }}">
					<div class="card-body">
						<div class="row">
							<div class="col-12 col-md-8 col-lg-7 col-xl-6 mx-auto">
								<button type="button" class="close" data-toggle="collapse" data-target="#reportAppForm" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<h3 class="card-title text-danger">@lang('frontend.apps.report_app'): <strong class="text-danger">{{ $app->complete_name }}</strong></h3>
								@csrf
								@method('POST')
								<input type="hidden" name="app_id" value="{{ $app->id }}" >
								<input type="hidden" name="report_user" value="" >

								@includeWhen($is_report_form, 'components.page-message', ['show_errors' => true])

								@if(!auth()->check())
								<div class="alert alert-info py-2 mb-1">
									<span class="icon-text-pair icon-color-reset icon-2x">
										<span class="fa fa-info-circle icon"></span>
										<span>@lang('frontend.apps.if_you_have_an_account_logging_in_will_increase_credibility_of_your_report')</span>
									</span>
								</div>
								<div class="form-group mb-1">
									<label for="reportEmail">@lang('frontend.apps.fields.email'):</label>
									<input type="email" name="report_email" id="reportEmail" class="form-control" placeholder="@lang('frontend.apps.fields.reportee_email_placeholder')" value="{{ old('report_email') }}" required>
								</div>
								@else
								<p class="mb-1">@lang('frontend.apps.reporting_as') <strong>{{ auth()->user()->name }}</strong> @if($email = auth()->user()->email) ({{ $email }}) @endif</p>
								@endif
								<div class="form-group mb-1">
									<label class="d-block mb-0">@lang('frontend.apps.fields.report_categories'):</label>
									<div class="d-flex flex-row flex-wrap">
										@foreach($report_categories as $rc)
										<div class="form-check form-check-inline" title="{{ $rc->description }}" data-toggle="tooltip" data-placement="bottom" data-custom-class="text-r090 tooltip-wider">
											<input type="checkbox" name="report_categories[]" value="{{ $rc->id }}" id="reportCategory-{{ $rc->id }}" class="form-check-input" {!! old_checked('report_categories', NULL, $rc->id) !!}>
											<label class="form-check-label" for="reportCategory-{{ $rc->id }}">{{ $rc->name }}</label>
										</div>
										@endforeach
									</div>
								</div>
								<div class="form-group mb-1">
									<label for="reportReason" class="d-block mb-0">@lang('frontend.apps.fields.report_reason'):</label>
									<textarea name="report_reason" id="reportReason" class="form-control show-resize" placeholder="@lang('frontend.apps.fields.report_reason_placeholder')" rows="2" maxlength="{{ $report_reason_limit }}" required>{{ old('report_reason') }}</textarea>
								</div>
								<div class="text-center mt-3">
									<button type="submit" class="btn btn-sm btn-primary btn-minw-100">@lang('frontend.apps.submit_report')</button>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="row details-panel text-wrap-word">
					<div class="col-12 mb-4 col-md-8 mb-md-0 col-lg-8 col-xl-9 details-panel-left">
						@if($app->visuals->count() > 0)
						<div>
							<div class="app-visuals-slides">
								<div class="splide img-maxed img-centered" id="app-visuals-slides-big" tabindex="0">
									<div class="splide__track">
										<ul class="splide__list">
											@foreach($app->visuals as $item)
											@if($item->type == 'image')
											<li class="splide__slide">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow">{{ trim($item->caption) }}</div>
											</li>
											@elseif($item->type == 'video')
											<li class="splide__slide splide-video" data-splide-youtube="{{ $item->embed_url }}">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow">{{ trim($item->caption) }}</div>
											</li>
											@endif
											@endforeach
										</ul>
									</div>
								</div>
							</div>
							<div class="app-visuals-slides mt-2">
								<div class="splide img-cover img-bordered has-arrow-navs" id="app-visuals-slides-small" tabindex="0">
									<div class="splide__track">
										<ul class="splide__list">
											@foreach($app->visuals as $item)
											<li class="splide__slide">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
											</li>
											@endforeach
										</ul>
									</div>
								</div>
							</div>
						</div>
						@else
						<div class="placeholder-visuals-empty mock-bg" style="height: 300px;">
							<h5 class="placeholder-text">@lang('frontend.apps.empties.visual_media')</h5>
						</div>
						@endif

						<div class="card mt-3">
							<div class="card-body">
								@if($app->url)
								<div class="mb-2">
									<span class="text-bold">@lang('frontend.apps.fields.app_url'):</span>
									<a target="_blank" class="btn btn-link" href="{{ $app->url }}">{{ $app->url }} <span class="fas fa-external-link-alt"></span></a>
								</div>
								@endif
								@if(trim($app->description))
								<div class="text-bold">@lang('frontend.apps.fields.description'):</div>
								<span class="text-pre-line text-110">{{ $app->description }}</span>
								@else
								<h5>@lang('frontend.apps.empties.description')</h5>
								@endif
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-lg-4 col-xl-3 details-panel-right">
						<div class="card">
							<div class="card-body">
								<h4>@lang('frontend.apps.author')</h4>
								<p><a href="{{ route('user.profile', ['user' => $app->owner->id]) }}">{{ $app->owner->name }}</a> TODO: clickable user to user page</p>
								<p>number of apps, user details, etc.</p>
								<p>@lang('frontend.apps.share_this_app'): --- TODO: socmed share buttons</p>
								<div class="text-center">
									<a href="#reportAppForm" class="btn btn-danger btn-sm px-3 btn-flex-row" data-toggle="collapse">
										<span class="fas fa-exclamation-triangle mr-2 text-090"></span>
										<span class="text-wrap-word">@lang('frontend.apps.report_this_app')</span>
										<span class="fas fa-exclamation-triangle ml-2 text-090"></span>
									</a>
								</div>
							</div>
						</div>
						<div class="card mt-3">
							<div class="card-body">
								<h5>@lang('frontend.apps.additional_information')</h5>
								<dl class="text-090">
									<dt>@lang('frontend.apps.fields.last_updated')</dt>
									@if($last_updated = ($app->updated_at ?? $app->created_at))
									<dd>@include('components/date-with-tooltip', ['date' => $last_updated])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.apps.fields.published_at')</dt>
									@if($app->published_at)
									<dd>@include('components/date-with-tooltip', ['date' => $app->published_at])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.apps.fields.long_name')</dt>
									<dd>{{ $app->name }}</dd>

									<dt>@lang('frontend.apps.fields.short_name')</dt>
									<dd>@vo_($app->short_name)</dd>

									<dt>@lang('frontend.apps.fields.version')</dt>
									<dd>@vo_($app->version_number)</dd>

									<dt>@lang('frontend.apps.fields.app_categories') ({{ count($app->categories) }})</dt>
									<dd>
										@if(count($app->categories) > 0)
										@foreach($app->categories as $category)
										<a href="{{ route('apps', ['c' => $category->id]) }}" class="btn btn-sm btn-light bordered rounded-pill" title="{{ __('frontend.apps.search_by_this_category_x', ['x' => $category->name]) }}" data-toggle="tooltip">{{ $category->name }}</a>
										@endforeach
										@else
										@vo_
										@endif
									</dd>

									<dt>@lang('frontend.apps.fields.tags') ({{ count($app->tags) }})</dt>
									<dd>
										@if(count($app->tags) > 0)
										@foreach($app->tags as $tag)
										<a href="{{ route('apps', ['t' => $tag->name]) }}" class="btn btn-sm btn-light bordered rounded-pill" title="{{ __('frontend.apps.search_by_this_tag_x', ['x' => $tag->name]) }}" data-toggle="tooltip">{{ $tag->name }}</a>
										@endforeach
										@else
										@vo_
										@endif
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{{--
		<div class="tab-pane fade" id="comments-tabpane" role="tabpanel">
			<div class="container">
				asd
			</div>
		</div>
		--}}
	</div>

</main>
@endsection

@include('libraries.splide')

@push('scripts')
<script>
jQuery(document).ready(function($) {
	$(document).on('click', '[data-toggle="lightbox"]', function(e) {
		e.preventDefault();
		$(this).ekkoLightbox({
			// alwaysShowClose: true
		});
	});
	$('[data-toggle="popover"]').popover({
		container: "body",
	});


	$("#reportReason").textareaShowLength({
		position: "top right",
	}).textareaAutoHeight({
		bypassHeight: false,
	});

	@if($is_report_form)
	Helpers.scrollTo($("#reportAppForm"));
	@endif


	@if($app->visuals->count() > 0)
	var splideOptionsBig = {
		type: "fade",
		gap: "1rem",
		rewind: true,
		width: "600px",
		height: "350px",
		autoHeight: true,
		// autoWidth: true,
		heightRatio: 9/16,
		arrows: false,
		pagination: false,
		drag: false,
		keyboard: true,
		// snap: true,
		// autoplay: true,
		interval: 10000,
		video: {
			// autoplay: true,
			loop: false,
			mute: true,
		},
	};

	var splideOptionsSmall = {
		type: "slide",
		// gap: 5,
		// padding: "4rem",
		// width: "600px",
		rewind: true,
		fixedWidth: 100,
		fixedHeight: 65,
		heightRatio: 9/16,
		arrows: true,
		pagination: false,
		isNavigation: true,
		keyboard: true,
		// focus: "center",
		// trimSpace: "move",
		// autoplay: true,
		// interval: 10000,
		breakpoints: {
			767: {
				fixedWidth: 75,
				fixedHeight: 50,
			},
		},
	};

	var splideSlidesSmall = new Splide("#app-visuals-slides-small", splideOptionsSmall);
	var splideSlidesBig = new Splide("#app-visuals-slides-big", splideOptionsBig);

	splideSlidesSmall.mount( window.splide.Extensions );
	splideSlidesBig.mount( window.splide.Extensions );
	splideSlidesBig.sync(splideSlidesSmall);
	splideAutoplayWithVideo(splideSlidesBig);

	@endif

	var $scrollToElm = $(".scroll-to-me");
	if($scrollToElm.length > 0) {
			Helpers.scrollTo($scrollToElm.first(), {
				animate: false,
			});
	}

});
</script>
@endpush
