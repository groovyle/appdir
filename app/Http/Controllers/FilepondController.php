<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use RahulHaque\Filepond\Services\FilepondService;
use RahulHaque\Filepond\Http\Controllers\FilepondController as ReferenceController;

class FilepondController extends Controller
{

	protected $disk;
	protected $tempDisk;
	protected $tempFolder;

	public function __construct()
	{
		$this->disk = config('filepond.disk', 'public');
		$this->tempDisk = config('filepond.temp_disk', 'local');
		$this->tempFolder = config('filepond.temp_folder', 'filepond/temp');
	}

	// An extension class to ReferenceController to cover more use cases

	/**
	 * FilePond ./restore route logic.
	 *
	 * @param  Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function restore(Request $request, FilepondService $service, $id)
	{
		$filepond = $service->retrieve($id);
		$path = Storage::disk($this->tempDisk)->path($filepond['filepath']);

		return response()->file($path, [
			'Content-Disposition'	=> 'inline; filename='.$filepond['filename'],
		]);
	}
}
