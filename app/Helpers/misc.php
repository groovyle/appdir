<?php

function sysuser_automator() {
	return \App\Models\SystemUsers\Automator::instance();
}

function elocollect() {
	return new \Illuminate\Database\Eloquent\Collection(...func_get_args());
}

// Checks whether an old input exists (e.g after validation fails).
// Only works if the submitted input has a csrf_field()
function old_input_exists() {
	return old('_token') !== NULL;
}

// Simplifies both integer parameters so that it returns:
// * -1 if $a < $b;
// * 0 if $a == $b;
// * 1 if $a > $b;
// Primarily used for array custom sort functions
function simplecmp($a, $b) {
	return $a < $b ? -1 : ($a > $b ? 1 : 0);
}

function dump_db($die = TRUE) {
	$fn = $die ? 'dd' : 'dump';
	$fn(DB::getQueryLog());
}

function get_youtube_id_from_url($url) {
	// Form 1: https://[www.]youtube.com/watch?v=video_id
	// Form 2: https://youtu.be/video_id
	// Form 3: https://www.youtube.com/embed/video_id
	// Form 4: video_id

	// https://webapps.stackexchange.com/questions/54443/format-for-id-of-youtube-video
	// Video ID only?
	$bare_pattern = '/^[A-Za-z0-9_-]$/';
	if(preg_match($bare_pattern, $url) === 1) {
		return $url;
	}

	// https://stackoverflow.com/questions/3452546/how-do-i-get-the-youtube-video-id-from-a-url
	// Full URL maybe
	$pattern = '/(?:[?&]v=|\/embed\/|\/1\/|\/v\/|https:\/\/(?:www\.)?youtu\.be\/)([^&\n?# ]+)/';
	if(preg_match($pattern, $url, $matches)) {
		return $matches[1];
	} else {
		return null;
	}
}

function get_youtube_url($id, $short = true) {
	return $short
		? 'https://youtu.be/'.$id
		: 'https://www.youtube-nocookie.com/watch?v='.$id
	;
}

function notnull() {
	$args = func_get_args();
	foreach($args as $value) {
		if($value)
			return $value;
	}
	return null;
}

function settings($key, ...$values) {
	$args = func_get_args();

	return notnull(App\Settings::get($key), ...$values);
}

function settings_set() {
	$args = func_get_args();
	return call_user_func_array([App\Settings::class, 'set'], $args);
}


function model_uses_soft_deletes($model) {
	return in_array(Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model));
}

function get_filters(array $only = null, array $defaults = []) {
	$queries = collect(request()->query());
	if($only)
		$queries = $queries->only($only);
	$filters = array_merge($defaults, $queries->all());

	// Need to turn null values into '' so that it's buildable with http_build_query()
	// NOTE: null values gets ignored by http_build_query, which gets troublesome
	// if you have default values
	foreach($filters as $k => $v) {
		if($v === null)
			$filters[$k] = '';
	}

	return $filters;
}

function get_count_from_list_query($query, $count_column = 'id', $without = null, $callback = null) {
	$without = $without === null ? ['columns', 'groups', 'orders'] : (array) $without;
	$query = $query->getQuery()->cloneWithout($without);
	if($callback) {
		$callback($query);
	}
	$count = $query->selectRaw('count(distinct '.$count_column.') as count_col')->value('count_col');
	return $count;
}

