@extends('report._layout.main')
<div class="container">
	<div class="report-gig">
		<div class="container">
			<h1 class="text-center">{{ $title }}</h1>
			<h4 class="text-center">{{ 	Carbon\Carbon::parse($start_date)->format('F j, Y') }} - {{ Carbon\Carbon::parse($end_date)->format('F j, Y') }}</h4>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-xs-12 reports__metric">
					<h3 class="reports__metric__title text-center">Gig counts</h3>
					<div class="container">
						<canvas id="gig-counts" width="1110" height="493"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title text-center">GIG PARTICIPATION (PLATFORMS)</h3>
					<div class="container">
						<canvas id="gig-platforms" width="525" height="223"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">GIG PARTICIPATION (PLATFORMS)</h3>
					<div class="container">
						<canvas id="gig-participation" width="525" height="223"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">GIG PUNCTUALITY (FIRST POST)</h3>
					<div class="container">
						<canvas id="gig-punctuality" width="525" height="223"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">GIG PUNCTUALITY (COMPLETION)</h3>
					<div class="container">
						<canvas id="gig-completion" width="525" height="223"></canvas>
					</div>
				</div>
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title">TOP PERFORMING GIGS</h3>
					<div class="container">
						<table class="table">
							<thead>
								<tr>
									<th data-key="title" class="pure-u-1-2">
										Name
									</th>
									<th data-key="completed_count" class="pure-u-1-4">
										Completed
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_high_gigs['data'] as $gig)
								<tr>
									<td>{{ $gig['title'] }}</td>
									<td>{{ $gig['completed_count'] }}</td>
									<td>{{ $gig['points'] }}</td>
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
									<th data-key="completed_count" class="pure-u-1-4">
										Completed
									</th>
									<th data-key="points" class="pure-u-1-4">
										Points
									</th>
								</tr>
							</thead>
							<tbody>
								@foreach($list_low_gigs['data'] as $gig)
								<tr>
									<td>{{ $gig['title'] }}</td>
									<td>{{ $gig['completed_count'] }}</td>
									<td>{{ $gig['points'] }}</td>
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
	var ctxGigNumber = document.getElementById("gig-counts").getContext('2d');
	var ctxGigPlatform = document.getElementById("gig-platforms").getContext('2d');
	var ctxGigParticipation = document.getElementById("gig-participation").getContext('2d');
	var ctxGigPunctuality = document.getElementById("gig-punctuality").getContext('2d');
	var ctxGigCompletion = document.getElementById("gig-completion").getContext('2d');

	var gigNumber = new Chart(ctxGigNumber, {!! json_encode($chart_gig_numbers) !!});
	var gigPlatform = new Chart(ctxGigPlatform, {!! json_encode($chart_gig_participation_platforms) !!})
	var gigParticipation = new Chart(ctxGigParticipation, {!! json_encode($chart_gig_participation_categories) !!})
	var gigPunctuality = new Chart(ctxGigPunctuality, {!! json_encode($chart_gig_punctuality_first_post) !!})
	var gigCompletion = new Chart(ctxGigCompletion, {!! json_encode($chart_gig_punctuality_completion) !!})
</script>