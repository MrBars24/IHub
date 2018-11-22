<template>
<div class="message-item">
	<div class="messages-area clearfix">
		<div class="img-message pull-left">
			<img :src="notification.sender.profile_picture_tiny" alt="notification.sender.name">
		</div><!-- /img-message -->


		<div class="main-msg pull-left">
			<h1>{{ notification.sender.name }}</h1>

			<p v-html="notification.message_cached"></p>

			<p><span>{{ notification.created_at | fromNow }}</span></p>
		</div><!-- /main-msg -->
	</div><!-- /messages-area -->

	<router-link v-if="notificationLink" :to="notificationLink" 
		class="message-readmore">
		Read more
	</router-link>
</div><!-- /message-item -->
</template>
<script>
import mixinUser from '../../mixins/user'
export default {
	name: 'Notification',

	mixins: [mixinUser],

	props: {
		notification: {
			type: Object,
			required: true
		}
	},

	computed: {
		/**
		 * fixed notification link coming from the server.
		 * http://influencerhub.localhost/bodecontagion/post/1
		 */
		notificationLink () {
			return this.notification.link.replace(App.baseUrl, '/')
		}
	}
}
</script>