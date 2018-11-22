<template>
	<div id="display-area">
		<div class="container text-center" id="login-container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<h1>Set up your account</h1></div>
				<div class="login-form col-md-6 col-md-offset-3 form-contents">
					<div class="body">
						<div class="bordered-box form-box">
							<div class="form-area">
								<form action="#" @submit.prevent="requestToken">

									<div class="alert alert-danger" v-if="form.errors.length">
										<p v-for="(error, key) in form.errors" :key="key">
											{{ error }}
										</p>
									</div>

									<div class="form-row clearfix" v-if="form.profile_picture">
										<div class="sign-up-profile-picture" :style="profilePicture"></div>
									</div>

									<div class="form-row clearfix">
										<!-- <div class="form-row-img pull-left">
											<img src="images/img-email.png" alt="Image">
										</div> -->
										<!-- /form-row-img -->
										<div class="form-row-input">
											<input type="text" required v-model="form.name" placeholder="Full name">
										</div>
										<!-- /form-row-input -->
									</div>
									<div class="form-row clearfix">
										<!-- <div class="form-row-img pull-left">
											<img src="images/img-email.png" alt="Image">
										</div> -->
										<!-- /form-row-img -->
										<div class="form-row-input">
											<input type="text" required v-model="form.slug" placeholder="Account ID">
										</div>
										<!-- /form-row-input -->
									</div>
									<div class="form-row clearfix">
										<!-- <div class="form-row-img pull-left">
											<img src="images/img-email.png" alt="Image">
										</div> -->
										<!-- /form-row-img -->
										<div class="form-row-input">
											<input type="email" required v-model="form.email" placeholder="Email">
										</div>
										<!-- /form-row-input -->
									</div>
									<!-- /form-row -->
									<div class="form-row clearfix">
										<!-- <div class="form-row-img pull-left">
											<img src="images/img-form-lock.png" alt="Image">
										</div> -->
										<!-- /form-row-img -->
										<div class="form-row-input" id="password">
											<input type="password" required v-model="form.password" placeholder="Set your password">
										</div>
										<!-- /form-row-input -->
									</div>
									<!-- /form-row -->
									<button type="submit" class="btn-submit" 
										:disabled="disabledButton">
										<i class="fa fa-spinner fa-pulse fa-fw" 
											v-if="loaders.submitting"></i> CONTINUE
									</button>
								</form>
							</div>
							<!-- /form-area -->
						</div>
						<!-- /bordered-box -->
					</div>
				</div>
			</div>
		</div>
		<div id="social-media-login" class="+text-center">
			<div class="panel" style="padding: 0;">
				<a class="button --facebook" :href="hrefSignup" 
					id="button-facebook">
					<i class="fa fa-facebook-official"></i> &nbsp; Sign up via Facebook
				</a>
			</div>
		</div>
	</div>
</template>
<script>
import ApiSettings from '../api/settings'
import ApiAuth from '../api/auth'
import ConfigAuth from '../config/auth'
import mixinHub from '../mixins/hub'
import slugify from 'slugify'
export default {
	mixins: [mixinHub],
	data () {
		return {
			form: {
				name: null,
				email: null,
				password: null,
				slug: null,
				profile_picture: null,
				errors: []
			},
			loaders: {
				submitting: false
			}
		}
	},
	mounted () {
		this.$store.dispatch('hideSplashScreen')
		
		let params = new URL(location.href).searchParams
		if (params.has('email')) {
			this.form.email = params.get('email')
		}
		if (params.has('name')) {
			this.form.name = params.get('name')
		}
		if (params.has('profile_picture')) {
			this.form.profile_picture = params.get('profile_picture')
		}
		// delete the variable stored in window namespace
		this.$store.dispatch('hideSplashScreen')
		delete window.redirect_url
	},
	watch: {
		'form.name' (value) {
			// slugify the name
			let slug = slugify(value, {
				lower: true
			})
			this.form.slug = slug
		},
		'form.slug' (value) {
			if (value != '') {
				this.checkSlug()
			}
		}
	},
	methods: {
		checkSlug () {
			this.form.errors = []
			// request ajax
			const apiAuth = new ApiAuth()
			apiAuth.checkSlug(this.form.slug)
				.then(response => {
					console.log(response.data)
				})
				.catch(error => {
					this.form.errors.push(error.response.data.slug[0])
					console.log(error.response.data)
				})
		},
		requestToken () {
			this.form.errors = []

			let configAuth = ConfigAuth.oauth
			let config = {
				grant_type: 'client_credentials'
			}
			Object.assign(configAuth, config)
			console.log(config, configAuth)

			this.loaders.submitting = true
			const apiAuth = new ApiAuth()
			apiAuth.token(configAuth)
				.then(response => this.register(response.data))
				.catch(error => {
					this.form.errors.push(error.response.data)
					this.loaders.submitting = false
				})
		},
		register (token) {
			let params = new URL(location.href).searchParams
			if (!params.has('membership') && !params.has('hub')) {
				return
			}
			let hub = params.get('hub')

			// configure header
			let config = {
				headers: {
					Authorization: `${token.token_type} ${token.access_token}`
				}
			}
			
			const apiSettings = new ApiSettings({slug: hub})

			this.loaders.submitting = true
			apiSettings.setupAccount(this.form, params.get('membership'), config)
				.then(response => {
					this.loaders.submitting = false
					this.$router.replace({
						name: 'login',
						params: {
							success: {
								type: 'updated',
								message: 'You can now login.'
							}
						}
					})
				})
				.catch(error => {
					this.form.errors.push(error.response.data)
					this.loaders.submitting = false
				})
		}
	},
	computed: {
		disabledButton () {
			let form = this.form
			return this.loaders.submitting || !form.name || !form.email || !form.password || !form.slug
		},
		hrefSignup () {
			let params = new URL(location.href).searchParams
			let href = '/social/facebook?socialite-action=signup&hub=' + params.get('hub')
			return href
		},
		profilePicture () {
			if (!this.form.profile_picture)
				return
			return {
				backgroundImage: `url(${this.form.profile_picture})`
			}
		}
	}
}
</script>