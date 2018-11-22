<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileStorage extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'file_storage';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['path', 'status'];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['web_path'];

	/// Section: Relations

	public function object()
	{
		return $this->morphTo();
	}

	/// Section: Mutators

	public function getWebPathAttribute()
	{
		return $this->getWebPath(true);
	}

	public function getRelativeWebPathAttribute()
	{
		return $this->getWebPath(false);
	}

	/// Section: Methods

	public function getWebPath($absolute = true)
	{
		$storage = Storage::disk('local.public');
		$file = str_replace($storage->getAdapter()->getPathPrefix(), '', $this->path);
		return ($absolute ? (url('/') . '/uploads/') : '') . str_replace('\\', '/', $file);
	}

	public function getFilePath()
	{
		return $this->path;
	}
}
