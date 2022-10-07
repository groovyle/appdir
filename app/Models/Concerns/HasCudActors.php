<?php

/**
 * Based on:
 * - Illuminate\Database\Eloquent\Concerns\HasTimestamps
 * - Illuminate\Database\Eloquent\SoftDeletes
 */

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;
use App\Models\LogAction;
use Illuminate\Database\Eloquent\SoftDeletes;

// CUD: Create, Update, Delete
trait HasCudActors {

	use HasSoftDeletesCheck,
		LoggedActions;

	/**
	 * Indicates if model actions should be tracked.
	 *
	 * @var bool
	 */
	public $cudActors = true;

	/**
	 * Indicates if creators, i.e 'create' actions should be tracked.
	 *
	 * @var bool
	 */
	public $cudCreators = true;

	/**
	 * Indicates if updaters, i.e 'update' actions should be tracked.
	 *
	 * @var bool
	 */
	public $cudUpdaters = true;

	/**
	 * Indicates if deleters, i.e 'delete' actions should be tracked.
	 *
	 * @var bool
	 */
	public $cudDeleters = true;

	/**
	 * Boot this trait.
	 *
	 * @return void
	 */
	public static function bootHasCudActors()
	{
		// Boot

		static::registerModelEvent('creating', function($model) {
			return $model->creatorPreSave($model);
		});

		static::registerModelEvent('updating', function($model) {
			return $model->updaterPreSave($model);
		});

		static::registerModelEvent('deleting', function($model) {
			return $model->deleterPreSave($model);
		});

		static::registerModelEvent('restoring', function($model) {
			return $model->restorerPreSave($model);
		});
	}

	/**
	 * Initialize (i.e, construct) this trait for an instance.
	 *
	 * @return void
	 */
	public function initalizeHasCudActors()
	{
		// Init
	}

	/**
	 * Update the model's updater id.
	 *
	 * @return bool
	 */
	public function touch()
	{
		if (! $this->updatersTracked()) {
			return false;
		}

		$this->updateUpdateActors();

		return $this->save();
	}

	/**
	 * Update the actor who updated the model.
	 *
	 * @return void
	 */
	protected function updateUpdateActors()
	{
		$user_id = $this->getCudActorId();
		$updatedByColumn = $this->getUpdatedByColumn();

		if (! is_null($updatedByColumn) && ! $this->isDirty($updatedByColumn)) {
			$this->setUpdatedBy($user_id);
		}

		$createdByColumn = $this->getCreatedByColumn();

		if (! $this->exists && ! is_null($createdByColumn) && ! $this->isDirty($createdByColumn)) {
			$this->setCreatedBy($user_id);
		}
	}

	/**
	 * Set the actor who created the model.
	 *
	 * @return void
	 */
	protected function _creatorPreSave($model)
	{
		$user_id = $model->getCudActorId();
		$createdByColumn = $model->getCreatedByColumn();

		if (! $this->exists && ! is_null($createdByColumn) && ! $this->isDirty($createdByColumn)) {
			$model->setCreatedBy($user_id);
		}
	}

	protected function creatorPreSave($model) {
		// Due to how Laravel timestamp marking works, upon creation the updated_at
		// is also updated, and vice versa.
		if($model->creatorsTracked()) {
			$model->_creatorPreSave($model);
			$model->_updaterPreSave($model);
		}
	}

	/**
	 * Set the actor who updated the model.
	 *
	 * @return void
	 */
	protected function _updaterPreSave($model)
	{
		$user_id = $model->getCudActorId();
		$updatedByColumn = $model->getUpdatedByColumn();

		if (! is_null($updatedByColumn) && ! $this->isDirty($updatedByColumn)) {
			$model->setUpdatedBy($user_id);
		}
	}

	protected function updaterPreSave($model)
	{
		// Due to how Laravel timestamp marking works, upon creation the updated_at
		// is also updated, and vice versa.
		if($model->updatersTracked() && $model->isDirty()) {
			$model->_updaterPreSave($model);
			// $model->_creatorPreSave($model);
		}
	}

	/**
	 * Set the actor who deleted the model.
	 *
	 * @return void
	 */
	protected function deleterPreSave($model)
	{
		if($model->deletersTracked()) {
			$user_id = $model->getCudActorId();
			$deletedByColumn = $model->getDeletedByColumn();

			if (! is_null($deletedByColumn) ) {
				$model->setDeletedBy($user_id);
			}
		}
	}

