<template>
	<div id="display-area">
		<div class="container">
			<div class="row">

				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="message-container">

						<div class="text-center" v-if="loaders.messages">
							<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
						</div>

						<!-- populate messages -->
						<message v-for="conversation in conversations" 
							:conversation="conversation"
							:key="conversation.id">
						</message>

					</div><!-- /message-container -->
				</div>

			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import MessageItem from "../components/messages/Item.vue";
import mixinHub from "../mixins/hub";
import mixinMessages from "../mixins/messages";
import mixinUser from "../mixins/user";

export default {
	name: "Inbox",

	mixins: [mixinHub, mixinMessages, mixinUser],

	components: {
		message: MessageItem
	},

	data() {
		return {
			loaders: {
				messages: false
			}
		};
	},

	mounted() {
		this.fetchMessages();
	},

	watch: {
		$route: "fetchMessages"
	},

	methods: {
		fetchMessages() {
			console.log("fetchMessages");
			this.loaders.messages = true;
			setTimeout(() => {
				this.$store.dispatch("getMessages", this.hub).then(response => {
					this.loaders.messages = false;
				});
			}, this.loadInterval);
		}
	}
};
</script>