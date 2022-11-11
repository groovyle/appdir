<?php

return [

'error'					=> 'Error',

'generic_title'			=> 'An Error Occurred',
'generic_description'	=> 'Sorry, your request cannot be processed because of an error.',

'error_persists_info'	=> 'If this error persists, please contact admininstrators with an explanation of what things you did before encountering this error',

// 401, unauthorized
'unauthorized_title'		=> 'Unauthorized',
'unauthorized_description'	=> 'Credentials mismatched.',

// 403, forbidden
'forbidden_title'			=> 'Forbidden',
'forbidden_description'		=> 'You are not allowed to do the action you requested.',

// 404, not found
'not_found_title'			=> 'Not Found',
'not_found_description'		=> 'Oops! The page you requested cannot be found.',

// 419, page expired (typically csrf mismatch)
'page_expired_title'		=> 'Page Expired',
'page_expired_description'	=> 'The page you requested has expired. Please reload said page.',

// 429, too many requests
'too_many_requests_title'		=> 'Too Many Requests',
'too_many_requests_description'	=> 'You requested too many times. Please try again later.',

// 500, internal server error
'internal_server_error_title'		=> 'Internal Server Error',
'internal_server_error_description'	=> 'Aww, scuffed server! Internal error occurred on the server such that your request couldn\'t be processed. Please contact administrators about this error.',

// 503, service unavailable
'service_unavailable_title'			=> 'Service Unavailable',
'service_unavailable_description'	=> 'Currently the server is not capable of processing requests. Please check again later.',

];
