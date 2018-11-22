<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class GigFeed extends Model
{
	use CommonTrait;
	
	/**
	* The database table used by the model.
	*
	* @var string
	*/
	protected $table = 'gig_feed';
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
	protected $fillable = [
		'type',
		'source_url',
		'hub_id',
		'options',
		'is_active',
		'hard_limit_days'
	];
	
	/**
	* The attributes that should be cast to native types.
	*
	* @var array
	*/
	protected $casts = [
		'options' => 'array'
	];

	protected $visible = [
		'id',
		'type',
		'source_url',
		'is_active',
		'options',
		'hard_limit_days'
	];

	/// Section: Methods

	public function setEvaluatedUrl()
	{
		// get the path then trim
		$path = trim(parse_url($this->source_url)['path'], '/');
		if (!is_null($path)) {
			$evaluatedUrl = [];
			if ($this->type === 'googlepluspage') {
				$evaluatedUrl = [
					'username' => $path
				];
			}
			else if ($this->type === 'pinterestboard') {
				list($user, $board) = explode('/',$path);
				$evaluatedUrl = [
					'u' => $user,
					'b' => $board
				];
			}
			else if ($this->type === 'linkedincompany') {
				list($companyurl, $company) = explode('/',$path);
				$evaluatedUrl = [
					'c' => $company
				];
			}
			else {
				$evaluatedUrl = [
					'u' => $path
				];
				if ($this->type === 'instagramaccount' || $this->type === 'facebookpage') {
					if (!is_null($this->options))
						$evaluatedUrl = array_merge($evaluatedUrl, $this->options);
				}
			}

			$this->evaluated_url = $evaluatedUrl;
		}
	}

	/// Section: Mutators

	public function setEvaluatedUrlAttribute(array $value)
	{
		// ensure evaluted_url value exists
		if ($value) {
			$this->attributes['evaluated_url'] = http_build_query($value);
		}
	}

	/**
	 * get the array representation of evaluated_url
	 */
	public function getEvaluatedUrlDataAttribute()
	{
		parse_str($this->evaluated_url, $data);
		return $data;
	}
	
	/// Section: Events
	
	/**
	* The "booting" method of the model
	*/
	protected static function boot()
	{
		parent::boot();
		
		// evaluate the url
		self::creating(function($feed) {
			$feed->setEvaluatedUrl();
		});
		
		self::updating(function($feed) {
			// get the original values before the update
			$original = $feed->getOriginal();
			if ($original['type'] !== $feed->type || $original['source_url'] !== $feed->source_url) {
				$feed->setEvaluatedUrl(); // re-evaluate the url
			}
		});
	}
}
