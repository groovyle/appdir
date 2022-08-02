<?php

namespace App\Models\Concerns;

trait HasFilteredAttributes {

	public function getAttributesExcept($except = [])
	{
		return $this->getAttributesByKeys($except, false);
	}

	public function getAttributesOnly($only = [])
	{
		return $this->getAttributesByKeys($only, true);
	}

	protected function getAttributesByKeys($filter = [], $positive = true)
	{
		$attributes = [];
		foreach ($this->getAttributes() as $key => $value) {
			if ( in_array($key, $filter) XOR $positive ) {
				continue;
			}
			$attributes[$key] = $value;
		}

		return $attributes;
	}

	// From Illuminate\Database\Eloquent\Model::getDirty()
	public function getDirtyExcept($except = [])
	{
		return $this->getDirtyByKeys($except, false);
	}

	// From Illuminate\Database\Eloquent\Model::getDirty()
	public function getDirtyOnly($only = [])
	{
		return $this->getDirtyByKeys($only, true);
	}

	protected function getDirtyByKeys($filter = [], $positive = true)
	{
		$dirty = [];
		foreach ($this->getAttributes() as $key => $value) {
			if ( in_array($key, $filter) XOR $positive ) {
				continue;
			}
			if (! $this->originalIsEquivalent($key, $value)) {
				$dirty[$key] = $value;
			}
		}

		return $dirty;
	}

	// From Illuminate\Database\Eloquent\Model::getChanges()
	public function getChangesExcept($except = [])
	{
		return $this->getChangesByKeys($except, false);
	}

	// From Illuminate\Database\Eloquent\Model::getChanges()
	public function getChangesOnly($only = [])
	{
		return $this->getChangesByKeys($only, true);
	}

	protected function getChangesByKeys($filter = [], $positive = true)
	{
		$changes = [];
		foreach ($this->getChanges() as $key => $value) {
			if ( in_array($key, $filter) XOR $positive ) {
				continue;
			}
			$changes[$key] = $value;
		}

		return $changes;
	}

}