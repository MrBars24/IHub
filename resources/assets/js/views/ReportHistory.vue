<template> 
<div class="report-gig">
	<div class="history-box center-block">
		<h1>Snapshot History</h1>
		<a v-if="this.screen" class="link" @click="historyBack">Back To Reports</a>
		
		<div class="list-container">
			<div class="row history-card" v-for="history in historyData" :key="history.id">
				<div class="col-xs-12 col-md-9">
					<h3 class="history-title">{{ history.export_file }}</h3>
					<span class="history-attribute">
						<label>Report type:</label> {{ history.screen.replace(/^\w/, c => c.toUpperCase()) }}
					</span>
					<span class="history-attribute">
						<label>Run type:</label> {{ getRunType(history.run_type) }}
					</span>
					<span class="history-attribute">
						<label>Date generated:</label> {{ computeAgo(history.created_at) }}
					</span>
				</div>
				<div class="col-xs-12 col-md-3 button-container">
					<a :href="history.download_url" target="_blank" class="btn-submit js-branding-button text-center">
						Download
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
</template> 
<script> 

import mixinHub from '../mixins/hub'
import mixinUser from '../mixins/user'
import moment from 'moment'

export default {
	name: 'Reporting',

	mixins: [mixinHub, mixinUser],

	data () {
		return {
			historyData : [],
			startDate : "",
			endDate : "",
			screen : "",
			historyUrl : "",
		}
	},

	mounted: function(){
		let params = this.getParameters();
		if(params.start_date != null){
			this.startDate = params.start_date;
		}

		if(params.end_date != null){
			this.endDate = params.end_date;
		}

		if(params.screen != null){
			this.screen = params.screen;
			this.historyUrl = {
				start_date : this.startDate,
				end_date : this.endDate,
			}
		}

		if (this.init) {
			this.fetchHistory();
		}
	},

	watch: {
		init (value) {
			if (value)
				this.fetchHistory();
		}
	},

	methods: {
		fetchHistory () {
			this.$store.dispatch('fetchReportHistory', {
				hub: this.hub,
				startDate: (this.startDate == "") ? "" : this.startDate,
				endDate: (this.endDate == "") ? "" : this.endDate,
				screen: (this.screen == "") ? "" : this.screen,
			}).then(response => {
				this.historyData = response.data.snapshots;
			})
		},

		computeAgo (date) {
			return moment(date).fromNow();
		},

		getRunType (type) {
			return (type === 'ondemand') ? 'On Demand' : 'Scheduled' 
		},

		historyBack () {
			this.$router.replace({
				name : 'report.'+ this.screen,
				query : this.historyUrl
			})
		}
	}
}
</script>