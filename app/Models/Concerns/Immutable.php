<?php

namespace App\Models\Concerns;

trait Immutable {

	// const UPDATED_AT = NULL;
	// const UPDATED_BY = NULL;
	// const DELETED_AT = NULL;
	// const DELETED_BY = NULL;


	/**
	 * Boot this trait.
	 *
	 * @return void
	 */
	public static function bootImmutable()
	{
		// Boot

		static::registerModelEvent('saving', function($model) {
			return false;
		});

		static::registerModelEvent('updating', function($model) {
			return false;
		});

		static::registerModelEvent('deleting', function($model) {
			return false;
		});

		static::registerModelEvent('restoring', function($model) {
			return false;
		});
	}

	public function save(array $options = []) {
		return false;
	}

	public function update(array $attributes = [], array $options = []) {
		return false;
	}

	public function delete() {
		return false;
	}
}
