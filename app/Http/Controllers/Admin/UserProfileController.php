<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\Prodi;
use App\User;

use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use RahulHaque\Filepond\Facades\Filepond;
use Gumlet\ImageResize;

class UserProfileController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');

		// There should be no authorization required here, because it's the user's
		// own, customized page.
	}

	public function show()
	{
		$user = Auth::user();

		$user->loadCount('apps');

		$data = [
			'user'	=> $user,
		];

		return view('admin/user_profile/index', $data);
	}

	public function editProfile()
	{
		//
		$user = $model = Auth::user();
		$back_url = route('admin.profile.index');
		$data = [
			'model'		=> $model,
			'action'	=> route('admin.profile.edit.save'),
			'method'	=> 'PATCH',
			'user'		=> $user,
			'back'		=> $back_url,
		];

		return view('admin/user_profile/edit', $data);
	}

	// PATCH
	public function updateProfile(Request $request)
	{
		$user = Auth::user();

		// Begin storing entries
		DB::beginTransaction();

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'name'			=> ['required', 'max:100'],
		];

		$validData = $request->validate($rules);

		$result = true;
		$messages = [];

		// Begin storing entries
		try {
			$user->name			= $request->input('name');

			$result = $user->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($messages);
		} else {
			DB::commit();

			// Pass a message
			if($user->wasChanged()) {
				$request->session()->flash('flash_message', [
					'message'	=> __('admin/profile.messages.update_successful'),
					'type'		=> 'success'
				]);
			}

			return redirect()->route('admin.profile.index');
		}
	}

	public function editPicture()
	{
		//
		$user = $model = Auth::user();
		$back_url = route('admin.profile.index');
		$data;
		$data = [
			'model'		=> $model,
			'action'	=> route('admin.profile.picture.save'),
			'method'	=> 'POST',
			'user'		=> $user,
			'back'		=> $back_url,
		];

		return view('admin/user_profile/picture', $data);
	}

	// POST
	public function updatePicture(Request $request)
	{
		$user = Auth::user();

		// dd($request->all());

		// Begin storing entries
		DB::beginTransaction();

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'pic_todo'			=> ['nullable'],
			'new_pic'			=> ['nullable'],
		];
		$pic_todo = $request->input('pic_todo');
		$input_new_pic = $request->input('new_pic');

		$validData = $request->validate($rules);

		if($pic_todo == 'change') {
			// Validate files
			$validFiles = Filepond::field($input_new_pic)->validate([
				'new_pic'	=> ['nullable', 'image', 'max:2048'],
			]);
		}

		$result = true;
		$messages = [];
		$uploaded_files = [];
		$delete_files = [];

		// Begin storing entries
		try {

			$save = false;

			// Make sure storage dir exists
			$storage = Storage::disk('public');
			$storage_rel_path = $user->getStorageDir();
			$storage_path = $storage->path($storage_rel_path);
			if(!$storage->has($storage_rel_path)) {
				$storage->createDir($storage_rel_path);
			}

			$new_pic_file = Filepond::field($input_new_pic)->getFile();
			if($pic_todo == 'change' && $new_pic_file) {
				// Resize image and convert to jpg
				$small_resize = settings('user.profile.picture_small_size');
				list($small_maxw, $small_maxh) = $small_resize;
				$small_resize = $small_maxw && $small_maxh;

				// Try to process the image anyway to potentially remove malicious codes
				// inside the file.
				$img = new ImageResize($new_pic_file->getPathname());

				// Set filename, random filename to avoid cached images not updated
				// by the browser
				$tmpname = $new_pic_file->hashName();
				$barename = pathinfo($tmpname, PATHINFO_FILENAME);
				$fname = $barename.'.jpg';
				$fpath = $storage_path.$fname;
				$frelpath = $storage_rel_path.$fname;

				$new_pic_result = true;
				try {
					// Only store rescaled version if not small
					if(!$small_resize
						|| ($img->getSourceWidth() <= $small_maxw
							&& $img->getSourceHeight() <= $small_maxh)
					) {
						// Original, no resize
						$img->scale(100);
					} else {
						// The scaled down image
						$img->crop($small_maxw, $small_maxh);
					}
					$img->save($fpath, IMAGETYPE_JPEG);
					$uploaded_files[] = $fpath;
				} catch(\Exception $e) {
					$new_pic_result = false;
					$error[] = __('admin/common.messages.failed_processing_image_upload', ['x' => $new_pic_file->getClientOriginalName()]);
				}

				// Delete old image
				if($user->pictureExists()) {
					$delete_files[] = $storage->path($user->picture);
				}

				if($new_pic_result) {
					// $user->picture = $fname;
					$user->picture = $frelpath;

					$save = true;
				}

				$result = $result && $new_pic_result && $storage->exists($frelpath);
			}

			if($pic_todo == 'remove' && $user->pictureExists()) {
				// Direct delete?
				// Storage::disk('public')->delete( $user->picture );
				// $result = $result && !$storage->exists($user->picture);

				// Delayed delete?
				$delete_files[] = $storage->path($user->picture);

				$user->picture = null;
				$save = true;
			}

			if($result && $save) {
				$result = $result && $user->save();
			}

			// Delete temp files
			if($result && $input_new_pic) {
				Filepond::field($input_new_pic)->delete();
			}

			if($result) {
				foreach($delete_files as $fpath) {
					if(file_exists($fpath)) {
						@unlink($fpath);
					}
				}
			} else {
				// Delete uploaded files
				foreach($uploaded_files as $fpath) {
					if(file_exists($fpath)) {
						@unlink($fpath);
					}
				}
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($messages);
		} else {
			DB::commit();

			if($user->wasChanged()) {
				// Pass a message
				$request->session()->flash('flash_message', [
					'message'	=> __('admin/profile.messages.update_successful'),
					'type'		=> 'success'
				]);
			}

			return redirect()->route('admin.profile.index');
		}
	}

	public function editPassword()
	{
		//
		$user = $model = Auth::user();
		$back_url = route('admin.profile.index');
		$data = [
			'model'		=> $model,
			'action'	=> route('admin.profile.password.save'),
			'method'	=> 'PATCH',
			'user'		=> $user,
			'back'		=> $back_url,
		];

		return view('admin/user_profile/password', $data);
	}

	// PATCH
	public function updatePassword(Request $request)
	{
		$user = Auth::user();

		// Begin storing entries
		DB::beginTransaction();

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'old_password'		=> ['required', 'password'],
			'new_password'		=> ['required', 'bail', 'min:5', 'max:50', 'confirmed'],
		];

		$validData = $request->validate($rules);

		$result = true;
		$messages = [];

		// Begin storing entries
		try {
			$user->password		= Hash::make($request->input('new_password'));

			$result = $user->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($messages);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/profile.messages.password_changed'),
				'type'		=> 'success'
			]);

			// TODO: do we log the user out?
			return redirect()->route('admin.profile.index');
		}
	}

}