function find_item_offset_from_list_query($query, $id) {
	$offset_query = (clone $query)->applyScopes();
	$orders = $offset_query->getQuery()->orders;
	$orders_bindings = $offset_query->getQuery()->bindings['order'];
	$item = $query->getModel()->find($id);
	$item_keyname = $query->getModel()->getKeyName();
	$item_key = $id;
	$binding_i = 0;
	if($orders && $item) {
		$orders_to_apply = [];
		foreach($orders as $order) {
			if(($order['type'] ?? null) == 'Raw') {
				// NOTE: opt to not support this, because there's no way to know
				// what column or derived value is being sorted from here.
				// Skipping is not an option because then the offset would be
				// inaccurate
				throw new \UnexpectedValueException('Finding offset from a list query does not support Raw order clauses, because there\'s no way to extract the expression and put it into the WHERE clause.');
				return;

				// Kinda tricky...
				// Attempt to get order
				$tmp = explode(' ', strrev($order['sql']), 2);
				$order = strrev($tmp[0]);
				if(in_array($order, ['asc', 'desc'])) {
					$sql = strrev($tmp[1]);
				} else {
					$sql = $order;
					$order = 'asc';
				}

				$binding_count = substr_count($sql, '?');
				$bindings = array_slice($orders_bindings, $binding_i, $binding_count);
				$offset_query->whereRaw($sql, $bindings);
				$binding_i += $binding_count;
			} elseif( array_key_exists($order['column'], $item->getAttributes())
				|| isset($item->{$order['column']}) ) {
				if($item->{$order['column']} !== null) {
					$orders_to_apply[] = function($query, $last) use($order, $item) {
						$query->where(function($query) use($order, $item, $last) {
							$query->where(
								$order['column'],
								($order['direction'] == 'asc' ? '<' : '>').(!$last ? '=' : ''),
								$item->{$order['column']}
							);
							if($last && $order['direction'] == 'asc') {
								// NULL values are always considered less, so
								// we also need to account for that when ascending
								$query->orWhereNull($order['column']);
							}
						});
					};
				} else {
					/**
					 * Special case when the ordered thingy is null.
					 * I think when ordering in db, NULL string values are considered
					 * even less than ''.
					 * Thus, we can think of it like this: NULL = 0, non-NULL = 1.
					 * When order is ASC, it means earlier items are also NULL,
					 * and later items are non-NULL. Vice-versa when order is DESC.
					 */
					$orders_to_apply[] = function($query, $last) use($order, $item_keyname, $item_key) {
						$query->where(function($query) use($order, $item_keyname, $item_key, $last) {
							$fn = $order['direction'] == 'asc' ? 'whereNull' : 'whereNotNull';
							$query->$fn($order['column']);
							$query->where(
								$item_keyname,
								($order['direction'] == 'asc' ? '<' : '>').(!$last ? '=' : ''),
								$item_key
							);
						});
					};
				}
			} else {
				// Column not found
				throw new \UnexpectedValueException('Order column `'.$order['column'].'` not found. Make sure the column exists by default and is not a derived column (columns in SELECT clauses do not work in WHERE clauses).');
			}
		}

		// Apply gradual filters
		for($i = 0; $i < count($orders_to_apply); $i++) {
			$offset_query->orWhere(function($query) use($orders_to_apply, $i) {
				for($j = 0; $j <= $i; $j++) {
					$orders_to_apply[$j]($query, $j == $i);
				}
			});
		}
		$offset_query->orWhere($item_keyname, $item_key);

		$offset = $offset_query->count();
	} else {
		$offset = $offset_query->where($item_keyname, '<=', $item_key)->count();
	}

	return $offset;
}

function self_redirect_url($query_except = [], $data = []) {
	return make_url_query(
		null,
		url_query_except($query_except, $data)
	);
}

function self_redirect($query_except = [], $data = []) {
	return redirect(self_redirect_url($query_except, $data));
}

// A collection of headers to prevent caching
// https://stackoverflow.com/a/1907705
function no_cache_headers($response) {
	$response
		->header('Cache-Control', 'no-store, no-cache, must-revalidate', true)
		->header('Pragma', 'no-cache', true)
		->header('Date', 'Sat, 26 Jul 1997 05:00:00 GMT', true)
		->header('Expires', '0', true)
	;
	return $response;
}


// NOTE: per the docs, a value of 0 means unlimited
function ini_max_post_size() {
	static $max_post_size = NULL;
	if(!is_callable('ini_get')) {
		// default guess in case ini_get is unavailable
		$max_post_size = '10M';
	} elseif($max_post_size === NULL) {
		$max_upload_size = ini_max_upload_size();
		$max_upload_num = ini_max_file_uploads();
		if($max_upload_size && $max_upload_num) {
			$max_post_size = ($max_upload_size * $max_upload_num);
		}

		$config_post_max_size = size_to_bytes(ini_get('post_max_size'));
		if($config_post_max_size > 0) {
			$max_post_size = min($max_post_size, $config_post_max_size);
		}
	}
	return $max_post_size;
}

function ini_max_upload_size() {
	static $max_upload_size = NULL;
	if(!is_callable('ini_get')) {
		// default guess in case ini_get is unavailable
		$max_upload_size = '2M';
	} elseif($max_upload_size === NULL) {
		$config_max_upload_size = size_to_bytes(ini_get('upload_max_filesize'));
		$config_post_max_size = size_to_bytes(ini_get('post_max_size'));

		// Figure out max upload size
		if($config_post_max_size > 0) {
			$max_upload_size = min($max_upload_size, $config_post_max_size);
		}
		if($config_max_upload_size > 0) {
			$max_upload_size = min($max_upload_size, $config_max_upload_size);
		}
	}
	return $max_upload_size;
}

function ini_max_file_uploads() {
	static $max_upload_num = NULL;
	if(!is_callable('ini_get')) {
		// default guess in case ini_get is unavailable
		$max_upload_num = 5;
	} elseif($max_upload_num === NULL) {
		$config_max_upload_num = (int) ini_get('max_file_uploads');

		$max_upload_num = min($max_upload_num, $config_max_upload_num);
	}
	return $max_upload_num;
}
