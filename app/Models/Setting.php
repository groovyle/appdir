<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	use Concerns\LoggedActions;
	//
	protected $table = 'settings';
	protected $primaryKey = 'key';
	protected $keyType = 'string';
	public $incrementing = false;
	public $timestamps = false;

	protected $guarded = ['key'];

	/*public function delete() {
		return false;
	}*/

}
