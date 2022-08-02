<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVisualBase extends Model
{
	use SoftDeletes;
	use Concerns\HasCudActors;
	use Concerns\HasFilteredAttributes;

	protected $table = 'app_visual_media';

	const TYPE_LOGO = 'logo';
	const TYPE_IMAGE = 'image';
	const TYPE_VIDEO = 'video';

	const SUBTYPE_VIDEO_YOUTUBE = 'youtube';

	protected $attributes = [
		'meta'	=> '[]',
	];

	protected $casts = [
		'meta'	=> 'array',
	];

	protected $guarded = [
		'id',
		'app_id',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_order', function (Builder $builder) {
			$builder->orderBy('order', 'asc');
		});
	}

	public function app() {
		return $this->belongsTo('App\Models\App');
	}

	public function getCompleteTypeAttribute() {
		return $this->type . (!empty($this->subtype) ? '.'.$this->subtype : '');
	}

	public function getMediaUrl($media_name = '') {
		return asset('storage/apps/'.$this->app_id.'/'.$media_name);
	}

	public function getUrlAttribute() {
		return $this->getMediaUrl($this->media_name);
	}

	public function getTypeTextAttribute() {
		return __('admin.app.visuals.type.'.str_replace('.', '_', $this->complete_type));
	}

	public function getMetaTextAttribute() {
		$texts = [];
		if(!empty($this->meta)) {
			foreach($this->meta as $key => $value) {
				/*if(filter_var($value, FILTER_VALIDATE_URL)) {
					$value = sprintf('<a href="%s" target="_blank">%s</a>', $value, $value);
				}*/
				$texts[] = __('admin.app.visuals.meta.'.$key).': '.$value;
			}
		} else {
			// $texts[] = '-';
		}

		return $texts;
	}
}
