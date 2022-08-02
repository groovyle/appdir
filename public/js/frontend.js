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
});

/*$("#app").popover({
	'selector': '[data-toggle="popover"]',
	'container': '#app',
	// Use the .popover-adjust to adjust the popover (if needed)
	'template': '<div class="popover popover-adjust" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
});*/

});