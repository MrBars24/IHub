<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Modules\Files\FileManager;
use App\FileStorage;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GigFeedPost extends Model
{
	use CommonTrait;

	protected $table = 'gig_feed_post';

	protected $fillable = [
		'link',
		'hub_id',
		'type',
		'url_profile'
	];

	protected $appends = [
		'thumbnail_web_path'
	];

	public $timestamps = [
		'originally_published_at'
	];

	/// Section: Relations

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function file()
	{
		return $this->morphOne(FileStorage::class, 'object');
	}

	/// Section: Mutator

	public function getThumbnailWebPathAttribute()
	{
		if(!is_null($this->file)) {
			return $this->file->web_path;
		}
		return $this->thumbnail;
	}

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

	/// Section: FileContextTrait

	/*public function getAbsFileSystem()
	{
		if(!is_null($this->hub)) {
			return $this->hub->getAbsFileSystem() . '/' . $this->getAttribute($this->getFileContextColumn());
		}
		return $this->getAttribute($this->getFileContextColumn());
	}*/
	public function getFilePath()
	{
		return $this->hub->getFilePath();
	}
}
