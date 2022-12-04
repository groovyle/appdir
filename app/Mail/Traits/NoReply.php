<?php

namespace App\Mail\Traits;

trait NoReply {

	public function fromNoReply() {
		$from = config('mail.from_noreply');
		$this->from($from['address'] ?? $from, $from['name'] ?? null);
		$this->replyTo = [];
		return $this;
	}

}
