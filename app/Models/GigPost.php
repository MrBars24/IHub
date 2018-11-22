<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;
use App\Components\CommonTrait;

class GigPost extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'gig_post';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['gig_id', 'post_id', 'status', 'rejection_reason'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
	];

	/**
	 * the attributes that should be casted
	 * @var [type]
	 */
	protected $casts = [
		'params' => 'object'
	];

	/// Section: Relations

	public function gig()
	{
		return $this->belongsTo(Gig::class);
	}

	public function post()
	{
		return $this->belongsTo(Post::class);
	}

	/// Section: Methods

	/**
	 * Pending GigPost but is set for scheduled post.
	 * - if the schedule is present 
	 * - then set the scheduled date as well
	 */
	public function waitForReview($schedule_at = null)
	{
		$this->status = 'pending';
		// if schedule date is present. set the scheduled date as well.
		if (!is_null($schedule_at)) {
			$this->schedule_result = 'pending';
			$this->schedule_at = $schedule_at;
		}
		$this->save();

		// @todo: send notification to hub manager for verification
	}

	public function publish()
	{
		// dispatch posts by marking them as 'pending'
		$this->post->load([
			'subPosts' => function($query) {
				$query->where('result', '=', 'not ready');
			}
		]);
		foreach($this->post->subPosts as $post) {
			$post->result = 'pending';
			$post->save();
		}

		$this->post->is_published = true;
		$this->post->save();

		// accrue points for influencer
		$hub = (!is_null($this->gig->hub)) ? $this->gig->hub : null;
		$membership = $this->post->author->getMembershipTo($hub);
		$membership->accruePoints($this->gig->points, 'gigcomplete', $this);

		// create published notification
		if($this->gig->require_approval) {
			// @todo: send notification to influencer that post is verified
		} else {
			// @todo: send notification to hub manager that post is published
		}

		// publish
		$this->status = 'verified';

		// unqueue schedule
		$this->schedule_at = null;
		$this->schedule_result = null;
		$this->schedule_error = null;
		$this->save();

		return true;
	}

	public function schedule($schedule_at)
	{
		// schedule
		$this->status = 'scheduled';

		// unqueue schedule
		$this->schedule_at = $schedule_at;
		$this->schedule_result = 'pending';
		$this->schedule_error = null;
		$this->save();

		return true;
	}

	public function cancel()
	{
		$this->status = 'superceded';
		// NOTE: should clear out the schedule data ?..
		// $this->schedule_at = null;
		// $this->schedule_result = null;
		// $this->schedule_error = null;
		$this->save();

		return true;
	}

	public function rollback()
	{
		// reset to pending status
		$this->status = 'pending';

		// unqueue schedule
		$this->schedule_at = null;
		$this->schedule_result = null;
		$this->schedule_error = null;
		$this->save();

		return true;
	}

	public function reject($reason)
	{
		// schedule
		$this->status = 'rejected';
		$this->rejection_reason = $reason;

		// unqueue schedule
		$this->schedule_at = null;
		$this->schedule_result = null;
		$this->schedule_error = null;
		$this->save();

		// @todo: send notification to influencer for failed verification

		return true;
	}
}
