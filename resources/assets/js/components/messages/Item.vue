<template>
<div class="message-item">
	<div class="messages-area clearfix">
		<div class="img-message pull-left">
			<img :src="talkingTo.profile_picture_tiny"
			 :alt="talkingTo.name">
		</div><!-- /img-message -->


		<div class="main-msg pull-left">
			<h1>{{ talkingTo.name }}</h1>

			<p>{{ lastMessage }}</p>

			<p><span>{{ conversation.created_at | fromNow }}</span></p>
		</div><!-- /main-msg -->
	</div><!-- /messages-area -->

	<router-link :to="routerLink" 
		class="message-readmore">
		Read more
	</router-link>
</div><!-- /message-item -->
</template>
<script>
import mixinUser from '../../mixins/user'
export default {
	mixins: [mixinUser],
	props: {
		conversation: {
			type: Object,
			required: true
		}
	},
	data () {
		return {}
	},
	computed: {
		// get whos' this user is talking to
		talkingTo () {
			const receiver = this.conversation.receiver
			const sender = this.conversation.sender
			const user = this.user
			// check if auth user is euqal to sender, return receiver
			return _.isEqual(user.id, sender.id) ? receiver : sender
		},
		lastMessage () {
			return this.conversation.message ? this.conversation.message : this.conversation.messages[0].message
		},
		routerLink () {
			return {
				name: 'message',
				params: {
					conversation_id: this.conversation.id
				}
			}
		}
	}
}
</script>
