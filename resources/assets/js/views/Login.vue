<template>
	<div id="display-area">
		<div class="container text-center" id="login-container">
			<div class="row" v-if="isLoggedIn">
				<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
			</div>
			<div class="row" v-else>
				<div class="col-md-8 col-md-offset-2">
					<h1>Login to your account</h1>
				</div>
				<div class="login-form col-md-6 col-md-offset-3 form-contents">
					<div class="body">
						<div class="bordered-box form-box">
							<div class="form-area">
								<form @submit.prevent="login">
									<div class="alert alert-danger" v-if="form.errors.length">
										<p v-for="(error, index) in form.errors" :key="index">
											{{ error }}
										</p>
									</div>
									<div class="alert alert-success" v-if="successMessage"> <!-- depreceate -->
										{{ successMessage.message }}
									</div>

									<!-- TODO: use this accross the app from now on. -->
									<div :class="$message.class" v-if="$message"> 
										{{ $message.text }}
									</div>

									<div class="form-row clearfix">
										<div class="form-row-input">
											<input type="text"
												name="username"
												placeholder="Email Address or Account ID" 
												class="ihub-input" 
												v-model="form.email">
										</div><!-- /form-row-input -->
									</div><!-- /form-row -->
									<div class="form-row clearfix">
										<div class="form-row-input" id="password">
											<input type="password" 
												placeholder="Password" 
												class="ihub-input" 
												v-model="form.password">
										</div><!-- /form-row-input -->
									</div> <!-- /form-row -->
									<button class="js-branding-button btn-submit" type="submit"
										:disabled="disableLogin">
										<i class="fa fa-spinner fa-pulse fa-fw" 
											v-if="loaders.submitting"></i> LOGIN
									</button>
								</form>
								<div class="form-group" id="lost-password">
									<router-link :to="{name:'forgot'}">
										I lost my password
									</router-link>
								</div>
								
							</div>
							<!-- /form-area -->
						</div>
						<!-- /bordered-box -->
					</div>
				</div>
			</div>
		</div>
		<div id="social-media-login" class="+text-center hidden-xs hidden-sm">
			<div class="panel" style="padding: 0;">
				<a href="#" id="button-facebook" v-if="isNativeApp" 
					@click.stop.prevent="openInAppBrowser(`${BASE_TOKEN}social/facebook?socialite-action=login&device=mobile`)"
					class="button --facebook">
					<i class="fa fa-facebook-official"></i> &nbsp; Login via Facebook
				</a>
				<a class="button --facebook" v-else href="/social/facebook?socialite-action=login" 
					id="button-facebook">
					<i class="fa fa-facebook-official"></i> &nbsp; Login via Facebook
				</a>
			</div>
		</div>
	</div>
</template>
<script>
import { BASE_TOKEN } from '../config/auth' 
import mixinCordova from "../mixins/cordova"

