<template>
<div class="settings-content">
	<h1>Messages
		<i v-if="loaders.notification" class="fa fa-spinner fa-pulse fa-fw"></i>
	</h1>
	<div class="row" v-show="!loaders.notification">
		<div class="col-md-12">
			<div class="detail-box">
				<div class="head">
					<p>Notifications</p>
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
								<div class="checkbox-area">

									<input class="styled-checkbox enabled-alerts" 
										id="styled-checkbox-push-notification" 
										type="checkbox" 
										v-model="form.receive_push_notifications"
										value="value1" :checked="form.receive_push_notifications">
									<label for="styled-checkbox-push-notification">Receive Push Notifications</label>

								</div><!-- /checkbox-area -->

								<div class="table-layout table-layout--messages">
									<div class="table-layout__header">
										<div class="row">
											<div class="col-xs-4 col-sm-2 col-sm-offset-6">
												<label>Web</label>
											</div>

											<div class="col-xs-4 col-sm-2">
												<label>Email</label>
											</div>

											<div class="col-xs-4 col-sm-2">
												<label>Mobile</label>
											</div>

										</div><!-- /row -->
									</div><!-- /table-layout__header -->

									<div class="table-layout__body">
										<div class="row" v-for="(setting, index) in form.notification_settings" :key="index">
											<div class="col-xs-12 col-sm-6">
												<label class="checkbox-group-label">{{ setting.label }}</label>
											</div>

											<div class="col-xs-4 col-sm-2">
												<div class="checkbox-area">
													<input class="styled-checkbox enabled-alerts" 
														:id="index | generateNameId('web')" 
														type="checkbox" 
														v-model="form.notification_settings[index].send_web"
														:checked="setting.send_web">
													<label :for="index | generateNameId('web')">&nbsp;</label>
												</div><!-- /checkbox-area -->
											</div>

											<div class="col-xs-4 col-sm-2">
												<div class="checkbox-area">
													<input class="styled-checkbox enabled-alerts" 
														:id="index | generateNameId('email')" 
														type="checkbox" 
														v-model="form.notification_settings[index].send_email"
														:checked="setting.send_email">
													<label :for="index | generateNameId('email')">&nbsp;</label>
												</div><!-- /checkbox-area -->
											</div>

											<div class="col-xs-4 col-sm-2">
												<div class="checkbox-area">
													<input class="styled-checkbox enabled-alerts" 
														:id="index | generateNameId('mobile')" 
														type="checkbox" 
														v-model="form.notification_settings[index].send_push"
														:checked="setting.mobile">
													<label :for="index | generateNameId('mobile')">&nbsp;</label>
												</div><!-- /checkbox-area -->
											</div>
										</div><!-- /row -->
									</div>

								</div>
							</form>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<button type="submit"
				@click.prevent="save" 
				:disabled="loaders.saving"
				class="btn-full-width btn-gig-save js-branding-button">
				<i v-if="loaders.saving" class="fa fa-spinner fa-pulse fa-fw"></i> 
				SAVE
			</button>
		</div>
	</div>
</div><!-- /tab-pane -->
</template>
<script>
import mixinHub from '../../mixins/hub'
export default {
	name: 'SettingsMessages',

	mixins: [mixinHub],

	data () {
		return  {
			loaders: {
				notification: true,
				saving: false
			},
			message: null,
			form: {
				notification_settings: [],
				receive_push_notifications: true
			}
		}
	},
	
	mounted () {
		if (this.init) {
			this.getSettings()
		}
	},

	watch: {
		'$route': 'getSettings',
		init (value) {
			if (value) {
				this.getSettings()
			}
		}
	},

	methods: {		
		getSettings () {
			this.loaders.notification = true
			this.$store.dispatch('getSettings', {hub: this.hub, tab: 'messages'})
				.then(response => {
					this.loaders.notification = false
					let settings = response.data.data.settings

					settings.notification_settings = settings.notification_settings.map(setting => {
						setting.send_email = Boolean(setting.send_email) 
						setting.send_web = Boolean(setting.send_web) 
						setting.send_push = Boolean(setting.send_push) 
						return setting
					})
					Object.assign(this.form, settings)
				})
				.catch(error => {
					this.loaders.notification = false
					console.log(error)
				})
		},

		save () {
			this.loaders.saving = true

			let data = {
				payload: this.form,
				hub: this.hub,
				tab: 'messages'
			}
			this.$store.dispatch('updateSettings', data)
			.then(response => {
				let settings = response.data.data.settings
				this.messages = Array.isArray(settings) ? null : settings
				this.loaders.saving = false
			})
			.catch(error => {
				this.loaders.saving = false
				console.error(error)
			})
		}
	},
	
	filters: {
		generateNameId (id, name) {
			return name + '-' + id
		},
	}
}
</script>