@includeWhen(isset($load_library) && $load_library, 'libraries.splide')

@stack('load-styles')
@stack('load-scripts')

<?php
$simple = !!($simple ?? false);
$rand = random_alpha(5);
?>
<div class="visuals-comparison-wrapper" id="{{ $rand }}-wrapper">
	<div class="new-visuals">
		@include('admin.app.changes.relations.visuals-comparison-snippet-slider', ['items' => $items['new'], 'title' => __('common.new'), 'name' => 'new', 'rand' => $rand])
	</div>

	<hr>

	<div class="old-visuals">
		@include('admin.app.changes.relations.visuals-comparison-snippet-slider', ['items' => $items['old'], 'title' => __('common.old'), 'name' => 'old', 'rand' => $rand])
	</div>
</div>

@if(!$simple)
<style>
.visuals-comparison-wrapper .splide__list {
	height: auto;
	align-items: center;
}
.visuals-comparison-wrapper .splide--slide {
	padding-bottom: 2em;
}
.visuals-comparison-wrapper .splide__slide img {
	width: 100%;
	height: 100%;
	object-fit: scale-down;
}
.visuals-comparison-wrapper .splide-caption {
	padding: 0.25em 0.5em;
	background-color: rgba(0,0,0,0.5);
	color: #ddd;
	/*font-size: 0.9rem;*/
	max-height: calc(3em * 1.2 + 0.5em); /* 3 lines + padding top bottom */
	line-height: 1.2;
	overflow-y: hidden;
}
.visuals-comparison-wrapper .splide__slide.splide-video {
	/*padding-bottom: calc(3em * 1.2 + 0.5em);*/
}
.visuals-comparison-wrapper .splide__slide:not(.splide-video) .splide-caption {
	box-sizing: border-box;
	position: absolute;
	z-index: 1;
	bottom: 0;
	left: 0;
	right: 0;
	transition: all 0.5s;
}
.visuals-comparison-wrapper .splide-caption:empty {
	display: none;
}
.visuals-comparison-wrapper .splide-caption:hover,
.visuals-comparison-wrapper .splide-caption:focus {
	max-height: 100%;
	overflow-y: auto;
}
.visuals-comparison-wrapper .splide-caption a {
	color: skyblue;
	text-decoration: underline;
}
.visuals-comparison-wrapper .splide-caption a:hover,
.visuals-comparison-wrapper .splide-caption a:focus {
	color: deepskyblue;
}
</style>
<script>
jQuery(document).ready(function($) {
	var autoplay = @json($autoplay);
	var splideOptions = {
		type: "slide",
		padding: "4rem",
		gap: "1rem",
		rewind: true,
		width: "500px",
		// height: "300px",
		autoHeight: true,
		// autoWidth: true,
		heightRatio: 9/16,
		arrows: true,
		drag: "free",
		snap: true,
		video: {
			autoplay: true,
			loop: false,
			mute: true,
		},
	};
	if(autoplay) {
		splideOptions = $.extend({}, splideOptions, {
			autoplay: true,
			interval: 5000,
		});
	}

	@if(count($items['new']) > 0)
	var splideNew = new Splide(@json('#'.$rand.'-new'), splideOptions);
	splideNew.mount( window.splide.Extensions );
	splideAutoplayWithVideo(splideNew);
	@endif

	@if(count($items['old']) > 0)
	var splideOld = new Splide(@json('#'.$rand.'-old'), splideOptions);
	splideOld.mount( window.splide.Extensions );
	splideAutoplayWithVideo(splideOld);
	@endif

});
</script>
@endif