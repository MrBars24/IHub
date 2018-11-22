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
			<h3 class="reports__metric__title">SHARES FROM PLATFORM</h3>
			<line-chart
				:data="socialShareData"
				:options="socialShareOpts"
			>
			</line-chart>
		</div>

		<div class="col-xs-12 reports__metric">
			<h3 class="reports__metric__title">AVERAGE FOLLOWER COUNTS</h3>
			<line-chart
				:data="socialFollowerData"
				:options="socialFollowerOpts"
			>
			</line-chart>
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
			currentScreen : 'social',
			socialShareData : [],
			socialShareOpts : [],
			socialFollowerData : [],
			socialFollowerOpts : [],
			startDate : "",
			endDate : "",
			exportUrl : "",
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
			this.chartData = this.fetchSocialReport()
		}
	},

	watch: {
		init (value) {
			if (value)
				this.exportUrl = `${App.baseUrl}api/${this.hub.slug}/reporting/preview/social`; 
				this.chartData = this.fetchSocialReport()
		}
	},

	methods: {
		fetchSocialReport () {
			this.$store.dispatch('fetchSocialReport', {
				hub: this.hub,
				startDate: (this.startDate == "") ? "" : this.startDate,
				endDate: (this.endDate == "") ? "" : this.endDate,
			}).then(response => {
				this.startDate = response.start_date;
				this.endDate = response.end_date;
				
				this.socialShareData = response.chart_social_shares.data;
				this.socialShareOpts = response.chart_social_shares.options;

				this.socialFollowerData = response.chart_social_followers.data;
				this.socialFollowerOpts = response.chart_social_followers.options;
			})
		},
		
		dateChange () {
			this.fetchSocialReport();
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