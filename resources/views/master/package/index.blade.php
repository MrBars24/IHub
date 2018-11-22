@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<div class="heading">
						<h1 class="text-center">Hello {{ $auth_user->name }}</h1>
						<h2 class="text-center">Welcome to the Influencer HUB package index page.</h2>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection