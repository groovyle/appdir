@includeWhen(isset($load_library) && $load_library, 'libraries.splide')

@stack('load-styles')
@stack('load-scripts')

<?php
$simple = !!($simple ?? false);
$rand = random_alpha(5);
?>
<div class="visuals-comparison-wrapper {{ $simple ? 'simple' : 'extended' }}" id="{{ $rand }}-wrapper">
	<div class="new-visuals">
		@include('admin.app.changes.relations.visuals-comparison-snippet-slider', ['items' => $items['new'], 'title' => __('common.new'), 'name' => 'new', 'rand' => $rand])
	</div>

	<hr>

	<div class="old-visuals">
		@include('admin.app.changes.relations.visuals-comparison-snippet-slider', ['items' => $items['old'], 'title' => __('common.old'), 'name' => 'old', 'rand' => $rand])
	</div>
</div>

@if(!$simple)
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