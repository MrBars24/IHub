<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Modules\Files\FileManager;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PostAttachment extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_attachment';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['title', 'description', 'source', 'resource', 'url', 'shortened_url', 'type'];

	/**
	 * The attributes that should be visible in arrays.
	 *
	 * @var array
	 */
	protected $visible = ['id', 'post_id', 'hub_id', 'title', 'description', 'source', 'type', 'media_path', 'media_path_large', 'url', 'file'];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['media_path', 'media_path_large'];

	/// Section: Relations

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function file()
	{
		return $this->morphOne(FileStorage::class, 'object');
	}

	/// Section: Mutators

	public function getMediaPathAttribute()
	{
		if($this->type == 'youtube') {

			// find video id using url
			$regex = '/^(?:https?:\/\/)?(?:youtu\.be\/|(?:www\.)?youtube\.com\/watch(?:\.php)?\?.*v=)([a-zA-Z0-9\-_]+)$/';
			if(preg_match($regex, $this->url, $matches) === 1) {
				$id = $matches[count($matches) - 1];
			} else {
				return null;
			}

			// build embed url and return
			$embed = 'https://www.youtube.com/embed/' . $id;
			return $embed;
		}
		elseif($this->type == 'vimeo') {

			// find video id using url
			$regex = '/^(?:https?:\/\/)?((?:www\.)?vimeo\.com\/)([a-zA-Z0-9\-_]+)$/';
			if(preg_match($regex, $this->url, $matches) === 1) {
				$id = $matches[count($matches) - 1];
			} else {
				return null;
			}

			// build embed url and return
			$embed = 'https://player.vimeo.com/video/' . $id;
			return $embed;
		}
		elseif(!is_null($this->file)) {
			return $this->file->web_path;
		}
		// default to link media
		return !is_null($this->resource) ? url("/safe-image?url=".rawurlencode($this->resource)) : null;
	}

	public function getMediaPathLargeAttribute()
	{
		if($this->type == 'image' && !is_null($this->file)) {
			return route('general::thumbnail', ['template' => 'large_ratio', 'file_path' => $this->file->relative_web_path]);
		}
		return $this->media_path;
	}

	/// Section: FileContextTrait

	public function getFilePath()
	{
		return $this->hub->getFilePath();
	}

	/// Section: Methods

	// @todo: move this to a trait along with the GigAttachment@storeFile
	public function storeFile()
	{
		// get file object
		$fileStorage = FileStorage::query()
			->whereNull('object_id')
			->whereNull('object_type')
			->where('path', 'like', '%' . $this->resource)
			->where('status', '=', 'staged')
			->first();

		// store object against file
		$fileStorage->object()->associate($this);
		
		// move file
		return app(FileManager::class)->store($fileStorage, $this->resource);		
	}

	public static function createFromScrape($scrape)
	{
		$obj = new static;

		// get source from the url or resource
		$url = $scrape->url;
		$obj->url = $scrape->url;

		// type

		// get list of all valid attachment types
		$validTypes = [
			'link',
			'image',
			'video',
			'youtube',
			'vimeo',
		];

		// determine type of attachment and treat appropriately
		$obj->type = $scrape->type;
		$rules = array(
			'youtube' => '/^(?:https?:\/\/)?(?:youtu\.be\/|(?:www\.)?youtube\.com\/watch(?:\.php)?\?.*v=)([a-zA-Z0-9\-_]+)$/',
			'vimeo'   => '/^(?:https?:\/\/)?((?:www\.)?vimeo\.com\/)([a-zA-Z0-9\-_]+)$/',
		);
		foreach($rules as $type => $rule) {
			// determine type by url
			if(preg_match($rule, $url, $matches) === 1) {
				$obj->type = $type;
			}
		}

		// final validation: if not in the valid types list, then just set to "link"
		if(!in_array($obj->type, $validTypes)) {
			$obj->type = 'link';
		}

		// source

		$parsedUrl = parse_url($url);
		$obj->source = $parsedUrl['host'];

		// resource

		if($obj->type == 'youtube') {
			$obj->resource = $scrape->url; // need embed url, not the web page url
		} elseif($obj->type == 'vimeo') {
			$obj->resource = $scrape->url; // need embed url, not the web page url
		} elseif($obj->type == 'video') {
			$obj->resource = $scrape->url;
		} else {
			$obj->resource = $scrape->image;
		}

		// title

		$obj->title = $scrape->title;

		// description

		$obj->description = $scrape->description;

		// resource
		// just make it visible in order to get the image_path on saving.
		$obj->makeVisible('resource');

		return $obj;
	}
}
