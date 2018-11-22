<template>
	<div id="display-area">
		<div class="container">
			<div class="row">

				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="message-container">

						<div class="text-center" v-if="loaders.notifications">
							<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
						</div>

						<div class="text-center" 
							v-if="!loaders.notifications && !notifications.length"
							v-cloak>
							<p>Data is Empty</p>
						</div>

						<!-- populate messages -->
						<notification v-for="notification in notifications" 
							:notification="notification"
							:key="notification.id">
						</notification>
					</div><!-- /message-container -->
				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import Notification from '../components/notifications/Item.vue'
import mixinHub from '../mixins/hub'
import mixinNotifications from '../mixins/notifications'

export default {
	name: 'Notifications',

	mixins: [mixinHub,mixinNotifications],

	components: {
		Notification
	},

	data () {
		return {
			loaders : {
				notifications: false
			}
		}
	},
	
	mounted () {
		this.fetchNotifications()
	},

	watch: {
		'$route': 'fetchNotifications'
	},

	methods: {
		fetchNotifications () {
			console.log('fetchNotifications')
			this.loaders.notifications = true
			setTimeout(() => {
				this.$store.dispatch('getNotifications', this.hub)
					.then(response => {
						this.loaders.notifications = false
					})
			}, this.loadInterval)
		}
	},
}
</script>