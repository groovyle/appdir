<?php

namespace App\Rules;

use App\Models\App;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AppDirectory implements Rule
{
	protected $attribute;

	protected $user_id;
	protected $except_value;
	protected $except_key;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct($user_id = NULL, $except_value = NULL, $except_key = NULL)
	{
		//
		$this->user_id = intval($user_id === NULL ? Auth::id() : $user_id);

		$this->except_value = $except_value;
		$this->except_key = $except_key !== NULL ? $except_key : (new App)->getQualifiedKeyName();
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
		}

		if(empty($this->user_id)) {
			return FALSE;
		}

		// Check whether a parent directory had been submitted as an app.
		// To do that, we must first have a collection of every possible ancestor
		// directory.
		// Check whether a directory had been submitted as an app.
		// The check is rather 'weird'; the directory must not be a descendant of
		// any existing app directories, AND must not be an ancestor of any
		// existing app directories either. In other words, all app directories
		// must be 'distinct', in a certain way.

		// Normalize value
		$value = trim($value, '/').'/';

		// Get a collection of every possible ancestor directory.
		$ancestors = generate_ancestors($value);
		if($ancestors) {
			$builder = Auth::user()->apps();
			if($this->except_value) {
				$builder->where($this->except_key, '!=', $this->except_value);
			}

			$builder->whereIn('directory', $ancestors);
			$ancestor_exists = $builder->exists();
		} else {
			$ancestor_exists = FALSE;
		}

		$builder = Auth::user()->apps();
		if($this->except_value) {
			$builder->where($this->except_key, '!=', $this->except_value);
		}
		$builder->where(function($query) use ($value) {
			// Descendants must not exist.
			// Also, the directory itself must not exist.
			$query->where('directory', $value)
				->orWhere('directory', 'like', db_escape_like($value).'%');
		});

		$descendants_exist = $builder->exists();

		$passes = !$ancestor_exists && !$descendants_exist;
		return $passes;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.app_directory', [
			'attribute' => lang_or_raw($this->attribute, 'validation.attributes.')
		]);
	}
}

