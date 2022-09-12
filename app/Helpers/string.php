<?php

/**
 * In helper files, it is intentional to omit the common function_exists() check
 * so that when we create any function name which already exists, we would know
 * right away.
 */

function nnl($str) {
	// -- Normalize New Line -- //
	/**
	 * Normalize newlines so that it's always \n instead of sometimes \r\n.
	 * Normalizing is needed so that a newline only count as 1 character for validation,
	 * where if it's \r\n it counts as 2 characters for mb_strlen(). This way
	 * newlines would count only as 1 character instead of 2.
	 * Do this for any textarea input data so that size rules like `max` works correctly.
	 */
	return str_replace(["\r\n", "\r"/*, "\n"*/], "\n", $str);
}

function request_replace_nl($request) {
	$all = $request->all();

	foreach(\Arr::dot($all) as $key => $value) {
		if(is_string($value) && strpos($value, "\r") !== false) {
			data_set($all, $key, nnl($value));
		}
	}

	$request->replace($all);
}

function escape_mysql_like_str($string) {
	return addcslashes($string, '\\_%');
}

function app_name() {
	return config('app.name');
}

function app_nick() {
	return config('app.short_name', app_name());
}

function make_title($title = '', $app_title = NULL) {
	$title = trim($title);
	if(!$app_title)
		$app_title = config('app.title', app_nick());

	$title_format = '%s | %s';
	return $title ? sprintf($title_format, $title, $app_title) : $app_title;
}

// https://www.php.net/manual/en/function.parse-url.php#106731
/**
 * Re-assembles the result from parsed_url() function into a string.
 *
 * @param array $parsed_url
 *
 * @return string
 */
function unparse_url($parsed_url) {
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
	return "$scheme$user$pass$host$port$path$query$fragment";
}

/**
 * Automatically adds a scheme if the passed URL does not include a scheme.
 *
 * @param string $url The URL to normalize.
 * @param array $allow A list of allowed schemes. Pass NULL to allow all schemes,
 * e.g ftp or ssh. Otherwise, the first element in the array will be used as the
 * normalization scheme.
 *
 * @return string
 */
function url_auto_scheme($url, $allow = ['http', 'https']) {
	$parsed = parse_url($url);
	if($parsed === FALSE) {
		// Not a valid URL
		return FALSE;
	}

	// Normalize scheme
	if(isset($parsed['scheme'])) {
		if(empty($allow)) {
			// Allow all schemes.
		} else {
			$allow = (array) $allow;
			if(!in_array($parsed['scheme'], $allow)) {
				unset($parsed['scheme']);
			}
		}
	}
	if(!isset($parsed['scheme'])) {
		$parsed['scheme'] = $allow[0];
	}

	return unparse_url($parsed);
}

// https://stackoverflow.com/a/42028380
/**
 * Escape special characters for a LIKE query.
 *
 * @param string $value
 * @param string $char
 *
 * @return string
 */
function db_escape_like(string $value, string $char = '\\'): string
{
	return str_replace(
		[$char, '%', '_'],
		[$char.$char, $char.'%', $char.'_'],
		$value
	);
}


/**
 * Generate a random string with specifiable keyspace/pool.
 *
 * @param int $length The length of the result random string.
 * @param string|array $keyspace The keyspace to use. Minimum length is 2. Default is alphanumeric.
 *
 * @return string
 */
function random_string($length, $keyspace = NULL, $pool = true) {
	static $random_pool = [];

	if($keyspace === NULL)
		$keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	elseif(is_array($keyspace))
		$keyspace = implode('', $keyspace);
	$max = mb_strlen($keyspace, '8bit') - 1;
	if($max < 1) {
		throw new Exception('$keyspace must be at least 2 characters long');
		return false;
	}
	$keyspace = str_shuffle($keyspace);
	$j = 1;
	do {
		$str = '';
		for($i = 0; $i < $length; $i++) {
			if(function_exists('openssl_random_pseudo_bytes')) {
				// 1. openssl_random_pseudo_bytes is a CSPRNG function which generates bytes
				// 2. bin2hex converts the binary data to hexadecimal
				// 3. hexdec converts it to decimal representation
				// 4. fmod is used instead of the % operator because the decimal
				// 		result is often a very large float more than PHP_INT_MAX
				// 5. intval to get the integer to be used as an array key
				$index = intval(fmod(hexdec(bin2hex(openssl_random_pseudo_bytes(min($length + $i, 64)))), $max));
			} else {
				// Less secure
				$index = mt_rand(0, $max);
			}
			$str .= $keyspace[$index];
		}

		if(!$pool || !in_array($str, $random_pool)) {
			// Unique
			$random_pool[] = $str;
			break;
		} elseif($j == 10) {
			// Try how many times?
			$j = 0;
			$max++;
		}
		$j++;
	} while(true);

	return $str;
}

function random_alpha($length, $pool = true) {
	$keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	return random_string($length, $keyspace, $pool);
}


function size_to_bytes($size) {
	$suffix = strtoupper(substr($size, -1));
	$suffixes = 'KMGT';
	$val = (int) $size;
	if(($pos = strpos($suffixes, $suffix)) === false) {
		return $val;
	}

	return $val * pow(1024, $pos + 1);
}

function bytes_to_text($bytes, $decimals = 2) {
	$suffix = strtoupper(substr($bytes, -1));
	$suffixes = 'KMGT';

	if(strpos($suffixes, $suffix) !== false) {
		return $bytes;
	}

	$val = (int) $bytes;
	$i = 0;
	while($val >= 1024 && $i < strlen($suffixes)) {
		$val /= 1024;
		$i++;
	}

	if($i > 0) {
		$offseter = pow(10, $decimals);
		$val = (floor($val * $offseter) / $offseter).' '.$suffixes[$i - 1].'B';
	} else {
		$val .= ' bytes';
	}

	return $val;
}

function text_truncate($text, $maxlen, $ellipsis = '…', $with_title = false) {
	$truncated = strlen($text) <= $maxlen ? $text : substr($text, 0, $maxlen).$ellipsis;
	if($with_title) {
		$truncated = sprintf('<span title="%s">%s</span>', e($text), e($truncated));
	}
	return $truncated;
}
