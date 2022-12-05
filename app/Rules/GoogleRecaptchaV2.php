<?php

namespace App\Rules;

use TimeHunter\LaravelGoogleReCaptchaV2\Validations\GoogleReCaptchaV2ValidationRule;

class GoogleRecaptchaV2 extends GoogleReCaptchaV2ValidationRule {

	protected $attribute;

	public function passes($attribute, $value)
	{
		//
		if(!$this->attribute) {
			$this->attribute = $attribute;
		}

		return call_user_func_array('parent::passes', func_get_args());
	}

	/**
	 * Note: overriding to allow translation
	 *
	 * @return string
	 */
	public function message()
	{
		return lang_or_raw($this->message, 'recaptcha.', [
			'attribute'	=> lang_or_raw($this->attribute, 'validation.attributes.'),
		]);
	}
}
