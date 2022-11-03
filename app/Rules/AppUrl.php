<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AppUrl implements Rule
{
	protected $attribute;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
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

		$parsed = parse_url($value);
		if($parsed !== FALSE) {
			// Remove the scheme part and add a dummy one later, because valid URLs
			// might or might not include a scheme.
			if(isset($parsed['scheme']))
				unset($parsed['scheme']);

			$url = 'http://'.unparse_url($parsed);
			$passes = filter_var($url, FILTER_VALIDATE_URL);
		} else {
			$passes = FALSE;
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
		return trans('validation.url', [
			'attribute'	=> lang_or_raw($this->attribute, 'validation.attributes.'),
			'value' => $this->value,
		]);
	}
}
