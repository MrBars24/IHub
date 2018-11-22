<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;
use App\Components\CommonTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

// 3rd Party
use Stevebauman\EloquentTable\TableTrait;

class Membership extends Model
{
	// App
	use CommonTrait;

	// Laravel
	use SoftDeletes;

	// 3rd Party
	use TableTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'membership';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'joined_at',
		'booted_at',
		'deleted_at'
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'send_alerts',
		'alert_frequency'
	];

	protected $casts = [
		'custom_fields' => 'array'
	];

	/// Section: Relations

	public function groups()
	{
		return $this->belongsToMany(MembershipGroup::class, 'membership_membership_group', 'membership_id', 'group_id');
	}

	public function hub()
	{
		return $this->hasOne(Hub::class, 'id', 'hub_id');
	}

	public function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function latestAlert()
	{
		return $this->hasOne(Alert::class);
	}

	public function categories()
	{
		return $this->belongsToMany(Category::class, 'alert_category_setting', 'membership_id', 'category_id')
			->withPivot('is_selected');
	}

	public function platforms()
	{
		return $this->belongsToMany(Platform::class, 'alert_platform_setting', 'membership_id', 'platform_id')
			->withPivot('is_selected');
	}

	public function pointAccruals()
	{
		return $this->hasMany(PointAccrual::class);
	}

	public function notificationSettings()
	{
		return $this->hasMany(NotificationSetting::class);
	}

	/// Section: Methods

	/**
	 * check if the membership is still pending
	 * @return boolean [description]
	 */
	public function isActive()
	{
		return $this->status != 'pending' && $this->is_active;
	}
	
	/**
	 * create membership data for the invited user via email
	 * @param  [type] $user [description]
	 * @param  [type] $hub  [description]
	 * @return [type]       [description]
	 */
	public static function invitedByEmail($user, $hub, $role = 'influencer')
	{
		$membership = new Membership;
		$membership->user_id = $user->id;
		$membership->hub_id = $hub->id;
		$membership->send_alerts = false;
		$membership->status = 'pending';
		$membership->role = $role;
		$membership->save();
		
		return $membership;
	}

	public function checkAccrued($action, $target)
	{
		$accrual = PointAccrual::query()
			->where('membership_id', '=', $this->id)
			->where('action_type', '=', $action)
			->where('target_id', '=', $target->id)
			->where('target_type', '=', get_class($target))
			->first();
		return !is_null($accrual);
	}

	public function accruePoints($points, $action, $target)
	{
		// check if not accrued
		if($this->checkAccrued($action, $target)) {
			return false;
		}

		// points accrual
		$accrual = new PointAccrual;
		$accrual->base_points = $points;
		$accrual->multiplier = $this->getPointsMultiplier();
		$accrual->points = $this->applyPointsMultiplier($points);
		$accrual->action_type = $action;
		$accrual->target()->associate($target);
		$this->pointAccruals()->save($accrual);

		// points tally
		$this->points += $accrual->points;
		$this->save();

		return true;
	}

	public function rollbackPoints($accrual, $target = null)
	{
		// get argument type: if 'accrual' is a string, it's actually an action type
		if(is_string($accrual)) {
			// if action type, target and membership must be specified
			if(is_null($target)) {
				return false;
			}
			$accrual = PointAccrual::query()
				->where('membership_id', '=', $this->id)
				->where('action_type', '=', $accrual)
				->where('target_id', '=', $target->id)
				->where('target_type', '=', get_class($target))
				->first();
			if(is_null($accrual)) {
				return false;
			}
		}

		// points accrual
		$points = $accrual->points;
		$accrual->delete();

		// points tally
		$this->points -= $points;
		$this->save();

		return true;
	}

	public function resetPoints($actionType)
	{
		// get points to reduce by
		$points = $this->points;
		$this->points = 0;
		$this->save();

		// reset
		$reset = new PointReset;
		$reset->points_before_reset = $points;
		$reset->action_type = $actionType;
		$reset->membership_id = $this->id;
		$reset->save();

		return true;
	}

	public function applyPointsMultiplier($points) // @note: might be better in a service provider if functionality expands
	{
		return $points * $this->getPointsMultiplier();
	}

	public function getPointsMultiplier()
	{
		$multiplier = 1;
		if($this->relationLoaded('groups') && $this->groups->count() > 0) {
			$multiplier = $this->groups->first()->multiplier; // assumes 1 group per membership
		}
		return $multiplier;
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

		// Model event: Membership->creating
		self::creating(function($obj) {
			$obj->joined_at = $obj->freshTimestamp();
		});

		// Model event: Membership->created
		// NOTE: @satoshi, we can now finally remove this, iv'e updated the settings controller
		// codes to insert or update the settings.
		self::created(function($obj) {
			// create notification settings for all notification types
			$coll = NotificationType::query()
				->whereIn('enabled_for', ['all', $obj->role])
				->get();
			$settings = collect();

			foreach($coll as $type) {
				$data = array(
					'type_id' => $type->id,
					'send_web' => $type->send_web,
					'send_email' => $type->send_email,
					'send_push' => $type->send_push,
				);
				$settings[] = new NotificationSetting($data);
			}
			$obj->notificationSettings()->saveMany($settings);

			// create platform settings for all platforms
			$platforms = Platform::query()
				->get()
				->keyBy('id')
				->keys()
				->all();
			$obj->platforms()->attach($platforms); // @todo: needs to be is_selected = 1, fix later

			// create category settings for all categories in hub
			// @note: this requires hub_id being set
			$categories = Category::query()
				->where('hub_id', '=', $obj->hub_id)
				->get()
				->keyBy('id')
				->keys()
				->all();
			$obj->categories()->attach($categories);
		});
	}
}
