<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AppChangelog extends Model
{
	protected $table = 'app_changelogs';

	public $timestamps = TRUE;
	const CREATED_AT = 'created_at';
	const UPDATED_AT = NULL;

	protected $attributes = [
		'is_verified' => false,
	];

	protected $casts = [
		'diffs'	=> 'array',
		'is_verified' => 'boolean',
	];

	protected $guarded = [
		'id',
		'app_id',
		'is_verified',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_order', function (Builder $builder) {
			// TODO: sort by creation order or version number?
			// $builder->orderBy('version', 'desc');
			$builder->latest();
		});
	}

	public function app() {
		return $this->belongsTo('App\Models\App');
	}

	public function verification() {
		return $this->hasMany('App\Models\AppVerification', 'changes_id');
	}

	public function nextVersionNumber() {
		return $this->version + 1;
	}

}