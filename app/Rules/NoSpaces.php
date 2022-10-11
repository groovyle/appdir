<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoSpaces implements Rule
{
	protected $attribute;

	protected $all_whitespaces = false;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct($all_whitespaces = false)
	{
		//
		$this->all_whitespaces = $all_whitespaces;
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

		if($this->all_whitespaces) {
			$pattern = '/\s/';
		} else {
			$pattern = '/ /';
		}

		return !preg_match($pattern, $value);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		$key = $this->all_whitespaces ? 'validation.no_whitespaces' : 'validation.no_spaces';
		return trans($key, [
			'attribute' => lang_or_raw($this->attribute, 'validation.attributes.')
		]);
	}
}
