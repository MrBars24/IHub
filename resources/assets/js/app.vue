<template>
<div v-cloak>
	<header-section></header-section>
  <notifications class="flash-message-holder" position="top" :duration="10000" classes="flash-message" 
		width="100%"/>
	<transition name="fade" mode="out-in" :duration="500">
		<router-view></router-view>
	</transition>
	<footer-section></footer-section>
</div>
</template>

<script>
import HeaderSection from "./views/partials/Header.vue"
import FooterSection from "./views/partials/Footer.vue"
import { Platform } from 'quasar-framework'
import Bootstrap from "./bootstrap"
import mixinCommon from "./mixins/common"
import Vue from "vue"
import mixinCordova from "./mixins/cordova"
Vue.use(Bootstrap.echo, require("./config/echo").default)
Vue.mixin(mixinCommon)

export default {
	name: "app",
	mixins: [mixinCordova],
	components: {
		HeaderSection,
		FooterSection
	},

	mounted() {
		this.initialize()
		this.initPushNotification()
	},

	created () {
		this.initializeCustomEvent()
	},

	watch: {
		'$route' (value) {
			this.initialize()
		}
	},

	computed: {
		isLoading() {
			return this.$store.state.isLoading
		}
	},
	data() {
		return {
			// isLoading: true
		};
	},
	methods: {
		initGlobalEvent(ev) {
			ev.stopPropagation()
			ev.preventDefault()
			// nav
			if (
				document.querySelector("#pushmenu").classList.contains("pushmenu-open")
			) {
				document.body.classList.remove("pushmenu-push-toright");
				document.querySelector("#nav_list").classList.remove("active");
				document.querySelector("#pushmenu").classList.remove("pushmenu-open");
			}
		},

		initPushNotification ()  {
			if (this.isNativeApp) {
				this.cordova.on('deviceready', () => {
					if (this.$oauth.isAuthenticated()) {
						this.initializeOneSignal()
					}
				})
			}
		},

		/**
		 * we validate the access_tokens and oauth_tokens if it matches each other so we can use this to
		 * get the api authenticated users in web.php route
		 * 
		 * @return {void}
		 */
		validateTokens() {
			// expire the token
			let oauth_token = this.$oauth.getItem('oauth_token', {
				path: '/'
			})
			let access_token = this.$oauth.getItem('access_token', {
				path: '/'
			})
			let hasOAuthToken = this.$oauth.Session.has('oauth_token', {
				path: '/'
			})
			if (!hasOAuthToken || oauth_token != access_token) {
				this.$oauth.logout()
					.then(response => {
						this.$store.dispatch('logout')
					})
			}
		},

		initialize() {
			let meta = this.$route.meta;

			if (!this.$store.state.Hub.selected.id && meta.requiresAuth) {
				this.$store.dispatch("getHubList")
					.then(response => {
						// don't validate tokens if it's a mobile app
						if (!Platform.is.mobile) {
							this.validateTokens()
						}
					})
					.then(response => {
						this.$store.dispatch("getAuthenticatedUser")
							.then(() => {
								this.$store.dispatch("checkInit")

								// initialize push notification
								this.initPushNotification()

								let name = this.$route.name
								let user = this.$store.state.user
								let hub = this.$store.state.Hub.selected

								// initialize realtime events
								this.initEcho(user)

								if (user.is_master) {
									// redirect to master page
									// this.$router.replace({
									// 	name: "master"
									// })
								} 
								else {
									// all redirects should configure here.
									let $route = this.$route
									this.authenticateTerms(user)


									if (!$route.params.hub_slug) {
										// if hub on route.params is empty
										this.$router.replace({
											name: "hub",
											params: { hub_slug: hub.slug },
											query: { redirect: $route.query.redirect }
										})
									}
								}

								this.$store.dispatch("hideSplashScreen")
						})
					})
					.catch(error => {
						this.$router.replace({
							name: "login",
							query: {
								redirect: "gigs.carousel"
							}
						})
					})
			} else {
				let user = this.$store.state.user
				if(user.membership && typeof user.membership.accepted_conditions != 'undefined') {
					if (!user.is_master) {
						this.authenticateTerms(user)
					}
				}
			}
		},

		// initialize realtime events
		// user must be an instance of \App\User not Hub
		initEcho(user) {
			let objectClass = user.object_class
			if (objectClass.includes('Hub') && user.original) {
				user = user.original
			}

			this.$echo
				.channel("User.Terms")
				.listen("Terms.UserTerms", data => {
					
					if(data.includes(user.id)) {
						this.$store.dispatch('resetTerms')
						this.authenticateTerms(user)
					}
				});

			this.$echo
				.private("ConversationNew." + user.id)
				.listen("Conversation.NewMessageSent", data => {
					// temporary fix because broadcast()->toOthers() is not working
					this.$store.commit("addConversationMessages", data);
				});
		},

		/**
		 * custom app event handler
		 */

		initializeCustomEvent () {
			document.addEventListener('influencerhub-account-linked', this.accountLinkedRedirect)
			document.addEventListener('influencerhub-account-loggedin', this.accountLoggedinRedirect)
			document.addEventListener('influencerhub-app-redirect', this.appRedirect)
		},

		accountLinkedRedirect (e) {
			this.$router.replace({
				name: 'settings.account',
				params: {
					'$message': e.detail
				}
			})
		},

		accountLoggedinRedirect (e) {
			this.$router.replace({
				name: 'login',
				params: {
					'$message': e.detail
				}
			})
		},
		
		appRedirect (e) {
			let url = e.detail.url
			this.$router.replace(url)
		},

		authenticateTerms(user) {
			if(user.membership.role == 'hubmanager') return; 
		
			if(!user.membership.accepted_conditions && this.$router.currentRoute.name != 'terms.condition') {
				this.$router.replace({ 
					name: "terms.condition" 
				}); 
			} 
		},
	}
}
</script>