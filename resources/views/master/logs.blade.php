@extends('master._layout.layout')

@section('main')
	<div class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
				@include('components.heading', ['heading' => $logs['title']])
				
				<div class="panel panel-default">
					<div class="panel-heading"><h2>Active hubs</h2></div>
					<div class="panel-body">
						@if($logs)
							<div class="table__responsive">
								<table class="table">
									<thead>
										<tr>
											@foreach($logs['columns'] as $column)
												<th class="cell">{{ $column['label'] }}</th>
											@endforeach
										</tr>
									</thead>
									<tbody>
										@foreach($logs['items'] as $item)
											<tr>
												@foreach($logs['columns'] as $key => $column)
													<td>{{ nested($item, $key, '--') }}</td>
												@endforeach
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
							{{ $logs['items']->links() }}
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection