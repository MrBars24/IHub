<?php

namespace App\Modules\Urls;

// App
use App\ScrapedUrl;

// 3rd Party
use Embed\Embed;
use Embed\Exceptions\InvalidUrlException;

class UrlManager
{
	/**
	 * Scrape url and store results
	 *
	 * @param string $url
	 * @return \App\ScrapedUrl|null The scraped object
	 */
	public function scrape($url)
	{
		// scrape
		try {
			$info = Embed::create($url);

			// store scraped results
			$obj = ScrapedUrl::firstOrNew(['requested_url' => $url]);
			$obj->requested_url = $url;
			$obj->title         = $info->title;
			$obj->description   = $info->description;
			$obj->url           = $info->url;
			$obj->type          = $info->type;
			$obj->image         = $info->image;
			$obj->image_width   = $info->image_width;
			$obj->image_height  = $info->image_height;
			$obj->embed_code    = $info->embed_code;
			$obj->embed_width   = $info->embed_width;
			$obj->embed_height  = $info->embed_height;
			$obj->aspect_ratio  = $info->aspect_ratio;
			$obj->author_name   = $info->author_name;
			$obj->author_url    = $info->author_url;
			$obj->provider_name = $info->provider_name;
			$obj->provider_url  = $info->provider_url;
			$obj->provider_icon = $info->provider_icon;
			$obj->published_at  = $info->published_at;
			$obj->license       = $info->license;
			$obj->response_code = null; // we store null here since it can be 200, or something else
			$obj->save();
		} catch(InvalidUrlException $e) {

			// store failed scraped results unless there's already a value cached
			$obj = ScrapedUrl::firstOrNew(['requested_url' => $url]);
			if(!$obj->exists) {
				$obj->requested_url = $url;
				$obj->response_code = $e->getCode();
				$obj->save();
			}
		}

		return $obj;
	}
}
