<?php

// namespace App\Modules\RssBridge\Bridges; 

/** 
 * This is a modified version of TwitterBridge from rss-bridge package
 * 
 * https://github.com/RSS-Bridge/rss-bridge
*/

class TwitterBridge extends \BridgeAbstract {
	const NAME = 'Twitter Bridge';
	const URI = 'https://twitter.com/';
	const CACHE_TIMEOUT = 300; // 5min
	const DESCRIPTION = 'returns tweets';
	const MAINTAINER = 'bodecontagion';
	const PARAMETERS = [
		'By username' => [
			'u' => [
				'name' => 'username',
				'required' => true,
				'exampleValue' => 'sebsauvage',
				'title' => 'Insert a user name'
			],
			'norep' => [
				'name' => 'Without replies',
				'type' => 'checkbox',
				'required' => false,
				'title' => 'Only return initial tweets'
			],
			'noretweet' => [
				'name' => 'Without retweets',
				'required' => false,
				'type' => 'checkbox',
				'title' => 'Hide retweets'
			],
			'nopic' => [
				'name' => 'Hide profile pictures',
				'type' => 'checkbox',
				'required' => false,
				'title' => 'Activate to hide profile pictures in content'
			],
			'noimg' => [
				'name' => 'Hide images in tweets',
				'type' => 'checkbox',
				'required' => false,
				'title' => 'Activate to hide images in tweets'
			]
		]
	];

	public function getName()
	{
		return 'Twitter ' . '@' . $this->getInput('u');
	}

	public function getURI()
	{
		return self::URI . urlencode($this->getInput('u'));
	}

