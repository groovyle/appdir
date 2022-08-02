<?php

namespace App\Http\Controllers\Admin;

use App\Models\App;
use App\Models\AppVerification;
use App\Models\VerificationStatus;
use App\Models\VerifierVerificationStatus;

use App\Http\Controllers\Controller;
use App\SystemDataProviders\SystemDataBroker;

use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;

class AppVerificationController extends Controller
{

	protected $provider;

	public function __construct(SystemDataBroker $broker)
	{
		$this->middleware('auth');

		$this->provider = $broker->provider();
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
		$data = [];
		$data['verified'] = App::with('thumbnail')->withCount('thumbnail')->where('is_verified', 1)->get();
		$data['unverified'] = App::with('thumbnail')->withCount('thumbnail')->where('is_verified', 0)->get();
		// $data['apps'] = [];

		return view('admin/app_verification/index', $data);
	}

	public function review(App $app)
	{
		//
		$data = [];
		$data['app'] = $app;
		$data['vstatus'] = VerifierVerificationStatus::all()->keyBy('code');

		return view('admin/app_verification/review', $data);
	}

	public function verify(Request $request, App $app)
	{
		//

		$rules = [
			'details'		=> ['required_without:comment', 'array'],
			'details.*'		=> ['nullable', 'string', 'max:200'],
			'comment'		=> ['required', 'string', 'max:200'],
			'status'		=> ['required', new ModelExists(VerifierVerificationStatus::class)]
		];
		$validData = $request->validate($rules);

		// Begin storing entries
		DB::beginTransaction();

		$ver = new AppVerification;
		$ver->verifier_id = $request->user()->id;
		$status = $request->input('status');
		$ver->status_id = $status;
		$ver->comment = $request->input('comment');

		$details = array_filter($request->input('details'));
		$ver->details = $details;

		$result = $app->verifications()->save($ver);

		// Set a shortcut in the app model if the resulting status is approved.
		$app->is_verified = $status == 'approved';
		$result = $result && $app->save() && $app->touch();

		if(!$result) {
			DB::rollback();

			// Pass a message...?
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.app_verification.message.verify_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput();
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.app_verification.message.verify_successful'),
				'type'		=> 'success'
			]);

			return redirect()->route('admin.app_verifications.index');
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//
	}

}
