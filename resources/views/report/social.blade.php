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
					<h3 class="reports__metric__title text-center">SHARES FROM PLATFORM</h3>
					<div class="container">
						<canvas id="social-share" width="1110" height="493"></canvas>
					</div>
				</div>
				<div class="col-xs-12 reports__metric">
					<h3 class="reports__metric__title text-center">AVERAGE FOLLOWER COUNTS</h3>
					<div class="container">
						<canvas id="social-follower" width="1110" height="493"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script>
	var ctxShare = document.getElementById("social-share").getContext('2d');
	var ctxFollower = document.getElementById("social-follower").getContext('2d')

	var socialShare = new Chart(ctxShare, {!! json_encode($chart_social_shares) !!});
	var socialFollower = new Chart(ctxFollower, {!! json_encode($chart_social_followers) !!})
</script>