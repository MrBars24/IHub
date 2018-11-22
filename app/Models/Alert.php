<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'alert';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['sent_at', 'read_at'];

	/// Section: Relation

	public function gigs()
	{
		return $this->belongsToMany(Gig::class, 'alert_gig', 'alert_id', 'gig_id')->withPivot('viewed_at');
	}

	/// Section: Methods

	public function generateHash()
	{
		return md5($this->id . '/' . $this->email);
	}

	public function getAlertPingUrl($hub, $absolute = true)
	{
		$params = [
			'hub'   => $hub->slug,
			'alert' => $this->id,
			'e'     => $this->generateHash(),
		];
		return route('hub::alert.read', $params, $absolute);
	}

	public function getGigClickthroughUrl($hub, $gig, $absolute = true)
	{
		$params = [
			'hub'   => $hub->slug,
			'alert' => $this->id,
			'gig'   => $gig->id,
			'e'     => $this->generateHash(),
		];
		return route('hub::alert.gig', $params, $absolute);
	}
}
