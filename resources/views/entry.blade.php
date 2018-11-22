<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="apple-touch-icon" sizes="57x57" href="/f/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/f/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/f/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/f/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/f/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/f/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/f/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/f/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/f/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="/f/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/f/favicon-194x194.png" sizes="194x194">
	<link rel="icon" type="image/png" href="/f/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="/f/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="\/f/favicon-16x16.png" sizes="16x16">
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<script>
		window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]); !!};

		window.App = {!! json_encode([
			'baseUrl'       => url('/') . '/',
			'baseEnv'       => app()->environment(),
			'secret'        => "berg709KSLpaqgXS6yQJyBqVeoqz4rBWlNrYzlXc", // get the value of this from .env and this should be environment based..
			'pusherKey'     => config('broadcasting.connections.pusher.key'),  // get the value of this from .env or config
			'pusherCluster' => config('broadcasting.connections.pusher.options.cluster')
		]); !!};
	</script>
	<!-- Script for polyfilling Promises on IE9 and 10 -->
	<script src='https://cdn.polyfill.io/v2/polyfill.min.js'></script>
	<script async data-cfasync="false" src="https://d29l98y0pmei9d.cloudfront.net/js/widget.min.js?k=Y2xpZW50SWQ9MjI4OSZob3N0TmFtZT1pbmZsdWVuY2VyaHViLnN1cHBvcnRoZXJvLmlv"></script>
</head>
<body class="pushmenu-push">
	<div class="splash-screen" id="js-splash-screen">
		<div class="splash-screen-items-wrapper">
			<img class="splash-screen-image" src="/images/logo-alt@2x.png" alt="Influencer Hub">
			<p class="splash-screen-text">Loading</p>
			<ul class="splash-screen-dots">
				<li class="dot dot-1"></li>
				<li class="dot dot-2"></li>
				<li class="dot dot-3"></li>
				<li class="dot dot-4"></li>
				<li class="dot dot-5"></li>
				<li class="dot dot-6"></li>
			</ul>
		</div>
	</div>
	<div id="app">
		<influencer-hub></influencer-hub>
	</div>
	<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
