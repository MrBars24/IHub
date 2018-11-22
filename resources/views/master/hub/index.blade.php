@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					@include('components.heading', ['heading' => 'Hubs'])
					@include('master._layout.alert')
					<a href="{{ route('master::hub.create') }}" class="btn btn-primary __mb1">Create Hub</a>
					<div class="panel panel-default">
						<div class="panel-heading"><h2>Active hubs</h2></div>
						<div class="panel-body">
							@if(!$active_hubs->isEmpty())
								{!! Form::open(['url' => route('master::hub.action'), 'method' => 'post', 'class' => '', 'id' => 'active-hubs-form']) !!}
								{!!
									$active_hubs_table->columns([
										'name' => 'Name',
										'manager' => 'Hub manager',
										'email' => 'Email address',
										'influencers' => 'Influencers',
										'posts' => 'Posts',
										'active_at' => 'Date active since',
										'actions' => ''
									])
									->attributes([
										'id' => 'table-active-hubs',
										'class' => 'datatable',
									])
									->render('components.datatable')
								!!}
								<div class="field text-right">
									{!! Form::button('Deactivate', ['name' => 'deactivate', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
								</div>
								{!! Form::close() !!}
							@else
								<p>There are no active hubs</p>
							@endif
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading"><h2>Inactive hubs</h2></div>
						<div class="panel-body">
							@if(!$inactive_hubs->isEmpty())
								{!! Form::open(['url' => route('master::hub.action'), 'method' => 'post', 'class' => '', 'id' => 'inactive-hubs-form']) !!}
								{!!
									$inactive_hubs_table->columns([
										'name' => 'Name',
										'manager' => 'Hub manager',
										'email' => 'Email address',
										'influencers' => 'Influencers',
										'posts' => 'Posts',
										'inactive_at' => 'Date inactive since',
										'actions' => '&nbsp'
									])
									->attributes([
										'id' => 'table-inactive-hubs',
										'class' => 'datatable',
									])
									->render('components.datatable')
								!!}
								<div class="field text-right">
									{!! Form::button('Delete', ['name' => 'delete', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
									{!! Form::button('Activate', ['name' => 'activate', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
								</div>
								{!! Form::close() !!}
							@else
								<p>There are no inactive hubs</p>
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
			$(document).on('click', '#table-inactive-hubs input[type="checkbox"]', function(){
				if(getCheckboxCount() > 0){
					$('#inactive-hubs-form button[type="submit"]').removeClass("+disabled");
				}else{
					$('#inactive-hubs-form button[type="submit"]').addClass("+disabled");
				}
			});

			$(document).on('click', '#table-active-hubs input[type="checkbox"]', function(){
				if(getCheckboxCount('active') > 0){
					$('#active-hubs-form button[type="submit"]').removeClass("+disabled");
				}else{
					$('#active-hubs-form button[type="submit"]').addClass("+disabled");
				}
			});

			$('#table-active-hubs').datatable({
				dataset: {!! $active_hubs->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '#active-hubs-form .table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-manager      col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-influencers  col-xs-0 col-sm-0 col-md-1 col-lg-1',
					'column-posts        col-xs-0 col-sm-0 col-md-2 col-lg-2',
					'column-date-active  col-xs-2 col-sm-2 col-md-2 col-lg-2',
					'column-actions      col-xs-1 col-sm-1 col-md-1 col-lg-1   text-right'
				],
				row: function(item) {
					return [
						$(item).nested('name'),
						$(item).nested('manager.user.name'),
						$(item).nested('manager.user.email'),
						$(item).nested('member_count'),
						$(item).nested('post_count'),
						$(item).nested('activated_at_formatted'),
						'<label data-rowaction><input type="checkbox" name="item[' + $(item).nested('id') + ']" value="yes" /></label>' +
						'<a data-rowlink class="button" href="' + $(item).nested('master_edit') + '"><i class="fa fa-external-link"></i></a>'
					];
				},
				modifyRow: function(item) {
					var classes = [
						'hub-item'
					];
				},
				pagination: true
			});

			$('#table-inactive-hubs').datatable({
				dataset: {!! $inactive_hubs->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '#inactive-hubs-form .table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-manager      col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-influencers  col-xs-0 col-sm-0 col-md-1 col-lg-1',
					'column-posts        col-xs-0 col-sm-0 col-md-2 col-lg-2',
					'column-date-deactive  col-xs-2 col-sm-2 col-md-2 col-lg-2',
					'column-actions      col-xs-1 col-sm-1 col-md-1 col-lg-1   text-right'
				],
				row: function(item) {
					return [
						$(item).nested('name'),
						$(item).nested('manager.user.name'),
						$(item).nested('manager.user.email'),
						$(item).nested('member_count'),
						$(item).nested('post_count'),
						$(item).nested('deactivated_at_formatted'),
						'<label data-rowaction><input type="checkbox" name="item[' + $(item).nested('id') + ']" value="yes" /></label>' +
						'<a data-rowlink class="button" href="' + $(item).nested('master_edit') + '"><i class="fa fa-external-link"></i></a>'
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
			function getCheckboxCount(type = 'inactive'){
				if(type == 'inactive') {
					return $('#table-inactive-hubs input[type="checkbox"]:checked').length;
				}else{
					return $('#table-active-hubs input[type="checkbox"]:checked').length;
				}
			}
		});
	</script>
@endsection