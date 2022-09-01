<?php

namespace App;

use App\SystemDataProviders\SystemDataBroker;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
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

	protected $attributes = [
		'entity' => 'user',
	];

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

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_with_trashed', function (Builder $builder) {
			// Set to always able to select trashed items
			$builder->withTrashed();
		});
	}

	public function getIsSystemAttribute() {
		return $this->entity == 'system';
	}

	public function getNameAttribute() {
		$name = $this->attributes['name'];
		if(!$this->is_system) {
			return $name;
		} else {
			$key = 'users.'.$name;
			return \Lang::has($key) ? \Lang::get($key) : $name;
		}
	}

	public function apps() {
		return $this->hasMany('App\Models\App', 'owner_id');
	}

	public function __toString() {
		return (string) $this->name;
	}
}
