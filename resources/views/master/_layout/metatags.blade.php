<title>{{ $title }}</title>
@foreach($meta as $key => $value)
	<meta name="{{ $key }}" content="{{ $value }}">
@endforeach