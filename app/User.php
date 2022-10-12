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
		'email_verified_at'	=> 'datetime',
		'is_blocked'		=> 'boolean',
	];

	protected $with = [
		'prodi',
		'roles',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_with_trashed', function (Builder $builder) {
			// Set to always able to select trashed items
			$builder->withTrashed();
		});
	}

	public function scopeRegular($query) {
		$query->where('entity', 'user');
	}

	public function scopeSystem($query) {
		$query->where('entity', 'system');
	}

	public function getIsSystemAttribute() {
		return $this->entity == 'system';
	}

	public function getIsRegularUserAttribute() {
		return $this->entity == 'user';
	}

	public function getNameAttribute() {
		if(!isset($this->attributes['name']))
			return null;

		$name = $this->attributes['name'];
		if(!$this->is_system) {
			return $name;
		} else {
			$key = 'users.'.$name;
			return lang_or_raw($name, 'users.');
		}
	}

	public function getRawNameAttribute() {
		return $this->attributes['name'] ?? null;
	}

	public function getEntityTypeAttribute() {
		return lang_or_raw($this->attributes['entity'], 'users.entity.');
	}

	public function apps() {
		return $this->hasMany('App\Models\App', 'owner_id');
	}

	public function prodi() {
		return $this->belongsTo('App\Models\Prodi', 'prodi_id')->withDefault();
	}

	public function blocks() {
		return $this->hasMany('App\Models\UserBlock', 'user_id');
	}

	public function all_blocks() {
		return $this->blocks()->withTrashed();
	}

	public function getIsBlockedAttribute() {
		return $this->attributes['is_blocked'] == 0
			&& $this->blocks_count == 0
		;
	}

	public function getRolesTextAttribute() {
		$roles = $this->roles;
		$text = '';
		if(count($roles) > 0) {
			$text = $roles->pluck('name')->implode(', ');
		} else {
			// $text = vo_();
			$text = null;
		}

		return $text;
	}

	public function __toString() {
		return (string) $this->name;
	}
}
