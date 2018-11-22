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
	<link rel="icon" type="image/png" href="/f/favicon-16x16.png" sizes="16x16">
	<!-- Script for polyfilling Promises on IE9 and 10 -->
</head>
<body>
	<div id="app">
		Your {{ $account["provider"] }} account is now connected.
	</div>

	<script>
		window.addEventListener('load', function() {
			var _account = window.account ? window.account : {data: null, error: true}
			localStorage.setItem('linked_account', JSON.stringify(_account))
		})
	</script>
</body>
</html>