	public function collectData()
	{
		$html = '';

		$html = getSimpleHTMLDOM($this->getURI());
		if(!$html) {
			returnServerError('Requested username can\'t be found.');
		}

		$hidePictures = $this->getInput('nopic');

		foreach($html->find('div.js-stream-tweet') as $tweet) {

			// Skip retweets?
			if($this->getInput('noretweet')
			&& $tweet->getAttribute('data-screen-name') !== $this->getInput('u')) {
				continue;
			}

			// Skip protmoted tweets
			$heading = $tweet->previousSibling();
			if(!is_null($heading) &&
				$heading->getAttribute('class') === 'promoted-tweet-heading'
			) {
				continue;
			}

			// remove 'invisible' content
			foreach($tweet->find('.invisible') as $invisible) {
				$invisible->outertext = '';
			}
			

			$item = [];
			// custom content
			$item['content'] = $tweet->find('div.js-tweet-text-container');
			$item['native_id'] = $tweet->getAttribute('data-tweet-id');
			
			// extract username and sanitize
			$item['username'] = $tweet->getAttribute('data-screen-name');
			// extract fullname (pseudonym)
			$item['fullname'] = $tweet->getAttribute('data-name');
			// get author
			$item['author'] = $item['fullname'] . ' (@' . $item['username'] . ')';
			// get avatar link
			$item['id'] = $tweet->getAttribute('data-tweet-id');
			$item['avatar'] = $tweet->find('img', 0)->src;
			// get TweetID
			// get tweet link
			$item['uri'] = self::URI . substr($tweet->find('a.js-permalink', 0)->getAttribute('href'), 1);
			// get tweet text
			// extract tweet timestamp
			$item['timestamp'] = $tweet->find('span.js-short-timestamp', 0)->getAttribute('data-time');
			// generate the title
			$item['title'] = strip_tags($this->fixAnchorSpacing($tweet->find('p.js-tweet-text', 0), '<a>'));

			switch($this->queriedContext) {
				case 'By list':
					// Check if filter applies to list (using raw content)
					if($this->getInput('filter')) {
						if(stripos($tweet->find('p.js-tweet-text', 0)->plaintext, $this->getInput('filter')) === false) {
							continue 2; // switch + for-loop!
						}
					}
					break;
				default:
			}

			$this->processContentLinks($tweet);
			$this->processEmojis($tweet);

			// get tweet text
			$cleanedTweet = str_replace(
				'href="/',
				'href="' . self::URI,
				$tweet->find('p.js-tweet-text', 0)->innertext
			);

			// fix anchors missing spaces in-between
			$cleanedTweet = $this->fixAnchorSpacing($cleanedTweet);

			// Add picture to content
			$picture_html = '';
			if(!$hidePictures) {
				$picture_html = <<<EOD
<a href="https://twitter.com/{$item['username']}">
<img
	style="align:top; width:75px; border:1px solid black;"
	alt="{$item['username']}"
	src="{$item['avatar']}"
	title="{$item['fullname']}" />
</a>
EOD;
			}

			// Add embeded image to content
			$image_html = '';
			$image = $this->getImageURI($tweet);
			if(!$this->getInput('noimg') && !is_null($image)) {
				// add enclosures
				$item['enclosures'] = array($image . ':orig');

				$image_html = <<<EOD
<a href="{$image}:orig">
<img
	style="align:top; max-width:558px; border:1px solid black;"
	src="{$image}:thumb" />
</a>
EOD;
			}

			// add content
			$item['content'] = <<<EOD
<div style="display: inline-block; vertical-align: top;">
	{$picture_html}
</div>
<div style="display: inline-block; vertical-align: top;">
	<blockquote>{$cleanedTweet}</blockquote>
</div>
<div style="display: block; vertical-align: top;">
	<blockquote>{$image_html}</blockquote>
</div>
EOD;

			// add quoted tweet
			$quotedTweet = $tweet->find('div.QuoteTweet', 0);
			if($quotedTweet) {
				// get tweet text
				$cleanedQuotedTweet = str_replace(
					'href="/',
					'href="' . self::URI,
					$quotedTweet->find('div.tweet-text', 0)->innertext
				);

				$this->processContentLinks($quotedTweet);
				$this->processEmojis($quotedTweet);

				// Add embeded image to content
				$quotedImage_html = '';
				$quotedImage = $this->getQuotedImageURI($tweet);
				if(!$this->getInput('noimg') && !is_null($quotedImage)) {
					// add enclosures
					$item['enclosures'] = array($quotedImage . ':orig');

					$quotedImage_html = <<<EOD
<a href="{$quotedImage}:orig">
<img
	style="align:top; max-width:558px; border:1px solid black;"
	src="{$quotedImage}:thumb" />
</a>
EOD;
				}

				$item['content'] = <<<EOD
<div style="display: inline-block; vertical-align: top;">
	<blockquote>{$cleanedQuotedTweet}</blockquote>
</div>
<div style="display: block; vertical-align: top;">
	<blockquote>{$quotedImage_html}</blockquote>
</div>
<hr>
{$item['content']}
EOD;
			}

			$item['description'] = $cleanedTweet;

			// put out
			$this->items[] = $item;
			array_unshift($this->items, $item);
		}
	}

	private function processEmojis($tweet){
		// process emojis (reduce size)
		foreach($tweet->find('img.Emoji') as $img) {
			$img->style .= ' height: 1em;';
		}
	}

	private function processContentLinks($tweet){
		// processing content links
		foreach($tweet->find('a') as $link) {
			if($link->hasAttribute('data-expanded-url')) {
				$link->href = $link->getAttribute('data-expanded-url');
			}
			$link->removeAttribute('data-expanded-url');
			$link->removeAttribute('data-query-source');
			$link->removeAttribute('rel');
			$link->removeAttribute('class');
			$link->removeAttribute('target');
			$link->removeAttribute('title');
		}
	}

	private function fixAnchorSpacing($content){
		// fix anchors missing spaces in-between
		return str_replace(
			'<a',
			' <a',
			$content
		);
	}

	private function getImageURI($tweet){
		// Find media in tweet
		$container = $tweet->find('div.AdaptiveMedia-container', 0);
		if($container && $container->find('img', 0)) {
			return $container->find('img', 0)->src;
		}

		return null;
	}

	private function getQuotedImageURI($tweet){
		// Find media in tweet
		$container = $tweet->find('div.QuoteMedia-container', 0);
		if($container && $container->find('img', 0)) {
			return $container->find('img', 0)->src;
		}

		return null;
	}
}
