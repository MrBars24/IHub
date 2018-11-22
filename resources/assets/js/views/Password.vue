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
								<div class="alert alert-danger" v-if="form.errors.length">
									<p v-for="(error, index) in form.errors" :key="index">
										{{ error }}
									</p>
								</div>
								<div class="alert alert-success" v-if="form.success.length">
									<p v-for="(message, index) in form.success" :key="index">
										{{ message }}
									</p>
								</div>
								<div class="form-row clearfix">
									<div class="form-row-input" id="email-address">
										<input type="email" v-model="form.email" 
											placeholder="Email Address">
									</div><!-- /form-row-input -->
								</div><!-- /form-row -->
								<button type="button" class="btn-submit" 
									@click.prevent.stop="resetPassword"
									id="send-verification-button"
									:disabled="!form.email || loaders.validating">
									<i class="fa fa-spinner fa-pulse fa-fw" 
										v-if="loaders.validating"></i> Send Verification
								</button>
								<div class="form-group" id="bck-to-login">
									<router-link :to="{name: 'login'}" class="back-to-login">
										Back to login form
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
	data () {
		return {
			form: {
				email: null,
				errors: [],
				success: []
			},
			loaders: {
				validating: false
			}
		}
	},
	
	mounted () {
		this.$store.dispatch('hideSplashScreen')	
	},

	methods: {
		resetPassword () {
			const authApi = new AuthApi()
			this.loaders.validating = true
			this.form.errors = []
			this.form.success = []

			authApi.sendPassword(this.form.email)
				.then(response => {
					this.loaders.validating = false
					let message = response.data.data.message
					if (response.data.success) {
						this.form.success.push(message)
					}
					else {
						this.form.errors.push(message)
					}
				})
				.catch(error => {
					console.log(error)
					if (error.response.status === 422) {
						this.form.errors.push(error.response.data.email[0])
					}
					else {
						this.form.errors.push('Something went wrong, please refresh the page.')
					}
					this.loaders.validating = false
				})
		}
	}
}
</script>