<template>
<div>
	<router-view name="submenu"></router-view>
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="text-center" v-if="loaders.leaderboard">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
					<div class="leaderboard-box">
						<div class="text-center" 
							v-if="!loaders.leaderboard && !leaderboard.length && loaders.loaded"
							v-cloak>
							<p>Data is Empty</p>
						</div>
						<!-- populate the lead -->
						<lead v-for="(lead,index) in leaderboard"
							:lead="lead"
							:key="lead.id"
							:rank="(index+1)">
						</lead>
						
					</div><!-- /leaderboard-box -->
				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import List from '../components/leaderboard/List.vue'
import mixinHub from '../mixins/hub'
import mixinLeaderboard from '../mixins/leaderboard'
export default {
	components: {
		'lead': List
	},

	mixins: [mixinHub, mixinLeaderboard],
	
	data () {
		return {
			loaders: {
				leaderboard: false,
				loaded: false
			}
		}
	},

	mounted () {
		if (this.init) {
			this.fetchLeaderboard()
		}
	},

	watch: {
		'$route': 'fetchLeaderboard',
		init (value) {
			if (value) {
				this.fetchLeaderboard()
			}
		}
	},

	methods: {
		fetchLeaderboard () {
			this.loaders.leaderboard = true
			this.loaders.loaded = false
			this.$store.dispatch('getLeaderboard', this.hub)
				.then(response => {
					this.loaders.loaded = true
					this.loaders.leaderboard = false
				})
		}
	}
}
</script>