<?php

namespace App\Models\Concerns;

trait HasSoftDeletesCheck {

	/**
	 * A flag which indicates whether the model uses SoftDeletes or not.
	 * If null, it will be automatically detected upon booting.
	 *
	 * @var bool|null
	 */
	public static $usesSoftDeletes = null;

	/**
	 * Boot this trait.
	 *
	 * @return void
	 */
	public static function bootHasSoftDeletesCheck()
	{
		// Boot
		if(static::$usesSoftDeletes === null) {
			static::$usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive(static::class));
		}
	}

	/**
	 * Determine if the model uses SoftDeletes.
	 *
	 * @return bool
	 */
	public function usesSoftDeletes()
	{
		return static::$usesSoftDeletes;
	}

}