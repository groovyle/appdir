;jQuery(document).ready(function($) {

var AppGlobals = window.AppGlobals || {};

AppGlobals._components = {};
AppGlobals.init = function(name, fn) {
	if(!AppGlobals._components.hasOwnProperty(name)) {
		AppGlobals._components[name] = {
			name: name,
			init: false,
			fn: fn || function() {},
		};
	}
	if(!AppGlobals._components[name].init) {
		AppGlobals._components[name].fn();
		AppGlobals._components[name].init = true;
	}
}

if($.fn.select2) {
	$.fn.select2.defaults.set('language', AppGlobals.lang);
	$.fn.select2.defaults.set('theme', 'bootstrap4');
}

$(document).on("click", 'a[href="#"]', function(e) {
	e.preventDefault();
});

$.fn.tooltip.Constructor.Default.whiteList.img.push("style");

$("body").tooltip({
	'selector': '[data-toggle="tooltip"]',
	'container': 'body',
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

$(".init-popover").popover();

});