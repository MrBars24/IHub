<template>
<div>
	<div class="submenu visible-xs visible-sm">
		<div class="container">
			<ul class="row text-center">
				<li :class="submenuClasses">
					<router-link :to="{name: 'my.gigs.scheduled'}" exact>
						<i class="js-branding-fa fa fa-clock-o"></i>
					</router-link>
				</li>
				<li :class="submenuClasses">
	        <router-link :to="{name: 'my.gigs.rejected'}" exact>
						<i class="js-branding-fa fa fa-ban"></i>
					</router-link>
				</li>
				<li :class="submenuClasses">
	        <router-link :to="{name: 'my.gigs.pending'}" exact>
						<i class="js-branding-fa fa fa-question-circle"></i>
					</router-link>
				</li>
				<li :class="submenuClasses" v-if="isHubManager">
					<router-link :to="{name: 'my.gigs.feed'}" exact>
						<i class="js-branding-fa fa fa-plus-circle"></i>
					</router-link>
				</li>
			</ul>
		</div>
	</div>
	<div id="display-area">
		<!-- <div class="bg-white-outer visible-md visible-lg"></div> -->
		<div class="container" v-cloak>
			<div class="row">
				<div class="col-md-4 menu-area visible-md visible-lg">
					<ul class="links sidemenu">
						<li>
							<router-link :to="{name: 'my.gigs.scheduled'}">
								<i class="js-branding-fa fa fa-clock-o"></i> Scheduled Posts
								<span v-if="total.scheduled" 
									class="js-branding-counter circle-counter">{{ total.scheduled }}</span>
							</router-link>
						</li>
						<li>
							<router-link :to="{name: 'my.gigs.rejected'}">
								<i class="js-branding-fa fa fa-ban"></i> Rejected Posts
								<span v-if="total.rejected" 
									class="js-branding-counter circle-counter">{{ total.rejected }}</span>
							</router-link>
						</li>
						<li>
							<router-link :to="{name: 'my.gigs.pending'}">
								<i class="js-branding-fa fa fa-question-circle"></i> Posts Pending Approval
								<span v-if="total.pending" 
									class="js-branding-counter circle-counter">{{ total.pending }}</span>
							</router-link>
						</li>
						<li v-if="isHubManager">
							<router-link :to="{name: 'my.gigs.feed'}">
								<i class="js-branding-fa fa fa-plus-circle"></i> Gig Feeds
								<span v-if="total.feeds_list" 
									class="js-branding-counter circle-counter">{{ total.feeds_list }}</span>
							</router-link>
						</li>
					</ul>
				</div>

				<div class="col-sm-12 col-md-7 col-md-offset-1 content-area">
					<router-view></router-view>
				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import mixinUser from '../mixins/user'
import mixinHub from '../mixins/hub'

export default {
	name: 'MyGigs',

	mixins: [mixinUser, mixinHub],

	mounted () {
		if (this.init) {
			this.fetchTotal()
		}
	},

	watch: {
		'$route': 'fetchTotal',
		init (value) {
			if (value)
				this.fetchTotal()
		}
	},

	methods: {
		fetchTotal () {
			if (!this.total.fetched) {
				this.$store.dispatch('getTotalMyGigs', this.hub)
					.then(response => {
						let routeParts = this.$route.name.split('.')
						if (routeParts.length > 3) {
							return
						}
						
						let data = response.data.data
						let tab = data.active_tab
						// ignore if the current tab has data
						let currentRoute = routeParts[2] === 'feed' ? 'feeds_list' : routeParts[2]
						if (data[currentRoute] > 0) 
							return
							
						tab = tab === 'feeds_list' ? 'feed' : tab
						this.$router.replace({
							name: 'my.gigs.'+tab
						})
					})
					.catch(error => {
						console.error(error)
					})
			}
		}
	},

	computed: {
		total () {
			let total = this.$store.state.MyGig.total
			return total
		},
		
		submenuClasses () {
			return this.isHubManager ? 'col-xs-3 nav-item' : 'col-xs-4 nav-item'
		}
	}
}
</script>