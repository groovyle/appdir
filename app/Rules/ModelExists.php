<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ModelExists implements Rule
{
	protected $attribute;

	protected $model;
	protected $key_name;
	protected $list_separator;
	protected $query_callback;
	protected $model_uses_soft_deletes;
	protected $with_trashed;
	protected $value;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct(string $model, string $key_name = null, string $list_separator = null, callable $query_callback = null, $with_trashed = true)
	{
		//
		$this->model = $model;
		$this->key_name = $key_name;
		$this->with_trashed = $with_trashed;
		$this->list_separator = $list_separator;
		$this->query_callback = $query_callback;

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
			$passes = TRUE;
			$list = $this->list_separator ? explode($this->list_separator, $value) : (array) $value;
			$list = array_unique($list);

			if($this->key_name === NULL) {
				$query = $this->model::whereKey($list);
			} else {
				$query = $this->model::whereIn($this->key_name, $list);
			}
			if($this->with_trashed && $this->model_uses_soft_deletes) {
				$query->withTrashed();
			}
			if($this->query_callback) {
				call_user_func_array($this->query_callback, [$query]);
			}
			$passes = $query->count() == count($list);
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
		// TODO: what about just using the validation.exists message?
		return trans('validation.model_exists', [
			'attribute' => trans('validation.attributes.'.$this->attribute),
			'model'     => $this->model
		]).' | '.implode(', ', [$this->key_name, $this->attribute, $this->value]); // TODO: fix message
	}
}
