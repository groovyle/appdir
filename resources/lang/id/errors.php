<?php

return [

'error'					=> 'Error',

'generic_title'			=> 'An Error Occurred',
'generic_description'	=> 'Maaf, permintaan Anda tidak dapat diproses karena terjadi error.',

'error_persists_info'	=> 'Jika error ini terjadi berulang kali, silakan hubungi admin dengan menjelaskan langkah apa saja yang Anda lakukan sebelum error terjadi',

// 401, unauthorized
'unauthorized_title'		=> 'Unauthorized',
'unauthorized_description'	=> 'Kredensial Anda tidak sesuai.',

// 403, forbidden
'forbidden_title'			=> 'Forbidden',
'forbidden_description'		=> 'Anda tidak diperbolehkan melakukan aksi tersebut.',

// 404, not found
'not_found_title'			=> 'Not Found',
'not_found_description'		=> 'Ups! Halaman yang Anda tuju tidak ditemukan di situs ini.',

// 419, page expired (typically csrf mismatch)
'page_expired_title'		=> 'Page Expired',
'page_expired_description'	=> 'Halaman yang Anda tuju telah kadaluarsa. Silakan muat ulang halaman tersebut.',

// 429, too many requests
'too_many_requests_title'		=> 'Too Many Requests',
'too_many_requests_description'	=> 'Permintaan Anda terlalu banyak. Silakan coba lagi nanti.',

// 500, internal server error
'internal_server_error_title'		=> 'Internal Server Error',
'internal_server_error_description'	=> 'Maaf, terjadi error internal di server sehingga permintaan Anda tidak dapat diproses. Silakan hubungi admin mengenai error ini.',

// 503, service unavailable
'service_unavailable_title'			=> 'Service Unavailable',
'service_unavailable_description'	=> 'Server sedang tidak bisa memproses permintaan Anda saat ini. Silakan coba beberapa saat lagi.',

];
