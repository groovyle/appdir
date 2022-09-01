<?php

namespace App\Models;

class AppChangelogRange implements \ArrayAccess
{

	public $items = null;
	public $models = null;
	protected $start;
	protected $end;
	protected $separator;

	public function __construct($start, $end = null, $separator = '-') {
		if(is_array($start) || $start instanceof ArrayAccess) {
			$this->setItems($start);
		} else {
			$this->start = $start;
			$this->end = $end;
		}

		$this->separator = $separator;
	}

	public function getStart() {
		return $this->start;
	}

	public function setStart($value) {
		$this->start = $value;
	}

	public function getEnd() {
		return $this->end;
	}

	public function setEnd($value) {
		$this->end = $value;
	}

	public function getSeparator() {
		return $this->separator;
	}

	public function setSeparator($value) {
		$this->separator = $value;
	}

	public function setItems(array $items) {
		if($items[0] instanceof AppChangelog) {
			$items = elocollect($items)->sortBy('created_at');
			$this->items = $items->pluck('version')->all();
			$this->models = $items->all();
		} else {
			$this->items = $items;
			$this->models = null;
			sort($this->items, SORT_NUMERIC);
		}
		$this->start = head($this->items);
		$this->end = last($this->items);
	}

	public function toArray() {
		return [$this->start, $this->end];
	}

	public function __toString() {
		return $this->start == $this->end
			? (string) $this->start
			: $this->start . $this->separator . $this->end
		;
	}

	// ArrayAccess
	public function offsetExists($offset) {
		return $offset == 0 || $offset == 1;
	}
	public function offsetGet($offset) {
		if($offset == 0)
			return $this->start;
		elseif($offset == 1)
			return $this->end;
	}
	public function offsetSet($offset,  $value) {
		if($offset == 0)
			$this->start = $value;
		elseif($offset == 1)
			$this->end = $value;
	}
	public function offsetUnset($offset) {
		$this->offsetSet($offset, null);
	}
	// END ArrayAccess

}