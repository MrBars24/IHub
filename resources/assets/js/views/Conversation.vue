<template>
	<div id="display-area">  
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3" 
					ref="conversation">
					
					<div class="alert alert-danger" v-if="errors.length">
						<span v-for="(error, index) in errors" :key="index">
							{{ error.message }}
						</span>
					</div>
					<div class="text-center" v-if="loaders.old">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>

				<!-- Populate the conversation -->
					<reply v-for="message in conversation.messages" 
						v-if="conversation.messages.length"
						:key="message.id" 
						:talking-to="talkingTo"
						:reply="message" v-cloak>	
					</reply>

					<div class="text-center" v-if="loaders.conversation">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
					<div ref="scrollTarget"></div>
				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import Vue from "vue";
import Reply from "../components/conversations/Reply.vue";
import mixinUser from "../mixins/user";
import mixinHub from "../mixins/hub";
import mixinMessages from "../mixins/messages";
import vueSmoothScroll from "vue-smoothscroll";

Vue.use(vueSmoothScroll);

export default {
	name: "Conversation",

	mixins: [mixinUser, mixinHub, mixinMessages],

	components: {
		Reply
	},

	data() {
		return {
			loaders: {
				conversation: true,
				old: false,
				initialLoaded: false
			},
			endOfConversation: false,
			errors: [],
			scrollPosition: 0
		};
	},

	mounted() {
		if (this.init) {
			this.initConversation();
		}
	},

	computed: {
		canScroll() {
			return (
				this.scrollPosition < 20 &&
				!this.loaders.old &&
				this.loaders.initialLoaded &&
				!this.endOfConversation
			);
		}
	},

	beforeDestroy() {
		if (!this.endOfConversation && this.$route.params.conversation_id) {
			window.removeEventListener("scroll", this.scroll);
		}
		this.$bus.$off("MessageSent", this.scrollToBottom);
	},

	beforeRouteLeave(from, to, next) {
		if (!this.endOfConversation && this.$route.params.conversation_id) {
			window.removeEventListener("scroll", this.scroll);
		}
		this.$bus.$off("MessageSent", this.scrollToBottom);
		next();
	},

	watch: {
		$route: "initConversation",

		"loaders.conversation"(value) {
			if (!value) this.scrollToBottom();
		},

		init(value) {
			if (value) {
				this.initConversation();
			}
		}
	},

	methods: {
		initConversation() {
			if (this.$route.params.conversation_id !== undefined) {
				this.fetchConversation();

				if (!this.endOfConversation)
					window.addEventListener("scroll", _.debounce(this.scroll), 1000);
			}
			this.$bus.$on("MessageSent", this.scrollToBottom);
		},

		scrollToBottom() {
			this.$SmoothScroll(this.$refs.scrollTarget);
		},

		fetchConversation() {
			this.loaders.conversation = true;
			let payload = this.getFetchPayload();
			this.$store
				.dispatch("getConversation", payload)
				.then(response => {
					this.initEcho();
					this.loaders.conversation = false;
					setTimeout(() => {
						this.loaders.initialLoaded = true;
					}, 2000);
					// bind scroll event
				})
				.catch(error => {
					this.errors.push(error);
				});
		},

		initEcho() {
			if (!this.$echo) return;

			this.$echo
				.private("Conversation." + this.conversation.id)
				.listen("Conversation.MessageSent", message => {
					// temporary fix because broadcast()->toOthers() is not working
					// if (message.sender_id !== this.user.id) {
						this.$store.dispatch("updateMessages", {
							type: "new",
							data: message
						});
						this.scrollToBottom();
					// }
				});
			console.log("initEcho")
			console.log(this.user)
		},

		scroll() {
			this.scrollPosition =
				window.pageYOffset ||
				document.documentElement.scrollTop ||
				document.body.scrollTop ||
				0;
			if (this.canScroll) {
				// remove event listener for scroll if reached the end?
				this.loaders.old = true;
				let payload = this.getFetchPayload();
				this.$store
					.dispatch("fetchOldMessages", payload)
					.then(response => {
						this.loaders.old = false;
					})
					.catch(error => {
						this.loaders.old = false;
						this.endOfConversation = true;
					});
			}
		},

		getFetchPayload() {
			const params = this.$route.params;
			let payload = {
				hub: this.hub,
				conversation_id: params.conversation_id
			};
			return payload;
		}
	}
};
</script>