	/**
	 * Override the SoftDeletes delete query to include our column.
	 * Use the following when using SoftDeletes in a class:
		use SoftDeletes, Concerns\HasCudActors {
			Concerns\HasCudActors::runSoftDelete insteadof SoftDeletes;
		}
	 *
	 * @see Illuminate\Database\Eloquent\SoftDeletes::runSoftDelete()
	 * @return void
	 */
	protected function runSoftDelete()
	{
		$query = $this->setKeysForSaveQuery($this->newModelQuery());

		$time = $this->freshTimestamp();

		$columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

		$this->{$this->getDeletedAtColumn()} = $time;

		// addition
		if($this->deletersTracked()) {
			$user_id = $this->getCudActorId();
			$deletedByColumn = $this->getDeletedByColumn();

			if (! is_null($deletedByColumn) ) {
				$this->{$this->getDeletedByColumn()} = $user_id;
				$columns[$this->getDeletedByColumn()] = $user_id;
			}
		}
		// end addition

		if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
			$this->{$this->getUpdatedAtColumn()} = $time;

			$columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
		}

		$query->update($columns);

		$this->syncOriginalAttributes(array_keys($columns));
	}

	/**
	 * Set the actor who restored the model.
	 *
	 * @return void
	 */
	protected function restorerPreSave($model)
	{
		if($model->deletersTracked() && $model->usesSoftDeletes()) {
			$user_id = $model->getCudActorId();
			$deletedByColumn = $model->getDeletedByColumn();

			if (! is_null($deletedByColumn) ) {
				$model->setDeletedBy(null);
			}
		}
	}

	/**
	 * Set the value of the "created by" attribute.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setCreatedBy($value)
	{
		$this->{$this->getCreatedByColumn()} = $value;

		return $this;
	}

	/**
	 * Set the value of the "updated by" attribute.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setUpdatedBy($value)
	{
		$this->{$this->getUpdatedByColumn()} = $value;

		return $this;
	}

	/**
	 * Set the value of the "deleted by" attribute.
	 *
	 * @param  mixed  $value
	 * @return $this
	 */
	public function setDeletedBy($value)
	{
		$this->{$this->getDeletedByColumn()} = $value;

		return $this;
	}

	/**
	 * Get the currently authenticated user (i.e the actor) id.
	 *
	 * @return int|null
	 */
	protected function getCudActorId()
	{
		return Auth::id();
	}

	/**
	 * Determine if model actions are tracked.
	 *
	 * @return bool
	 */
	public function actorsTracked()
	{
		return $this->cudActors;
	}

	/**
	 * Determine if creators are tracked.
	 *
	 * @return bool
	 */
	public function creatorsTracked()
	{
		return $this->actorsTracked() && $this->cudCreators;
	}

	/**
	 * Determine if updaters are tracked.
	 *
	 * @return bool
	 */
	public function updatersTracked()
	{
		return $this->actorsTracked() && $this->cudUpdaters;
	}

	/**
	 * Determine if deleters are tracked.
	 *
	 * @return bool
	 */
	public function deletersTracked()
	{
		return $this->actorsTracked() && $this->cudDeleters;
	}

	/**
	 * Get the name of the "created by" column.
	 *
	 * @return string
	 */
	public function getCreatedByColumn()
	{
		return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
	}

	/**
	 * Get the name of the "updated by" column.
	 *
	 * @return string
	 */
	public function getUpdatedByColumn()
	{
		return defined('static::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
	}

	/**
	 * Get the name of the "deleted by" column.
	 *
	 * @return string
	 */
	public function getDeletedByColumn()
	{
		return defined('static::DELETED_BY') ? static::DELETED_BY : 'deleted_by';
	}

	/**
	 * Get the fully qualified "created by" column.
	 *
	 * @return string
	 */
	public function getQualifiedCreatedByColumn()
	{
		return $this->qualifyColumn($this->getCreatedByColumn());
	}

	/**
	 * Get the fully qualified "updated by" column.
	 *
	 * @return string
	 */
	public function getQualifiedUpdatedByColumn()
	{
		return $this->qualifyColumn($this->getUpdatedByColumn());
	}

	/**
	 * Get the fully qualified "deleted by" column.
	 *
	 * @return string
	 */
	public function getQualifiedDeletedByColumn()
	{
		return $this->qualifyColumn($this->getDeletedByColumn());
	}


	// Relationships
	public function createdBy() {
		return $this->belongsTo('App\User', $this->getCreatedByColumn());
	}

	public function updatedBy() {
		return $this->belongsTo('App\User', $this->getUpdatedByColumn());
	}

	public function deletedBy() {
		return $this->belongsTo('App\User', $this->getDeletedByColumn());
	}

}
