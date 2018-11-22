<?php

// namespace App\Modules\RssBridge\Bridges; 
// non-namespaced class can't find the namespaced classes

/** 
 * Default RssBridge to parse the rss links
 * 
 * https://github.com/RSS-Bridge/rss-bridge
 */

class RssBridge extends \FeedExpander {

	const MAINTAINER = 'pauder';
	const NAME = 'Rss Bridge';
	const URI = '';
	const DESCRIPTION = 'Returns the newest images on a board';

	const PARAMETERS = [];

	protected $url;

	public function parseData($url)
	{
		$this->url = $url;
		$this->collectExpandableDatas($url); // do a data formatting ?
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getURI(){
    	return self::URI;
	}

	public function getName(){
		return self::NAME;
	}

	public function collectData() {}
}
