@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
			
				<div class="col-sm-4">
					<a href="logs/postdispatch" class="btn btn-block btn-primary">Post Dispatch Queue /Logs</a>
				</div>
				<div class="col-sm-4">
				<a href="logs/migration" class="btn btn-block btn-primary">Migration Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/gigfeedpost" class="btn btn-block btn-primary">Gig Feed Post Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/gigfeed" class="btn btn-block btn-primary">Gig Feed Queue Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/facebookconnections" class="btn btn-block btn-primary">Facebook Connection Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/twitterconnections" class="btn btn-block btn-primary">Twitter Connection Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/instagramconnections" class="btn btn-block btn-primary">Instagram Connection Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/notification" class="btn btn-block btn-primary">Notification Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/sessions" class="btn btn-block btn-primary">Session Logs</a>
				</div>
				<div class="col-sm-4">
					<a href="logs/login-histories" class="btn btn-block btn-primary">Login History Logs</a>
				</div>
				
			</div>
		</div>
	</div>
@endsection