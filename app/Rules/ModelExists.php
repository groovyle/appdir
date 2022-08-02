<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelExists implements Rule
{
	protected $attribute;

	protected $model;
	protected $key_name;
	protected $value;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(string $model, string $key_name = NULL)
	{
		//
		$this->model = $model;
		$this->key_name = $key_name;
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
		if(!$this->attribute) {
			$this->attribute = $attribute;
			$this->value = $value;
		}
		//
		$passes = FALSE;

		if(class_exists($this->model)) {
			if($this->key_name === NULL) {
				$passes = $this->model::whereKey($value)->exists();
			} else {
				$passes = $this->model::where($this->key_name, $value)->exists();
			}
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
		return trans('validation.model_exists', [
			'attribute' => trans('validation.attributes.'.$this->attribute),
			'model'     => $this->model
		]).' | '.implode(', ', [$this->key_name, $this->attribute, $this->value]); // DUMMY
	}
}
