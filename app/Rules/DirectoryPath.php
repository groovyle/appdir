<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DirectoryPath implements Rule
{
	protected $attribute;

	protected $checkExists;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(bool $checkExists = FALSE)
	{
		//
		$this->checkExists = $checkExists;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		//
		if(!$this->attribute) {
			$this->attribute = $attribute;
		}

		// Allow only a limited set of possible characters
		$passes = preg_match('#^([\w\d_-][\w\d._-]+/)+$#i', $value);
		if($this->checkExists) {
			$passes = $passes && is_dir($value);
		}
		return $passes;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.directory_path', [
			'attribute' => trans('validation.attributes.'.$this->attribute)
		]);
	}
}
