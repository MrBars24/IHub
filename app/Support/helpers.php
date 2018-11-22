<?php

// App
use App\User;

// Laravel
use Illuminate\Support\Collection;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Request;

// 3rd Party
use Carbon\Carbon;

if(!function_exists('session_user')) {
	/**
	* Get session user. More of a hack to get around oauth auth limitations.
	*
	* @return \App\User
	*/
	function session_user()
	{
		return User::getSessionUser()->first();
	}
}

if(!function_exists('gather')) {
	/**
	* Create a collection from the given value.
	*
	* @param  mixed   $value
	* @param  string  $type
	* @return \Illuminate\Support\Collection
	*/
	function gather($value = null, $type = Collection::class)
	{
		return new $type($value);
	}
}

if(!function_exists('ab')) {
	/**
	* Test a condition and then return either the first or second value based on that test.
	*
	* @param boolean $test
	* @param mixed $a
	* @param mixed $b
	*/
	function ab($test, $a, $b = null)
	{
		return $test ? $a : $b;
	}
}

if(!function_exists('attr')) {
	/**
	* Get a nested attribute in the object.
	*
	* @param mixed $obj
	* @param string $path
	* @param mixed $alt
	*/
	function attr($obj, $path, $alt = null)
	{
		$ref = (object) $obj;
		// immediate attributes
		if(isset($ref->{$path})) {
			return $ref->{$path};
		}
		// nested attributes
		elseif(strpos($path, '.') !== false) {
			$attrs = explode('.', $path);
			$curr = $ref;
			// traverse down path until end, or null is hit
			foreach($attrs as $attr) {
				if(!isset($curr->{$attr})) {
					return $alt;
				}
				$curr = $curr->{$attr};
			}
			return $curr;
		}
		return $alt;
	}
}

if(!function_exists('nested')) {
	/**
	* Get a deeply nested object or value using array dot syntax.
	* eg: nested($user, 'roles:first.permissions:first.label')
	*
	* @param  $object
	* @param  $path
	* @param  $alt
	* @return float
	*/
	function nested($object, $path, $alt = null)
	{
		$path = str_replace(':', '.:', $path);
		$path = preg_replace('/^\./', '', $path);
		$parts = explode('.', $path);
		$curr = $object;
		foreach($parts as $part) {
			// array access
			if($curr instanceof \Traversable) {
				if(isset($curr[$part])) {
					// traverse down
					$curr = $curr[$part];
				} else {
					// read special case parts
					switch($part) {
						case ':first':
						$curr = group($curr)->first();
						break;
						case ':last':
						$curr = group($curr)->last();
						break;
						case ':rand':
						case ':random':
						$curr = group($curr)->random();
						break;
						default:
						$curr = $alt;
						break;
					}
				}
				// object access
			} else {
				if(isset($curr->$part)) {
					$curr = $curr->$part;
				} else {
					$curr = $alt;
				}
			}
			if(is_null($curr)) {
				break;
			}
		}
		return $curr;
	}
}

if(!function_exists('enl2br')) {
	/**
	* All in one function to escape a string then call nl2br.
	*
	* @param  string  $value
	* @return string
	*/
	function enl2br($value)
	{
		return nl2br(e($value));
	}
}

if(!function_exists('divide')) {
	/**
	* Safely divide a number.
	*
	* @param  $top
	* @param  $bottom
	* @return float
	*/
	function divide($top, $bottom)
	{
		if($bottom == 0) {
			return 0;
		}
		return $top / $bottom;
	}
}

if(!function_exists('vd')) {
	/**
	* Dump the passed variables.
	*
	* @param  mixed
	* @return void
	*/
	function vd()
	{
		array_map(function ($x) {
			(new Dumper)->dump($x);
		}, func_get_args());
	}
}

if(!function_exists('ql')) {
	/**
	* Get the connection query log.
	*
	* @return array
	*/
	function ql()
	{
		$querylog = \DB::getQueryLog();
		try {
			$debugbar = app('debugbar');
			if(!is_null($debugbar) && !is_null($debugbar->getCollector('queries'))) {
				$statements = $debugbar->getCollector('queries')->collect();
				$statements = $statements['statements'];
				$count = max(count($querylog), count($statements));
				$list = array();
				for($i = 0; $i < $count; $i++) {
					$sql = !empty($statements) ? $statements[$i]['sql'] : '';
					//$sql = str_replace("\n", "", $sql);
					$row = array(
						'sql' => $sql,
						'query' => $querylog[$i]['query'],
						'bindings' => $querylog[$i]['bindings'],
						'time' => $querylog[$i]['time'],
					);
					$list[] = $row;
				}
			} else {
				$list = $querylog;
			}
		} catch(\Exception $e) {
			$list = $querylog;
		}
		return $list;
	}
}

