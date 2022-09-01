<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AppChangelogCollection extends Collection
{

	public function __construct($items = []) {
		parent::__construct($items);
	}

	public function rangeArray($reversed = false) {
		$range = [];
		$keys = array_keys($this->items);
		for($i = 0, $j = 0; $i < count($keys); $i++) {
			$start = $this->getItemVersion($keys[$i]);
			$range[$j] = new AppChangelogRange($start);
			$models = [];
			$break = false;
			for($current = $start; $i < count($keys); $i++, $current++) {
				if($current == $this->getItemVersion($keys[$i])) {
					$range[$j]->setEnd($current);
					if(($model = $this->items[$keys[$i]]) instanceof AppChangelog) {
						$models[] = $model;
					}
				} else {
					$break = true;
					break;
				}
			}
			if($models) {
				$range[$j]->setItems($models);
			}
			$i--;

			if($break) {
				$j++;
			}
		}

		return $range;
	}

	public function rangeText($separator = ', ') {
		return implode($separator, $this->rangeArray());
	}

	public function getItemVersion($offset) {
		return $this->items[$offset] instanceof AppChangelog
			? $this->items[$offset]->version
			: $this->items[$offset]
		;
	}

	public function __toString() {
		return $this->rangeText();
	}

}