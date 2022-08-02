;jQuery(document).ready(function($) {

var AppGlobals = window.AppGlobals || {};

if($.fn.select2) {
	$.fn.select2.defaults.set('language', AppGlobals.lang);
	$.fn.select2.defaults.set('theme', 'bootstrap4');
}

$(document).on("click", 'a[href="#"]', function(e) {
	e.preventDefault();
});

$("#app").tooltip({
	'selector': '[data-toggle="tooltip"]',
	'container': '#app',
	// Use the .tooltip-adjust to adjust the tooltip (if needed)
	'template': '<div class="tooltip tooltip-adjust" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
});

$(".init-popover").popover();

});