<?php

namespace App;

use App\DataManagers\UserManager;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Database\Eloquent\SoftDeletes;
use RahulHaque\Filepond\Traits\HasFilepond;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Mail\VerifyEmail as VerifyEmailMail;

class User extends Authenticatable implements MustVerifyEmailContract
{
	use Notifiable;
	use HasRolesAndAbilities;
	use SoftDeletes;
	use HasFilepond;
	use Models\Concerns\LoggedActions;

	use MustVerifyEmail;

	public static $ignoreVerificationEmailErrors = false;

	protected $attributes = [
		'entity' => 'user',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password', 'prodi_id', 'lang',
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

	protected $withCount = [
		'blocks',
		'all_blocks',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_with_trashed', function (Builder $builder) {
			// Set to always able to select trashed items
			$builder->withTrashed();
		});


		// Use our own email format
		VerifyEmail::toMailUsing([UserManager::class, 'verifyEmailMail']);
	}

	public function scopeRegular($query, $state = true) {
		$query->where('entity', $state ? '=' : '!=', 'user');
	}

	public function scopeSystem($query, $state = true) {
		$query->where('entity', $state ? '=' : '!=', 'system');
	}

	public function scopeBlocked($query, $blocked = true) {
		$query->where('is_blocked', $blocked ? 1 : 0);

		$has_fn = $blocked ? 'whereHas' : 'whereDoesntHave';
		$query->$has_fn('blocks');
	}

	public static function getFrontendItem($id, $fail = true, $scope = true) {
		$query = static::query();
		// $query->withoutGlobalScope('_with_trashed');
		$query->whereKey($id);
		$query->system(false);

		if($scope) {
			$query->blocked(false);
		}

		$fn = $fail ? 'firstOrFail' : 'first';

		return $query->$fn();
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

	public function getNameEmailAttribute() {
		$name = $this->name;
		if(!$this->is_system) {
			if(!$name)
				$name = $this->email;
			elseif($this->email)
				$name .= ' ('.$this->email.')';
		}

		return $name;
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

	public function public_apps() {
		return $this->apps()->isListed();
	}

	public function prodi() {
		return $this->belongsTo('App\Models\Prodi', 'prodi_id')->withDefault();
	}

	public function blocks() {
		return $this->hasMany('App\Models\UserBlock', 'user_id')->latest();
	}

	public function inactive_blocks() {
		return $this->blocks()->onlyTrashed();
	}

	public function all_blocks() {
		return $this->blocks()->withTrashed();
	}

	public function getIsBlockedAttribute() {
		return $this->attributes['is_blocked'] == 1
			|| $this->blocks_count > 0
		;
	}

	public function getSortedRolesAttribute() {
		return UserManager::getSortedRoles($this->roles);
	}

	public function getProfilePictureAttribute() {
		return $this->getProfilePicture();
	}

	public function getStorageDir($relative = true) {
		$relpath = 'users/'.$this->id.'/';
		return $relative ? $relpath : Storage::disk('public')->path($relpath);
	}

	public function pictureExists($return_path = false) {
		$pic = $this->attributes['picture'];
		$picpath = /*$this->getStorageDir().*/$pic;
		$exists = false;

		if($pic) {
			$exists = Storage::disk('public')->exists($picpath);
		}

		return $return_path && $exists ? $picpath : $exists;
	}

	public function getProfilePicture($with_default = true) {
		$picpath = $this->pictureExists(true);

		if(!$picpath) {
			return !$with_default ? null : asset('img/default-user-logo.png');
		} else {
			return asset('storage/'.$picpath);
		}
	}

	public function getRolesTextAttribute() {
		$roles = $this->sorted_roles;
		$text = '';
		if(count($roles) > 0) {
			$text = $roles->map(function($item) {
				return $item->title ?: $item->name;
			})->implode(', ');
			// $text = $roles->pluck('name')->implode(', ');
		} else {
			// $text = vo_();
			$text = null;
		}

		return $text;
	}

	public function getIsMeAttribute() {
		return $this->exists && $this->id !== null && $this->id == Auth::id();
	}

	public function getShareNameAttribute() {
		return get_words_before($this->name, 20, 1);
	}

	public function getIsEmailVerifiedAttribute() {
		if(!config('auth.verify_email'))
			return true;
		else
			return !is_null($this->email_verified_at);
	}

	public function getIsVerifiedAttribute() {
		if($this->isAn('admin') || $this->isA('superadmin'))
			return true;

		return $this->is_email_verified;
	}

	public function __toString() {
		return (string) $this->name;
	}


	/**
	 * Send the email verification notification.
	 *
	 * @return void
	 */
	public function sendEmailVerificationNotification()
	{
		try {
			// $this->notify(new VerifyEmail);
			$this->notifyNow(new VerifyEmail);
		} catch (\Exception $e) {
			if(static::$ignoreVerificationEmailErrors) {
				// Do nothing
			} else {
				throw $e;
			}
			return false;
		}
	}


}
