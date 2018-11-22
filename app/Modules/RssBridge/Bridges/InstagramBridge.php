<?php

// namespace App\Modules\RssBridge\Bridges; 

/** 
 * This is a modified version of Instagram from rss-bridge package
 * 
 * https://github.com/RSS-Bridge/rss-bridge
*/

class InstagramBridge extends \BridgeAbstract {

	const MAINTAINER = 'pauder';
	const NAME = 'Instagram Bridge';
	const URI = 'https://instagram.com/';
	const DESCRIPTION = 'Returns the newest images';

	const PARAMETERS = [
		[
			'u' => [
				'name' => 'username',
				'required' => true
			],
			'media_type' => [
				'name' => 'Media type',
				'type' => 'list',
				'required' => false,
				'values' => array(
					'Both' => 'all',
					'Video' => 'video',
					'Picture' => 'picture'
				),
				'defaultValue' => 'all'
			]
		]
	];

	public function collectData()
	{
		$html = getSimpleHTMLDOM($this->getURI())
			or returnServerError('Could not request Instagram.');

		$innertext = null;

		foreach($html->find('script') as $script) {
			if('' === $script->innertext) {
				continue;
			}

			$pos = strpos(trim($script->innertext), 'window._sharedData');
			if(0 !== $pos) {
				continue;
			}

			$innertext = $script->innertext;
			break;
		}

		$json = trim(substr($innertext, $pos + 18), ' =;');
		$data = json_decode($json);
		
		$user = $data->entry_data->ProfilePage[0]->graphql->user;
		// if ($user->is_private) {
		//   returnServerError('User is Private');
		// }

		// if ($user->has_blocked_user) {
		//   returnServerError('You were blocked by this user.');
		// }

		$username = $user->username;
		
		// instagram changed its API
		$userMedia = $user->edge_owner_to_timeline_media->edges;

		foreach($userMedia as $media) {
			$media = $media->node;
			// Check media type
			switch($this->getInput('media_type')) {
				case 'all': break;
				case 'video':
					if($media->is_video === false) continue 2;
					break;
				case 'picture':
					if($media->is_video === true) continue 2;
					break;
				default: break;
			}

			$item = [];
			$item['uri'] = self::URI . 'p/' . $media->shortcode  . '/';
			$item['content'] = '<img src="' . htmlentities($media->display_url) . '" />';

			// has caption
			if (isset($media->edge_media_to_caption->edges[0]->node->text)) {
				$item['title'] = $media->edge_media_to_caption->edges[0]->node->text;
			} else {
				$item['title'] = basename($media->display_url);
			}
			$item['timestamp'] = $media->taken_at_timestamp;
			
			// custom version
			$item['description'] = $item['title'];
			$item['enclosures'][] = htmlentities($media->display_url);
			$item['author'] = $user->full_name . ' (@' . $user->username . ')';
			$item['id'] = $user->id;
			array_unshift($this->items, $item);
		}
	}

	public function getName()
	{
		if(!is_null($this->getInput('u'))) {
			return $this->getInput('u') . ' - Instagram Bridge';
		}

		return parent::getName();
	}

	public function getURI()
	{
		if(!is_null($this->getInput('u'))) {
			return self::URI . urlencode($this->getInput('u'));
		}

		return parent::getURI();
	}
}
