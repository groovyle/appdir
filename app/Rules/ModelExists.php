<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelExists implements Rule
{
	protected $attribute;

	protected $model;
	protected $key_name;
	protected $model_uses_soft_deletes;
	protected $with_trashed;
	protected $value;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(string $model, string $key_name = NULL, $with_trashed = true)
	{
		//
		$this->model = $model;
		$this->key_name = $key_name;
		$this->with_trashed = $with_trashed;

		$this->model_uses_soft_deletes = model_uses_soft_deletes($model);
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
				$query = $this->model::whereKey($value);
			} else {
				$query = $this->model::where($this->key_name, $value);
			}
			if($this->with_trashed && $this->model_uses_soft_deletes) {
				$query->withTrashed();
			}
			$passes = $query->exists();
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
		]).' | '.implode(', ', [$this->key_name, $this->attribute, $this->value]); // TODO: fix message
	}
}
