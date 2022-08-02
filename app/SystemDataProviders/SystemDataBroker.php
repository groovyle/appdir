<?php

namespace App\SystemDataProviders;

class SystemDataBroker {

	protected $data_provider;
	static protected $instance;

	public function __construct($type = NULL) {
		if($type === NULL) {
			$type = config('data_provider.type', 'virtualmin');
		}
		switch($type) {
			case 'virtualmin':
				$this->data_provider = new VirtualminDataProvider;
				break;
		}
	}

	public function provider() {
		return $this->data_provider;
	}

	public function __call($name, $arguments) {
		$exists = method_exists($this->data_provider, $name);
		$callable = is_callable([$this->data_provider, $name]);
		if($exists && $callable) {
			$result = call_user_func_array([$this->data_provider, $name], $arguments);
			return $result;
		} else {
			throw new \BadMethodCallException(sprintf(' Call to undefined method %s::%s()', get_class($this->data_provider), $name));
		}
	}

	static public function __callStatic($name, $arguments) {
		if(!static::$instance) {
			$class = get_class();
			static::$instance = new $class;
		}

		return call_user_func_array([static::$instance, $name], $arguments);
	}

}

