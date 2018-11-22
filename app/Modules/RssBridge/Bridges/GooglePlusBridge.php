<?php

// namespace App\Modules\RssBridge\Bridges; 

/** 
 * This is a modified version of GooglePlus from rss-bridge package
 * 
 * https://github.com/RSS-Bridge/rss-bridge
*/

class GooglePlusBridge extends \BridgeAbstract{

	protected $_title;
	protected $_url;

	const MAINTAINER = 'Grummfy';
	const NAME = 'Google Plus Post Bridge';
	const URI = 'https://plus.google.com/';
	const CACHE_TIMEOUT = 600; //10min
	const DESCRIPTION = 'Returns user public post (without API).';

	const PARAMETERS = [
    [
		  'username' => [
        'name' => 'username or Id',
        'required' => true
      ]
    ]
  ];

	public function collectData(){
		$username = $this->getInput('username');

		// Usernames start with a + if it's not an ID
		if(!is_numeric($username) && substr($username, 0, 1) !== '+') {
			$username = '+' . $username;
		}

		// get content parsed
		$html = getSimpleHTMLDOM(self::URI . urlencode($username) . '/posts')
      or returnServerError('No results for this query.');
  
		// get title, url, ... there is a lot of intresting stuff in meta
		$this->_title = $html->find('meta[property=og:title]', 0)->getAttribute('content');
		$this->_url = $html->find('meta[property=og:url]', 0)->getAttribute('content');

		// I don't even know where to start with this discusting html...
		foreach($html->find('div[jsname=WsjYwc]') as $post) {
			$item = [];

			$item['author'] = $item['fullname'] = $post->find('div div div div a', 0)->innertext;
			$item['id'] = $post->find('div div div', 0)->getAttribute('id');
			$item['uri'] = self::URI . $post->find('div div div a', 1)->href;

			$timestamp = $post->find('a.o8gkze span', 0);
			if($timestamp) {
				$item['timestamp'] = strtotime('+' . preg_replace(
						'/[^0-9A-Za-z]/',
						'',
						$timestamp->getAttribute('aria-label')));
      }

      $item['description'] = $post->find('div[jsname="EjRJtf"]', 0)->plaintext;

			$this->items[] = $item;
		}
	}

	public function getName(){
		return $this->_title ?: 'Google Plus Post Bridge';
	}

	public function getURI(){
		return $this->_url ?: parent::getURI();
	}
}
