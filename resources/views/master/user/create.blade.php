@extends('master._layout.layout')

@section('main')
<div class="content">
		<div class="container-fluid">
			@include('components.heading', ['heading' => 'Create User'])

			@include('master._layout.alert')

			{!! Form::model(['user' => $user], ['action' => $form_action, 'method' => 'post', 'id' => 'item-form']) !!}
				<div class="panel panel-default">
					<div class="panel-heading"><h2>Sub Heading</h2></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12 col-sm-6 col-lg-5 col-lg-offset-1">
								<div class="field">
									{!! Form::label('name', 'Name', ['class' => 'field__label']) !!}
									{!! Form::text('name', null, ['class' => 'field__text']) !!}
								</div>
							</div>
							<div class="col-xs-12 col-sm-6 col-lg-5">
								<div class="field">
									{!! Form::label('email', 'Email address', ['class' => 'field__label']) !!}
									{!! Form::text('email', null, ['class' => 'field__text']) !!}
								</div>
							</div>
							<div class="col-xs-12 col-sm-12 col-lg-10 col-lg-offset-1">
								<div class="field">
									{!! Form::label('summary', 'Summary', ['class' => 'field__label']) !!}
									{!! Form::textarea('summary', null, ['class' => 'field__text']) !!}
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
						</div>
					</div>
				</div>

				<div class="field text-center">
					{!! Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-primary btn-md']) !!}
				</div>
			{!! Form::close() !!}
		</div>
	</div>
@endsection