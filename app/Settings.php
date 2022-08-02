<?php

namespace App;

use App\Models\Settings as Model;

class Settings
{

	public static $autocast = true;
	public static $fail = true;

	public static function castValue($value) {
		$value = trim($value);

		if(is_numeric($value))
			return $value + 0;

		$lower = strtolower($value);
		if(in_array($lower, ['true', 'false']))
			return $lower == 'true';
		if($lower === 'null')
			return null;
		if(preg_match('/^[0-9]+-[0-9]+$/', $value)) {
			// Range
			return array_map('intval', explode('-', $value));
		}

		$json = json_decode($value, true);
		if(json_last_error() === 0 && is_array($json))
			return $json;

		return $value;
	}

	public static function get($key) {
		if(static::$fail)
			return static::castValue( Model::findOrFail($key)->value );
		else
			return static::castValue( Model::whereKey($key)->value('value') );
	}

	public static function getItem($key) {
		if(static::$fail) {
			$model = Model::findOrFail($key);
		} else {
			$model = Model::whereKey($key)->first();
			if(!$model)
				$model = new Model;
		}
		$model->value = static::castValue($model->value);
		return $model;
	}

	public static function set($key, $value, $attributes = []) {
		$model = Model::find($key);
		if(!$model) {
			$model = new Model;
			$model->key = $key;
		}

		$model->value = $value;
		$model->fill($attributes);

		return $model->save();
	}

}