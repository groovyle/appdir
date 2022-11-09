<?php

namespace App\Models\Concerns;

use App\Models\LogAction;
use Illuminate\Support\Facades\Auth;

trait LoggedActions {

	use HasSoftDeletesCheck;

	/**
	 * Indicates if all the actions are logged in a log table.
	 *
	 * @var bool
	 */
	public static $globalActionsLogged = true;

	/**
	 * Indicates if all the actions are logged in a log table.
	 *
	 * @var bool
	 */
	public $actionsLogged = true;

	/**
	 * A flag of whether to log the next action, or to log it yourself.
	 *
	 * @var bool
	 */
	public $nextActionLogged = true;

	/**
	 * Container for passing data between event handlers.
	 *
	 * @var bool
	 */
	protected $loggedActionsTransientData = NULL;

	/**
	 * Who the actor would be for all logged actions. Null value
	 * defaults to the currently logged in user.
	 *
	 * @var null|int|App\User
	 */
	public $actionsActor = null;

	/**
	 * Who the actor would be for the *next* logged actions. Null value
	 * defaults to the currently logged in user.
	 *
	 * @var null|int|App\User
	 */
	public $nextActionActor = null;

	/**
	 * Boot this trait.
	 *
	 * @return void
	 */
	public static function bootLoggedActions()
	{
		// Boot
		static::registerModelEvent('created', function($model) {
			return $model->logCreated($model);
		});
		static::registerModelEvent('updating', function($model) {
			return $model->logUpdating($model);
		});
		static::registerModelEvent('updated', function($model) {
			return $model->logUpdated($model);
		});
		static::registerModelEvent('deleted', function($model) {
			return $model->logDeleted($model);
		});
		static::registerModelEvent('forceDeleted', function($model) {
			return $model->logForceDeleted($model);
		});
		static::registerModelEvent('restored', function($model) {
			return $model->logRestored($model);
		});
	}

	/**
	 * Initialize (i.e, construct) this trait for an instance.
	 *
	 * @return void
	 */
	public function initalizeLoggedActions()
	{
		// Init
	}

	public static function globalToggle($state = true)
	{
		static::$globalActionsLogged = $state;
	}


	/**
	 * Do stuff post-create, like logging.
	 *
	 * @return void
	 */
	protected function logCreated($model)
	{
		$model->logAction($model, 'create');
	}

	/**
	 * Do stuff pre-update, like logging.
	 *
	 * @return void
	 */
	protected function logUpdating($model)
	{
		// NOTE: don't use isDirty() or getDirty() since we need to hide fields
		// hidden with $this->hidden[]
		if($model->isNonHiddenDirty()) {
			$model->loggedActionsSetTransientData($model->getNonHiddenDirty());
			// $model->logAction($model, 'updating', $model->getNonHiddenDirty());
		}
	}

	/**
	 * Do stuff post-update, like logging.
	 *
	 * @return void
	 */
	protected function logUpdated($model)
	{
		$data = $model->loggedActionsGetTransientData();
		if(!empty($data)) {
			$model->logAction($model, 'update', null, $data);
			// $model->logAction($model, 'update', $model->getNonHiddenDirty());
		}
	}

	/**
	 * Do stuff post-delete, like logging.
	 *
	 * @return void
	 */
	protected function logDeleted($model)
	{
		// Has to check whether the ID still exists
		if( ($id = $model->getKey()) ) {
			$model->logAction($model, 'delete');
		}
	}

	/**
	 * Do stuff post-force delete, like logging.
	 *
	 * @return void
	 */
	protected function logForceDeleted($model)
	{
		// Has to check whether the ID still exists
		if( $model->usesSoftDeletes() && ($id = $model->getKey()) ) {
			$model->logAction($model, 'force delete');
		}
	}

	/**
	 * Do stuff post-restore, like logging.
	 *
	 * @return void
	 */
	protected function logRestored($model)
	{
		if($model->usesSoftDeletes()) {
			$model->logAction($model, 'restore');
		}
	}

	/**
	 * Set transient data to be fetched later.
	 *
	 * @return void
	 */
	protected function loggedActionsSetTransientData($data)
	{
		$this->loggedActionsTransientData = $data;
	}

	/**
	 * Get/fetch transient data and set it to null.
	 *
	 * @return mixed
	 */
	protected function loggedActionsGetTransientData()
	{
		$data = $this->loggedActionsTransientData;
		$this->loggedActionsTransientData = NULL;
		return $data;
	}

	public function getNonHiddenDirty() {
		return collect($this->getDirty())->except($this->hidden)->all();
	}

	// SEE: Illuminate\Database\Eloquent\Concerns\HasAttributes::isDirty()
	public function isNonHiddenDirty($attributes = null) {
		return $this->hasChanges(
			$this->getNonHiddenDirty(), is_array($attributes) ? $attributes : func_get_args()
		);
	}

	/**
	 * Determine if model actions are logged.
	 *
	 * @return bool
	 */
	public function actionsLogged()
	{
		return config('database.action_logging')
			&& static::$globalActionsLogged
			&& $this->actionsLogged
		;
	}

	/**
	 * Set to not log at all.
	 *
	 * @return void
	 */
	public function dontLog()
	{
		$this->actionsLogged = false;
		return $this;
	}

	/**
	 * Determine if the next action is to be logged automatically.
	 *
	 * @return bool
	 */
	public function nextActionLogged()
	{
		return $this->nextActionLogged;
	}

	/**
	 * Set the *next* action is to *not* be logged automatically.
	 *
	 * @return bool
	 */
	public function dontLogNextAction()
	{
		$this->nextActionLogged = false;
		return $this;
	}

	/**
	 * Set the *next* action is to be logged automatically.
	 *
	 * @return bool
	 */
	public function logNextAction()
	{
		$this->nextActionLogged = true;
		return $this;
	}

	/**
	 * Set all actions' actor.
	 *
	 * @return $this
	 */
	public function setActionsActor($actor)
	{
		$this->actionsActor = $actor;
		return $this;
	}

	/**
	 * Set the *next* action's actor.
	 *
	 * @return $this
	 */
	public function setNextActionActor($actor)
	{
		$this->nextActionActor = $actor;
		return $this;
	}

	/**
	 * Get the *next* action's actor.
	 *
	 * @return bool
	 */
	public function getNextActionActor()
	{
		if(isset($this->nextActionActor)) {
			$actor = $this->nextActionActor;
			$this->nextActionActor = null;
		} else {
			$actor = $this->actionsActor;
		}

		/*if($actor instanceof \App\Models\SystemUsers\Base) {
			$actor = (string) $actor;
		} else*/if(is_object($actor)) {
			$actor = $actor->getKey();
		} elseif($actor === null) {
			$actor = Auth::id();
		}

		return $actor;
	}

	public function logAction($model, $action, $actor = null)
	{
		if(!$model->actionsLogged())
			return;

		if(!$model->nextActionLogged()) {
			$model->logNextAction();
			return;
		}

		if(is_callable($action)) {
			call_user_func_array($action, [ $model ]);
			return;
		}

		if($actor === null) {
			$actor = $this->getNextActionActor();
		}
		if(is_array($action)) {
			$params = array_merge([ $model ], $action, [ $actor ]);
		} else {
			$params = func_get_args();
			$params[2] = $actor;
		}
		call_user_func_array([LogAction::class, 'logModel'], $params);
	}

	/**
	 * Relationship to access the model's action logs
	 *
	 * @return LogAction
	 */
	public function actions_log()
	{
		return $this->morphMany('App\Models\LogAction', 'entity');
	}

}
