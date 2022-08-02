<?php

// https://stackoverflow.com/a/47123077

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FQDN implements Rule
{
	protected $attribute;

	protected $allow_top_level;

	protected $prefix = '';
	protected $suffix = '';

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(array $data = [], $allow_top_level = FALSE)
	{
		//
		if(isset($data['prefix'])) {
			$this->prefix = (string) $data['prefix'];
		}
		if(isset($data['suffix'])) {
			$this->suffix = (string) $data['suffix'];
		}

		$this->allow_top_level = (bool) $allow_top_level;
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
		$value = $this->prefix . $value . $this->suffix;

		if($this->allow_top_level) {
			$pattern = '/^(?!:\/\/)(?=.{1,255}$)(([\w-]{1,63}\.){1,127}((?![0-9]*$)[a-z0-9-]+\.?)|([\w-]{1,63}))$/i';
		} else {
			$pattern = '/^(?!:\/\/)(?=.{1,255}$)(([\w-]{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i';
		}

		return preg_match($pattern, $value);
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.fqdn', [
			'attribute' => trans('validation.attributes.'.$this->attribute)
		]);
	}
}
