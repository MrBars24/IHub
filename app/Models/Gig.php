<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\FileContextInterface;
use App\Components\FileContextTrait;
use App\Components\SluggableInterface;
use App\Components\SluggableTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gig extends Model implements FileContextInterface, SluggableInterface
{
	// App
	use CommonTrait, FileContextTrait, SluggableTrait;

	// Laravel
	use SoftDeletes;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'gig';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title',
		'place_count',
		'description',
		'ideas',
		'ideas_facebook',
		'ideas_twitter',
		'ideas_linkedin',
		'ideas_pinterest',
		'ideas_youtube',
		'ideas_instagram',
		'points',
		'commence_at',
		'deadline_at',
		'require_approval',
		'is_active'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'commence_at',
		'deadline_at',
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
	 * the attributes that should be included for the json|array calls
	 *
	 * @var array
	 */
	protected $appends = [
		'description_cached',
		'ideas_cached',
		'has_expired'
	];

	/// Section: Relations

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function categories()
	{
		return $this->belongsToMany(Category::class, 'gig_category', 'gig_id', 'category_id');
	}

	public function platforms()
	{
		return $this->belongsToMany(Platform::class, 'gig_platform', 'gig_id', 'platform_id');
	}

	public function rewards()
	{
		return $this->hasMany(Reward::class);
	}

	public function attachments()
	{
		return $this->hasMany(GigAttachment::class);
	}

	public function posts()
	{
		return $this->belongsToMany(Post::class, 'gig_post', 'gig_id', 'post_id');
	}

	/// Section: Mutators

	public function getSluggableAttribute()
	{
		$pos = strpos($this->slug, $this->id . '-');
		return $this->id . '-' . (($pos === false) ? $this->slug : str_replace_first($this->id . '-', '', $this->slug));
	}

	public function getDescriptionCachedAttribute()
	{
		return render_content($this->description);
	}

	public function getIdeasCachedAttribute()
	{
		return render_content($this->ideas);
	}

	public function getHasExpiredAttribute()
	{
		$now = carbon();
		return !$this->is_live && !is_null($this->deadline_at) && $this->deadline_at->lte($now);
	}

	/// Section: FileContextTrait

	/**
	 * The prefix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextPrefix()
	{
		return 'g';
	}

	/// Section: SluggableTrait

	/**
	 * The column to generate the slug from.
	 *
	 * @return string
	 */
	public function getSlugSource()
	{
		return 'title';
	}

	/// Section: Events

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		// Model event: Gig->saving
		self::saving(function($obj) {
			$now = $obj->freshTimestamp();

			// within date bounds
			if(!is_null($obj->commence_at) && !is_null($obj->deadline_at)) {
				$obj->is_live = $obj->commence_at <= $now && $obj->deadline_at > $now;
			}
			// before deadline
			elseif(!is_null($obj->deadline_at)) {
				$obj->is_live = $obj->deadline_at > $now;
			}
			// after commencement
			elseif(!is_null($obj->commence_at)) {
				$obj->is_live = $obj->commence_at <= $now;
			}
			// leave unpublished; dates should be set
			else {
				$obj->is_live = false;
			}
		});

		// Model event: Gig->created
		self::created(function($obj) {
			// update slug with new slug after saving the object to the database
			$obj->slug = $obj->sluggable;
			$obj->save();
		});
	}
}
