;

if(jQuery) {
	var $ = jQuery;

	window.Helpers = function() {
		var fillDataString = function(str, data, remove, startMarker, endMarker) {

			if(typeof startMarker === "undefined")
				startMarker = "__";
			if(typeof endMarker === "undefined")
				endMarker = startMarker;

			if(data && typeof data === "object") {
				for(var k in data) {
					var value = data[k] != null ? data[k] : "";
					var pattern = new RegExp(startMarker + escapeRegex(k.toUpperCase()) + endMarker, "g");
					str = str.replace(pattern, value);
				}
			}

			var replaceBlanks;
			if(typeof remove === "undefined")
				replaceBlanks = true;
			else if(typeof remove === "boolean")
				replaceBlanks = remove;
			else
				replaceBlanks = true;

			var blankReplacement = "";
			if(typeof remove !== "boolean" && remove) {
				blankReplacement = ""+ remove;
			}
			if(replaceBlanks) {
				str = str.replace(/__[A-Z_]+__/g, blankReplacement);
			}

			return str;
		};

		var fillDataClasses = function(wrapper, data, className) {
			var $wrapper = $(wrapper);
			if(!className) className = 'data';
			for(var k in data) {
				var value = data[k] != null ? data[k] : '';
				$wrapper.find('[data-field="'+ k +'"].'+ className +'-value').val(value);
				$wrapper.find('[data-field="'+ k +'"].'+ className +'-text').text(value);
				$wrapper.find('[data-field="'+ k +'"].'+ className +'-html').empty().append(value);
				$wrapper.find('[data-field="'+ k +'"].'+ className +'-check').prop('checked', !!value);
				$wrapper.find('[data-field="'+ k +'"].'+ className +'-title')
					.attr('title', value)
					.attr('data-original-title', value);
			}
		};

		var clearDataClasses = function(wrapper, className) {
			var $wrapper = $(wrapper);
			if(!className) className = 'data';
			$wrapper.find('[data-field].'+ className +'-value').val(null);
			$wrapper.find('[data-field].'+ className +'-text').text('');
			$wrapper.find('[data-field].'+ className +'-html').empty();
			$wrapper.find('[data-field].'+ className +'-check').prop('checked', false);
			$wrapper.find('[data-field].'+ className +'-title')
				.removeAttr('title')
				.removeAttr('data-original-title');
		};

		var gatherDataClassesValue = function(wrapper, className) {
			var $wrapper = $(wrapper);
			if(!className) className = 'data';
			var data = {};
			$wrapper.find('[data-field].'+ className +'-value, [name].'+ className +'-value').each(function(i, item) {
				var $item = $(this);
				var field = $item.data("field") || $item.attr("name");
				var disabled = $item.is("disabled") || $item.prop("disabled");
				if(disabled || !field)
					return;

				if($item.not("[type=checkbox],[type=radio]") || $item.prop("checked")) {
					data[field] = $item.val();
				}
			});

			return data;
		};

		// Random string generator
		// https://stackoverflow.com/a/1349426
		// Added a lil twist
		var _uniqueRandomPool = new Set; // ideally uses a set, but the argument can be an array, so...
		var randomString = function(length, except) {
			var result           = '';
			var characters       = 'abcdefghijklmnopqrstuvwxyz0123456789';
			var charactersLength = characters.length;
			var exceptionPool    = typeof except === "undefined" ? _uniqueRandomPool : except;
			var breakLoop = false;
			do {
				result = '';
				for( var i = 0; i < length; i++ ) {
					result += characters.charAt(Math.floor(Math.random() * charactersLength));
				}
				if(exceptionPool instanceof Set) {
					if( ! exceptionPool.has(result) ) {
						break;
					}
				}

				if(except !== false) {
					breakLoop = exceptionPool instanceof Set
						? ! exceptionPool.has(result)
						: exceptionPool.indexOf(result) === -1
					;

					if(breakLoop && typeof except === "undefined") {
						// Uses internal pool
						_uniqueRandomPool.add(result);
					}
				} else {
					breakLoop = true;
				}
			} while(!breakLoop);
			return result;
		};

		// https://stackoverflow.com/a/3561711/7770384
		var escapeRegex = function(string) {
			return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
		};

		/* Debounce function taken from underscore.js */
		var debounce = function(func, wait, immediate) {
			var timeout;
			return function() {
				var context = this, args = arguments;
				var later = function() {
					timeout = null;
					if (!immediate) func.apply(context, args);
				};
				if (immediate && !timeout) func.apply(context, args);
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		};

		/* Find closest scroll parent/box */
		// https://stackoverflow.com/a/42543908
		function getScrollParent(element, includeHidden) {
			var style = getComputedStyle(element);
			var excludeStaticParent = style.position === "absolute";
			var overflowRegex = includeHidden ? /(auto|scroll|hidden)/ : /(auto|scroll)/;

			// NOTE: return body or document?
			// var defaultParent = document.body;
			var defaultParent = document;

			if (style.position === "fixed") return defaultParent;
			for (var parent = element; (parent = parent.parentElement);) {
				style = getComputedStyle(parent);
				if (excludeStaticParent && style.position === "static") {
					continue;
				}
				if (overflowRegex.test(style.overflow + style.overflowY + style.overflowX)) return parent;
			}

			return defaultParent;
		}

		var scrollToDefaultOptions = {
			offset: 10,
			animate: false,
			duration: 400,
			animateOptions: undefined,
		};

		// Scroll to element, factoring in the navbar height and a little offset
		var scrollTo = function(element, options) {
			options = $.extend(scrollToDefaultOptions, options);

			var offset = 0 + options.offset;
			var $element = $(element);
			var $navbar = $("#top-navbar, .navbar").first();

			var scrollTo = $element.offset().top - $navbar.outerHeight() - offset;
			var progressEvent = function() {
				$element.trigger("scrolling.scrollto");
			}
			var postEvent = function() {
				$element.trigger("scrolled.scrollto");
			}

			progressEvent();
			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$("html, body").animate({
					scrollTop: scrollTo
				}, animateOptions);
				setTimeout(postEvent, options.duration);
			} else {
				$("html, body").scrollTop(scrollTo);
				postEvent();
			}
		};

		// Scroll to element relative to its overflow parent, not the page
		var parentScrollTo = function(element, options, $parentContext) {
			options = $.extend(scrollToDefaultOptions, {
				// e.g put 50 or "50%" to make the element
				// appear in the middle of its parent
				percentageOffset: 0,
			}, options);

			var offset = 0 + options.offset;
			var $element = $(element),
				$parent,
				isRoot = false;

			if($element.length == 0) {
				// Element does not exist
				return;
			}

			var progressEvent = function() {
				$element.trigger("scrolling.parentscrollto");
			}
			var postEvent = function() {
				$element.trigger("scrolled.parentscrollto");
			}

			if($parentContext instanceof jQuery)
				$parent = $parentContext;
			else if($parentContext instanceof Element || typeof $parentContext === "string")
				$parent = $($parentContext);
			else
				$parent = $(getScrollParent($element[0]));

			if($parent.is(document)) {
				$parent = $("html, body");
			}
			if($parent.is("html, body")) {
				isRoot = true;
			}
			var parentOffset = $parent.offset().top;
			var scrollTo = ($element.offset().top - parentOffset) - offset;
			if(!isRoot) {
				scrollTo = $parent.scrollTop() + scrollTo;
			}

			if(options.percentageOffset) {
				var percentOffset = options.percentageOffset;
				if(typeof percentOffset == "string")
					percentOffset = parseFloat(percentOffset); // ignore the percent sign
				scrollTo -= $parent.height() * percentOffset / 100;
			}

			progressEvent();
			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$parent.animate({
					scrollTop: scrollTo
				}, animateOptions);
				setTimeout(postEvent, options.duration);
			} else {
				$parent.scrollTop(scrollTo);
				postEvent();
			}
		};

		// Scroll to element (horizontally), factoring in the sidebar width and a little offset
		var hScrollTo = function(element, options) {
			options = $.extend(scrollToDefaultOptions, options);

			var offset = 0 + options.offset;
			var $element = $(element);
			var $sidebar = $("#left-sidebar"),
				sidebarWidth = $sidebar.is(".visible") ? $sidebar.outerWidth() : 0;

			var scrollTo = $element.offset().left - sidebarWidth - offset;
			var progressEvent = function() {
				$element.trigger("scrolling.hscrollto");
			}
			var postEvent = function() {
				$element.trigger("scrolled.hscrollto");
			}

			progressEvent();
			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$("html, body").animate({
					scrollTop: scrollTo
				}, animateOptions);
				setTimeout(postEvent, options.duration);
			} else {
				$("html, body").scrollTop(scrollTo);
				postEvent();
			}
		};

		// Scroll to element horizontally relative to its overflow parent, not the page
		var parentHScrollTo = function(element, options, $parentContext) {
			options = $.extend(scrollToDefaultOptions, {
				// e.g put 50 or "50%" to make the element
				// appear in the middle of its parent
				percentageOffset: 0,
			}, options);

			var offset = 0 + options.offset;
			var $element = $(element),
				$parent,
				isRoot = false;

			if($element.length == 0) {
				// Element does not exist
				return;
			}

			var progressEvent = function() {
				$element.trigger("scrolling.parenthscrollto");
			}
			var postEvent = function() {
				$element.trigger("scrolled.parenthscrollto");
			}

			if($parentContext instanceof jQuery)
				$parent = $parentContext;
			else if($parentContext instanceof Element || typeof $parentContext === "string")
				$parent = $($parentContext);
			else
				$parent = $(getScrollParent($element[0]));

			if($parent.is(document)) {
				$parent = $("html, body");
			}
			if($parent.is("html, body")) {
				isRoot = true;
			}
			var parentOffset = $parent.offset().left;
			var scrollTo = $parent.scrollLeft() + ($element.offset().left - parentOffset) - offset;
			if(!isRoot) {
				scrollTo = $parent.scrollLeft() + scrollTo;
			}

			if(options.percentageOffset) {
				var percentOffset = options.percentageOffset;
				if(typeof percentOffset == "string")
					percentOffset = parseFloat(percentOffset) / 100; // ignore the percent sign
				scrollTo -= $parent.height() * percentOffset;
			}

			progressEvent();
			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$parent.animate({
					scrollLeft: scrollTo
				}, animateOptions);
				setTimeout(postEvent, options.duration);
			} else {
				$parent.scrollLeft(scrollTo);
				postEvent();
			}
		};

		var flashElement = function(element) {
			var $elm = $(element);
			$elm.removeClass("flash-element").addClass("flash-element");
			$elm.trigger("flashing.flashelement");
			setTimeout(function() {
				$elm.removeClass("flash-element");
				$elm.trigger("flashed.flashelement");
			}, 3000);
		};

		var scrollAndFlash = function(element, scrollOptions, flashOptions) {
			var $element = $(element);
			$element.one("scrolled.scrollto", function(e) {
				Helpers.flashElement($element, flashOptions);
			});
			Helpers.scrollTo($element, scrollOptions);
		}

		// Remove a certain value in an array
		// https://stackoverflow.com/questions/5767325/how-can-i-remove-a-specific-item-from-an-array
		var removeArrayElement = function(arr, needle, multiple, isStrict) {
			if(typeof multiple === "undefined")
				multiple = true;
			if(typeof isStrict === "undefined")
				isStrict = false;

			// Do not modify original array
			var copy = arr.slice(0);
			if(!multiple) {
				var pos = isStrict ? copy.indexOf(needle) : copy.findIndex(function(v) { return v == needle; });
				if(pos > -1) {
					copy.splice(needle, 1);
				}
			} else {
				copy = copy.filter(function(item) {
					return isStrict ? item !== needle : item != needle;
				});
			}

			return copy;
		};

		// Unique values in array
		// https://stackoverflow.com/a/11437129/7770384
		var uniqueArrayElements = function(arr) {
			var j = {};

			arr.forEach(function(v) {
				j[v +"::"+ typeof v] = v;
			});

			return Object.keys(j).map(function(v) {
				return j[v];
			});
		}

		// https://css-tricks.com/snippets/javascript/move-cursor-to-end-of-input/
		// I think it's safe to use for non-text inputs
		var moveCursorToEnd = function(el) {
			if(el instanceof jQuery)
				el = el.get(0);
			if(!el)
				return;

			if (typeof el.selectionStart == "number") {
				el.selectionStart = el.selectionEnd = el.value.length;
			} else if (typeof el.createTextRange != "undefined") {
				el.focus();
				var range = el.createTextRange();
				range.collapse(false);
				range.select();
			}
		}

		var focusAndSelectText = function(el) {
			if(el instanceof jQuery)
				el = el.get(0);
			if(!el)
				return;

			el.focus();
			el.setSelectionRange(0, el.value.length);
		}

		var compareForSort = function(a, b) {
			if(typeof a == "number" && typeof b == "number")
				return (a > b ? 1 : (a < b ? -1 : 0));

			var compare = ("" + a).localeCompare(b);
			if(compare < 0)
				return -1;
			else if(compare > 0)
				return 1;
			else
				return 0;
		}

		var sortBy = function(arr, key) {
			arr.sort(function(a, b) {
				return compareForSort(a[key], b[key]);
			});
			return arr;
		}

		// Ask user to stay on page if there are unsaved modifications
		var _dontLeave = function(elm, options) {
			var $elm = $(elm); // usually a form
			var _hasChanged = false;
			var defaultOptions = {
				// You can either trigger modified() manually,
				// OR pass in a modificationsCheck() function that will be called
				// on specific intervals.
				modificationsCheck: null, // override me
				modificationsCheckInterval: 500,

				// Ignore the following period at the start of the page to allow
				// scripts to init the page without changing the state.
				delay: 1000,

				// You can either use the event names written below,
				// OR just straight up override them (kinda selfish ngl).
				onModified: function() {
					$elm.trigger('modified.dontleave.kuri', { hasChanged: _hasChanged });
				},
				onRevert: function() {
					$elm.trigger('revert.dontleave.kuri', { hasChanged: _hasChanged });
				},
				onToggle: function() {
					$elm.trigger('toggle.dontleave.kuri', { hasChanged: _hasChanged });
				},

				scrollToElement: false,
				whitelist: $(), // e.g the submit/save button
			};
			options = $.extend({}, defaultOptions, options);

			var stopUnload = function(e) {
				// This check is a bit scuffed but will do for now
				var $trigger = $(e.target.activeElement);
				if($trigger.is(options.whitelist)) {
					// Abort
					return;
				}

				if(hasChanged()) {
					if(options.scrollToElement)
						scrollTo($elm);

					// The text message is usually not configurable (depends on
					// the browser) to prevent abuse for scams and other things.
					return 'You might have unsaved changes';
				}
			}
			$(window).on('beforeunload', stopUnload);

			var hasChanged = function(state) {
				if(typeof state === 'undefined')
					return _hasChanged;

				var before = _hasChanged;
				_hasChanged = !!state;
				if(typeof options.onSet === 'function')
					options.onSet();
				if(_hasChanged && typeof options.onModified === 'function')
					options.onModified();
				if(!_hasChanged && typeof options.onRevert === 'function')
					options.onRevert();
				if(_hasChanged != before && typeof options.onToggle === 'function')
					options.onToggle();
			}

			var delayPassed = true;
			if(typeof options.delay === "number") {
				delayPassed = false;
				setTimeout(function() {
					delayPassed = true;
				}, options.delay);
			}

			var _modified = function() {
				if(!delayPassed)
					return;

				hasChanged(true);
			}
			var modified = debounce(_modified, 100);

			var revert = function() {
				hasChanged(false);
			}

			var modCheckInterval;
			var startCheckInterval = function() {
				modCheckInterval = setInterval(function() {
					hasChanged(options.modificationsCheck());
				}, options.modificationsCheckInterval);
				return modCheckInterval;
			}
			var clearCheckInterval = function() {
				return clearInterval(modCheckInterval);
			}
			if(typeof options.modificationsCheck === 'function') {
				startCheckInterval();
			}

			var destroy = function() {
				clearCheckInterval();
				$(window).off('beforeunload', stopUnload);
				$elm.off('.dontleave.kuri');
			}

			return {
				hasChanged,
				modified,
				revert,
				startCheckInterval,
				clearCheckInterval,
				destroy,
			}
		}
		var dontLeave = function() {
			return _dontLeave.apply(null, arguments);
		}

		// https://stackoverflow.com/a/5182103/7770384
		var removeClassStartingWith = function(elm, classStartList) {
			var $elm = elm;
			classStartList = classStartList.split(" ").map(function(item) {
				return escapeRegex(item);
			});
			var rx = new RegExp("(^|\\s)("+ classStartList.join("|") +")\\S+", "g");
			return $elm.removeClass(function(index, className) {
				return (className.match(rx) || []).join(" ");
			});
		}

		var isNumberKey = function(event) {
			var key = typeof event == "object" ? event.which || event.keyCode : parseInt(event);

			var isNumberKey = key >= 48 && key <= 57;
			// var isNumpadKey = key >= 96 && key <= 105

			return isNumberKey;
		}

		var clamp = function(num, low, high) {
			if(low > high) {
				var tmp = low;
				low = high;
				high = tmp;
			}

			if(num < low)
				num = low;
			if(num > high)
				num = high;

			return num;
		}

		return {
			fillDataString,
			fillDataClasses,
			clearDataClasses,
			gatherDataClassesValue,
			randomString,
			escapeRegex,
			debounce,
			getScrollParent,
			scrollTo,
			parentScrollTo,
			hScrollTo,
			parentHScrollTo,
			flashElement,
			scrollAndFlash,
			removeArrayElement,
			uniqueArrayElements,
			moveCursorToEnd,
			focusAndSelectText,
			compareForSort,
			sortBy,
			dontLeave,
			removeClassStartingWith,
			isNumberKey,
			clamp,
		};
	}();


	$.fn.extend({
		noEnterSubmit: function(options) {
			if( ! this.is("form") ) {
				console.log("Cannot prevent [Enter] key submission because the specified element is not a form element.", this);
				return this;
			}

			var defaultOptions = {
				triggerChange: true,
				inputSelector: null,
			}
			options = $.extend(defaultOptions, options);

			// Prevent submit when pressing [Enter]
			function preventEnterFromSubmitting(e) {
				if(e.keyCode == 13 && !e.isDefaultPrevented()) {
					e.preventDefault();
					Helpers.moveCursorToEnd(e.target);
					if(options.triggerChange) {
						$(e.target).trigger("change");
					}
					console.log("Prevented [Enter] key submission.", e.target);
				}
			}

			var ignoredTypes = [
				"hidden",
				"checkbox", "radio",
				"file", "image",
				"range",
				"button", "reset", "submit",
			];
			var selectors = ignoredTypes.map(function(item) {
				return ':not([type="'+ item +'"])';
			});
			var selector = options.inputSelector || "input"+ selectors.join("");

			this.on("keypress", selector, preventEnterFromSubmitting);
			return this;
		},
		onlyNumbers: function(options) {
			var $elm = this;

			var defaultOptions = {
				selector: null,
			}
			options = $.extend(defaultOptions, options);

			var handler = function(event) {
				var key = event.which || event.keyCode;
				return key <= 31 // less than 31 usually indicates a nav key like tab, arrow, etc
					|| Helpers.isNumberKey(event)
				;
			}

			if(!options.selector)
				this.on("keypress", handler);
			else
				this.on("keypress", options.selector, handler);

			return this;
		},

		// Credits: https://stackoverflow.com/a/25621277 (third option in the answer)
		// ... with some customizations
		textareaAutoHeight: function (options) {
			var defaultOptions = {
				bypassHeight: true,
				selector: null, // "textarea.auto-height"
				extraSpaceCounteraction: 0.5,
			}
			options = $.extend(defaultOptions, options);

			function _autoHeight(element) {
				if($(element).attr("wrap") == "off") {
					console.log("Wrapping is not allowed for this element by the use of [wrap=\"off\"] attribute", element);
					return $(element);
				}

				var $element = $(element),
					curHeight = $element.height(),
					boxSizing = $element.css("boxSizing"),
					fontSize = $element.css("fontSize"),
					lineHeight = $element.css("lineHeight"),
					// paddingTop = $element.css("paddingTop"),
					// paddingBottom = $element.css("paddingBottom"),
					resizable = ["vertical", "both"].indexOf($element.css("resize")) !== -1,
					targetHeight;

				// console.log(boxSizing, paddingTop, paddingBottom);

				if(!options.bypassHeight) {
					$element.css({ "height": "auto" }).addClass("auto-height-init");
					targetHeight = element.scrollHeight;
				} else {
					// Make it really small to force calculation of scrollHeight based
					// on the text itself, not on the rows attribute or other styles such
					// as min-height
					// NOTE bypassHeight: be careful using this option, as it sometimes
					// can mess up the window's (or any scroll parent's) scroll position
					// and reset it
					$element.removeAttr("rows").css({
						"height": 1,
					}).addClass("auto-height-init-bypass");

					var baseHeight = parseFloat(lineHeight);
					targetHeight = Math.max(element.scrollHeight, baseHeight);
				}
				// There seems to be a little extra space calculated by scrollHeight
				// so counteract it a little
				targetHeight -= parseFloat(fontSize) * options.extraSpaceCounteraction;

				/*if(boxSizing != "content-box") {
					targetHeight += parseFloat(paddingTop) + parseFloat(paddingBottom);
				}*/

				var finalHeight;
				if(!resizable) {
					finalHeight = targetHeight;
				} else {
					finalHeight = targetHeight > curHeight ? targetHeight : curHeight;
				}

				$element.height(finalHeight);
				if(finalHeight != curHeight) {
					$element.trigger("autoheight");
				}

				return $element;
			}

			// Event handler
			function autoHeightHandler(event) {
				return _autoHeight(event.target || this);
			}
			function handleAutoHeightParents(elm) {
				var $elm = $(elm),
					$target = $elm.closest(".tab-pane, .modal-dialog"),
					$collapseTarget = $elm.closest(".collapse");

				var handler = function(e) {
					$elm.trigger("input");
				}
				var collapseHandler = function(e) {
					// Have to check whether the event occurred on the element
					// itself, to guard from descendant collapse elements' event
					// bubbling up
					if(e.target != e.currentTarget)
						return;
					handler(e);
				}

				// If inside a tab pane, find the toggle first
				var id = $target.prop("id");
				if($target.is(".tab-pane") && id) {
					$target = $('.nav-link[href="#'+id+'"], .nav-link[data-target="#'+id+'"]');
				}

				if($target.length > 0) {
					// Using 'shown' events instead of 'show' because when 'show'
					// is triggered, the element is not visible yet, thus height
					// calculation will still miss. May be weird because 'shown'
					// waits for the transition to complete and the layout might
					// jerk out a bit, but that's just how it is
					$target.off("shown.bs.tab shown.bs.modal", handler)
							.on("shown.bs.tab shown.bs.modal", handler)
					;
				}
				if($collapseTarget.length > 0) {
					$collapseTarget.off("shown.bs.collapse", collapseHandler)
									.on("shown.bs.collapse", collapseHandler)
					;
				}
			}

			if(options.selector) {
				// Delegate to elements specified by the selector
				this.off("input", options.selector, autoHeightHandler);
				this.find(options.selector).each(function() {
					handleAutoHeightParents(this);
					_autoHeight(this);
				});
				return this.on("input", options.selector, autoHeightHandler);
			} else {
				// Attach to currently selected elements
				return this.each(function() {
					handleAutoHeightParents(this);
					_autoHeight(this).off("input", autoHeightHandler).on("input", autoHeightHandler);
				});
			}
		},

		textareaShowLength: function (options) {
			var defaultOptions = {
				position: "top right",
				maxLength: null,
				separator: "/",
				debounce: null,
				showOnFull: true,
				classOnFull: "is-full",
				progressOnFull: 1,
				showOnMid: false,
				classOnMid: "half-full",
				progressOnMid: 0.5,
				showFillProgress: true,
				fillProgressClass: function(length, max) {
					var progress = length / max;
					if(options.showOnMid && progress >= options.progressOnMid)
						return options.classOnMid;
					if(options.showOnFull && progress >= options.progressOnFull)
						return options.classOnFull;
					return "";
				},
				fillProgressClassPrefix: "tlen-progress--",
				hideOnEmpty: true,
			}
			options = $.extend(defaultOptions, options);

			return this.each(function(i, item) {
				var $item = $(item);
				var posClass = "textarea-length-"+ options.position.replace(" ", "-");
				var $container, $ta, $length;
				if( !$item.is(".textarea-length-container") && !$item.parent().is(".textarea-length-container") ) {
					// Init parent and sibling first
					$ta = $item;
					$container = $("<div>").addClass("textarea-length-container");
					$container.insertAfter($ta);
					$ta.appendTo($container);
				} else {
					// Find parent and sibling
					if($item.is(".textarea-length-container")) {
						$container = $item;
						$ta = $container.children("textarea").first();
					} else {
						$ta = $item;
						$container = $ta.parent();
					}
					$length = $container.children(".textarea-length").first();
				}
				if(!$length || $length.length == 0) {
					$length = $("<span>").addClass("textarea-length").insertAfter($ta);
				}
				var maxLength = options.maxLength === null
					? $ta.prop("maxLength") || $ta.data("maxLength") || $container.data("maxLength") || 0
					: parseInt(maxLength) || 0
				;
				var hasMaxLength = maxLength > -1;
				var separator = ""+ options.separator;

				var fillProgressClass;
				if(typeof options.fillProgressClass == "function") {
					fillProgressClass = options.fillProgressClass.bind($length[0]);
				} else {
					fillProgressClass = function() { return options.fillProgressClass; };
				}

				$container.addClass(posClass);

				var _updateLength = function() {
					var value = ""+ $ta.val();
					_updateLengthElement(value.length);
				}
				var _updateLengthElement = function(length) {
					length = parseInt(length);
					if(options.hideOnEmpty && length == 0) {
						$length.addClass("d-none").empty();
						return;
					}

					var lengthText = hasMaxLength ? `${length}${separator}${maxLength}` : (""+length);
					$length.removeClass("d-none").html(lengthText);

					if(options.showFillProgress && options.fillProgressClass) {
						var classes = ""+ fillProgressClass(length, maxLength);

						var prefix = options.fillProgressClassPrefix;
						classes = classes.split(" ").filter(x => !!x);
						if(prefix) {
							Helpers.removeClassStartingWith($length, prefix);
							classes = classes.map(function(v) {
								return prefix + v;
							});
						}
						$length.addClass(classes.join());
					}
				}
				var updateLength;
				if(options.debounce !== false) {
					updateLength = Helpers.debounce(_updateLength, parseInt(options.debounce) || 100);
				} else {
					updateLength = _updateLength;
				}

				$ta.on("input", updateLength);
				updateLength();
			});
		},

		readMore: function(options) {
			var defaultOptions = {
				maxLines: 3,
				expandText: "Read more",
				collapseText: "Read less",
				expandedClass: "expanded",
				collapsedClass: "collapsed",
				handleBaseClass: "read-more-handle",
				handleClass: "",
				// extraSpace is in px, mainly used to give extra breathing space
				// for the underparts of letters like lowercase g or p, which cuts
				// below the baseline. This space is also to ensure that those letters
				// don't render/clip into the next element. For 16px (1rem), this was 3px
				extraSpace: function(item) {
					var $item = $(item);
					var fontSize = parseFloat($item.css("fontSize"));
					return fontSize / 16 * 3;
				},
				centeredHandle: false,
				autoScroll: true,
				indicatorLine: true,
			};
			options = $.extend({}, defaultOptions, options);
			var $items = $(this);

			var handleClass = options.handleBaseClass +" "+ options.handleClass;
			// Use <a> instead of <span> so it can be focused
			var $handleTemplate = $("<a>").prop("href", "#").addClass(handleClass);
			if(options.centeredHandle) {
				$handleTemplate.addClass("centered");
			}
			var autoScrollOptions = $.extend({
				animate: true,
			}, typeof options.autoScroll === "object" ? options.autoScroll : {});
			$handleTemplate.on("click", function(e) {
				e.preventDefault();

				var $handle = $(this);
				var $item = $handle.closest(".read-more-wrapper");
				var expanded = isExpanded($item);
				updateState($item, !expanded);
				$item.focus();
				if(/*!expanded && */options.autoScroll) {
					Helpers.scrollTo($item, autoScrollOptions);
				}
			});

			var getMaxLines = function(item) {
				var $item = $(item);
				return parseInt($item.data("maxLines") || options.maxLines);
			}

			var isOverflowing = function(element, height) {
				if(element instanceof $) element = element[0];
				if(height)
					return element.scrollHeight > height;
				else
					return element.scrollHeight > element.offsetHeight;
			}

			var isExpanded = function(item) {
				var $item = $(item);
				return $item.is("."+ options.expandedClass);
			}

			var isStandby = function(item) {
				var $item = $(item);
				return !$item.is("."+ options.expandedClass) && !$item.is("."+ options.collapsedClass);
			}

			var getHandle = function(item) {
				var $item = $(item);
				var $handle = $item.find("."+ options.handleBaseClass);
				if($handle.length == 0) {
					$handle = $handleTemplate.clone(true).appendTo($item);
				}

				return $handle;
			}

			var initItem = function(item) {
				var $item = $(item);
				$item.addClass("read-more-wrapper");
				if(options.indicatorLine) {
					$item.addClass("with-indicator");
				}

				checkState(item);
			}

			var standbyItem = function(item) {
				var $item = $(item);
				var $handle = getHandle(item);

				$handle.addClass("hidden");
				$item.css("maxHeight", "");
				$item.removeClass(options.expandedClass);
				$item.removeClass(options.collapsedClass);
			}

			var checkState = function(item) {
				var $item = $(item);
				var $handle = getHandle(item);
				var maxHeight = calculateMaxHeight(item);
				if(isOverflowing(item, maxHeight)) {
					$handle.removeClass("hidden");
					if(isStandby(item)) {
						// Init item
						updateState(item, false);
					} else {
						// Refresh item state
						updateState(item, isExpanded(item));
					}
				} else {
					standbyItem(item);
				}
			}

			var calculateMaxHeight = function(item) {
				var $item = $(item);

				var boxSizing = $item.css("boxSizing");
				var paddingTop = parseFloat($item.css("paddingTop"));
				var paddingBottom = parseFloat($item.css("paddingBottom"));
				var borderTop = parseFloat($item.css("borderTopWidth"));
				var borderBottom = parseFloat($item.css("borderBottomWidth"));
				var lineHeight = parseFloat($item.css("lineHeight"));
				var extraSpace = parseFloat(typeof options.extraSpace === "function" ? options.extraSpace(item) : options.extraSpace);

				var maxHeight = lineHeight * getMaxLines(item);
				if(boxSizing == "border-box") {
					maxHeight += paddingTop + paddingBottom + borderTop + borderBottom;
				}
				maxHeight += extraSpace;

				return maxHeight;
			}

			var updateState = function(item, state) {
				// state = true means to expand it

				var $item = $(item);
				var $handle = getHandle(item);

				$item.toggleClass(options.expandedClass, state);
				$item.toggleClass(options.collapsedClass, !state);
				$handle.html(!state ? options.expandText : options.collapseText);

				if(state) {
					// to expand
					$item.css("maxHeight", "");
				} else {
					// to collapse
					// Calculate the proper height
					var targetHeight = calculateMaxHeight(item);
					$item.css("maxHeight", targetHeight);
				}
			}

			var _resizeHandler = function() {
				// $items.trigger("change");
				$items.each(function(i, item) {
					checkState(item);
				});
			}
			var resizeHandler = Helpers.debounce(_resizeHandler, 100, false);

			$(window).on("resize", function(e) {
				resizeHandler();
			});
			return $items.each(function(i, item) {
				initItem(item);

				var debouncedCheckState = Helpers.debounce(checkState, 100, false);
				$(item).on("change", function(e) {
					debouncedCheckState(item);
				});
			});
		},

		// Hides Bootstrap's .invalid-feedback upon input change, so as to not
		// persist the message
		autoHideFeedback: function(options) {
			var defaultOptions = {
				inputSelector: "input:not[type=hidden], textarea, select, .form-control",
				feedbackSelector: ".invalid-feedback",
				triggerEvent: "change",
				// Instead of finding the feedbacks one by one, delegate an event
				// on the wrapper element
				delegate: false,
			};
			options = $.extend(defaultOptions, options);

			var toggleElement = function(elm, show) {
				$(elm).toggleClass("d-none", !show);
			}
			var hideElement = function(elm) {
				return toggleElement(elm, false);
			}
			var showElement = function(elm) {
				return toggleElement(elm, true);
			}

			this.each(function(index, form) {
				if(!options.delegate) {
					var $feedbacks = $(form).find(options.feedbackSelector);
					$feedbacks.each(function(i, item) {
						var $item = $(item);
						var $relatedInput = $item.prevAll(options.inputSelector);
						if($relatedInput.length == 0) {
							// Try to guess if it's nested somewhere
							var siblings = $item.prevAll();
							siblings.each(function(s, sibling) {
								$relatedInput = $(sibling).find(options.inputSelector);
								if($relatedInput.length > 0) {
									return false;
								}
							});
						}

						// Skip item if input was not found
						if($relatedInput.length == 0) {
							return;
						}

						// The event
						$relatedInput.on(options.triggerEvent, function(e) {
							hideElement($feedback);
						});
					});
				} else {
					// Delegate event instead
					$(form).on(options.triggerEvent, options.inputSelector, function(e) {
						// Try to find the feedback element
						var $feedback = $(this).nextAll(options.feedbackSelector);
						if($feedback.length == 0) {
							$feedback = $(this).parent().find(options.feedbackSelector);
						}
						if($feedback.length == 0) {
							return;
						}

						hideElement($feedback);
					});
				}
			});
		},
	});


	// Flash an element
	$(document).on("click", ".btn-flash-elm", function(e) {
		var target = $(this).data("flashTarget"),
			$target = $(target).first(),
			scrollOptions = $(this).data("scrollOptions") || {},
			flashOptions = $(this).data("flashOptions") || {}
		;

		// Scroll to element and flash it
		if($target.length) {
			e.preventDefault();
			scrollOptions = $.extend({
				animate: true,
			}, scrollOptions);
			Helpers.scrollAndFlash($target, scrollOptions, flashOptions);
		}
	});

	$(document).on("show.bs.collapse", ".collapse-scrollto", function(e) {
		// Make sure the event is on itself, not on any descendants
		if(e.target != e.currentTarget)
			return;

		var scrollOffset = $(this).data("scrollOffset");
		var options = $.extend({
			animate: true,
			offset: 50,
		}, {
			offset: scrollOffset,
		});

		// Need to defer because during "show" event the element is not visible yet,
		// so it doesn't have a scroll offset. To scroll we need to do calculations
		// right after the element is set to be visible.
		setTimeout(function() {
			Helpers.parentScrollTo(e.target, options);
		}, 10);
	});

	$(document).on("change", "select[multiple].compile-values", function(e) {
		var target = $(this).data("compileTo"),
				$target = $(target)
		;
		if(target && $target.length > 0) {
			var compiled = $(this).val().filter(x => !!(String(x).trim())).join(",");
			$target.val(compiled);
		}
	});

}