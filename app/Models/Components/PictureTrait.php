<?php

namespace App\Components;

use App\Modules\Files\FileManager;
use App\FileStorage;

// Laravel
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

// 3rd Party
use Image;
trait PictureTrait
{
	/**
	 * TODO: configure or add the profile_picture_web_path and  
	 * cover_picture_web_path in the $visible and $appends attributes of model
	 */

	/// Section: Methods

	/**
	 * store and move the file
	 *
	 * @param  string           $filename
	 * @return \App\FileStorage $fileStorage          
	 */
	public function storeFile($filename)
	{
		// get file object
		$fileStorage = FileStorage::query()
			->whereNull('object_id')
			->whereNull('object_type')
			->where('path', 'like', '%' . $filename)
			->where('status', '=', 'staged')
			->first();

		// store object against file
		$fileStorage->object()->associate($this);

		// move file
		app(FileManager::class)->store($fileStorage, $filename);
		return $filename;
	}

	/**
	 * crop the stored file
	 *
	 * @param  string           $filename
	 * @param  string           $crop_settings
	 * @return \App\FileStorage $fileStorage
	 */
	public function cropFile($filename, $crop_settings)
	{
		// get file object
		$fileStorage = FileStorage::query()
			->where('path', 'like', '%' . $filename)
			->where('status', '=', 'stored')
			->first();

		// convert crop_settings to object
		if (is_string($crop_settings)) {
			$crop_settings = json_decode($crop_settings);
		}

		$image = Image::make($fileStorage->path);
		
		// crop
		// - crop settings should based on the original image.
		$image = $image->crop(
			intval($crop_settings->width),
			intval($crop_settings->height),
			intval($crop_settings->x),
			intval($crop_settings->y)
		);

		// move
		$storage = Storage::disk('local.public');
		$path = $storage->getAdapter()->getPathPrefix($this->filesystem . '/' . $filename);
		$image->save($path . '/' . $this->filesystem . '/c' . $filename);
	}

	/// Section: Getters

	public function getProfilePictureMediumAttribute()
	{
		$basicPath = $this->getCroppedImage($this->profile_picture_cropping, $this->profile_picture, 180, 180);
		if(!is_null($basicPath)) {
			return $basicPath;
		}
		return $this->original_profile_picture_web_path;
	}

	public function getProfilePictureTinyAttribute()
	{
		$basicPath = $this->getCroppedImage($this->profile_picture_cropping, $this->profile_picture, 50, 50);
		if(!is_null($basicPath)) {
			return $basicPath;
		}
		return $this->original_profile_picture_web_path;
	}

	public function getOriginalProfilePictureWebPathAttribute()
	{
		if(!is_null($this->profile_picture)) {
			return url('/') . '/uploads/' . $this->filesystem . '/' . $this->profile_picture;
		}
		return url('/') . '/images/img-profile-120.gif'; // return the default app avatar
	}

	public function getCoverPictureWebPathAttribute()
	{
		$croppedImage = $this->getCroppedImage($this->cover_picture_cropping, $this->cover_picture, 1440, 577); // was 1440, 280
		if(!is_null($croppedImage)) {
			return $croppedImage;
		}
		return $this->original_cover_picture_web_path;
	}

	public function getOriginalCoverPictureWebPathAttribute()
	{
		if(!is_null($this->cover_picture)) {
			return url('/') . '/uploads/' . $this->filesystem . '/' . $this->cover_picture;
		}
	}

	/**
	 * default to tiny
	 */
	protected function getCroppedImage($crop_settings, $image, $width = 50, $height = 50)
	{
		if(!is_null($crop_settings) && !is_null($image)) {
			$cropping = json_decode($crop_settings); // automatic attribute casting isn't working ?
			return url('/') . "/avatar/{$cropping->x}/{$cropping->y}/{$cropping->width}/{$cropping->height}/{$width}/{$height}/{$this->filesystem}/{$image}"; // @todo: need to use the route name instead of hard coded url structure
		}
		return null;
	}
}