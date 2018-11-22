<template>
	<div :class="['conversation-area', 'clearfix', replyClass]">
		<div class="img-convo">
			<img :src="replyUser.profile_picture_tiny" :alt="replyUser.name"> <!-- dynamic -->
		</div><!-- /img-convo -->
		<div class="conversation-box">
			<img :src="imgArrow" alt="Image"> <!-- dynamic -->
			<p v-html="reply.message_cached"></p>
			<div class="convo-time text-right">
				<p><span>{{ reply.created_at | fromNow }}</span></p>
			</div><!-- /convo-time -->
		</div><!-- /conversation-box -->
	</div><!-- /conversation-area -->
</template>
<script>
import mixinUser from '../../mixins/user'
export default {
	mixins: [mixinUser],
	props: {
		reply: {
			type: [Object, Array],
			required: true
		},
		talkingTo: {
			type: Object,
			required: true,
		}
	},

	computed: {
		imgArrow () {
			// right arrow for the authenticated user
			let arrow = `/images/img-arrow-`
			let type = this.isReply ? 'right' : 'left'
			return resolveStaticAsset(arrow + type + '.png')
		},
		replyClass () {
			return this.isReply ? 'reply' : ''
		},
		// check if this is a reply 
		// if sender is equal to auth user's talking to. then return 
		isReply () {
			const picker = ['id','object_class', 'name', 'profile_picture', 'slug']
			const sender = _.pick(this.reply.sender, picker)
			return !_.isEqual(this.talkingTo, sender)
		},
		replyUser () {
			let reply = this.isReply ? this.user : this.reply.sender
			return reply ? reply : {profile_image: null}
		}
	}
}
</script>