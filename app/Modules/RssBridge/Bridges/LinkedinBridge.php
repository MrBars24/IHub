<?php


// namespace App\Modules\RssBridge\Bridges; 
// non-namespaced class can't find the namespaced classes

/** 
 * This is a modified version of Linkedin from rss-bridge package
 * 
 * https://github.com/RSS-Bridge/rss-bridge
 */

class LinkedinBridge extends \BridgeAbstract {

	const MAINTAINER = 'regisenguehard';
	const NAME = 'LinkedIn Company';
	const URI = 'https://www.linkedin.com/';
	const CACHE_TIMEOUT = 21600; //6
	const DESCRIPTION = 'Returns most recent actus from Company on LinkedIn.
 (https://www.linkedin.com/company/<strong style=\"font-weight:bold;\">apple</strong>)';

	const URL_LOGIN = "https://www.linkedin.com/uas/login";

	const PARAMETERS = array( array(
		'c' => array(
			'name' => 'Company name',
			'required' => true
		)
	));

	public function collectData(){
		$html = '';
		$link = self::URI . 'company/' . $this->getInput('c');

        $html = getSimpleHTMLDOM($this->fetchData($link));

		foreach($html->find('//*[@id="my-feed-post"]/li') as $element) {
			$title = $element->find('span.share-body', 0)->innertext;
			if($title) {
				$item = array();
				$item['uri'] = $link;
				$item['title'] = mb_substr(strip_tags($element->find('span.share-body', 0)->innertext), 0, 100);
				$item['content'] = strip_tags($element->find('span.share-body', 0)->innertext);
				$this->items[] = $item;
				$i++;
			}
		}
    }
    
    private function fetchData($link)
    {

		$linkedin_login_page = self::URI . "uas/login";
		// get the login page
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $linkedin_login_page);
		curl_setopt($ch, CURLOPT_REFERER, self::URI);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7)');
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		$login_content = curl_exec($ch);

		$username = env('LINKEDIN_USERNAME');
		$password = env('LINKEDIN_PASSWORD');
		
		$var = [
            'isJsEnabled' => 'false',
            'source_app' => '',
            'clickedSuggestion' => 'false',
            'session_key' => trim($username),
            'session_password' => trim($password),
            'signin' => 'Sign In',
            'session_redirect' => '',
            'trk' => '',
			'fromEmail' => ''
		];
        $var['loginCsrfParam'] = $this->fetch_value($login_content, 'type="hidden" name="loginCsrfParam" value="', '"');
        $var['csrfToken'] = $this->fetch_value($login_content, 'type="hidden" name="csrfToken" value="', '"');
        $var['sourceAlias'] = $this->fetch_value($login_content, 'input type="hidden" name="sourceAlias" value="', '"');

        $post_array = [];
		foreach ($var as $key => $value)
		{
			$post_array[] = urlencode($key) . '=' . urlencode($value);
		}
		$post_string = implode('&', $post_array);
		
		curl_setopt($ch, CURLOPT_URL, self::URI."uas/login-submit");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

		$store = curl_exec($ch);
		
		// check if the login logic was a success
		if (stripos($store, "session_password-login-error") !== false) {
			$err = trim(strip_tags($this->fetch_value($store, '<span class="error" id="session_password-login-error">', '</span>')));	
			throw new \Exception('Login Error: '. $err);
		}
		// login successful, fetch the company page
		elseif (stripos($store, 'profile-nav-item') !== false) {
				curl_setopt($ch, CURLOPT_URL,"https://www.linkedin.com/company/facebook");
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "");
				$content = curl_exec($ch);
				curl_close($ch);
				logger($content); // geting an unauthenticated page. 
				return $content;
		}
		else {
			throw new \Exception('Unknown Error');
		}

	}

	private function fetch_value($str, $find_start = '', $find_end = '')
	{
		if ($find_start == '')
		{
			return '';
		}
		$start = strpos($str, $find_start);
		if ($start === false)
		{
			return '';
		}
		$length = strlen($find_start);
		$substr = substr($str, $start + $length);
		if ($find_end == '')
		{
			return $substr;
		}
		$end = strpos($substr, $find_end);
		if ($end === false)
		{
			return $substr;
		}
		return substr($substr, 0, $end);
	}
}
