;
jQuery(document).ready(function($) {

$(document).on("click", 'a[href="#"]', function(e) {
	e.preventDefault();
});

$("#app").tooltip({
	'selector': '[data-toggle="tooltip"]',
	'container': '#app',
	// Use the .tooltip-adjust to adjust the tooltip (if needed)
	'template': '<div class="tooltip tooltip-adjust" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
	// In Bootstrap 4.6 this is possible through the 'customClass' option, but
	// we're using 4.4 so a workaround has to be made.
	// 'customClass': 'qwe',
	// Workaround to add custom classes onto the tooltip
	'placement': function(tip, elm) {
		var $tip = $(tip),
			$elm = $(elm);

		var customClass = $elm.data("customClass");
		if(customClass) {
			if(typeof customClass == "function")
				customClass = customClass();
			$tip.addClass(customClass);
		}

		// Return default behaviour
		var placement = $elm.data("placement");
		if(typeof placement == "function")
			placement = placement(tip, elm);
		return placement || "top";
	},
});

/*$("#app").popover({
	'selector': '[data-toggle="popover"]',
	'container': '#app',
	// Use the .popover-adjust to adjust the popover (if needed)
	'template': '<div class="popover popover-adjust" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
});*/

var $toTop = $("#to-top");
var toTopHidden = true;
var toTopToggle = function(show) {
	show = typeof show === "undefined" ? true : !!show;
	if(show) {
		$toTop.stop().addClass("show");
		toTopHidden = false;
	} else {
		$toTop.stop().blur().removeClass("show fading").trigger("mouseout");
		toTopHidden = true;
	}
}
var toTopScrollHandler = function() {
	if ($(window).scrollTop() > 200 && toTopHidden) {
		toTopToggle(true);
	} else if ($(window).scrollTop() <= 200 && !toTopHidden) {
		toTopToggle(false);
	}
}

$toTop.click(function(e) {
	e.preventDefault();

	$("html, body").animate({
		scrollTop: 0
	}, 1000, "swing", function(){
		toTopToggle(false);
	});
	$toTop.blur().addClass("fading").trigger("mouseout");
});

$(window).scroll(toTopScrollHandler);
toTopScrollHandler();

});