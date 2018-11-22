<template>
	<div id="display-area">  
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3" 
					ref="conversation">


					<div class="text-center" v-if="loaders.conversation">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>

					<!-- or search user ? -->

					<div class="text-center" v-if="talkingTo && !conversation">
						<p>Start conversation with {{ talkingTo.name }} by saying Hello!.</p>
					</div>

				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import Message from '../api/message'
import mixinHub from '../mixins/hub'
import mixinUser from '../mixins/user'
export default {
	mixins: [mixinHub,mixinUser],
	data () {
		return {
			talkingTo:null,
			conversation: null,
			loaders: {
				conversation: false,
				user: false
			}
		}
	},
	mounted () {
		if (this.init)
			this.getConversation()
	},
	watch: {
		'$route': 'getConversation',
		init (value) {
			if (value) {
				this.getConversation()
			}
		} 
	},
	methods: {
		getConversation () {
			this.loaders.conversation = true
			const message = new Message(this.hub)
			message.getWrite (this.$route.params.user_slug)
				.then(response => {
					let data = response.data.data
					if (data.conversation === undefined) {
						// create dummy conversation object
						const conversation = {
							messages: [],
							sender: this.user,
							receiver: data.receiver,
							pagination: null,
						}
						this.$store.commit('setConversation', {conversation })
					}
					else {
						this.$router.replace({
							name: 'message',
							params: {
								conversation_id: data.conversation.id
							}
						})
					}
					this.loaders.conversation = false
				})
				.catch(error => {
					this.loaders.conversation = false
					// a possible 404 code so let's redirect user to inbox
					this.$router.replace({
						name: 'messages.inbox'
					})
				})
		}
	}
}
</script>