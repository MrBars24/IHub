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
		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">TOTAL POINT ACCRUALS</h3>
			<line-chart
				:data="influencerTotalPoints"
				:options="influencerTotalPointsOpts"
			>
			</line-chart>
		</div>

		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">AVERAGE POINT ACCRUALS</h3>
			<line-chart
				:data="influencerAveragePoints"
				:options="influencerAveragePointsOpts"
			>
			</line-chart>
		</div>

		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">TOP PERFORMERS</h3>
			<table-chart
				:data="performerHighList"
				:columns="performerColumns"
			>
			</table-chart>
		</div>

		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">BOTTOM PERFORMERS</h3>
			<table-chart
				:data="performerLowList"
				:columns="performerColumns"
			>
			</table-chart>
		</div>

		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">TOP INFLUENCERS</h3>
			<table-chart
				:data="influencerHighList"
				:columns="influencerColumns"
			>
			</table-chart>
		</div>

		<div class="col-xs-6 reports__metric">
			<h3 class="reports__metric__title">BOTTOM INFLUENCERS</h3>
			<table-chart
				:data="influencerLowList"
				:columns="influencerColumns"
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
			currentScreen : 'influencers',
			influencerAveragePoints : [],
			influencerTotalPoints : [],
			influencerAveragePointsOpts : [],
			influencerTotalPointsOpts : [],
			influencerHighList : [],
			influencerLowList : [],
			performerHighList : [],
			performerLowList : [],
			startDate : "",
			endDate : "",
			exportUrl : "",
			performerColumns : [
				'name',
				'points',
			],
			influencerColumns : [
				'name',
				'points',
				'followers',
			],
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
			this.chartData = this.fetchInfluencersReport()
		}
	},

	watch: {
		init (value) {
			if (value)
				this.exportUrl = `${App.baseUrl}api/${this.hub.slug}/reporting/preview/influencers`; 
				this.chartData = this.fetchInfluencersReport()
		}
	},

	methods: {
		fetchInfluencersReport () {
			this.$store.dispatch('fetchInfluencersReport', {
				hub: this.hub,
				startDate: (this.startDate == "") ? "" : this.startDate,
				endDate: (this.endDate == "") ? "" : this.endDate,
			}).then(response => {
				this.startDate = response.start_date;
				this.endDate = response.end_date;

				this.influencerAveragePoints = response.chart_influencer_average_points.data;
				this.influencerTotalPoints = response.chart_influencer_total_points.data;
				
				this.influencerAveragePointsOpts = response.chart_influencer_average_points.options;
				this.influencerTotalPointsOpts = response.chart_influencer_total_points.options;
				
				this.influencerHighList = response.list_high_influencers;
				this.influencerLowList = response.list_low_influencers
				
				this.performerHighList = response.list_high_performers;
				this.performerLowList = response.list_low_performers;
			})
		},
		
		dateChange () {
			this.fetchInfluencersReport();
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