<?php

namespace App\DataManagers;

use App;
use Auth;
use Illuminate\Support\Carbon;
use App\User;

class LanguageManager {

	public static $languages = [
		'id',
		'en',
	];

	public static function getList($sorted = true) {
		$list = [];
		foreach(static::$languages as $l) {
			$list[$l] = strtoupper($l);
		}
		if($sorted)
			natcasesort($list);

		return $list;
	}

	public static function getTranslated($lang) {
		return lang_or_raw($lang, 'languages.');
	}

	public static function getTranslatedList($sorted = true) {
		$list = [];
		foreach(static::$languages as $l) {
			$list[$l] = static::getTranslated($l);
		}
		if($sorted)
			natcasesort($list);

		return $list;
	}

	public static function setLocale($locale) {
		$fallback_locale = config('app.fallback_locale');

		// PHP functions locale
		setlocale(LC_TIME, [
			$locale,
			$fallback_locale,
		]);

		// App translator locale
		// Will overwrite config('app.locale')
		App::setLocale($locale);

		// Carbon locale
		Carbon::setLocale($locale);
		Carbon::setFallbackLocale($fallback_locale);
	}

}
