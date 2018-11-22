@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			@include('components.heading', ['heading' => $hub->name])

			@include('master._layout.alert')
			@include('components.tab-component', ['part' => 'tab-pane-top'])

				@include('components.tab-component', ['part' => 'tab-content-top', 'tab' => 'details'])

					{!! Form::model(['hub' => $hub], ['action' => [$form_action, $hub->slug], 'method' => 'put', 'id' => 'item-form']) !!}
						<div class="panel panel-default">
							<div class="panel-heading"><h2>Details</h2></div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
										<div class="field">
											{!! Form::label('name', 'Name', ['class' => 'field__label']) !!}
											{!! Form::text('name', $hub->name, ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5">
										<div class="field">
											{!! Form::label('email', 'Email address', ['class' => 'field__label']) !!}
											{!! Form::text('email', $hub->email, ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-lg-12 text-center">
										<div class="field">
											{!! Form::label('is_active', 'Active', ['class' => 'field__label']) !!}
											{!! Form::checkbox('is_active', null, $hub->is_active) !!}
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="field text-center">
							<a href="/{{ $hub->slug }}" class="btn btn-primary btn-md" target="_blank">View</a>
							{!! Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-primary btn-md']) !!}
						</div>
					{!! Form::close() !!}

				@include('components.tab-component', ['part' => 'tab-content-bottom'])

				@include('components.tab-component', ['part' => 'tab-content-top', 'tab' => 'members'])

					<div class="panel panel-default">
						<div class="panel-heading">
							<h2>Hub Manger</h2>
						</div>
						<div class="panel-body">
							@if(isset($hub->manager->user))
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
										<h3>Name : {{ $hub->manager->user->name }}</h3>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5">
										<h3>Email : {{ $hub->manager->user->email }}</h3>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
										<h3>Appointed as hub manager : {{ Carbon\Carbon::parse($hub->manager->user->hubmanager_at)->format('dS M Y') }}</h3>
									</div>
								</div>
							@else
								<p>There is no hub manager</p>
							@endif
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h2>Members</h2>
						</div>
						<div class="panel-body">
							@if(!$members->isEmpty())
								{!! Form::open(['url' => route('master::hub.memberaction', $hub->slug), 'method' => 'put', 'class' => '', 'id' => 'member-list-form']) !!}
									{!!
										$hub_members_table->columns([
											'owned_by' => 'Name',
											'owner_email' => 'Email',
											'status' => 'Status',
											'joined_at' => 'Joined at',
											'points' => 'Points',
											'actions' => ''
										])
										->means('owned_by', 'user.name')
										->means('owner_email', 'user.email')
										->attributes([
											'id' => 'table-members',
											'class' => 'datatable',
										])
										->render('components.datatable')
									!!}
									<div class="field text-right">
										{!! Form::button('Remove from hub', ['name'=>'hub_remove', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Promote to hub manager', ['name'=>'user_promote', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Reset Points', ['name'=>'reset_points', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs +disabled']) !!}
										{!! Form::button('Reset All Points', ['name'=>'reset_all', 'type' => 'submit', 'class' => 'btn btn-primary btn-sm btn-full-xs']) !!}
									</div>
								{!! Form::close() !!}
							@else
								<p>There are no members</p>
							@endif
						</div>
					</div>

				@include('components.tab-component', ['part' => 'tab-content-bottom'])

			@include('components.tab-component', ['part' => 'tab-pane-bottom'])
		</div>
	</div>
@endsection

@section('custom-js')
	<script>
		jQuery(function($) {
			$(document).on('click', '#table-members input[type="checkbox"]', function(){
				if(getCheckboxCount('active') > 0) {
					$('#member-list-form button[type="submit"]:not(:last-child)').removeClass("+disabled");
				}else{
					$('#member-list-form button[type="submit"]:not(:last-child)').addClass("+disabled");
				}
			});

			$('#table-members').datatable({
				dataset: {!! $members->toJson() !!},
				datasetKey: 'pending',
				paginationBefore: '.table__responsive',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-2 col-sm-2 col-md-3 col-lg-2',
					'column-status       col-xs-0 col-sm-0 col-md-1 col-lg-2',
					'column-joined-at    col-xs-3 col-sm-3 col-md-3 col-lg-3',
					'column-points       col-xs-2 col-sm-2 col-md-2 col-lg-2',
					'column-actions      col-xs-1 col-sm-1 col-md-1 col-lg-1   text-right'
				],
				row: function(item) {
					return [
						$(item).nested('user.name'),
						$(item).nested('user.email'),
						$(item).nested('status'),
						$(item).nested('joined_at_formatted'),
						$(item).nested('points'),
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
			function getCheckboxCount() {
				return $('#table-members input[type="checkbox"]:checked').length;
			}
		});
	</script>
@endsection