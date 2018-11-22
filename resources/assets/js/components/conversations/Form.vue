<template>
<div class="send-msg clearfix">
	<div class="container">

		<div class="send-box row">
			<form action="#" method="post" @submit.prevent="send">
				<div class="col-xs-8 col-sm-9">
					<textarea ref="formMessage"
						v-model="form.message"
						placeholder="Write message here..." 
						@keydown.enter.prevent.exact="send"
						class="pull-left">
					</textarea>
				</div>
				<div class="col-xs-4 col-sm-3">
					<button type="submit" class="btn-submit js-branding-button"
						:disabled="disableSend">
						<i v-if="loaders.sending" class="fa fa-spinner fa-spin"></i> Send
					</button>
				</div>
			</form>
		</div>
	</div>
</div><!-- /send-msg -->
</template>
<script>
import mixinsMessages from '../../mixins/messages'
import mixinHub from '../../mixins/hub'
import Message from '../../api/message'
import autosize from 'autosize'
export default {
	mixins: [mixinsMessages, mixinHub],
	data () {
		return {
			form: {
				message: ''
			},
			loaders: {
				sending: false
			}
		}
	},

	mounted () {
		this.$nextTick(() => {
			autosize(this.$refs.formMessage)
		})
	},

	methods: {
		send () {
			this.loaders.sending = true

			let _message = new Message(this.hub)
			let routeParams = this.$route.params
			let params = {
				hub: this.hub,
				conversation_id: routeParams.conversation_id,
			}
			
			if (!this.conversation.id) {
				// start new conversation
				// get the receiver
				const entity = this.conversation.receiver
				_message.sendNew(entity.slug, this.form, this.echoConfig)
					.then(response => {
						this.$router.replace({
							name: 'message',
							params: {
								conversation_id: response.data.data.conversation.id
							}
						})
						this.form.message = ''
						this.loaders.sending = false
					})
					.catch(error => {
						console.error(error)
						this.loaders.sending = false
					})
			}
			else {
				// send to old
				_message.send(params, this.form, this.echoConfig)
				.then(response => {
					let data = response.data.data
					let payload = {
						type: 'new',
						data: data.message
					}
					this.loaders.sending = false
					this.$store.dispatch('updateMessages', payload)
					this.form.message = ''
					this.$bus.$emit('MessageSent')
				})
				.catch(error => {
					console.error(error)
					this.loaders.sending = false
					this.form.message = ''
				})
			}
		},
	},

	computed: {
		disableSend () {
			return !this.form.message || this.loaders.sending
		},
		echoConfig () {
			return {
				headers: {
					'X-Socket-Id': this.$echo.connector.socketId()
				}
			}
		}
	}


}
</script>