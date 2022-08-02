<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUser extends Model
{
	//
	protected $primary_key = 'user_id';
	public $incrementing = FALSE;
	public $timestamps = FALSE;


	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = ['user_id'];

	public function user() {
		return $this->belongsTo('App\User');
	}
}
