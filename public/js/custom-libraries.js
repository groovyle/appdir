;

jQuery(document).ready(function($) {

	window.splideAutoplayWithVideo = function(splide) {
		var Autoplay = splide.Components.Autoplay;
		var pauseTimer;
		splide.on("active", function(active) {
			clearTimeout(pauseTimer);

			var slide = active.slide;
			if(slide.matches(".splide__slide--has-video")
				|| slide.querySelectorAll(".splide__slide__container--has-video").length > 0 ) {
				Autoplay.pause();
			}/* else if(Autoplay.isPaused()) {
				Autoplay.play();
			}*/
		}).on("video:play", function() {
			clearTimeout(pauseTimer);

			Autoplay.pause();
		}).on("video:pause", function() {
			// wait on the pause event because pause action and buffering video
			// (e.g skipping ahead then buffering) count as pause action
			/*pauseTimer = setTimeout(function() {
				Autoplay.play();
			}, 10000);*/
			// Autoplay.play();
		}).on("video:ended", function() {
			clearTimeout(pauseTimer);

			// next immediately, then autoplay like normal
			setTimeout(function() {
				splide.go(">");
				Autoplay.play();
			}, 1000);
		});
	}

});