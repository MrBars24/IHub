<template>
		<div id="display-area">
		<div class="container text-center" id="login-container">
			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<h1>Reset your password</h1>
				</div>
				<div class="login-form col-md-6 col-md-offset-3 form-contents">
					<div class="body">
						<div class="bordered-box form-box">
							<div class="form-area">
								
								<div class="alert alert-danger" v-if="errors.length">
									<p v-for="(error, index) in errors" :key="index">
										{{ error }}
									</p>
								</div>
								<div class="alert alert-success" v-if="success.length">
									<p v-for="(message, index) in success" :key="index">
										{{ message }}
									</p>
								</div>

								<div class="form-row">
									<div class="form-row-input" id="email-address">
										<input type="email" v-model="form.email" 
											placeholder="Email Address">
									</div><!-- /form-row-input -->
								</div><!-- /form-row -->

								<div class="form-row">
									<div class="form-row-input">
										<input type="password" 
											v-model="form.password" 
											maxlength="85" 
											placeholder="Password">
									</div>
								</div><!-- /form-row -->

								<div class="form-row">
									<div class="form-row-input">
										<input type="password" 
											v-model="form.password_confirmation"
											maxlength="85" 
											placeholder="Confirm Password">
									</div>
								</div><!-- /form-row -->

								<button type="button" class="btn-submit" 
									@click.prevent.stop="resetPassword"
									id="send-verification-button"
									:disabled="canReset">
									<i class="fa fa-spinner fa-pulse fa-fw" 
										v-if="loaders.resetting"></i> Reset Password
								</button>
								<div class="form-group" id="bck-to-login">
									<router-link :to="{name: 'login'}" class="back-to-login">
										Back to Login
									</router-link>
								</div>
							</div><!-- /form-area -->
						</div><!-- /bordered-box -->
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import AuthApi from '../api/auth'

export default {
	name: 'ResetPassword',

	data () {
		return {
			form: {
				password: null,
				password_confirmation: null,
				token: null,
				email: null
			},
			loaders: {
				resetting: false
			},
			success: [],
			errors: []
		}
	},
	
	mounted () {
		this.$store.dispatch('hideSplashScreen')	
		this.form.token = this.$route.params.token
	},

	methods: {
		resetPassword () {
			const authApi = new AuthApi()
			this.loaders.resetting = true
			this.errors = []
			this.success = []

			authApi.resetPassword(this.form)
				.then(response => {
					this.loaders.resetting = false
					let message = response.data.data.message
					if (response.data.success) {
						this.success.push(message)
					}
					else {
						this.errors.push(message)
					}
				})
				.catch(error => {
					if (error.response.status === 422) {
						_.forEach(error.response.data, values => {
							this.errors.push(values[0])
						})
					}
					else {
						this.errors.push('Something went wrong, please refresh the page.')
					}
					this.loaders.resetting = false
				})
		}
	},

	computed: {
		canReset () {
			return !this.form.password ||!this.form.password_confirmation ||
				this.form.password !== this.form.password_confirmation ||
				!this.form.email || 
				!this.form.token || // just to make sure. lol
				this.loaders.resetting
		}
	}
}
</script>

