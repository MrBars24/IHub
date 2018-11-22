@extends('email.layout.standard')

@section('content-inner')
	<div class="alert alert-error" style="text-overflow: clip;
    word-break: break-all;
    display: block;">
    {{ $content }}
  </div>
@endsection