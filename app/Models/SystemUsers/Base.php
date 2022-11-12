<?php

namespace App\Models\SystemUsers;

use App\User;

class Base extends User {

	public $table = 'users';
	protected $attributes = [
		'entity' => 'system',
		'password' => '',
	];
	protected $fillable = [];

	public $system_user = true;
	const USER_ID = null;

	public static function instance()
	{
		return static::find(static::USER_ID);
	}

	public static function generateInstance() {
		$user = new static;
		$user->id = static::USER_ID;
		$user->password = '';
		return $user;
	}

	public function getNameAttribute()
	{
		return __($this->email ?: $this->name);
	}

	public function update(array $attributes = [], array $options = [])
	{
		return false;
	}

	public function delete()
	{
		return false;
	}

}