@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					@include('components.heading', ['heading' => 'Users'])
					@include('master._layout.alert')
					<a href="{{ route('master::user.create') }}" class="btn btn-primary __mb1">Create User</a>
					<div class="panel panel-default">
						<div class="panel-heading"><h2>Active Users</h2></div>
						<div class="panel-body">
							@if(!$active_users->isEmpty())
								{!! Form::open(['url' => route('master::user.action'), 'method' => 'put', 'class' => '', 'id' => 'active-user-list-form']) !!}
									{!!
										$users_table->columns([
											'name' => 'Name',
											'email' => 'Email',
											'hubs' => 'Hubs',
											'created_at' => 'Created',
											'actions' => ''
										])
										->attributes([
											'id' => 'table-active-users',
											'class' => 'datatable',
										])
										->render('components.datatable')
									!!}

									<div class="field text-right">
										{!! Form::button('Deactivate', ['name'=>'deactivate', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Remove from Hubs', ['name'=>'hubs_remove', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
									</div>
								{!! Form::close() !!}
							@else
								<p>There are no active users</p>
							@endif
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading"><h2>Inactive Users</h2></div>
						<div class="panel-body">
							@if(!$inactive_users->isEmpty())
								{!! Form::open(['url' => route('master::user.action'), 'method' => 'put', 'class' => '', 'id' => 'inactive-user-list-form']) !!}
									{!!
										$users_table->columns([
											'name' => 'Name',
											'email' => 'Email',
											'hubs' => 'Hubs',
											'created_at' => 'Created',
											'actions' => ''
										])
										->attributes([
											'id' => 'table-inactive-users',
											'class' => 'datatable',
										])
										->render('components.datatable')
									!!}

									<div class="field text-right">
										{!! Form::button('Activate', ['name'=>'activate', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Delete', ['name'=>'delete', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
									</div>
								{!! Form::close() !!}
							@else
								<p>There are no inactive users</p>
							@endif
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
@endsection

@section('custom-js')
	<script>
		jQuery(function($) {
			$(document).on('click', '#table-active-users input[type="checkbox"]', function(){
				if(getCheckboxCount() > 0){
					$('#active-user-list-form button[type="submit"]').removeClass("+disabled");
				}else{
					$('#active-user-list-form button[type="submit"]').addClass("+disabled");
				}
			});

			$(document).on('click', '#table-inactive-users input[type="checkbox"]', function(){
				if(getCheckboxCount('inactive') > 0){
					$('#inactive-user-list-form button[type="submit"]').removeClass("+disabled");
				}else{
					$('#inactive-user-list-form button[type="submit"]').addClass("+disabled");
				}
			});

			$('#table-active-users').datatable({
				dataset: {!! $active_users->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '#active-user-list-form .table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-hubs         col-xs-0 col-sm-0 col-md-6 col-lg-5',
					'column-created-at   col-xs-2 col-sm-2 col-md-2 col-lg-2',
					'column-actions      col-xs-1 col-sm-1 col-md-0 col-lg-1   text-right'
				],
				row: function(item) {
					
					return [
						$(item).nested('name'),
						$(item).nested('email'),
						$(item).nested('hubs'),
						$(item).nested('created_at_formatted'),
						'<label data-rowaction><input type="checkbox" name="item[' + $(item).nested('id') + ']" value="yes" /></label>' +
						'<a data-rowlink class="button" href="' + $(item).nested('user_edit') + '"><i class="fa fa-external-link"></i></a>'
					];
				},
				modifyRow: function(item) {
					var classes = [
						'hub-item'
					];
				},
				pagination: true
			});

			$('#table-inactive-users').datatable({
				dataset: {!! $inactive_users->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '#inactive-user-list-form .table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-hubs         col-xs-0 col-sm-0 col-md-6 col-lg-5',
					'column-created-at   col-xs-2 col-sm-2 col-md-2 col-lg-2',
					'column-actions      col-xs-1 col-sm-1 col-md-0 col-lg-1   text-right'
				],
				row: function(item) {
					return [
						$(item).nested('name'),
						$(item).nested('email'),
						$(item).nested('hubs'),
						$(item).nested('created_at_formatted'),
						'<label data-rowaction><input type="checkbox" name="item[' + $(item).nested('id') + ']" value="yes" /></label>' +
						'<a data-rowlink class="button" href="' + $(item).nested('user_edit') + '"><i class="fa fa-external-link"></i></a>'
					];
				},
				modifyRow: function(item) {
					var classes = [
						'hub-item'
					];
				},
				pagination: true
			});
			
			// helpers
			function getCheckboxCount(type = 'active'){
				if(type == 'active') {
					return $('#table-active-users input[type="checkbox"]:checked').length;
				}else{
					return $('#table-inactive-users input[type="checkbox"]:checked').length;
				}
			}
		});
	</script>
@endsection