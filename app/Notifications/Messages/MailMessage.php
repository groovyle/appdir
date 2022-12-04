<?php

namespace App\Notifications\Messages;

use Illuminate\Notifications\Messages\MailMessage as MailMessageBase;

class MailMessage extends MailMessageBase {

	public function fromNoReply() {
		$from = config('mail.from_noreply');
		$this->from($from['address'] ?? $from, $from['name'] ?? null);
		$this->replyTo = [];
		return $this;
	}

}
