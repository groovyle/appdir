<?php

namespace App;

use App\Models\Setting as SettingModel;

class Settings
{

	public static $autocast = true;
	public static $fail = true;

	public static function castValue($value) {
		$value = trim($value);

		if(is_numeric($value))
			return $value + 0;

		if(truthy($value)) return true;
		if(falsy($value)) return false;

		$lower = strtolower($value);
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
			return static::castValue( SettingModel::findOrFail($key)->value );
		else
			return static::castValue( SettingModel::whereKey($key)->value('value') );
	}

	public static function getItem($key) {
		if(static::$fail) {
			$model = SettingModel::findOrFail($key);
		} else {
			$model = SettingModel::whereKey($key)->first();
			if(!$model)
				$model = new SettingModel;
		}
		$model->value = static::castValue($model->value);
		return $model;
	}

	public static function set($key, $value, $attributes = []) {
		$model = SettingModel::find($key);
		if(!$model) {
			$model = new SettingModel;
			$model->key = $key;
		}

		$model->value = $value;
		$model->fill($attributes);

		return $model->save();
	}

}