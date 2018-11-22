<?php

namespace App\Components;

// App
use App\Entity;
use App\Hub;
use App\Modules\Urls\UrlManager;

trait MessageTrait
{
	/// Section: Mutators

	/**
	 * Return the message for this object
	 *
	 * @return string The message
	 */
	public function getMessageAttribute()
	{
		// get cached message
		if(!is_null($this->getAttribute('message_cached'))) {
			return $this->getAttribute('message_cached');
		}

		// cache and return rendered message
		$this->cache();
		$this->save();

		return $this->getAttribute('message_cached');
	}

	/**
	 * Return the raw message for this object
	 *
	 * @return string The message
	 */
	public function getMessageRawAttribute()
	{
		// BUG: this is null if the Post was from the GigFeedPost
		return $this->getAttributeFromArray('message');
	}

	/**
	 * Return the plain text version of the message for this object
	 *
	 * @return string The message
	 */
	public function getMessagePlainAttribute()
	{
		return $this->getAttributeFromArray('message');
	}

	/**
	 * Returns the list of links in a message
	 *
	 * @return array
	 */
	public function getMessageLinksAttribute()
	{
		return $this->extractLinks($this->getAttributeFromArray('message'));
	}

	/// Section: Methods

	public function extractLinks($string)
	{
		// collect the links
		$links = array();

		$urlRegex = '/^(http|https)?(?:\:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;\(\)\[\]\"\\\'\{\}]*[\.]+[A-Z0-9+&@#\/%?=~_\-|$!:,.;\(\)\[\]\"\\\'\{\}]+[A-Z0-9+&@#\/%?=~_|$!:,.;\(\)\[\]\"\\\'\{\}]/i';
		$string = trim($string);
		$parts = preg_split('/\s+/', $string); // get string parts
		preg_match_all('/\s+/', $string, $glue); // get white spaces of string

		// look for URL's and create links
		foreach($parts as $i => $part) {
			// url
			$url = $part;
			$um = app(UrlManager::class);

			// check part is a valid and active URL
			if(preg_match($urlRegex, $part, $match) === 1 && $um->isActiveDomain($url)) {// @todo: fix this to support url checking (see old app)
				$links[] = $match[0];
			}
		}
		return $links;
	}

	/**
	 * Render message from stored string
	 *
	 * @return string
	 */
	public function render()
	{
		// get raw message, make sure to avoid calling the mutator by using getAttributeFromArray
		$message = trim($this->getAttributeFromArray('message'));

		// should we add tag links to profiles?
		$tags = [];
		$addTags = false;
		if(isset($this->supportInternalTagging) && $this->supportInternalTagging) {
			$addTags = true;
		}

		// @todo: need to fix the url regex below to ignore "..."
		//$message = str_replace('...', '&hellip;', $message);

		// url regex
		$urlRegex = '/^(http|https)?(?:\:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;\(\)\[\]\"\\\'\{\}]*[\.]+[A-Z0-9+&@#\/%?=~_\-|$!:,.;\(\)\[\]\"\\\'\{\}]+[A-Z0-9+&@#\/%?=~_|$!:,.;\(\)\[\]\"\\\'\{\}]/i';

		// get message parts
		$parts = preg_split('/\s+/', $message);

		// get white spaces in between message parts
		preg_match_all('/\s+/', $message, $joints);
		if(is_array($joints) && !empty($joints)) {
			$joints = $joints[0];
			$joints[] = ''; // add a last empty joint to match parts collection size
		}

		// scan parts for url's
		$counter = 0;
		$newParts = [];
		$newJoints = [];
		foreach($parts as $i => $part) {

			// (for now, we won't validate url's here for performance reasons)

			// confirm we've found a url
			if(preg_match($urlRegex, $part, $match) === 1) {
				$match = $match[0];

				// get sub parts (start, match, finish)
				$subparts = preg_split($urlRegex, $part);
				$start = array_shift($subparts);
				$finish = array_shift($subparts);

				// build
				$href = htmlspecialchars($match);
				$text = $match;
				// truncate text if too long
				if(strlen($text) > 32) {
					$text = htmlspecialchars(substr($text, 0, 30)) . '&hellip;';
				} else {
					$text = htmlspecialchars($text);
				}
				$linkStart = '<a href="' . add_url_scheme($href) . '" target="_blank">';
				$linkFinish = '</a>';
				$partStart = htmlspecialchars($start);
				$partFinish = htmlspecialchars($finish);
				$newParts[$counter] =
					$partStart .
					str_replace($match, $linkStart . $text . $linkFinish, $match) .
					$partFinish;
				$newJoints[$counter] = $joints[$counter];
				$counter++;
			}
			// handle tags if supported
			elseif($addTags && strpos($part, '@') === 0) {
				// get all tags within part so we can get all slugs
				$subParts = explode('@', $part);
				$subParts = array_filter($subParts, 'strlen'); // 1 based index

				// collect unique slugs and add to parts
				foreach($subParts as $j => $slug) {
					$tags[$slug] = $slug;
					$newParts[$counter] = '@' . htmlspecialchars($slug);
					$newJoints[$counter] = ($j == count($subParts)) ? $joints[$i] : ''; // add an empty joint or complete joint
					$counter++;
				}
			}
			// handle normal text
			else {
				$newParts[$counter] = htmlspecialchars($part);
				$newJoints[$counter] = $joints[$i];
				$counter++;
			}
		}

		// query for entities @todo monitor the performance of this part of the code
		if(!empty($tags)) {
			$tags = Entity::query() // @todo move outside of this function, query for active users/hubs
				->whereIn('entity_slug', $tags)
				->get()
				->keyBy('entity_slug');

			// get hub
			$hub = $this->hub; // @todo monitor the performance of this query
			$hub_slug = $hub->exists ? $hub->slug : '~~~~~~';

			// find parts and replace with entity links
			$slugs = $tags->pluck('entity_slug', 'entity_slug')->toArray();
			foreach($newParts as $i => $part) {
				$slug = str_replace('@', '', $part);
				$entity = null;
				if(isset($slugs[$slug])) {
					$entity = $tags[$slug];
				}
				if(!is_null($entity)) {
					if($entity->entity_type == Hub::class) {
						$route = route('hub::hub.profile', [$hub_slug]);
						$slug = 'about';
					} else {
						$route = route('hub::user.profile', [$hub_slug, $entity->entity_slug]);
					}
					$route = str_replace('/api/', '/', $route);

					$newParts[$i] = '<a data-route-slug="' . $slug . '" class="comment__entity-tag" href="' . $route . '">' . $part . '</a>';
				}
			}
		}

		// build rendered message
		$output = '';
		foreach($newParts as $i => $part) {
			$output .= $part . (isset($newJoints[$i]) ? $newJoints[$i] : '');
		}
		return $output;
	}

	/**
	 * Render and cache the output message
	 */
	public function cache()
	{
		// get rendered message
		try {
			// cache message
			$this->message_cached = $this->render();
			$this->cached_at = carbon();
			$this->failed_cache_attempts = 0;
			$this->failed_cache_reason = null;
		} catch(\Exception $e) {
			$this->message_cached = null;
			$this->failed_cache_reason = $e->getTraceAsString();
			$this->failed_cache_attempts++;
		}
	}

	/// Section: Events

	/**
	 * Boot the trait for a model.
	 *
	 * @return void
	 */
	public static function bootMessageTrait()
	{
		// attach model events for any model that inherits this trait
		// model event: MessageTrait->saving
		static::saving(function($obj) {

			// set cached message
			if(is_null($obj->getAttributeValue('message_cached'))) {
				$obj->cache();
			}
		});
	}
}