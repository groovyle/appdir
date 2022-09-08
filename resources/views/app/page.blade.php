@extends('layouts.app')

@section('outer-content')
<!-- TODO: meta tags -->
<main class="flex-grow-1 app-page">

	<div class="app-header full-page-tabs">
		<div class="container">
			<h1 class="app-title mb-1">
				{{ $app->name }}
				@if($app->short_name)
				<small>({{ $app->short_name }})</small>
				@endif
			</h1>
			<div class="app-subtitle segmented mb-3">
				<span>@lang('frontend.by') {{ $app->owner }}</span>
				<span>@lang('frontend.fields.short_name'): @vo_($app->short_name)</span>
				<span>@lang('frontend.x_views')</span>
				<span class="text-muted">
					@lang('frontend.fields.published_at')
					@if($app->published_at)
					@include('components/date-with-tooltip', ['date' => $app->published_at, 'reverse' => true])
					@else
					@vo_
					@endif
				</span>
			</div>
		</div>
		<div class="container container-tabs">
			<div class="nav nav-tabs" id="main-page-tabs" role="tablist">
				<a class="nav-item nav-link active" href="#details-tabpane" id="details-tab" data-toggle="tab" role="tab">@lang('frontend.details')</a>
				<a class="nav-item nav-link" href="#comments-tabpane" id="comments-tab" data-toggle="tab" role="tab">@lang('frontend.comments') <span class="badge badge-secondary ml-1">0</span></a>
			</div>
		</div>
	</div>

	<div class="app-content tab-content pt-3 px-3 pb-5" id="main-page-tabpanes">
		<div class="tab-pane fade show active" id="details-tabpane" role="tabpanel">
			<div class="container">
				<div class="row details-panel">
					<div class="col-12 mb-4 col-md-8 mb-md-0 col-lg-8 col-xl-9 details-panel-left">
						@if($app->visuals->count() > 0)
						<div>
							<div class="app-visuals-slides">
								<div class="splide img-maxed img-bordered-all img-centered" id="app-visuals-slides-big" tabindex="0">
									<div class="splide__track">
										<ul class="splide__list">
											@foreach($app->visuals as $item)
											@if($item->type == 'image')
											<li class="splide__slide">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow mt-1">{{ trim($item->caption) }}</div>
											</li>
											@elseif($item->type == 'video')
											<li class="splide__slide splide-video" data-splide-youtube="{{ $item->embed_url }}">
												<div class="splide__slide__container">
													<img src="{{ $item->thumbnail_url }}" >
												</div>
												<div class="splide-caption has-arrow mt-1">{{ trim($item->caption) }}</div>
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
							<h5 class="placeholder-text">@lang('frontend.empties.visual_media') This app has no showcase of what or how it looks like...</h5>
						</div>
						@endif

						<div class="card mt-3">
							<div class="card-body">
								@if(trim($app->description))
								<div class="text-bold">@lang('frontend.fields.description'):</div>
								<span class="text-pre-line text-110">{{ $app->description }}</span>
								@else
								<h5>@lang('frontend.empties.description') This app has no description...</h5>
								@endif
								@if($app->url)
								<div class="mt-2">
									<span class="text-bold">@lang('frontend.fields.app_url'):</span>
									<a target="_blank" class="btn btn-link" href="{{ $app->url }}">{{ $app->url }} <span class="fas fa-external-link-alt"></span></a>
								</div>
								@endif
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-lg-4 col-xl-3 details-panel-right">
						<div class="card">
							<div class="card-body">
								<h4>@lang('frontend.author')</h4>
								<p>{{ $app->owner->name }}</p>
								<p>number of apps, user details, etc.</p>
								<p>@lang('frontend.share_this_app'): ---</p>
								<div class="text-center">
									<button type="button" class="btn btn-danger btn-sm text-nowrap px-3">
										<span class="fas fa-exclamation-triangle mr-1 text-090"></span>
										<span class="text-wrap-word">@lang('frontend.report_this_app')</span>
										<span class="fas fa-exclamation-triangle ml-1 text-090"></span>
									</button>
								</div>
							</div>
						</div>
						<div class="card mt-3">
							<div class="card-body">
								<dl class="text-090">
									<dt>@lang('frontend.fields.last_updated')</dt>
									@if($last_updated = ($app->updated_at ?? $app->created_at))
									<dd>@include('components/date-with-tooltip', ['date' => $last_updated])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.fields.published')</dt>
									@if($app->published_at)
									<dd>@include('components/date-with-tooltip', ['date' => $app->published_at])</dd>
									@else
									<dd>@vo_</dd>
									@endif

									<dt>@lang('frontend.fields.app_categories')</dt>
									<dd>
										<ul class="pl-3">
											@foreach ($app->categories as $category)
											<li>{{ $category->name }}</li>
											@endforeach
										</ul>
									</dd>

									<dt>@lang('frontend.fields.tags')</dt>
									<dd>
										<ul class="pl-3">
											@foreach ($app->tags as $tag)
											<li>{{ $tag->name }}</li>
											@endforeach
										</ul>
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="comments-tabpane" role="tabpanel">
			<div class="container">
				asd
			</div>
		</div>
	</div>

	<div class="card mt-4 d-none">
		<h2 class="card-header">{{ $app->name }}</h2>

		<div class="card-body">
			<div class="row justify-content-center">
				<div class="col col-sm-8 border-right">
					{!! description_text($app->description) !!}
					<div class="app-tags">
						<small class="font-weight-bold">Tags</small>
						<br>
						@forelse ($app->tags as $tag)
						<a href="#" class="btn btn-sm btn-light border rounded-pill" data-toggle="popover" data-content="{{ $tag->name }}" data-trigger="focus" data-placement="top">{{ $tag->name }}</a>
						@empty
						&ndash;
						@endforelse
					</div>
					<hr>
					<div>
						URL: <a href="{{ $app->full_url }}" target="_blank">{{ $app->full_url }} <span class="fa-fw fas fa-external-link-alt"></span></a>
					</div>
					<div>
						<a href="{{ route('apps.preview', [$app->slug]) }}">Preview</a>
					</div>
					@if ($app->visuals_count)
					<div class="row mx-n1">
						@foreach ($app->visuals as $visual)
						@php
						$i = $loop->iteration;
						@endphp
						<div class="col w-auto px-1 mr-1 mb-1 flex-grow-0">
							<div class="border bg-white d-flex justify-content-center align-items-stretch" style="width: 8rem; height: 8rem;">
								<a href="{{ $visual->url }}" class="d-flex justify-content-center align-items-stretch overflow-hidden" data-toggle="lightbox" data-gallery="visuals">
									<img src="{{ $visual->url }}" class="mh-100" alt="visual {{ $i }}">
								</a>
							</div>
						</div>
						@endforeach
					</div>
					@endif
				</div>
				<div class="col col-sm-4">
					<h4>Author</h4>
					<p>{{ $app->owner->name }}</p>
					<dl>
						<dt>Date Published</dt>
						<dd>{{ $app->last_verification->updated_at->translatedFormat('j F Y, H:i') }}</dd>

						<dt>Categories</dt>
						<dd>
							<ul class="pl-3">
								@foreach ($app->categories as $category)
								<li>{{ $category->name }}</li>
								@endforeach
							</ul>
						</dd>
					</dl>
				</div>
			</div>
		</div>
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

	var autoplayWithVideo = function(splide) {
		var Autoplay = splide.Components.Autoplay;
		var pauseTimer;
		var interval = (splide.options.interval || 5000) / 2;
		splide.on("active", function(active) {
			clearTimeout(pauseTimer);

			var slide = active.slide;
			if(slide.matches(".splide__slide--has-video")
				|| slide.querySelectorAll(".splide__slide__container--has-video").length > 0 ) {
				Autoplay.pause();
			} else if(Autoplay.isPaused()) {
				Autoplay.play();
			}
		}).on("video:play", function() {
			clearTimeout(pauseTimer);

			Autoplay.pause();
		}).on("video:pause", function() {
			// wait on the pause event because pause action and buffering video
			// (e.g skipping ahead then buffering) count as pause action
			pauseTimer = setTimeout(function() {
				Autoplay.play();
			}, interval);
		}).on("video:ended", function() {
			clearTimeout(pauseTimer);

			// next immediately, then autoplay like normal
			setTimeout(function() {
				splide.go(">");
				Autoplay.play();
			}, interval / 2);
		});
	}


	var splideSlidesSmall = new Splide("#app-visuals-slides-small", splideOptionsSmall);
	var splideSlidesBig = new Splide("#app-visuals-slides-big", splideOptionsBig);
	splideSlidesBig.sync(splideSlidesSmall);

	splideSlidesSmall.mount( window.splide.Extensions );
	splideSlidesBig.mount( window.splide.Extensions );
	// autoplayWithVideo(splideSlides);

	@endif

});
</script>
@endpush
