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
	if(count($args) == 1 && is_array($args[0]))
		$args = $args[0];

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
