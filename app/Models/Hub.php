<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\EntityInterface;
use App\Components\FileContextInterface;
use App\Components\FileContextTrait;
use App\Components\SluggableInterface;
use App\Components\SluggableTrait;
use App\Components\PictureTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

// 3rd Party
use Stevebauman\EloquentTable\TableTrait;

class Hub extends Model implements FileContextInterface, SluggableInterface, EntityInterface
{
	// App
	use CommonTrait, FileContextTrait, SluggableTrait, PictureTrait;

	// 3rd Party
	use TableTrait;

	// Laravel
	use SoftDeletes;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'hub';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 
		'summary',
		'profile_picture',
		'cover_picture',
		'profile_picture_cropping',
		'cover_picture_cropping',
		'profile_picture_display',
		'profile_picture_tiny',
		'profile_picture_medium',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
		'activated_at',
		'deactivated_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'custom_fields' => 'json',
		'profile_picture_cropping' => 'json',
		'cover_picture_cropping' => 'json'
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [
		'cover_picture_web_path',
		'original_profile_picture_web_path',
		'original_cover_picture_web_path',
		'branding_header_logo_web_path',
		'email_logo_web_path',
		'profile_picture_tiny',
		'profile_picture_medium',
		'master_edit',
	];

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
		return 'slug';
	}

	/// Section: Relations
	
	public function membershipGroups()
	{
		return $this->hasMany(MembershipGroup::class);
	}

	public function members()
	{
		return $this->hasMany(Membership::class);
	}

	public function influencers()
	{
		return $this->hasMany(Membership::class)
			->where('role', '=', 'influencer');
	}

	public function manager()
	{
		return $this->hasOne(Membership::class)
			->where('role', '=', 'hubmanager');
	}

	public function conversations()
	{
		return $this->hasMany(Conversation::class);
	}

	public function posts()
	{
		return $this->morphMany(Post::class, 'author');
	}

	/// Section: Mutators

	public function getBrandingHeaderLogoWebPathAttribute()
	{
		if(!is_null($this->branding_header_logo)) {
			return url('/') . '/uploads/' . $this->filesystem . '/' . $this->branding_header_logo;
		}
		return url('/') . '/images/logo.png'; // return the absolute path of the influencer hub logo.
	}

	public function getSharingMetaLinkedinAttribute()
	{
		if ($this->attributes['sharing_meta_linkedin']) {
			return $this->attributes['sharing_meta_linkedin'];
		}
		return $this->name;
	}

	public function getEmailLogoWebPathAttribute()
	{
		if(!is_null($this->email_logo)) {
			return url('/') . '/uploads/' . $this->filesystem . '/' . $this->email_logo;
		}
		return url('/') . '/images/logo.png'; // return the absolute path of the influencer hub logo.
	}

	/// Section: FileContextTrait

	/**
	 * The prefix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextPrefix()
	{
		return 'h';
	}

	/// Section: EntityInterface

	/**
	 * Retrieve the owners of this entity
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getOwners()
	{
		// check cache
		if(!is_null($this->owners)) {
			return $this->owners;
		}

		// load memberships
		if(!$this->relationLoaded('members.user')) {
			$this->load([
				'members.user'
			]);
		}

		// get filtered members
		$filtered = $this->members->filter(function($item) {
			return $item->role == 'hubmanager';
		})->pluck('user')->keyBy('id');

		// cache
		$this->owners = $filtered;

		return $filtered;
	}

	protected $owners = null;

	/// Section: Methods

	/**
	 * Create a new hub
	 *
	 * @param  string  $name
	 * @param  string  $slug
	 * @param  string  $filesystem
	 * @param  string  $summary
	 * @param  mixed   $hubmanager
	 * @return Hub
	 * @throws \Exception
	 */
	public static function seedFromDefault($name, $slug, $filesystem, $summary, $hubmanager = null)
	{
		// create hub
		$hub = new Hub();
		$hub->name = $name;
		$hub->slug = $slug;
		$hub->filesystem = $filesystem;
		$hub->summary = $summary;
		$hub->save();

		// assign hub manager
		$user = null;
		if(!is_null($hubmanager)) {

			// by user email
			if(is_string($hubmanager)) {
				$user = User::where('email', '=', $hubmanager)->first();
			}
			// by user id
			elseif(is_integer($hubmanager)) {
				$user = User::find($hubmanager);
			}
			// by user object
			elseif($hubmanager instanceof User) {
				$user = $hubmanager;
			}
			// not supported
			else {
				throw new \Exception('parameter "hubmanager" unable to identify user');
			}

			// check point: found user?
			if(is_null($user)) {
				return $hub;
			}

			// create membership
			$membership = new Membership();
			$membership->user_id = $user->id;
			$membership->hub_id = $hub->id;
			$membership->status = 'member';
			$membership->is_active = true;
			$membership->role = 'hubmanager';
			$membership->save();

			// set hub manager email on hub object
			$hub->email = $user->email;
			$hub->save();
		}

		return $hub;
	}

	/// Section: Admin

	/**
	 * ATTRIBUTE master_edit
	 * @return string
	 */
	public function getMasterEditAttribute()
	{
		return route('master::hub.edit', [$this]);
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

		// Model event: Hub->saving
		self::saving(function($hub) {

			// if attribute is_active changes from false to true, set a fresh timestamp for activated_at
			if($hub->getOriginal('is_active') == 0 && $hub->getAttribute('is_active') == 1) {
				$hub->activated_at = Carbon::now();
			}

			// if attribute is_active changes from true to false, set a fresh timestamp for deactivated_at
			if($hub->getOriginal('is_active') == 1 && $hub->getAttribute('is_active') == 0) {
				$hub->deactivated_at = Carbon::now();
			}
		});
	}
}
