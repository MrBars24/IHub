<?php

namespace App\Modules\Files;

// Laravel
use App\FileStorage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileManager
{
	/**
	 * @var array
	 */
	protected $types = [];

	/**
	 * FileManager constructor
	 */
	public function __construct()
	{
		$this->types = config('filesystems.filetypes');
	}

	/**
	 * Stage file before permanently storing it. This can be used for previewing
	 *
	 * @param mixed $file
	 * @return array
	 * @throws \Exception
	 */
	public function stage($file)
	{
		// setup storage
		$storage = Storage::disk('local.temp');

		// mime types to extensions
		$types = $this->getFileTypes();

		// get file info
		if($file instanceof \Intervention\Image\Image) {
			$mimeType = $file->mime();
		} else {
			$mimeType = $file->getMimeType();
		}
		$filename = time() . '_' . str_random(12);

		// check point: supported mime type
		if(!isset($types[$mimeType])) {
			throw new \Exception('File with mime type "' . $mimeType . '" is not supported in this app');
		}

		// ensure file has file extension, otherwise it will be blocked
		$extParts = explode('.', $filename);
		$ext = array_pop($extParts);
		if($ext == '' || $ext == $filename) {
			$filename .= $types[$mimeType];
		}

		// store
		$path = $storage->getAdapter()->getPathPrefix();

		// via intervention make (remote url, etc)
		if($file instanceof \Intervention\Image\Image) {
			$file->save($path . '/' . $filename);
		}
		// via file upload
		else {
			$storage->put($filename, File::get($file));
		}

		// log
		$this->log($path . '/' . $filename, 'staged');
		// NOTE: use $path . '/' . $filename instaed of url('/') . '/temp/' . $filename ?.. 
		// response
		return [
			'path' => $filename,
			'full_path' => url('/') . '/temp/' . $filename,
			'size' => $file->getSize(),
			'type' => $mimeType
		];
	}

	/**
	 * Permanently store file. This can be used for on-going usage
	 *
	 * @param  \App\FileStorage  $fileStorage
	 * @param  string            $filename
	 * @return string
	 */
	public function store($fileStorage, $filename = null)
	{
		// setup storage
		$storage = Storage::disk('local.public');

		// related objects
		$object   = $fileStorage->object;
		$path     = $fileStorage->path;
		$filename = !is_null($filename) ? $filename : $object->resource;

		// get final relative and absolute file path of file
		$newPath = $object->getFilePath() . DIRECTORY_SEPARATOR . $filename;
		$filePath = $storage->getAdapter()->applyPathPrefix($newPath);

		// move file
		$storage->put($newPath, File::get($path)); // under the [storage], we're copying to [filename] the contents of the file

		// save database object
		$fileStorage->path = $filePath;
		$fileStorage->status = 'stored';
		$fileStorage->save();

		return $filename;
	}

	/**
	 * Log the file into the database
	 *
	 * @param  string  $path
	 * @param  string  $status
	 * @param  mixed   $context
	 * @return \App\FileStorage
	 */
	protected function log($path, $status, $context = null)
	{
		// clean up the full path
		$path = str_replace('\\/', '\\', $path);
		$path = str_replace('\\\\', '\\', $path);
		$path = str_replace('/\\', '\\', $path);

		$record = new FileStorage;
		$record->path = $path;
		$record->status = $status;
		if(!is_null($context)) {
			// store reference to context object for later use (eg: grabbing filesystem value)
			$record->object()->associate($context);
		}
		$record->save();
		return $record;
	}

	/**
	 * Generate a random file name
	 *
	 * @param mixed $file
	 * @return string The generated file name
	 */
	public function generateFileName($file)
	{
		// string path
		if(is_string($file)) {
			$parts = explode('.', $file);
			$ext = array_pop($parts);
		}
		// intervention
		elseif($file instanceof \Intervention\Image\Image) {

			// mime types to extensions
			$types = $this->getFileTypes();

			// get file info
			$mimeType = $file->mime();

			// check point: supported mime type
			if(!isset($types[$mimeType])) {
				throw new \Exception('File with mime type "' . $mimeType . '" is not supported in this app');
			}

			// get extension
			$ext = $types[$mimeType];
		}
		// file object
		else {
			$ext = $file->getClientOriginalExtension();
		}
		return str_random(28) . '.' . $ext;
	}

	/**
	 * Get the list of supported file types.
	 *
	 * @param mixed $tags
	 * @return array
	 */
	public function getFileTypes($tags = null)
	{
		$tags = is_array($tags) ? $tags : func_get_args();

		$flat = [];
		if(!is_null($tags) && !empty($tags)) {
			foreach($tags as $tag) {
				$flat += $this->types[$tag];
			}
		} else {
			foreach($this->types as $key => $tag) {
				$flat += $this->types[$key];
			}
		}
		return $flat;
	}
}
