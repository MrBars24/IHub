<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	@include('master._layout.premeta', ['locale' => 'utf-8'])
	@include('master._layout.metatags', ['title' => config('app.name'), 'meta' => ['description' => '', 'keywords' => '']])
	@include('master._layout.favicon', ['title' => config('app.name')])
	@include('master._layout.ogtags', ['tags' => ['title' => config('app.name'), 'url' => '', 'site_name' => config('app.name'), 'description' => '', 'type' => 'website', 'image' => 'share-image.jpg']])
	@include('master._layout.styles', ['styles' => [
		'/css/master.css',
	]])
	@yield('custom-css')
</head>
<body @yield('body')>
<div id="wrapper" class="layout__wrapper">
	@yield('header')
	<main class="layout-main">
		@yield('sidebar')
		@yield('main')
	</main>
	@yield('footer')
	@include('master._layout.scripts', ['scripts' => [
		'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js',
		'/js/master.js',
		'/js/components.js',
		'/js/vendor.js',
	]])
	@yield('custom-js')
</div>
<div class="layout__other"></div>
</body>
</html>