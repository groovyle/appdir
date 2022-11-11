<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestErrorPagesController extends Controller
{
	//

	public function page(Request $request, $code = null) {

		$data = [
			// Mock exception
			'exception'	=> new \RuntimeException,
		];

		if($code && view()->exists('errors.'.$code)) {
			return view('errors.'.$code, [
				'exception'	=> new HttpException($code),
			]);
		}

		return view('errors.generic', $data);
	}

}
