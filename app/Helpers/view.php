<?php

/**
 * In helper files, it is intentional to omit the common function_exists() check
 * so that when we create any function name which already exists, we would know
 * right away.
 */

function tab_title($title = '') {
	$appname = config('app.name');
	$title = trim($title);
	if($title) {
		$title = $title . ' - ' . $appname;
	} else {
		$title = $appname;
	}
	return $title;
}

function generate_options($arr, $selected_values = '', $value_key = 'id', $text_key = 'name') {
	$html = array();
	$selected_values = array_map('strval', (array) $selected_values);
	foreach($arr as $i => $item) {
		if(is_object($item)) {
			$value = $item->$value_key;
			$text = $item->$text_key;
		} elseif(is_array($item)) {
			$value = $item[$value_key];
			$text = $item[$text_key];
		} else {
			$value = $text = $item;
		}
		$value = (string) $value;
		$selected = in_array($value, $selected_values) ? 'selected="selected"' : '';
		$html[] = sprintf('<option value="%s" %s>%s</option>', htmlspecialchars($value), $selected, htmlspecialchars($text));
	}
	return implode("\n", $html);
}

function generate_ancestors($str, $delimiter = '.', $trailing_delimiter = FALSE) {
	$ancestors = array();

	if(strlen($str)) {
		$str = preg_replace('/'.preg_quote($delimiter, '/').'+/i', $delimiter, $str);
		$parts = explode($delimiter, $str);
		$cumulated = '';
		foreach($parts as $i => $part) {
			if(!$part && $i > 0) {
				// Ignore empty parts if it's not the first item.
				continue;
			}

			$cumulated .= $part;
			$ancestors[] = $cumulated . ($trailing_delimiter ? $delimiter : '');

			$cumulated .= $delimiter;
		}
		if($ancestors) {
			// Remove the last element, which is the final descendant.
			array_pop($ancestors);
		}
	}

	return $ancestors;
}

function status_badge($text, $style, $attrs = '') {
	if($attrs)
		$attrs = ' '.$attrs;
	return sprintf('<span class="badge cursor-default badge-%s"%s>%s</span>', $style, $attrs, $text);
}

function badge_number($value, $max = 99, $no_zero = true) {
	$num = intval($value);
	if($num > $max)
		$num = $max.'+';
	elseif($num == 0 && $no_zero)
		$num = '';
	return $num;
}

function description_text($text) {
	return nl2br(e(trim($text)), false);
}

function old_conditional($key, $valueToCheck = NULL, $compareTo = NULL, $default = NULL, $output = '') {
	$old = old($key, $valueToCheck);
	if($old !== NULL) {
		if($compareTo !== NULL) {
			if(is_array($old)) {
				$out = in_array($compareTo, $old);
			} else {
				$out = $compareTo == $old;
			}
		} else {
			$out = TRUE;
		}

		if($out) {
			return $output;
		}
	}

	return $default;
}

function old_checked($key, $valueToCheck = NULL, $compareTo = 1, $default = NULL, $output = ' checked="checked"') {
	// More often, unchecked checkboxes won't even be present in the old input
	// (since it wasn't submitted in the first place), so nullify default old()
	// value if other input exists but this specific key doesn't.
	if(old_input_exists() && old($key) === NULL) {
		$valueToCheck = NULL;
	}
	return old_conditional($key, $valueToCheck, $compareTo, $default, $output);
}

function old_selected($key, $valueToCheck = NULL, $compareTo = NULL, $default = NULL, $output = ' selected="selected"') {
	return old_conditional($key, $valueToCheck, $compareTo, $default, $output);
}

function muted_text($text, $with_markup = true, $with_class = true) {
	$markup = '<em $classes>%s</em>';
	if(is_string($with_markup))
		$markup = $with_markup;
	$class = ' class="text-muted cursor-default"';
	if(is_string($with_class))
		$class = $with_class;

	if(!!$with_markup) {
		$markup = str_replace('$classes', !!$with_class ? $class : '', $markup);
		$text = sprintf($markup, $text);
	}
	return $text;
}

function empty_text() {
	return call_user_func_array('muted_text', array_merge(['('.__('common.empty').')'], func_get_args()));
}
function none_text() {
	return call_user_func_array('muted_text', array_merge(['('.__('common.none').')'], func_get_args()));
}

function value_or_empty($value, $replacement = null, $escape = true) {
	$additional_args = array_slice(func_get_args(), 3);
	if(is_string($value))
		$value = trim($value);
	if($replacement === null) {
		$replacement = call_user_func_array('empty_text', $additional_args);
	}
	return !!$value || in_array($value, [0, '0'], true) ? ( $escape ? e($value) : $value ) : $replacement;
}

function value_or_none($value, $replacement = null, $escape = true) {
	$additional_args = array_slice(func_get_args(), 3);
	if(is_string($value))
		$value = trim($value);
	if($replacement === null) {
		$replacement = call_user_func_array('none_text', $additional_args);
	}
	return !!$value || in_array($value, [0, '0'], true) ? ( $escape ? e($value) : $value ) : $replacement;
}

// Alias to value_or_empty()
function voe($value = null) {
	$args = array_slice(func_get_args(), 1);
	return call_user_func_array('value_or_empty', array_merge([$value, null], $args));
}
// Alias to value_or_none()
function von($value = null) {
	$args = array_slice(func_get_args(), 1);
	return call_user_func_array('value_or_none', array_merge([$value, null], $args));
}
// Alias to value_or_empty(), with dash as replacement instead of text
function vo_($value = null) {
	$args = array_slice(func_get_args(), 1);
	return call_user_func_array('value_or_empty', array_merge([$value, '<span>&ndash;</span>'], $args));
}

function html_attributes($attributes, $escape = false) {
	$result = implode(' ', array_map(function($k, $v) use ($escape) {
		return sprintf('%s="%s"', $k, $escape ? e($v) : $v);
	}, array_keys($attributes), $attributes));
	return $result;
}

function pretty_username($user, $is_an_owner = false) {
	$atts = [];
	$atts['class'] = ['username'];
	if($is_an_owner) {
		$atts['class'][] = 'is-owner';
	}

	$color = null;
	if( !($user instanceof \App\User) ) {
		$name = $user;
	} else {
		$name = $user->name;
		if($user->is_system) {
			$color = 'text-purple';
		}
	}

	if($color) {
		$atts['class'][] = $color;
	}

	$atts['class'] = implode(' ', $atts['class']);
	$text = sprintf('<span %s>%s</span>', html_attributes($atts), $name);
	return $text;
}

function lang_or_raw($value, $prefix = '') {
	$key = $prefix.$value;
	$additional_params = array_slice(func_get_args(), 2);
	$params = array_merge([$key], $additional_params);
	return \Lang::has($key) ? call_user_func_array(['\\Lang', 'get'], $params) : $value;
}

function active_menu_by_route($route, $exact = false) {
	if($route == null)
		return false;

	$current = Route::currentRouteName();
	$route = (array) $route;
	if($exact) {
		foreach($route as $r) {
			if($r == $current)
				return true;
		}
	} else {
		foreach($route as $r) {
			if(Str::startsWith($current, $r))
				return true;
		}
	}

	return false;
}

function menu_active($state) {
	return $state ? 'active' : '';
}
