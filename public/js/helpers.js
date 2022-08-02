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

			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$("html, body").animate({
					scrollTop: scrollTo
				}, animateOptions);
			} else {
				$("html, body").scrollTop(scrollTo);
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
				$parent;

			if($parentContext instanceof jQuery)
				$parent = $parentContext;
			else if($parentContext instanceof Element || typeof $parentContext === "string")
				$parent = $($parentContext);
			else
				$parent = $element.parent();

			var scrollTo = $parent.scrollTop() + ($element.offset().top - $parent.offset().top) - offset;

			if(options.percentageOffset) {
				var percentOffset = options.percentageOffset;
				if(typeof percentOffset == "string")
					percentOffset = parseFloat(percentOffset) / 100; // ignore the percent sign
				scrollTo -= $parent.height() * percentOffset;
			}

			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$parent.animate({
					scrollTop: scrollTo
				}, animateOptions);
			} else {
				$parent.scrollTop(scrollTo);
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

			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$("html, body").animate({
					scrollTop: scrollTo
				}, animateOptions);
			} else {
				$("html, body").scrollTop(scrollTo);
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
				$parent;

			if($parentContext instanceof jQuery)
				$parent = $parentContext;
			else if($parentContext instanceof Element || typeof $parentContext === "string")
				$parent = $($parentContext);
			else
				$parent = $element.parent();

			var scrollTo = $parent.scrollLeft() + ($element.offset().left - $parent.offset().left) - offset;

			if(options.percentageOffset) {
				var percentOffset = options.percentageOffset;
				if(typeof percentOffset == "string")
					percentOffset = parseFloat(percentOffset) / 100; // ignore the percent sign
				scrollTo -= $parent.height() * percentOffset;
			}

			if(options.animate) {
				var animateOptions = $.extend({ duration: options.duration }, options.animateOptions);
				$parent.animate({
					scrollLeft: scrollTo
				}, animateOptions);
			} else {
				$parent.scrollLeft(scrollTo);
			}
		};

		var flashElement = function(element) {
			var $elm = $(element);
			$elm.removeClass("flash-element").addClass("flash-element");
			setTimeout(function() {
				$elm.removeClass("flash-element");
			}, 3000);
		};

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
			scrollTo,
			parentScrollTo,
			hScrollTo,
			parentHScrollTo,
			flashElement,
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
				if(e.keyCode == 13) {
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
				delegate: false,
				bypassHeight: true,
				delegateTo: "textarea.auto-height",
				extraSpaceCounteraction: 0.5,
			}
			options = $.extend(defaultOptions, options);

			function _autoHeight(element) {
				if($(element).attr("wrap") == "off") {
					console.log("Wrapping is not allowed for this element by the use of [wrap=\"off\"] attribute", element);
					return $(element);
				}

				var $element = $(element),
					boxSizing = $element.css("boxSizing"),
					fontSize = $element.css("fontSize"),
					lineHeight = $element.css("lineHeight"),
					// paddingTop = $element.css("paddingTop"),
					// paddingBottom = $element.css("paddingBottom"),
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

				$element.height(targetHeight);
				$element.trigger("autoheight");

				return $element;
			}

			// Event handler
			function autoHeightHandler(event) {
				return _autoHeight(event.target || this);
			}

			if(options.delegate) {
				this.off("input", options.delegateTo, autoHeightHandler);
				this.find(options.delegateTo).each(function() {
					_autoHeight(this);
				});
				return this.on("input", options.delegateTo, autoHeightHandler);
			} else {
				return this.each(function() {
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
				var separator = ""+ options.separator;

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

					$length.removeClass("d-none").html(`${length}${separator}${maxLength}`);

					if(options.showFillProgress && options.fillProgressClass) {
						var classes = ""+ options.fillProgressClass(length, maxLength);

						var prefix = options.fillProgressClassPrefix;
						classes = classes.split(" ");
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
}