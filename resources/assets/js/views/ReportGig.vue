<template> 
<div class="report-gig">
	<div class="row reports__metric">
		<div class="col-xs-6">
			<h1>Reporting</h1>
			<router-link class="link"
				:to="{ name: 'report.history', query: { screen: currentScreen, start_date: startDate, end_date: endDate }}">
				View snapshot history
			</router-link>
		</div>
		<div class="col-xs-6">
			<div class="row">
				<div class="col-xs-6">
					<label for="start_date" class="field__label">Start date</label>
					<input class="custom-select"
						placeholder="YYYY/MM/DD"
						type="date"
						id="start_date"
						v-model="startDate"
						@change="dateChange">
				</div>
				<div class="col-xs-6">
					<label for="end_date" class="field__label">End date</label>
					<input class="custom-select"
						placeholder="YYYY/MM/DD"
						type="date"
						id="end_date"
						v-model="endDate"
						@change="dateChange">
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 reports__metric">
			<h3 class="reports__metric__title">Gig counts</h3>
			<line-chart
				:data="gigNumbersData"
				:options="gigNumbersOption"
			>
			</line-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">GIG PARTICIPATION (PLATFORMS)</h3>
			<doughnut-chart
				:data="gigPlatformsData"
				:options="gigPlatformsOption"
			>
			</doughnut-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">GIG PARTICIPATION (PLATFORMS)</h3>
			<doughnut-chart
				:data="gigParticipationData"
				:options="gigParticipationOption"
			>
			</doughnut-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">GIG PUNCTUALITY (FIRST POST)</h3>
			<line-chart
				:data="gigPunctualityData"
				:options="gigPunctualityOption"
			>
			</line-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">GIG PUNCTUALITY (COMPLETION)</h3>
			<line-chart
				:data="gigCompletionData"
				:options="gigCompletionOption"
			>
			</line-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">TOP PERFORMING GIGS</h3>
			<table-chart
				:data="gigHighList"
				:columns="columns"
			>
			</table-chart>
		</div>
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">BOTTOM PERFORMING GIGS</h3>
			<table-chart
				:data="gigLowList"
				:columns="columns"
			>
			</table-chart>
		</div>
	</div>

	<div class="panel reports__export"> 
		<p>Use the button below to download the current report</p> 
		<button class="btn-full-width btn-gig-save js-branding-button" @click="downloadReport" ref="expButton">Download Report</button>
	</div> 
</div>
</template> 
<script> 

import LineChart from '../components/charts/LineChart.vue';
import DoughnutChart from '../components/charts/DoughnutChart.vue';
import TableChart from '../components/charts/TableChart.vue';
import mixinHub from '../mixins/hub'
import mixinUser from '../mixins/user'

export default {
	name: 'Reporting',

	mixins: [mixinHub, mixinUser],

	data () {
		return {
			currentScreen : 'gigs',
			gigNumbersData : [],
			gigNumbersOption : [],
			gigPlatformsData : [],
			gigPlatformsOption : [],
			gigParticipationData : [],
			gigParticipationOption : [],
			gigCompletionData : [],
			gigCompletionOption : [],
			gigHighList : [],
			gigLowList : [],
			startDate : "",
			endDate : "",
			exportUrl : "",
			columns : [
				'title',
				'completed_count',
				'points',
			]
		}
	},

	components: {
		LineChart,
		DoughnutChart,
		TableChart
	},

	mounted: function(){
		let params = this.getParameters();
		if(params.start_date != null){
			this.startDate = params.start_date;
		}

		if(params.end_date != null){
			this.endDate = params.end_date;
		}

		if (this.init) {
			this.chartData = this.fetchGigReport()
		}
	},

	watch: {
		init (value) {
			if (value) {
				this.exportUrl = `${App.baseUrl}api/${this.hub.slug}/reporting/preview/gigs`; 
				this.chartData = this.fetchGigReport()
			}
		}
	},

	methods: {
		fetchGigReport () {
			this.$store.dispatch('fetchGigReport', {
				hub: this.hub,
				startDate: (this.startDate == "") ? "" : this.startDate,
				endDate: (this.endDate == "") ? "" : this.endDate,
			}).then(response => {
				this.startDate = response.start_date;
				this.endDate = response.end_date;

				this.gigNumbersData = response.chart_gig_numbers.data;
				this.gigNumbersOption = response.chart_gig_numbers.options;

				this.gigPlatformsData = response.chart_gig_participation_platforms.data;
				this.gigPlatformsOption = response.chart_gig_participation_platforms.options;

				this.gigParticipationData = response.chart_gig_participation_categories.data;
				this.gigParticipationOption = response.chart_gig_participation_categories.options;

				this.gigPunctualityData = response.chart_gig_punctuality_first_post.data;
				this.gigPunctualityOption = response.chart_gig_punctuality_first_post.options;

				this.gigCompletionData = response.chart_gig_punctuality_completion.data;
				this.gigCompletionOption = response.chart_gig_punctuality_completion.options;

				this.gigHighList = response.list_high_gigs;
				this.gigLowList = response.list_low_gigs;
			})
		},
		
		dateChange () {
			this.fetchGigReport();
		},

		downloadReport () {
			this.$refs.expButton.innerHTML = "Generating";
			this.$refs.expButton.classList.add("+disabled");

			this.$store.dispatch('fetchGeneratedReport', {
				hub: this.hub,
				url : this.exportUrl
			}).then((cb) => {
				this.$refs.expButton.innerHTML = "Download Report";
				this.$refs.expButton.classList.remove("+disabled");
			})
		}
	}
}
</script>