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
	'boundary': 'window',
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
			placement = placement.apply(this, [tip, elm]);
		return placement || "top";
	},
});


// Call the following function upon loading/appending html ajax content
window.initDefaultClasses = function(context) {
	var $context = context ? $(context) : $("body");

	$context.find(".init-popover").removeClass(".init-popover").popover();
	$context.find(".init-readmore").removeClass(".init-readmore").addClass("text-pre-wrap").readMore();
	$context.find("form.no-enter-submit").noEnterSubmit();
}
window.initDefaultClasses();


var $logoutForm = $("#logout-form");
$(document).on("click", ".btn-logout", function(e) {
	e.preventDefault();
	$logoutForm.submit();
});


var $mainContent = $(".main-content").first();
if($mainContent.length > 0 && $mainContent.is(".scroll-to-me")) {
	// Wait for the DOM to finish updating, like e.g select[multiple] with Select2
	// will change its height
	setTimeout(function() {
		Helpers.scrollTo($mainContent, { animate: false });
		$mainContent.removeClass("scroll-to-me");
	}, 100);
}

if($("body").is(".layout-fixed")) {
	// Auto-center the last active menu item in the sidebar.
	// Why last? In case of a treeview menu, get to the last one.
	var $activeMenuItems = $(".nav-sidebar .nav-link.active").last();
	if($activeMenuItems.length > 0) {
		Helpers.parentScrollTo($activeMenuItems, {
			animate: false,
			offset: 0,
			percentageOffset: 50,
		});
	}
}

});