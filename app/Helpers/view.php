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
	return sprintf('<span class="badge badge-%s"%s>%s</span>', $style, $attrs, $text);
}

function description_text($text) {
	return nl2br(e(trim($text)), false);
}

function old_conditional($key, $valueToCheck = NULL, $compareTo = NULL, $default = NULL, $output = '') {
	$old = old($key, $valueToCheck);
	if($old !== NULL) {
		if($compareTo !== NULL) {
			if($compareTo == $old) {
				$out = TRUE;
			} else {
				$out = FALSE;
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
	return old_conditional($key, $valueToCheck, $compareTo, $default, $output);
}

function old_selected($key, $valueToCheck = NULL, $compareTo = NULL, $default = NULL, $output = ' selected="selected"') {
	return old_conditional($key, $valueToCheck, $compareTo, $default, $output);
}

function muted_text($text, $with_markup = true, $with_class = true) {
	$markup = '<em $classes>%s</em>';
	if(is_string($with_markup))
		$markup = $with_markup;
	$class = ' class="text-muted"';
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

function value_or_empty($value, $replacement = null) {
	$additional_args = array_slice(func_get_args(), 2);
	if($replacement === null) {
		$replacement = call_user_func_array('empty_text', $additional_args);
	}
	return !!$value || in_array($value, [0, '0'], true) ? e($value) : $replacement;
}

function value_or_none($value, $replacement = null) {
	$additional_args = array_slice(func_get_args(), 2);
	if($replacement === null) {
		$replacement = call_user_func_array('none_text', $additional_args);
	}
	return !!$value || in_array($value, [0, '0'], true) ? e($value) : $replacement;
}

// Alias to value_or_empty()
function voe($value) {
	$args = array_slice(func_get_args(), 1);
	return call_user_func_array('value_or_empty', array_merge([$value, null], $args));
}
// Alias to value_or_none()
function von($value) {
	$args = array_slice(func_get_args(), 1);
	return call_user_func_array('value_or_none', array_merge([$value, null], $args));
}
// Alias to value_or_empty(), with dash as replacement instead of text
function vo_($value) {
	return call_user_func_array('value_or_empty', [$value, '<span>&ndash;</span>']);
}