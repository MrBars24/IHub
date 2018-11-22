<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div id="app">
	<form action="{{ route('test::sandpit.post', ['method' => 'commandRunner']) }}" method="post" style="width: 600px; margin: 100px auto;">
		@if($error)
			<div style="color: red">{{ $error }}</div>
		@endif
		@if($success)
			<div style="color: green">{{ $success }}</div>
		@endif
		<h2>Run an Artisan command from below:</h2>
		<ul>
			@foreach($commands as $key => $command)
				<li>
					<label><input type="radio" name="command" value="{{ $key }}" /> {{ $command['label'] }}</label>
				</li>
			@endforeach
		</ul>
		<input type="password" name="password" value="" placeholder="Type verification password to run this command" style="width: 300px;" />
		<input type="submit" name="submit" value="Run" style="width: 120px;" />
		{{ csrf_field() }}
	</form>
</div>
</body>
</html>