<?php

namespace App\Models;

use App\Models\Concerns\Immutable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// NOTE: this is a mock model since Gate checks need a class as reference
class StatsAppActivities extends Model
{
	protected $table = 'apps';

	use Concerns\Immutable;

}
