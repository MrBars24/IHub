<?php

namespace App\Http\Controllers\General;

// App
use App\Http\Controllers\Controller;
use App\Modules\Urls\UrlManager;
use App\Modules\Files\FileManager;

// Laravel
use App\PostAttachment;
use App\GigAttachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
	/**
	 * POST /api/attachment/upload
	 * ROUTE general::attachment.upload [api.php]
	 *
	 * Upload an attachment from the app
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function fileUpload(Request $request)
	{
		$input = $request->file('file');

		// stage file ready to be copied across
		$file = app(FileManager::class)->stage($input);

		// response
		$data = [
			'data' => [
				'file' => $file
			],
			'route' => 'general::attachment.upload',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/attachment/scrape
	 * ROUTE general::attachment.scrape [api.php]
	 *
	 * Scrape a remote url and store the url information
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function fileScrape(Request $request)
	{
		$url = $request->input('url');
		$context = $request->input('context', 'post');

		// scrape url
		$info = app(UrlManager::class)->scrape($url);

		// check point: successful scrape
		if(!is_null($info->response_code) && $info->response_code != 200) {

			// response
			$data = [
				'data' => [
					'url_info' => null
				],
				'route' => 'general::attachment.scrape',
				'success' => false
			];
			return response()->json($data);
		}

		// create attachment
		if ($context === 'gig') {
			$attachment = GigAttachment::createFromScrape($info);
		}
		else {
			$attachment = PostAttachment::createFromScrape($info);
		}

		// response
		$data = [
			'data' => [
				'url_info' => $info->toArray(),
				'attachment' => $attachment->toArray(),
			],
			'route' => 'general::attachment.scrape',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/attachment/copy
	 * ROUTE general::attachment.copy [api.php]
	 *
	 * Copy a remote url and save the file onto server
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function fileCopy(Request $request)
	{

	}
}
