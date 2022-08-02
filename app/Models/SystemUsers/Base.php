<?php

namespace App\Models\SystemUsers;

use App\User;

class Base extends User {

	public $table = 'users';
	protected $fillable = [];

	public $system_user = true;
	const USER_ID = null;

	public static function instance()
	{
		return static::find(static::USER_ID);
	}

	public function getNameAttribute()
	{
		return __($this->email ?: $this->name);
	}

	public function save(array $options = [])
	{
		return false;
	}

	public function update(array $attributes = [], array $options = [])
	{
		return false;
	}

	public function delete()
	{
		return false;
	}

	public function __toString()
	{
		return $this->getKey();
	}

}