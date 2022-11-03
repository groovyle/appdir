<?php

function dump_db($die = TRUE) {
	$fn = $die ? 'dd' : 'dump';
	$fn(DB::getQueryLog());
}

// Gets morph key, i.e the final morph name stored in database
// Copied from Illuminate\Database\Eloquent\Concerns\HasRelationships
function get_morph_alias($classname) {
	$morphMap = Illuminate\Database\Eloquent\Relations\Relation::morphMap();

	if (! empty($morphMap) && in_array($classname, $morphMap)) {
		return array_search($classname, $morphMap, true);
	}

	return $classname;
}
// Reverse of the above
function get_morph_model($name, $fallback = false) {
	$model = Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($name);
	if($model === null && $fallback)
		$model = $name;
	return $model;
}

function model_uses_soft_deletes($model) {
	return in_array(Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model));
}

function get_count_from_list_query($query, $count_column = 'id', $without = null, $callback = null) {
	$without = $without === null ? ['columns', 'groups', 'orders'] : (array) $without;
	$query = $query->getQuery()->cloneWithout($without);
	if($callback) {
		$callback($query);
	}
	$count = $query->selectRaw('count(distinct '.$count_column.') as count_col')->value('count_col');
	return $count;
}

function find_item_offset_from_list_query($query, $id) {
	$offset_query = (clone $query)->applyScopes();
	$orders = $offset_query->getQuery()->orders;
	$orders_bindings = $offset_query->getQuery()->bindings['order'];
	$item = $query->getModel()->find($id);
	$item_keyname = $query->getModel()->getKeyName();
	$item_key = $id;
	$binding_i = 0;
	if($orders && $item) {
		$orders_to_apply = [];
		foreach($orders as $order) {
			if(($order['type'] ?? null) == 'Raw') {
				// NOTE: opt to not support this, because there's no way to know
				// what column or derived value is being sorted from here.
				// Skipping is not an option because then the offset would be
				// inaccurate
				throw new \UnexpectedValueException('Finding offset from a list query does not support Raw order clauses, because there\'s no way to extract the expression and put it into the WHERE clause.');
				return;

				// Kinda tricky...
				// Attempt to get order
				$tmp = explode(' ', strrev($order['sql']), 2);
				$order = strrev($tmp[0]);
				if(in_array($order, ['asc', 'desc'])) {
					$sql = strrev($tmp[1]);
				} else {
					$sql = $order;
					$order = 'asc';
				}

				$binding_count = substr_count($sql, '?');
				$bindings = array_slice($orders_bindings, $binding_i, $binding_count);
				$offset_query->whereRaw($sql, $bindings);
				$binding_i += $binding_count;
			} elseif( array_key_exists($order['column'], $item->getAttributes())
				|| isset($item->{$order['column']}) ) {
				if($item->{$order['column']} !== null) {
					$orders_to_apply[] = function($query, $last) use($order, $item) {
						$query->where(function($query) use($order, $item, $last) {
							$query->where(
								$order['column'],
								($order['direction'] == 'asc' ? '<' : '>').(!$last ? '=' : ''),
								$item->{$order['column']}
							);
							if($last && $order['direction'] == 'asc') {
								// NULL values are always considered less, so
								// we also need to account for that when ascending
								$query->orWhereNull($order['column']);
							}
						});
					};
				} else {
					/**
					 * Special case when the ordered thingy is null.
					 * I think when ordering in db, NULL string values are considered
					 * even less than ''.
					 * Thus, we can think of it like this: NULL = 0, non-NULL = 1.
					 * When order is ASC, it means earlier items are also NULL,
					 * and later items are non-NULL. Vice-versa when order is DESC.
					 */
					$orders_to_apply[] = function($query, $last) use($order, $item_keyname, $item_key) {
						$query->where(function($query) use($order, $item_keyname, $item_key, $last) {
							$fn = $order['direction'] == 'asc' ? 'whereNull' : 'whereNotNull';
							$query->$fn($order['column']);
							$query->where(
								$item_keyname,
								($order['direction'] == 'asc' ? '<' : '>').(!$last ? '=' : ''),
								$item_key
							);
						});
					};
				}
			} else {
				// Column not found
				throw new \UnexpectedValueException('Order column `'.$order['column'].'` not found. Make sure the column exists by default and is not a derived column (columns in SELECT clauses do not work in WHERE clauses).');
			}
		}

		// Apply gradual filters
		for($i = 0; $i < count($orders_to_apply); $i++) {
			$offset_query->orWhere(function($query) use($orders_to_apply, $i) {
				for($j = 0; $j <= $i; $j++) {
					$orders_to_apply[$j]($query, $j == $i);
				}
			});
		}
		$offset_query->orWhere($item_keyname, $item_key);

		$offset = $offset_query->count();
	} else {
		$offset = $offset_query->where($item_keyname, '<=', $item_key)->count();
	}

	return $offset;
}
