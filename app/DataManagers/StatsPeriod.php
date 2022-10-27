<?php

namespace App\DataManagers;

// Placeholder class for statistic period items
class StatsPeriod {

	public function __construct($attributes = []) {
		foreach($attributes as $key => $value) {
			$this->$key = $value;
		}
	}

	public function __get($key) {
		if(!property_exists($this, $key)
			&& \Str::startsWith($key, ['total', 'count', 'max', 'min', 'sum', 'avg', 'num_'])) {
			// Common numeric properties, return 0 instead of null
			return 0;
		}

		return $this->$key;
	}

}
