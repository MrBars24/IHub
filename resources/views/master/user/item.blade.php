@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			@include('components.heading', ['heading' => $user->name])

			@include('master._layout.alert')
			@include('components.tab-component', ['part' => 'tab-pane-top'])

				@include('components.tab-component', ['part' => 'tab-content-top', 'tab' => 'details'])

					{!! Form::model(['user' => $user], ['action' => [$form_action, $user->slug], 'method' => 'put', 'id' => 'item-form']) !!}
						<div class="panel panel-default">
							<div class="panel-heading"><h2>Sub Heading</h2></div>
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
										<div class="field">
											{!! Form::label('name', 'Name', ['class' => 'field__label']) !!}
											{!! Form::text('name', $user->name, ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5">
										<div class="field">
											{!! Form::label('email', 'Email address', ['class' => 'field__label']) !!}
											{!! Form::text('email', $user->email, ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-lg-10 col-lg-offset-1">
										<div class="field">
											{!! Form::label('summary', 'Summary', ['class' => 'field__label']) !!}
											{!! Form::textarea('summary', $user->summary, ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
										<div class="field">
											{!! Form::label('password', 'Password', ['class' => 'field__label']) !!}
											{!! Form::password('password', ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-6 col-lg-5">
										<div class="field">
											{!! Form::label('confirm_password', 'Confirm Password', ['class' => 'field__label']) !!}
											{!! Form::password('confirm_password', ['class' => 'field__text']) !!}
										</div>
									</div>
									<div class="col-xs-12 col-sm-12 col-lg-12 text-center">
										<div class="field">
											{!! Form::label('is_active', 'Active', ['class' => 'field__label']) !!}
											{!! Form::checkbox('is_active', null, $user->is_active) !!}
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="field text-center">
							{!! Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-primary btn-md']) !!}
						</div>
					{!! Form::close() !!}

				@include('components.tab-component', ['part' => 'tab-content-bottom'])

				@include('components.tab-component', ['part' => 'tab-content-top', 'tab' => 'hubs'])

					<div class="panel panel-default">
						<div class="panel-heading"><h2>Current Hubs</h2></div>
						<div class="panel-body">
							<div class="row">
							@if(!$hubs->isEmpty())
								{!!
									$hubs->columns([
										'name' => 'Name',
										'manager' => 'Hub manager',
										'email' => 'Email address'
									])
									->attributes([
										'id' => 'table-hubs',
										'class' => 'datatable',
									])
									->render('components.datatable')
								!!}
							@else
								<p>There are no current hubs</p>
							@endif
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading"><h2>Pending Hubs</h2></div>
						<div class="panel-body">
							<div class="row">
							@if(!$pending_hubs->isEmpty())
								{!!
									$pending_hubs->columns([
										'name' => 'Name',
										'manager' => 'Hub manager',
										'email' => 'Email address'
									])
									->attributes([
										'id' => 'table-pending-hubs',
										'class' => 'datatable',
									])
									->render('components.datatable')
								!!}
							@else
								<p>There are no pending hubs</p>
							@endif
							</div>
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
			$('#table-hubs').datatable({
				dataset: {!! $hubs->toJson() !!},
				datasetKey: 'pending',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-manager      col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2'
				],
				row: function(item) {
					console.log(item);
					return [
						$(item).nested('name'),
						$(item).nested('manager.user.name'),
						$(item).nested('email')
					];
				},
				modifyRow: function(item) {
					var classes = [
						'hub-item'
					];
				},
				pagination: true
			});

			$('#table-pending-hubs').datatable({
				dataset: {!! $pending_hubs->toJson() !!},
				datasetKey: 'pending',
				columns: [
					'column-name         col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-manager      col-xs-3 col-sm-3 col-md-2 col-lg-2',
					'column-email        col-xs-3 col-sm-3 col-md-2 col-lg-2'
				],
				row: function(item) {
					console.log(item);
					return [
						$(item).nested('name'),
						$(item).nested('manager.user.name'),
						$(item).nested('email')
					];
				},
				modifyRow: function(item) {
					var classes = [
						'hub-item'
					];
				},
				pagination: true
			});
		});
	</script>
@endsection