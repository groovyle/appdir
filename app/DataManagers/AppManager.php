<?php

namespace App\DataManagers;

use App\Models\App;
use App\Models\AppChangelog;
use App\Models\AppVerification;
use App\Models\AppVisualMedia;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppManager {

	const DIFF_UNCHECKED_ATTRIBUTES = [
		'id',
		'slug',
		'owner_id',
		'version_id',
		'is_verified',
		'is_published',
		'published_at',
		'is_reported',
		'reported_at',
		'is_private',
		'page_views',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'deleted_by',
		'deleted_at',
		'verifications',
		'visuals',
		'tags',
		'categories',
		'relations',
	];

	protected static $app_data = [];

	protected static function getAppKey(App $model)
	{
		return get_class($model).':'.$model->getKey();
	}

	protected static function getAppData(App $model)
	{
		return static::$app_data[static::getAppKey($model)] ?? [];
	}

	protected static function setAppData(App $model, $data = [])
	{
		static::$app_data[static::getAppKey($model)] = $data;
	}

	// Call this on a fresh $model, i.e before doing anything at all
	public static function prepareForVersionDiff(App $model)
	{
		$attributes = collect($model->getAttributes())->filter(function($value, $key) {
			return !in_array($key, static::DIFF_UNCHECKED_ATTRIBUTES);
		})->all();

		$relations = static::compileRelationsDataForDiff($model);

		static::setAppData($model, [
			'original' => [
				'attributes'	=> $attributes,
				'relations'		=> $relations,
			],
		]);
	}

	protected static function compileRelationsDataForDiff(App $model)
	{
		// Load fresh relations
		/*$model->load([
			'visuals',
			'tags',
			'categories',
		]);*/
		// $relations = $model->getRelations();
		$compiled = [];

		if($model->exists) {
			// Visuals
			$visuals = [];
			foreach($model->visuals as $item) {
				$item->caption = $item->caption ?: null; // to avoid difference between '' and null (or other falsy values)
				$visuals[$item['id']] = $item->getAttributesOnly(['id', 'order', 'caption']);
			}
			ksort($visuals);
			$compiled['visuals'] = $visuals;

			// Logo
			$logo = [optional($model->logo)->id];
			$compiled['logo'] = $logo;

			// Tags
			$tags = $model->tags->pluck('name')->sort()->values()->all();
			$compiled['tags'] = $tags;

			// Categories
			$categories = $model->categories->pluck('id')->sort()->values()->all();
			$compiled['categories'] = $categories;
		}

		return $compiled;
	}

	// Return format is [ $a_outer, $b_outer, $intersection ], with outers
	// in terms of venn diagrams, i.e $a_outer means elements in $a that is not
	// in $b, just like array diff
	protected static function separateItems(array $a, array $b)
	{
		$a_outer = [];
		$b_outer = [];
		$intersection = [];

		$all_keys = array_unique(array_merge(array_keys($a), array_keys($b)));
		foreach($all_keys as $k) {
			$different = false;
			if(!isset($b[$k])) {
				$different = true;
				$a_outer[] = $a[$k];
			} elseif(!isset($a[$k])) {
				$different = true;
				$b_outer[] = $b[$k];
			}
			if($different)
				continue;

			$diff_a = count(array_diff_assoc($a[$k], $b[$k])) != 0;
			$diff_b = count(array_diff_assoc($b[$k], $a[$k])) != 0;
			if($diff_a || $diff_b) {
				$different = true;
				if($diff_a)
					$a_outer[] = $a[$k];
				if($diff_b)
					$b_outer[] = $b[$k];
			} else {
				$intersection[] = $a[$k];
			}
		}

		return [
			$a_outer,
			$b_outer,
			$intersection,
		];
	}

	// This function can be called before or after $model->save(), it only gathers
	// data surrounding the current vs original state of the model
	public static function generateDiffs(App $model)
	{
		$is_edit = $model->exists;
		/*if(!$model->exists) {
			throw new \OutOfBoundsException('App has to exist to make a diff.');
		}*/

		$app_data = static::getAppData($model);
		$diffs = [];

		// Compare attributes
		$original_attributes = data_get($app_data, 'original.attributes', []);
		// Use getDirty() on before save, and getChanges() on after save
		// In the case of a new item, just get all attributes
		if(!$is_edit || $model->wasRecentlyCreated) {
			// New
			// Filter out empty values
			$method = $model->wasRecentlyCreated ? 'getAttributesExcept' : 'getDirtyExcept';
			$diff_attributes = array_filter($model->$method(static::DIFF_UNCHECKED_ATTRIBUTES));
			$original_attributes = array_intersect_key($original_attributes, $diff_attributes);
		} else {
			// Edit
			$method = $model->wasChanged() ? 'getChangesExcept' : 'getDirtyExcept';
			$attributes = $model->$method(static::DIFF_UNCHECKED_ATTRIBUTES);
			$changes = array_intersect_key($attributes, $original_attributes);
			$diff_attributes = array_diff_assoc($changes, $original_attributes);
		}

		// Diff format for attributes is:
		// $attr = [ 'new' => $new_arr, 'old' => $old_arr ]
		foreach($diff_attributes as $key => $value) {
			$diffs['attributes']['new'][$key] = $value;
			$diffs['attributes']['old'][$key] = $original_attributes[$key] ?? null;
		}

		// Diff format for relations is:
		// $rel = [ 'new' => new_arr, 'old' => old_arr ]
		// This is to make checks for old diffs easier because the values are arrays, so checking:
		//  isset($rel['old'])
		// is way easier than doing:
		//  count($rel) == '2' && isset($rel[1])
		// because a corner case where only new values are available is possible,
		// where $rel = $new_arr

		$original_relations = data_get($app_data, 'original.relations', []);
		$relations = static::compileRelationsDataForDiff($model);
		if(isset($relations['visuals'])) {
			$old_visuals = $original_relations['visuals'] ?? [];
			$new_visuals = $relations['visuals'] ?? [];
			$separated = static::separateItems($new_visuals, $old_visuals);

			if(count($separated[0]) > 0 || count($separated[1]) > 0)
				$diffs['relations']['visuals'] = ['new' => $new_visuals, 'old' => $old_visuals];
		}
		if(isset($relations['logo'])) {
			$old_rel = $original_relations['logo'] ?? [];
			$new_rel = $relations['logo'] ?? [];
			if( ! array_same_elements($old_rel, $new_rel) )
				$diffs['relations']['logo'] = ['new' => $new_rel, 'old' => $old_rel];
		}
		if(isset($relations['tags'])) {
			$old_rel = $original_relations['tags'] ?? [];
			$new_rel = $relations['tags'] ?? [];
			if( ! array_same_elements($old_rel, $new_rel) )
				$diffs['relations']['tags'] = ['new' => $new_rel, 'old' => $old_rel];
		}
		if(isset($relations['categories'])) {
			$old_rel = $original_relations['categories'] ?? [];
			$new_rel = $relations['categories'] ?? [];
			if( ! array_same_elements($old_rel, $new_rel) )
				$diffs['relations']['categories'] = ['new' => $new_rel, 'old' => $old_rel];
		}

		return $diffs;
	}

	// Call this function only in a db transaction
	// For edits, call this function BEFORE $model->save() or push()
	// For new items, call this function AFTER $model->save() or push()
	// TODO: maybe do this before save() and provide the edited attributes (AND relations), because
	// doing it after save() means applying the diff BEFORE generating the diff, right?
	// If we want to stage the modifications for verification, we have to NOT apply
	// any edits/diffs, and instead just insert new changelogs and then later apply
	// the diffs upon approval
	public static function makeVersionDiff(App $model, $save = true, $comment = NULL)
	{
		if(DB::transactionLevel() == 0) {
			throw new \RuntimeException('Making a version diff is only available inside a database transaction.');
		}
		/*if(!$model->exists) {
			throw new \OutOfBoundsException('App has to exist to make a diff.');
		}*/

		// TODO: for edits, dont allow saving before diffing
		// TODO: check config for disabled staging
		// NOTE: how to detect changes in any of the relationships?
		$is_edit = $model->exists && !$model->wasRecentlyCreated;
		if($is_edit && $model->wasChanged()) {
			throw new \RuntimeException('App must not be saved before generating diffs to make sure modifications are staged.');
		}

		$return = [
			'status' => true,
			'changes' => [],
			'model' => null,
		];

		$diffs = static::generateDiffs($model);
		if(!$is_edit) {
			$diffs['is_new'] = true;
		}
		$return['changes'] = $diffs;
		if(empty($diffs)) {
			return $return;
		}

		$next_version = $model->nextVersionNumber();

		$changelog = new AppChangelog([
			'version'		=> $next_version,
			'diffs'			=> $diffs,
			'comment'		=> $comment,
		]);
		if($model->exists) {
			$changelog->app_id = $model->id;
			$changelog->based_on_id = optional($model->floating_changes->last())->id
				?? optional($model->version)->id
				?? optional($model->changelogs()->oldest()->first())->id
				?? null;
		}
		if($save) {
			$model->changelogs()->save($changelog);
		}

		$return['model'] = $changelog;

		return $return;
	}

	public static function diffSave(App $model, $comment = null)
	{
		$result = static::makeVersionDiff($model, true, $comment);
		$result['saved'] = false;
		$result['diff_status'] = $result['status'];
		$result['status'] = true;

		if(!$result['diff_status'])
			return $result;

		$is_edit = $model->exists && !$model->wasRecentlyCreated;
		$verf_add = settings('app.creation_needs_verification', false);
		$verf_edit = settings('app.modification_needs_verification', false);

		$needs_verf = (!$is_edit && $verf_add) || ($is_edit && $verf_edit);
		/*if(!$needs_verf) {
			// TODO: not here, but on verifications
			// Set to published
			$model->is_verified = 1;
		}*/

		$version_id = optional($result['model'])->id;

		if(!$is_edit) {
			// New item, just save
			// We don't really need to do this actually, the item should exist already
			// static::applyDiff($model, $result['changes'], false);
			// $result['saved'] = true;
			$model->version()->associate($result['model']);
			$result['saved'] = $model->save();

			// Generate first verification step for the new item
			$verif = new AppVerification;
			$verif->app_id = $model->id;
			$verif->verifier_id = $model->owner_id;
			$verif->status_id = 'unverified';
			$verif->base_changes_id = $version_id;
			$verif->concern = AppVerification::CONCERN_NEW_ITEM;
			$result['saved'] = $result['saved'] && $verif->save();

			if($result['saved'])
				$verif->changelogs()->attach($result['model']);

			if(!$verf_add) {
				// $result['model']->is_verified = 1;

				// TODO: generate a verification to publish the changes immediately,
				// and/or approve it before committing it
				// Also set it to published
				$result['saved'] = $result['saved'] && static::verifyAndApplyChanges($model, collect([$result['model']]), true);
			}
		} else {
			// Edit

			// Generate verification step for the edit
			$verif = new AppVerification;
			$verif->app_id = $model->id;
			$verif->verifier_id = request()->user()->id;
			if($model->last_changes->is_rejected
				|| $model->last_verification->status == 'resubmitted') {
				$verif->status_id = 'resubmitted';
			} elseif(!$model->has_committed) {
				$verif->status_id = 'unverified';
			} else {
				$verif->status_id = 'revised';
			}
			$verif->base_changes_id = optional($model->version)->id;
			$verif->concern = AppVerification::CONCERN_EDIT_ITEM;
			$result['saved'] = $verif->save();

			if($result['saved'])
				$verif->changelogs()->attach($result['model']);

			if($verf_edit) {
				// Pending updates, save nothing to the main item, the diff
				// had been saved earlier by makeVersionDiff
				$result['saved'] = true;
			} else {
				// Apply changes immediately
				// NOTE: do not use $result['changes'], because we're saving all pending
				// updates

				// Reset item before saving because we might be using a pending
				// item right now, so that current relations deletion
				// can be done accurately
				$to_save = $model->find($model->getKey());
				$compiled = static::getVersionsChanges($to_save, 'void', null, function($query) {
					$query->floating();
				});
				$result['saved'] = static::verifyAndApplyChanges($to_save, $compiled['versions'], false);
			}
		}

		$result['status'] = $result['diff_status'] && $result['saved'];

		// TODO: verification stuff

		return $result;
	}

	public static function transformDiffsForDisplay($diffs)
	{
		// Mock model
		$model = new App;

		if(isset($diffs['attributes'])) {
			$new_attributes = $diffs['attributes']['new'] ?? $diffs['attributes'] ?? [];
			$old_attributes = $diffs['attributes']['old'] ?? [];

			// Transform foreign keys into names
			foreach($new_attributes as $key => $value) {
				$fk_name = substr($key, 0, -3);
				// NOTE that $model does not exist in this context; might have to make
				// a temporary $app just to get the FK names
				if(substr($key, -3) === '_id' && method_exists($model, $fk_name)) {
					$related = $model->$fk_name()->getQuery()->getModel();
					$old_value = $old_attributes[$key] ?? null;
					$tmp = $related::find(array_filter([$value, $old_value]))->keyBy($related->getKeyName());

					// New
					$new_attributes[$fk_name] = $tmp[$value]->name ?? $tmp[$value]->code ?? null;
					unset($new_attributes[$key]);

					// Old
					$old_attributes[$fk_name] = $tmp[$value]->name ?? $tmp[$value]->code ?? null;
					unset($old_attributes[$key]);
				}
			}
			$diffs['attributes'] = [
				'new'	=> $new_attributes,
				'old'	=> $old_attributes,
			];
		}

		if(isset($diffs['relations'])) {
			$relations = $diffs['relations'] ?? [];

			// Turn visuals into items
			if(isset($relations['visuals'])) {
				$new_rel = collect($relations['visuals']['new'] ?? $relations['visuals'] ?? [])->keyBy('id');
				$old_rel = collect($relations['visuals']['old'] ?? [])->keyBy('id');
				$related = $model->visuals()->getQuery()->getModel();

				$new_items = $related::withTrashed()->find($new_rel->keys()->filter()->all())->keyBy('id');
				foreach($new_items as $id => $item) {
					$item->fill( collect($new_rel[$id])->except('id')->all() );
				}

				$old_items = $related::withTrashed()->find($old_rel->keys()->filter()->all())->keyBy('id');
				foreach($old_items as $id => $item) {
					$item->fill( collect($old_rel[$id])->except('id')->all() );
				}

				$relations['visuals']['new'] = $new_items;
				$relations['visuals']['old'] = $old_items;
			}

			// Turn logo into item
			if(isset($relations['logo'])) {
				$new_rel = collect($relations['logo']['new'] ?? $relations['logo'] ?? []);
				$old_rel = collect($relations['logo']['old'] ?? []);
				$related = $model->logo()->getQuery()->getModel();

				$new_item = $related::withTrashed()->find($new_rel->filter()->all())->first();
				$old_item = $related::withTrashed()->find($old_rel->filter()->all())->first();

				$relations['logo']['new'] = $new_item;
				$relations['logo']['old'] = $old_item;
			}

			// Transform simple relations' IDs into names
			$relation_plucks = [
				'tags'			=> 'name',
				'categories'	=> 'name',
			];
			foreach($relation_plucks as $relname => $key) {
				if(isset($relations[$relname])) {
					$new_rel = $relations[$relname]['new'] ?? $relations[$relname] ?? [];
					$old_rel = $relations[$relname]['old'] ?? [];

					$related = $model->$relname()->getQuery()->getModel();
					$relations[$relname]['new'] = $related::withTrashed()->find($new_rel)->pluck($key)->sort()->values();
					$relations[$relname]['old'] = $related::withTrashed()->find($old_rel)->pluck($key)->sort()->values();
				}
			}

			$diffs['relations'] = $relations;
		}

		return $diffs;
	}

	// Call this function only in a db transaction
	public static function applyDiff(App $app, $changes, $mock)
	{
		if(DB::transactionLevel() == 0 && !$mock) {
			throw new \RuntimeException('Applying version diff is only available inside a changesbase transaction.');
		}

		if(!$mock && !$app->exists) {
			return false;
		}

		$new_attributes = $changes['attributes']['new'] ?? $changes['attributes'] ?? [];
		foreach($new_attributes as $key => $value) {
			if(!in_array($key, static::DIFF_UNCHECKED_ATTRIBUTES) && !method_exists($app, $key)) {
				// Normal attribute
				$app->$key = $value;
				continue;
			}
		}

		if(isset($changes['relations'])) {
			$relations = $changes['relations'];

			if(isset($relations['visuals'])) {
				$new_visuals = collect($relations['visuals']['new'] ?? $relations['visuals'])->keyBy('id');
				$new_visuals_ids = $new_visuals->keys()->all();

				if(!$mock) {
					$visuals = $app->visuals->keyBy('id');
					$visuals_ids = $visuals->keys()->all();

					$ids_to_delete = array_diff($visuals_ids, $new_visuals_ids);
					$ids_to_restore = array_diff($new_visuals_ids, $visuals_ids);

					// Delete outlier
					foreach($visuals as $id => $vis) {
						$vis->dontLog();
						if(in_array($id, $ids_to_delete)) {
							// Should be soft delete
							$vis->delete();
						}
					}

					// Restore new ones
					// It is assumed the new ones were soft deleted (i.e not permanent delete).
					// Don't use a mass query and instead fetch invididual models
					// so that events are handled properly
					$to_restore = $app->visuals()->withTrashed()->whereKey($ids_to_restore)->get();
					foreach($to_restore as $vis) {
						$vis->dontLog();
						$vis->restore();
					}

					// Reload this relationship
					$app->load('visuals');
				} else {
					// Mock item
					$app->load(['visuals' => function($query) use($new_visuals_ids) {
						$query->withTrashed()->whereKey($new_visuals_ids);
					}]);
				}

				// Modify existing ones
				foreach($app->visuals as $vis) {
					if(isset($new_visuals[$vis->id])) {
						$vis->fill( collect($new_visuals[$vis->id])->except('id')->all() );
						if(!$mock) {
							$vis->dontLog();
							$vis->save();
						}
					}
				}
				$app->setRelation('visuals', $app->visuals->sortBy('order'));
			}

			if(isset($relations['logo'])) {
				// Can't use isset() because the value could be null
				$new_rels = $relations['logo'];
				if(array_key_exists('new', $new_rels))
					$new_rels = $new_rels['new'];

				if(!$mock) {
					$old_logo = $app->logo;
					if($old_logo)
						$old_logo->dontLog()->delete();

					$logo = $app->logo()->withTrashed()->whereKey($new_rels)->first();
					if($logo)
						$logo->dontLog()->restore();

					// Reload this relationship
					$app->load('logo');
				} else {
					$app->load(['logo' => function($query) use($new_rels) {
						$query->withTrashed()->whereKey($new_rels);
					}]);
				}
			}

			if(isset($relations['tags'])) {
				$rels = $app->tags->pluck('name')->sort()->all();
				$new_rels = $relations['tags']['new'] ?? $relations['tags'];

				if(!$mock) {
					$app->tags()->sync($new_rels);

					// Reload this relationship
					$app->load('tags');
				} else {
					// $app->load(['tags' => function($query) use($new_rels) {
					// 	$query->withTrashed()->whereKey($new_rels);
					// }]);
					// Can't use the above because this relation uses pivot,
					// the keys needed might have been removed in the pivot
					$new_items = $app->tags()->getRelated()->withTrashed()->whereKey($new_rels)->get();
					$app->setRelation('tags', $new_items);
				}
			}

			if(isset($relations['categories'])) {
				$rels = $app->categories->pluck('id')->sort()->all();
				$new_rels = $relations['categories']['new'] ?? $relations['categories'];

				if(!$mock) {
					$app->categories()->sync($new_rels);

					// Reload this relationship
					$app->load('categories');
				} else {
					// $app->load(['categories' => function($query) use($new_rels) {
					// 	$query->withTrashed()->whereKey($new_rels);
					// }]);
					// Can't use the above because this relation uses pivot,
					// the keys needed might have been removed in the pivot
					$new_items = $app->categories()->getRelated()->withTrashed()->whereKey($new_rels)->get();
					$app->setRelation('categories', $new_items);
				}
			}
		}

		// Finally, save
		if(!$mock) {
			$result = $app->save();

			// Don't use refresh() because it will reload all relations,
			// instead just reload relations above as necessary
			// $app->refresh();
		}

		return $app;
	}

	public static function goToVersion(App $app, $version)
	{
		$changes = static::getVersionsChanges($app, $version)['changes'];

		// We don't really need to return the app since it gets modified
		return static::applyDiff($app, $changes, false);
	}

	public static function getMockItem($app_id, $version)
	{
		$app = App::findOrFail($app_id);
		$compiled = static::getVersionsChanges($app, $version, 'void');

		$mock = new App;
		$mock->id = $app_id;
		$mock->owner_id = $app->owner_id;
		$mock->is_mock = true;
		$mock->original_version_number = $app->version_number;
		$mock->setRelation('version', $compiled['versions']->last());
		return static::applyDiff($mock, $compiled['changes'], true);
	}

	public static function getVersionsChanges(App $model, $to_version, $from_version = NULL, $query_callback = NULL)
	{
		$changes = [];
		$from_void = false;
		$to_void = false;
		$version_check = $to_version;

		$return = [
			'changes'		=> [],
			'versions'		=> elocollect(),
			'final_version'	=> null,
		];

		if($to_version === 'void') {
			// Target version is the latest
			$target = $model->changelogs()->first();
			$version_check = $target->version;
			if(!$target) {
				throw new \OutOfBoundsException("App does not have the target version.");
			}
			$to_void = true;
		}

		if(!$model->exists/* || $model->version_number == $version_check*/) {
			return $return;
		}

		// Determine direction, newer or older?
		// Then find everything in between
		if(!$to_void) {
			$target = $model->changelogs()->where('version', $to_version)->first();
			if(!$target) {
				throw new \OutOfBoundsException("App target version=$to_version doesn't exist.");
			}
		}

		if($from_version === 'void') {
			$from = $model->changelogs()->withoutGlobalScope('_order')->first();
			if(!$from) {
				throw new \OutOfBoundsException("App does not have the starting version.");
			}
			$from_void = true;
		} else {
			$from_version = $from_version ?? $model->version_number ?? $model->lastVersionNumber();
			$from = $model->changelogs()->where('version', $from_version)->first();
			if(!$from) {
				throw new \OutOfBoundsException("App starting version=$from_version doesn't exist.");
			}
		}

		$start_time = $from->fromDateTime($from->created_at);
		$end_time = $target->fromDateTime($target->created_at);
		$same = false;
		if(($from_void || $to_void) && $from->id == $target->id) {
			// Case where from = target
			$direction = 'asc';
			$same = true;
		} else {
			if($start_time < $end_time) {
				// Update
				$direction = 'asc';
			} else {
				// Regress
				$direction = 'desc';
				// Swap
				$tmp = $start_time;
				$start_time = $end_time;
				$end_time = $tmp;
			}
		}
		// Query
		$query = $model->changelogs()->withoutGlobalScope('_order')
					->whereBetween('created_at', [$start_time, $end_time])
					->orderBy('created_at', $direction)
		;
		if(!$target->is_rejected) {
			// Exclude rejected versions if the target version isn't rejected
			$query->rejected(false);
		}
		if(is_callable($query_callback)) {
			$query_callback($query);
		}
		$versions = $query->get();

		if(count($versions) > 0) {
			if($direction == 'asc') {
				// Skip the starting (assumed current) version
				/**
				 * Updating works by applying the next version, example:
				 * If updating from 1.1 to 1.7, then get changes from 1.2 to 1.7,
				 * then apply 1.2 and so on up to applying 1.7. After applying 1.7 then
				 * the version would be 1.7.
				 * Skipping does not apply when building an item from the ground up
				 */
				if($versions[0]->id == $from->id && (!$from_void || $versions[0]->version != '1')) {
					$versions->shift();
				}
				$compiled = static::compileVersionsChanges($versions, 'asc');
				$changes = $compiled;
			} else {
				// Skip the target version
				/**
				 * Regression works by undoing the current version, example:
				 * If regressing from 1.9 to 1.5, then get changes from 1.9 to 1.6,
				 * then undo 1.9 and so on up to undoing 1.6. After undoing 1.6 then
				 * the version would be 1.5.
				 * A corner case would be if the diffs array only contain 'new' changes
				 * with no references to the 'old' or original data, in which case
				 * regression attempt cannot be done. If this happens, then fallback to
				 * building the item from the ground up, i.e from absolute zero up until
				 * the target version.
				 */
				$versions->pop();
				$compiled = static::compileVersionsChanges($versions, 'desc');
				// If format is invalid, i.e *any* old values are missing,
				// then regress to zero build
				if($compiled === false) {
					// Regress
					$changes = static::getVersionsChanges($model, $to_version, 'void', $query_callback)['changes'];
				} else {
					$changes = $compiled;
				}
			}
		}

		$return['changes'] = $changes;
		$return['versions'] = $versions;
		$return['final_version'] = optional($versions->last())->version;
		$return['final_version_id'] = optional($versions->last())->id;

		return $return;
	}

	public static function compileVersionsChanges($versions, $direction = 'asc')
	{
		$changes = [];
		if(count($versions) == 0) {
			return $changes;
		}

		if($direction == 'asc') {
			foreach($versions as $v) {
				// Take special care of relations
				// Don't use recursive merge on the relations because we always want to
				// replace (not merge) each relation
				$diffs = collect($v->diffs);

				if(isset($diffs['attributes'])) {
					$attrs = $diffs->pull('attributes');
					// Merge attributes
					$new_attributes = $attrs['new'] ?? $attrs ?? [];
					$changes['attributes']['new'] = array_merge($changes['attributes']['new'] ?? [], $new_attributes);
					// Only set old values if not yet set
					$old_attributes = $attrs['old'] ?? [];
					$changes['attributes']['old'] = array_merge($old_attributes, $changes['attributes']['old'] ?? []);
				}

				if(isset($diffs['relations'])) {
					$rels = $diffs->pull('relations');
					foreach($rels as $key => $rel) {
						// $changes['relations'][$key]['new'] = array_merge($changes['relations'][$key]['new'] ?? [], $rel['new'] ?? $rel);
						// // Only set old values if not yet set
						// $changes['relations'][$key]['old'] = array_merge($rel['old'] ?? [], $changes['relations'][$key]['old'] ?? []);
						if(array_key_exists('new', $rel))
							$changes['relations'][$key]['new'] = $rel['new'];
						// Only set old values if not yet set
						if(array_key_exists('old', $rel)
							&& !array_key_exists('old', $changes['relations'][$key]))
							$changes['relations'][$key]['old'] = $rel['old'];
					}
				}
			}
		} else {
			// Try to flip new and old values
			// Some formats are invalid, so return false
			foreach($versions as $v) {
				// Take special care of relations
				// Don't use recursive merge on the relations because we always want to
				// replace (not merge) each relation
				$diffs = collect($v->diffs);

				if(isset($diffs['attributes'])) {
					$attrs = $diffs->pull('attributes');
					if(!isset($attrs['old']) || !isset($attrs['new'])) {
						return false;
					}
					// Merge attributes from old diffs
					$new_attributes = $attrs['old'];
					$changes['attributes']['new'] = array_merge($changes['attributes']['new'] ?? [], $new_attributes);
					// Set old values from new diffs
					$old_attributes = $attrs['new'];
					$changes['attributes']['old'] = array_merge($old_attributes, $changes['attributes']['old'] ?? []);
				}

				if(isset($diffs['relations'])) {
					$rels = $diffs->pull('relations');
					foreach($rels as $key => $rel) {
						if(!isset($rel['old']) || !isset($rel['new'])) {
							return false;
						}
						// $changes['relations'][$key]['new'] = array_merge($changes['relations'][$key]['new'] ?? [], $rel['old']);
						// // Set old values from new diffs
						// $changes['relations'][$key]['old'] = array_merge($rel['new'], $changes['relations'][$key]['old'] ?? []);
						if(isset($rel['new']))
							$changes['relations'][$key]['old'] = $rel['new'];
						if(isset($rel['old']))
							$changes['relations'][$key]['new'] = $rel['old'];
					}
				}
			}
		}

		return $changes;
	}

	public static function applyVersionsChanges(App $model, $to_version, $from_version = NULL, $query_callback = NULL)
	{
		if(is_array($to_version) || $to_version instanceof \ArrayAccess) {
			$versions = null;
			if($to_version[0] instanceof AppChangelog) {
				$versions = elocollect($to_version)->sortBy('id');
			} else {
				$versions = $model->changelogs()->findMany($to_version)->get()->reverse()->values();
			}
			$changes = static::compileVersionsChanges($versions);
			$compiled = [
				'changes'		=> $changes,
				'versions'		=> $versions,
				'final_version'		=> optional($versions->last())->version,
				'final_version_id'	=> optional($versions->last())->id,
			];
		} else {
			$compiled = static::getVersionsChanges($model, $to_version, $from_version, $query_callback);
			$changes = $compiled['changes'];
		}

		static::applyDiff($model, $changes, false);

		if($compiled['final_version_id']) {
			$model->version()->associate($compiled['final_version_id'])->save();
		}

		return $compiled;
	}

	public static function getPendingVersion(App $model, $is_approved = false, $associate_version = true)
	{
		if(is_array($is_approved) || $is_approved instanceof \ArrayAccess) {
			$pending_changes = $is_approved;
		} else {
			$pending_changes = !$is_approved ? $model->floating_changes : $model->approved_changes;
		}
		$compiled_changes = AppManager::compileVersionsChanges($pending_changes);

		// Mock
		$pending = App::find($model->id);
		AppManager::applyDiff($pending, $compiled_changes, true);

		if($associate_version && count($pending_changes) > 0) {
			$version = collect($pending_changes)->last();
			$pending->setRelation('version', $version);
			$pending->version_id = $version->id;
		}

		return [$model, $pending, $compiled_changes, $pending_changes];
	}

	// This skips the approval and just commits the changes immediately
	public static function verifyAndApplyChanges(App $model, $changelogs, $publish = false, $user = null) {
		if(!$user)
			$user = \App\Models\SystemUsers\Automator::instance();
		$user = optional($user);

		// Make sure all the changelogs status are not rejected
		$all_ok = $changelogs->every(function($item) {
			return $item->status != AppChangelog::STATUS_REJECTED;
		});
		if(!$all_ok) {
			return false;
		}

		// Apply the thing
		$base_version = optional($model->version ?? $model->changelogs()->oldest()->get());
		$model->is_verified = 1;

		$model->setToPublished();
		if(!$publish) {
			$model->setToPrivate();
		}

		if($model->is_reported) {
			$model->setToReported(false);
		}
		$compiled = AppManager::applyVersionsChanges($model, $changelogs);

		// Verification
		$verif = new AppVerification;
		$verif->app_id = $model->id;
		$verif->verifier_id = $user->id;
		$verif->base_changes_id = $base_version->id;
		if($publish) {
			$verif->status_id = 'published';
			$verif->concern = AppVerification::CONCERN_PUBLISH_ITEM;
		} else {
			$verif->status_id = 'applied';
			$verif->concern = AppVerification::CONCERN_COMMIT;
		}
		$result = $verif->save();

		// Attach the changelogs
		if($result)
			$verif->changelogs()->attach($changelogs->pluck('id')->all());

		// Change the changelogs' status
		foreach($changelogs as $cl) {
			$cl->is_verified = 1;
			$cl->status = AppChangelog::STATUS_COMMITTED;
			$result = $result && $cl->save();
		}

		// Change the current version
		$model->version_id = $changelogs->last()->id;
		$result = $result && $model->save();

		return $result;
	}

	public static function publishItem(App $model, $changelogs, $user = null) {

	}

	public static function scopeListQuery($query, &$view_mode, $owned = true, $user = null) {
		if(!$user)
			$user = Auth::user();

		$query->where(function($query) use($user, &$view_mode, $owned) {
			if($owned) {
				// Always able to view owned items
				$query->where('a.owner_id', $user->id);
			}

			// More scope filters
			$query->orWhere(function($query) use($user, &$view_mode, $owned) {
				if($user->can('view-all', App::class)) {
					// No scope filter, enable all
					$view_mode = 'all';
					$query->whereRaw('1');
				} elseif($user->can('view-any-in-prodi', App::class)) {
					// Only ones in the same prodi
					$view_mode = 'prodi';
					$query->where('prodi.id', $user->prodi_id);
					$query->whereNotNull('prodi.id');
				} else {
					// Only owned
					$view_mode = 'owned';
				}
			});
		});
	}

}
