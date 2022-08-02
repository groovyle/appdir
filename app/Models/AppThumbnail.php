<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppThumbnail extends Model
{
	//

	public function app() {
		return $this->belongsTo('App\Models\App');
	}

	public function getUrlAttribute() {
		// return Storage::disk('public')->url($this->file_path);
		return asset('storage/'.$this->file_path);
	}
}
