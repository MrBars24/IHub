@extends('report._layout.main')
<div class="container">
	<div class="report-gig">
		<div class="container">
			<h1 class="text-center">{{ $title }}</h1>
			<h4 class="text-center">{{ 	Carbon\Carbon::parse($start_date)->format('F j, Y') }} - {{ Carbon\Carbon::parse($end_date)->format('F j, Y') }}</h4>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title text-center">TOTAL POINT ACCRUALS</h3>
					<div class="container">
						<canvas id="influencer-total" width="525" height="223"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title text-center">AVERAGE POINT ACCRUALS</h3>
					<div class="container">
						<canvas id="influencer-average" width="525" height="223"></canvas>
					</div>
				</div>
				
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">TOP PERFORMERS</h3>
					<div class="container">
						<table class="table">
							<thead>
								<tr>
									<th data-key="title" class="pure-u-1-2">
										Name
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_high_performers['data'] as $influencer)
								<tr>
									<td>{{ $influencer['name'] }}</td>
									<td>{{ $influencer['points'] }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">BOTTOM PERFORMERS</h3>
					<div class="container">
						<table class="table">
							<thead>
								<tr>
									<th data-key="title" class="pure-u-1-2">
										Name
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_low_performers['data'] as $influencer)
								<tr>
									<td>{{ $influencer['name'] }}</td>
									<td>{{ $influencer['points'] }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">BOTTOM INFLUENCERS</h3>
					<div class="container">
						<table class="table">
							<thead>
								<tr>
									<th data-key="title" class="pure-u-1-2">
										Name
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
									<th data-key="completed_count" class="pure-u-1-4">
										Followers
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_high_influencers['data'] as $influencer)
								<tr>
									<td>{{ $influencer['name'] }}</td>
									<td>{{ $influencer['points'] }}</td>
									<td>{{ $influencer['followers'] }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">BOTTOM PERFORMING GIGS</h3>
					<div class="container">
						<table class="table">
							<thead>
								<tr>
									<th data-key="title" class="pure-u-1-2">
										Name
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
									<th data-key="completed_count" class="pure-u-1-4">
										Followers
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_low_influencers['data'] as $influencer)
								<tr>
								<td>{{ $influencer['name'] }}</td>
									<td>{{ $influencer['points'] }}</td>
									<td>{{ $influencer['followers'] }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script>
	var ctxTotal = document.getElementById("influencer-total").getContext('2d');
	var ctxAverage = document.getElementById("influencer-average").getContext('2d')

	var influencerTotal = new Chart(ctxTotal, {!! json_encode($chart_influencer_total_points) !!});
	var influencerAverage = new Chart(ctxAverage, {!! json_encode($chart_influencer_average_points) !!})
</script>