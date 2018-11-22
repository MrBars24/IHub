<template>
	<div class="settings-content">
		<h1>Account Details</h1>
		<div class="row">
			<div class="col-md-12">
				<div class="detail-box">
					<div class="head">
						<p>Login Details</p>
					</div><!-- /head -->

					<div class="body">
						<div class="bordered-box form-box">
							<div class="alert alert-success" v-if="messages">
								<p v-for="(message, index) in messages" :key="index">
									{{ message }}
								</p>
							</div>
							<div class="form-area">
								<form action="#">
									<div class="form-row">
										<div class="form-row clearfix">
											<div class="form-row-input">
												<div class="icon-container">
													<svg-filler class="icon-container__icon" :path="getPath('msg')" width="25px" height="25px" :fill="colorFill" />
												</div>
												<!-- <i class="fa fa-envelope" aria-hidden="true"></i> -->
												<input type="email" 
													v-model="form.email" 
													maxlength="85" 
													placeholder="Email">
											</div>
										</div><!-- /form-row-input -->
									</div><!-- /form-row -->

									<div class="form-row">
										<div class="form-row-input">
											<div class="icon-container">
												<svg-filler class="icon-container__icon" :path="getPath('lock')" width="25px" height="25px" :fill="colorFill" />
											</div>
											<input type="password" 
												v-model="form.password" 
												maxlength="85" 
												placeholder="Password">
										</div>
									</div><!-- /form-row -->

									<div class="form-row">
										<div class="form-row-input">
											<div class="icon-container">
												<svg-filler class="icon-container__icon" :path="getPath('lock')" width="25px" height="25px" :fill="colorFill" />
											</div>
											<input type="password" 
												v-model="form.password_re"
												maxlength="85" 
												placeholder="Confirm Password">
										</div>
									</div><!-- /form-row -->

									<button type="submit"
										@click.prevent="submitSettingsAccount" 
										:disabled="disableSave"
										class="btn-submit js-branding-button">
										<i v-if="loaders.login" class="fa fa-spinner fa-pulse fa-fw"></i> 
										SAVE
									</button>
								</form>
							</div><!-- /form-area -->
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="detail-box">
					<div class="head">
						<p>Social Networks</p>
					</div><!-- /head -->
					
					<div class="body">
						<div class="bordered-box">
							<div class="dynamic-list">
								<div class="text-center" v-if="loaders.accounts">
									<i class="fa fa-spinner fa-pulse fa-fw fa-2x"></i>
								</div>
								<social-accounts :account-user-id="accountUserId" v-else></social-accounts>
							</div><!-- /dynamic-list -->
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="btn-area">
					<button @click="logout" class="btn-logout btn-full-width"
						:disabled="loaders.loggingOut">
						<svg-filler class="icon-container-static__icon" :path="getSvgPath('logout')" width="36px" height="36px" :fill="'#ffffff'" />
						<i v-if="loaders.loggingOut" class="fa fa-spinner fa-pulse fa-fw"></i>
						<span>LOGOUT</span>
					</button>
				</div><!-- /btn-area -->
			</div>
		</div>
	</div><!-- /tab-pane -->
</template>
<script>
import SocialAccounts from '../../components/settings/SocialAccounts.vue'
import mixinHub from '../../mixins/hub'
import mixinUser from '../../mixins/user'
export default {
	name: 'SettingsAccount',

	mixins: [mixinHub,mixinUser],

	components: {
		SocialAccounts
	},

	data () {
		return {
			form: {
				email: null,
				password: null,
				password_re: null
			},
			colorFill: '#999999',
			messages: null,
			loaders: {
				accounts: true,
				login: false,
				loggingOut: false
			}
		}
	},
	
	mounted () {
		if (this.init) {
			this.getAccounts()
		}
	},

	watch: {
		'$route': 'getAccounts',
		init (value) {
			if (value) {
				this.getAccounts()
			}
		}
	},

	methods: {
		getPath (platform) {
			return '../images/svg/icon-' + platform + '.svg';
		},
		getAccounts () {
			this.loaders.accounts = true
			this.$store.dispatch('getSettings', {hub: this.hub})
				.then(response => {
					this.loaders.accounts = false
					let settings = response.data.data.settings
					Object.assign(this.form, _.pick(settings, 'email'))
				})
				.catch(error => {
					this.loaders.accounts = false
					console.log(error)
				})
		},
		submitSettingsAccount () {
			let data = {
				payload: this.form,
				hub: this.hub,
			}
			this.loaders.login = true
			this.$store.dispatch('updateSettings', data)
				.then(response => {
					let settings = response.data.data.settings
					this.messages = Array.isArray(settings) ? null : settings
					this.form.password = ''
					this.form.password_re = ''
					this.loaders.login = false
				})
				.catch(error => {
					console.error(error)
					this.loaders.login = false
				})
		},
		// logout
		logout () {
			this.loaders.loggingOut = true
			this.$oauth.logout()
				.then(response => {
					this.$store.dispatch('logout')
						.then(response => {
							this.loaders.loggingOut = false
							this.$router.replace({name: 'login'})
						})
				})
				.catch(error => {
					console.error(error)
					this.loaders.loggingOut = false
				})
		}
	},
	computed: {
		disableSave () {
			return !this.form.email ||
				!this.form.password || 
				!this.form.password_re || 
				this.loaders.login
		},

		accountUserId () {
			let user_id = this.isHubManager ? this.user.original.id : this.user.id
			return user_id
		}
	}
}
</script>