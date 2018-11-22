@extends('master._layout.master')

@section('csstyle', 'csstyle')

@section('sidebar')
	@include('components.sidebar')
@endsection

@section('header')
	@include('master._layout.header')
@endsection

@section('footer')
	@include('master._layout.footer')
@endsection