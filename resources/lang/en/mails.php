<?php

return [

'salutation'	=> "Regards, \n".app_name(),

'verify_account'	=> [
	'subject'		=> 'Verify Your Email Address - '.app_name(),
	'greeting'		=> 'Hi :user,',
	'intro'			=> "Thank you for registering an account at ".app_name().". \nYour new account's status is inactive. To activate your account, please verify this email address using the following link:",
	'action'		=> 'Verify Email Address',
	'outro'			=> "After your account is activated, you will be able to fully use all the available features on our website. \nIf you did not register an account, just ignore this email.",
],

];
