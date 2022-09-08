<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorScheme extends Model
{
	use Concerns\Immutable;

	protected $table = 'color_schemes';
	public $timestamps = false;

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_order', function ($query) {
			$query->orderBy('name');
		});
	}

	public function scopeLight($query) {
		$query->whereIn('chroma', ['light', 'both']);
	}

	public function scopeDark($query) {
		$query->whereIn('chroma', ['dark', 'both']);
	}

	public function scopeFlexible($query) {
		$query->where('chroma', 'both');
	}

	public function getColorsAttribute() {
		return array_map('trim', explode(',', $this->attributes['colors']));
	}

	public function getColorAttribute() {
		return optional($this->colors)[0];
	}

}