export default {
	name: 'Login',

	mixins: [mixinCordova],

	data () {
		return {
			form: {
				email: null,
				password: null,
				errors: []
			},
			isLoggedIn: false,
			loaders: {
				submitting: false
			}
		}
	},

	watch: {
		successMessage (value) {
			if (value) {
				setTimeout (() => {
					this.successMessage = false
				}, 2000)
			}
		}
	},

	computed: {
		disableLogin () {
			return this.loaders.submitting || !this.form.email || !this.form.password
		},
		successMessage: {
			get ( value ) {
				if (!this.$route.params.success) return false
				return {
					message: this.$route.params.success.message
				}
			},
			set (value) {
				return false;
			}
		}
	},

	mounted () {
		this.checkAccessToken()
		this.checkFlashedMessages()
		this.checkRegistrationData()
		this.$store.dispatch('hideSplashScreen')
	},

	methods: {
		inAppLoadStop(event) {
			// @note: we are expecting a url from social callback like this one: "https://ihubapp2.dev.bodecontagion.com/#_=_"
			if (!event.url.endsWith("#_=_")) {
				return
			}

			// clear the storage for any subsequent calls

			let loop = setTimeout(() => {
				this.refInAppBrowser.executeScript({
					code: "JSON.parse(localStorage.getItem( 'oauth_tokens' ))"
				}, (values) => {
					let tokens = values[0] // values always return an array
					// If the tokens were set, clear the interval and close the InAppBrowser.
					if (tokens.access_token) {
						// store the tokens to global window variable
						window.oauth_tokens = tokens
						clearInterval(loop)
						this.refInAppBrowser.close()

						// check and authenticate the user.
						this.checkAccessToken()
					}
				})
			})
		},

		// Internal codes
		checkRegistrationData () {
			if (window.redirect_url) {
				console.log(redirect_url)
				let params = new URL(window.redirect_url).searchParams
				this.$router.replace({
					name: 'signup',
					query: {
						'membership': params.get('membership'),
						'hub': params.get('hub'),
						'email': params.get('email'),
						'name': window.display_name,
						'profile_picture': window.profile_picture
					}
				})
			}
		},
		checkAccessToken () {
			// check if it's a redirect back from the social authentication
			// - store oauth tokens in LocalStorage
			if (window.oauth_tokens) {
				this.$oauth.storeSession(window.oauth_tokens)
				this.$oauth.addAuthHeaders()
				this.$store.dispatch('storeToken', {
					oauth_token: window.oauth_tokens.access_token,
					// fill in device_token, device_os
					// device_token: this.cordova.,
					// device_os: 'ios'
				})

				
				let hub = this.$store.state.Hub.selected
				this.$store.dispatch('initApp') // reinitialize the app
					.then(response => {
						this.isLoggedIn = true
						let route = this.buildLoginRedirectRoute(hub)
						this.$router.replace(route)
						this.loaders.submitting = false
					})
			}
			// clear the oauth_tokens attached window variable
			window.oauth_tokens = null
		},
		checkFlashedMessages () {
			if (window.flash_message) {
				window.flash_message.forEach(message => {
					this.form.errors.push(message)
				})
			}
		},
		login ($event) {
			this.isLoggedIn = false
			this.form.errors = []
			// temporary solution to clear the $message global variable
			if (this.$message) {
				this.$set(this.$route.params, '$message', undefined)
			}
			this.loaders.submitting = true

			let form = this.form
			let xhr = this.$oauth.login(form.email, form.password)
				.then(response => {
					this.$store.dispatch('hideSplashScreen', false) // show splashscreen
					// store tokens
					this.$store.dispatch('storeToken', {
						'oauth_token': response.data.access_token
					}).then(response => {
						let hub = this.$store.state.Hub.selected
						this.$store.dispatch('initApp') // reinitialize the app
							.then(response => {
								this.isLoggedIn = true

								// build redirect route
								let route = this.buildLoginRedirectRoute(hub)


								// dispatch getAuthenticatedUser
								this.$store.dispatch("getAuthenticatedUser").then(() => {
									this.$store.dispatch("checkInit");
									let user = this.$store.state.user;

									if(user.is_master){
										location.href = "/master"; // redirect if user is master
									}
								})

								this.$router.replace(route)
								this.loaders.submitting = false
								console.log('redirecting..', JSON.stringify(route))
							})
					})
				})
				.catch(error => {
					this.form.errors.push(error.response.data.message)
					this.loaders.submitting = false
				})
		},
		
		buildLoginRedirectRoute (hub) {
			let $route = this.$route

			let params = {}
			let query = {}

			// copy old params
			if ($route.params.$message) {
				params = $route.params.$message.from.params
			}
			else {
				params = {
					hub_slug: hub.slug
				}
			}

			if ($route.query.redirect) {
				query = {
					redirect: $route.query.redirect
				}
			}

			let route = {
				name: 'hub',
				params,
				query
			}

			return route
		}
	},

	beforeRouteLeave (to, from, next) {
		this.$store.dispatch('hideSplashScreen')
		if (this.refInAppBrowser) {
			this.refInAppBrowser.removeEventListener('loadstop', this.inAppLoadStop)
		}
		next()
	}
}
</script>
