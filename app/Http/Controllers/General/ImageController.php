<?php

namespace App\Http\Controllers\General;

// App
use App\Http\Controllers\Controller;
use Closure;

// Laravel
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// 3rd Party
use Intervention\Image\ImageManagerStatic;

class ImageController extends Controller
{
	/**
	 * GET /thumb/{template}/{file_path}
	 * ROUTE general::thumbnail [web.php]
	 *
	 * Render a thumbnail version of an image
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string $template
	 * @param  string $file_path
	 * @return \Illuminate\Http\Response
	 */
	public function thumbnail(Request $request, $template, $file_path)
	{
		// response
		return $this->thumbImage($template, $file_path);
	}

	/**
	 * GET /avatar/{xc}/{yc}/{wc}/{hc}/{wf}/{hf}/{file_path}
	 * ROUTE general::avatar [web.php]
	 *
	 * Render a cropped version of an image
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  float $xc - x cropper
	 * @param  float $yc - y cropper
	 * @param  float $wc - width cropper
	 * @param  float $hc - height cropper
	 * @param  float $wf - width final
	 * @param  float $hf - height final
	 * @param  string $file_path
	 * @return \Illuminate\Http\Response
	 */
	public function avatar(Request $request, $xc, $yc, $wc, $hc, $wf, $hf, $file_path)
	{
		// response
		return $this->croppedImage($xc, $yc, $wc, $hc, $wf, $hf, $file_path);
	}

	/**
	 * GET /safe-image?url={external_image_url}
	 * ROUTE general::process.image [web.php]
	 *
	 * Render a safe version of image
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function safeImage(Request $request)
	{
		// valid file types
		$types = ['image/png', 'image/jpeg', 'image/gif'];

		// params
		$file = $request->input('url');

		try{
			// get file name
			$path = parse_url($file, PHP_URL_PATH);
			$pathFragments = explode('/', $path);
			$imageName = end($pathFragments);

			// get storage and settings
			$cacheMinutes = config('imagecache.lifetime');

			// get template
			$template = $this->getTemplate('safe_image');

			
			// generate thumbnail and cache
			$cacheimage = ImageManagerStatic::cache(function($image) use($file, $template){
				$img = $image->make($file)->filter($template);
			}, $cacheMinutes);

			
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
				// if the browser has a cached version of this image, send 304
				header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'],true,304);
				exit;
			}
			
			// define mime type
			$mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $cacheimage);
			
			// return http response
			return new Response($cacheimage, 200, [
				'Content-Type' => $mime,
				'Cache-Control' => 'max-age=86400, public',
				'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 60),
				'Last-Modified' => gmdate('D, d M Y H:i:s \G\M\T', time()),
				'Etag' => md5($cacheimage)
			]);	

		}catch(\Intervention\Image\Exception\NotReadableException $e){
			// throw response when the url is not a valid image
			return new Response(['message' => 'The URL specified is not a valid image'], 400, [
				'Content-Type' => 'application/json'
			]);
		}

	}

	// Section: Helpers

	/**
	 * Create a thumbnail image from a file path
	 *
	 * @param  string  $template
	 * @param  string  $file_path
	 * @return Response
	 */
	private function thumbImage($template, $file_path)
	{
		// get storage and settings
		$cacheMinutes = config('imagecache.lifetime');
		$storage = Storage::disk('local.public');
		$fullpath = $storage->getAdapter()->applyPathPrefix($file_path);

		// get template
		$template = $this->getTemplate($template);

		// generate thumbnail and cache
		$cacheimage = ImageManagerStatic::cache(function($image) use ($template, $fullpath) {

			if($template instanceof Closure) {
				// build from closure callback template
				$template($image->make($fullpath));
			} else {
				// build from filter template
				$image->make($fullpath)->filter($template);
			}

		}, $cacheMinutes);

		// response
		return $this->buildResponse($cacheimage);
	}

	/**
	 * Render a cropped version of an image
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  float $xc - x cropper
	 * @param  float $yc - y cropper
	 * @param  float $wc - width cropper
	 * @param  float $hc - height cropper
	 * @param  float $wf - width final
	 * @param  float $hf - height final
	 * @param  string $file_path
	 * @return \Illuminate\Http\Response
	 */
	private function croppedImage($xc, $yc, $wc, $hc, $wf, $hf, $file_path)
	{
		// get storage and settings
		$cacheMinutes = config('imagecache.lifetime');
		$storage = Storage::disk('local.public');
		$fullpath = $storage->getAdapter()->applyPathPrefix($file_path);

		// generate thumbnail and cache
		$cacheimage = ImageManagerStatic::cache(function($image) use ($fullpath, $wc, $hc, $xc, $yc, $wf, $hf) {
			return $image->make($fullpath)->crop($wc, $hc, $xc, $yc)->resize($wf, $hf);
		}, $cacheMinutes);

		// response
		return $this->buildResponse($cacheimage);
	}

	/**
	 * [intervention] Returns corresponding template object from given template name
	 *
	 * @param  string $template
	 * @return mixed
	 */
	private function getTemplate($template)
	{
		$template = config("imagecache.templates.{$template}");

		switch(true) {
			// closure template found
			case is_callable($template):
				return $template;

			// filter template found
			case class_exists($template):
				return new $template;

			default:
				// template not found
				abort(404);
				break;
		}
	}

	/**
	 * [intervention] Builds HTTP response from given image data
	 *
	 * @param  string $content
	 * @return \Illuminate\Http\Response
	 */
	private function buildResponse($content)
	{
		// define mime type
		$mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content);

		// return http response
		return new Response($content, 200, [
			'Content-Type' => $mime,
			'Cache-Control' => 'max-age='.(config('imagecache.lifetime')*60).', public',
			'Etag' => md5($content)
		]);
	}
}
