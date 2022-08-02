<?php

namespace App\Models;

use App\Models\AppVisualBase;
use Illuminate\Database\Eloquent\Builder;

class AppLogo extends AppVisualBase
{
	protected $attributes = [
		'order'	=> 1,
		'meta'	=> '[]',
	];

	public function __construct(array $attributes = []) {
		$this->attributes['type'] = static::TYPE_LOGO;
		parent::__construct($attributes);
	}

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_type', function (Builder $builder) {
			$builder->where('type', static::TYPE_LOGO);
		});
	}
}
