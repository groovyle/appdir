<?php

namespace App\Mail;

use Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
	use Queueable, SerializesModels;
	use Traits\NoReply;

	public $user;
	public $verification_url;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($user, $verification_url)
	{
		//
		$this->user = $user ?: Auth::user();
		$this->verification_url = $verification_url;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$this->fromNoReply();

		return $this->markdown('emails.verify-account');
	}
}
