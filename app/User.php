<?php

namespace App;

use App\SystemDataProviders\SystemDataBroker;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Database\Eloquent\SoftDeletes;
use RahulHaque\Filepond\Traits\HasFilepond;

class User extends Authenticatable
{
	use Notifiable;
	use HasRolesAndAbilities;
	use SoftDeletes;
	use HasFilepond;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function getHomeDirectoryAttribute() {
		// DUMMY
		$home_directory = '/home/user123/';
		return $home_directory;
	}

	public function system_user() {
		return $this->hasOne('App\Models\SystemUser');
	}

	public function getSystemAttribute() {
		return $this->system_user ? SystemDataBroker::getUser($this->system_user->domain, $this->system_user->username) : [];
	}

	public function apps() {
		return $this->hasMany('App\Models\App', 'owner_id');
	}
}