if(!function_exists('carbon')) {
	/**
	* Return a new Carbon date object.
	*
	* @param  string  $time
	* @param  string  $tz
	* @return Carbon
	*/
	function carbon($time = 'now', $tz = null)
	{
		return new Carbon($time, $tz);
	}
}

if(!function_exists('dumptomail')) {
	/**
	* send the dumped data to email
	* NOTE: i'll remove this later
	*/
	function dumptomail($emailData) {
		Mail::send('email.log', $emailData, function($message) {
			// send platform information
			$fromName = 'Influencer Hub';
			
			// compile message
			$message
			->from('noreply@influencerhub.com')
			->to('ejlocop@gmail.com')
			->subject("Logs");
		});
	}
}

if(!function_exists('relativeDate')) {
	/**
	* Display the specified date relative to now or another specified date (eg. "22 hours from now").
	*
	* @param  DateTime|string  $to
	* @return string
	*/
	function relativeDate($to)
	{
		$now = carbon();
		$seconds = $to->diffInSeconds($now);
		$seconds = $to > $now ? $seconds * -1 : $seconds;
		
		return $now->subSeconds($seconds)->diffForHumans();
	}
}

if(!function_exists('is_ajax')) {
	/**
	* Check if the current request is AJAX.
	*
	* @return string
	*/
	function is_ajax()
	{
		$headers = getallheaders();
		if(!isset($headers['X-Requested-With'])) {
			return false;
		}
		return Request::ajax() || is_phonegap();
	}
}

if(!function_exists('is_phonegap')) {
	/**
	 * Check if the current request is from Phonegap.
	 * 
	 * @return boolean
	 */
	function is_phonegap()
	{
		$headers = getallheaders();
		if(!isset($headers['X-Requested-With'])) {
			return false;
		}
		return 'com.adobe.phonegap.app' == $headers['X-Requested-With'] || 
			'com.bodecontagion.ihubapp' == $headers['X-Requested-With'] || 
			'com.influencerhub.app' == $headers['X-Requested-With'];
	}

}

if(!function_exists('getallheaders')) {
	/**
	* Fetches all HTTP headers from the current request.
	* This function is an alias for apache_request_headers(). Please read the apache_request_headers() documentation for more information on how this function works.
	*
	* @return array|boolean
	*/
	function getallheaders()
	{
		if(!is_array($_SERVER)) {
			return array();
		}
		
		$headers = array();
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}

if(!function_exists('add_url_scheme')) {
	/**
	 * add http|https to non-http|https links
	 */
	function add_url_scheme($url, $scheme = 'http://')
	{
		if (parse_url($url, PHP_URL_SCHEME) === null) {
			return $scheme . $url;
		}
		return $url;
	}
}

if(!function_exists('render_content')) {
	/**
	 * Copied from MessageTrait render()
	 *
	 * Renders content
	 */
	function render_content($message, $supportInternalTagging = false, $hub = null)
	{
		// should we add tag links to profiles?
		$tags = [];
		$addTags = false;
		if(isset($supportInternalTagging) && $supportInternalTagging) {
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
			//$hub = $this->hub; // @todo monitor the performance of this query
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
}

if (!function_exists('fix_links')) {
	/**
	 * fix links in text
	 * - replace shortened url with its href tag
	 * - strip tags
	 * 
	 * @param string $text
	 * @param array|string|null $ignores ignores the links that starts with this list.
	 * @return string
	 */
	function fix_links($text, $ignores = null) {
		// convert to html to easily find tags.
		$node = str_get_html($text);
		
		// fix $ignores pattern
		$pattern = '';
		if (gettype($ignores) == 'array') {
			$pattern = implode("|", $ignores);
		}
		else if (gettype($ignores) == 'string') {
			$pattern = str_replace(',', '|', $ignores);
		}

		$regex = null;
		if (!is_null($ignores) && $pattern != '') {
			$regex = '/^'.$pattern.'/';
		}

		// find anchor tags
		foreach($node->find('a') as $a) {
			$text = $a->plaintext;

			// skip links that starts with $ignores param
			if (!is_null($regex) && preg_match($regex, $text) !== 0) {
				continue;
			}

			$href = $a->href;
			$a->innertext = $href;
		}

		// strip the tags
		$cleanedText = strip_tags($node);
		
		return $cleanedText;
	}
}