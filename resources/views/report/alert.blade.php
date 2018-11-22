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
					<h3 class="reports__metric__title text-center">ALERT INTERACTIONS</h3>
					<div class="container">
						<canvas id="alert-interaction" width="1110" height="493"></canvas>
					</div>
				</div>

				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title text-center">ALERT CLICK THROUGH RATES</h3>
					<div class="container">
						<canvas id="alert-click" width="525" height="223"></canvas>
					</div>
				</div>
				
				<div class="col-xs-6 reports__metric">
					<h3 class="reports__metric__title text-center">CATEGORY PREFERENCES</h3>
					<div class="container">
						<canvas id="alert-category" width="525" height="223"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script>
	var ctxInteraction = document.getElementById("alert-interaction").getContext('2d');
	var ctxClick = document.getElementById("alert-click").getContext('2d')
	var ctxCategory = document.getElementById("alert-category").getContext('2d')

	var alertInteraction = new Chart(ctxInteraction, {!! json_encode($chart_alert_interactions) !!});
	var alertClick = new Chart(ctxClick, {!! json_encode($chart_alert_clickthrough_rates) !!})
	var alertCategory = new Chart(ctxCategory, {!! json_encode($chart_alert_category_preferences) !!})
</script>