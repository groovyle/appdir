<?php

return [

	'type'			=> env('DATA_PROVIDER', 'virtualmin'),

	'verify_peer'	=> env('DATA_PROVIDER_VERIFY_PEER', TRUE),

	'virtualmin' => [
		'base_url'		=> env('VIRTUALMIN_BASE_URL', ''),
		'username'		=> env('VIRTUALMIN_USERNAME', ''),
		'password'		=> env('VIRTUALMIN_PASSWORD', ''),
	],

];
