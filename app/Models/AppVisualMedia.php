<?php

namespace App\Models;

use App\Models\AppVisualBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AppVisualMedia extends AppVisualBase
{
	protected $attributes = [
		'order'	=> 99,
		'meta'	=> '[]',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_type', function (Builder $builder) {
			$builder->where('type', '<>', static::TYPE_LOGO);
		});
	}

	public function getMediaUrl($media_name = '') {
		return asset('storage/apps/'.$this->app_id.'/'.$media_name);
	}

	public function getUrlAttribute() {
		if($this->type == 'image') {
			return $this->getMediaUrl($this->media_name);
		} else {
			switch($this->complete_type) {
				case 'video.youtube':
					return get_youtube_url($this->media_name);
					break;
			}
		}
	}

	public function getEmbedUrlAttribute() {
		// Mostly for video embed URLs
		if($this->type == 'image') {
			return $this->getMediaUrl($this->media_name);
		} else {
			switch($this->complete_type) {
				case 'video.youtube':
					return get_youtube_url($this->media_name, false);
					break;
			}
		}
	}

	public function getSmallUrlAttribute() {
		if($this->type == 'image') {
			return $this->getMediaUrl($this->media_small_name);
		} else {
			switch($this->complete_type) {
				case 'video.youtube':
					return asset('img/thumbnails/youtube.png');
					break;
			}
		}
	}

	public function getThumbnailUrlAttribute() {
		if($this->type == 'image') {
			return $this->getMediaUrl($this->media_small_name ?: $this->media_name);
		} else {
			switch($this->complete_type) {
				case 'video.youtube':
					return asset('img/thumbnails/youtube.png');
					break;
			}
		}
	}
}
