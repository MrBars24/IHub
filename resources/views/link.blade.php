<!DOCTYPE html>
<html lang="en">
<body>
	<script>
		window.appUri = {!! json_encode([
			'uri' => $uri,
			'fallback' => $fallback,
			'redirected' => $redirected
		]) !!}
	</script>
	<script>
	// temporary solution: let's assume that all users have app installed on their devices.
	window.location = window.appUri.uri;

	// redirect the user to fallback url after 1 sec
	setTimeout(function() {
		window.location = `${window.appUri.fallback}?redirected=${window.appUri.is_redirecting}`
	}, 1000)

	</script>
</body>
</html>

