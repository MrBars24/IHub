<template>
<footer>
	<div class="btn-footer" v-if="isOnProfile"> 
		<!-- show in profile -->
		<router-link v-if="isViewingOther"
			class="btn-full-width visible-sm visible-xs js-branding-button"
			:to="{ name: 'message', params: { conversation_id: undefined, user_id: profileUser.id, user_slug: profileUser.slug } }">
			Get in Touch
		</router-link>

		<router-link v-else
			class="btn-full-width visible-sm visible-xs js-branding-button"
			:to="{name: 'my.gigs'}">
			View My Gigs
		</router-link>
	</div><!-- /btn-footer -->

	<div class="mobile-gig-accept visible-sm visible-xs" 
		v-if="showGigAcceptButton">
		<router-link :to="routeWrite"
			class="btn-full-width js-branding-button">
			Accept
		</router-link>
	</div>

	<div class="mobile-footer-menu visible-sm visible-xs" v-if="showMobileFooter">
		<ul>
			<li>
				<router-link :to="{ name: 'newsfeed' }" 
					class="icon-feed" 
					exact>
				</router-link>
			</li>
			<li>
				<router-link :to="{ name: 'leaderboard' }" 
					class="icon-award" 
					exact>
				</router-link>
			</li>
			<li>
				<router-link :to="{ name: 'gigs' }" 
					class="icon-carousel">
				</router-link>
			</li>
			<li>
				<router-link :to="routeProfile"
					class="icon-b-profile">
				</router-link>
			</li>
			<li>
				<router-link :to="{ name: 'messages' }" 
					class="icon-speech">
					<!-- <span class="circle-counter" v-show="messages.length">0</span> -->
					<!-- <span class="js-branding-counter circle-counter">4</span>					 -->
				</router-link>
			</li>
		</ul>
	</div><!-- /mobie-footer-menu -->
	
	<conversation-form v-else-if="isOnConversation"></conversation-form>
	<post-authoring-form v-if="isOnWrite"></post-authoring-form>
</footer>
</template>
<script>
import ConversationForm from "../../components/conversations/Form.vue";
import PostAuthoringForm from "../../components/write/Form.vue";
import mixinUser from "../../mixins/user";
import mixinHub from "../../mixins/hub";
import mixinMessages from "../../mixins/messages";
import mixinNotifications from "../../mixins/notifications";
export default {
	name: "Footer",

	mixins: [mixinUser, mixinHub, mixinMessages, mixinNotifications],

	components: {
		ConversationForm,
		PostAuthoringForm
	},

	data() {
		return {
			messages: [],
			carouselInitialized: false,
			routeWrite: {
				name: "write",
				params: {
					from: "gig",
					gig_id: undefined
				},
				query: {
					gig: undefined
				}
			}
		};
	},
	
	mounted () {
		this.$bus.$on('gig-carousel-initialized', this.showCarouselButton)
		this.$bus.$on('gig-carousel-change', this.updateRouteWrite)
	},

	destroy () {
		this.$bus.$off('gig-carousel-initialized', this.showCarouselButton)
		this.$bus.$off('gig-carousel-change', this.updateRouteWrite)
	},

	beforeRouteLeave (to, from, next) {
		this.carouselInitialized = false
	},

	methods: {
		/**
		 * @param {boolean} hasGigs
		 */
		showCarouselButton (hasGigs = false) {
			this.carouselInitialized = hasGigs
		},
		updateRouteWrite (gigId) {
			this.routeWrite.params.gig_id = gigId
			this.routeWrite.query.gig = gigId
		}
	},

	computed: {
		showGigAcceptButton() {
			return this.isOnGigCarousel && 
				(this.carouselInitialized && 
					!this.isHubManager && 
					this.$store.state.Gig.list.length)
		},
		totalMessagesNotifications() {
			return this.newNotifications.length + this.newMessages.length;
		},
		routeProfile() {
			let user = this.$store.state.user;
			console.log(user);

			return {
				name: "profile",
				params: {
					hub_slug: this.$route.params.hub_slug,
					user_slug: this.isHubManager ? "about" : this.user.slug
				}
			};
		},
		isOnProfile() {
			return this.$route.name === "profile.home";
		},
		isOnConversation() {
			return _.startsWith(this.$route.name, "message.");
		},
		isOnWrite() {
			return this.$route.name === "write";
		},
		isOnGigCarousel() {
			return this.$route.name == 'gigs.carousel'
		},
		isOnCreateOrEditGig() {
			let name = this.$route.name;
			return name === "gigs.new" || name === "gigs.edit";
		},
		showMobileFooter() {
			return (
				this.isAuthenticated &&
				!this.isOnConversation &&
				!this.isOnWrite &&
				!this.isOnCreateOrEditGig
			);
		},
		isViewingOther() {
			if (this.$route.name && !this.$route.name.startsWith("profile")) return;
			return this.user.id !== this.profileUser.id;
		},
		profileUser() {
			return this.$store.state.Profile.user;
		}
	}
};
</script>