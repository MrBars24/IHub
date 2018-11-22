@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					@include('components.heading', ['heading' => 'Users'])
					@include('master._layout.alert')
					<a href="{{ route('master::staff.create') }}" class="btn btn-primary __mb1">Create Staff</a>
					<div class="panel panel-default">
						<div class="panel-heading"><h2>Staffs</h2></div>
						<div class="panel-body">
							@if(!$staffs->isEmpty())
								{!! Form::open(['url' => route('master::staff.action'), 'method' => 'put', 'class' => '', 'id' => 'staff-list-form']) !!}
									{!!
										$staffs->columns([
											'name' => 'Name',
											'email' => 'Email',
											'is_active' => 'Active',
											'created_at' => 'Created',
											'actions' => ''
										])
										->attributes([
											'id' => 'table-staffs',
											'class' => 'datatable',
										])
										->render('components.datatable')
									!!}

									<div class="field text-right">
										{!! Form::button('Deactivate', ['name'=>'deactivate', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Demote to user', ['name'=>'user_demote', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
									</div>
								{!! Form::close() !!}
							@else
								<p>There are no staffs</p>
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
			$(document).on('click', '#table-staffs input[type="checkbox"]', function(){
				if(getCheckboxCount() > 0){
					$('#staff-list-form button[type="submit"]').removeClass("+disabled");
				}else{
					$('#staff-list-form button[type="submit"]').addClass("+disabled");
				}
			});

			$('#table-staffs').datatable({
				dataset: {!! $staffs->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '.table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-3',
					'column-email        col-xs-4 col-sm-4 col-md-2 col-lg-3',
					'column-is-active    col-xs-0 col-sm-0 col-md-6 col-lg-1',
					'column-created-at   col-xs-4 col-sm-4 col-md-2 col-lg-4',
					'column-actions      col-xs-1 col-sm-1 col-md-0 col-lg-1   text-right'
				],
				row: function(item) {
					return [
						$(item).nested('name'),
						$(item).nested('email'),
						$(item).nested('is_active'),
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
			function getCheckboxCount(){
				return $('#table-staffs input[type="checkbox"]:checked').length;
			}
		});
	</script>
@endsection