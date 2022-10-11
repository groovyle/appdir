<?php

/**
 * In helper files, it is intentional to omit the common function_exists() check
 * so that when we create any function name which already exists, we would know
 * right away.
 */

// Determine if two lists have the same elements regardless of order
// Only works on simple lists, i.e non-dimensional arrays
function array_same_elements(array $a, array $b) {
	return count($a) == count($b) && count(array_diff($a, $b)) == 0;
}

function query_in_bindstring($arr) {
	if($arr instanceof \Illuminate\Contracts\Support\Arrayable)
		$arr = $arr->toArray();
	return '('.implode(', ', array_fill(0, count((array) $arr), '?')).')';
}

// Useful for sorting search results, sorts by how early the keyword is found
function sort_strpos($collection, $keyword = '', $keys = [], $case_sensitive = false) {
	if($keyword == '' || !$keys)
		return $collection;

	$fn = $case_sensitive ? 'strpos' : 'stripos';
	$collection = collect($collection);
	$collection = $collection->sort(function($a, $b) use ($fn, $keyword, $keys) {
		foreach($keys as $key) {
			$pos1 = call_user_func_array($fn, [$a->$key, $keyword]);
			$pos2 = call_user_func_array($fn, [$b->$key, $keyword]);
			if($pos1 != $pos2) {
				return $pos1 <=> $pos2;
			}
		}

		return 0;
	});
	return $collection;
}