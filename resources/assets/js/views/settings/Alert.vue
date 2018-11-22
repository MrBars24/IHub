<template>
	<div class="settings-content">
		<h1>Alerts
			<i v-if="loaders.alerts" class="fa fa-spinner fa-pulse fa-fw"></i>
		</h1>
		<div class="row">
			<div class="col-md-12">
				<div class="detail-box">
					<div class="head">
						<p>Gig Alerts</p>
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
									<div class="form-field">
										<label>Send out gig alerts to</label>
										<input type="text" 											
											:placeholder="form.email"
											readonly>
									</div><!-- /form-field -->

									<div class="form-field clearfix">
										<label>Send out gig alerts</label>
										<select class="custom-select" v-model="form.membership.alert_frequency">
											<option :value="key" :key="key"
												v-for="(frequency, key) in temps.alert_frequency">
												{{ frequency }}
											</option>
										</select>
									</div><!-- /form-field -->

									<div class="checkbox-area">

										<input class="styled-checkbox enabled-alerts" 
											id="styled-checkbox-gig-alert" 
											type="checkbox" 
											v-model="form.membership.send_alerts">
										<label for="styled-checkbox-gig-alert">Enable alerts</label>

									</div><!-- /checkbox-area -->

								</form>
							</div><!-- /form-area -->
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->

				<div class="detail-box">
					<div class="head">
						<p>Categories</p>
					</div><!-- /head -->

					<div class="body">
						<div class="bordered-box form-box">
							<div class="form-area">
								<form action="#">
									<div class="form-field clearfix">
										<label>Prefered gig categories</label>
										<div class="gig-categories-list">
											<div class="checkbox-area">
												<category-list v-model="form.membership.categories"
													:items="form.membership.categories">
												</category-list>
											</div><!-- /checkbox-area -->
										</div><!-- /gig-categories-list -->
									</div><!-- /form-field -->

									<div class="form-field clearfix">
										<label>Preferred Platforms</label>
										<div class="dynamic-list prefered-platform-list clearfix">
											<platform-list v-model="form.membership.platforms" 
												:items="form.membership.platforms">
											</platform-list>
										</div><!-- /prefered-platfrom-list -->
									</div><!-- /form-field -->

								</form>
							</div><!-- /form-area -->
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->

				<button type="submit" @click.prevent="save" :disabled="loaders.saving"
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
import mixinUser from '../../mixins/user'
import CategoryList from '../../components/settings/CategoryList.vue'
import PlatformList from '../../components/settings/PlatformList.vue'
export default {
	name: 'SettingsAlert',

	mixins: [mixinHub,mixinUser],

	components: {
		CategoryList,
		PlatformList
	},

	data () {
		return {
			loaders: {
				alerts: false,
				saving: false
			},
			messages: null,
			form: {
				email: null,
				membership: {
					alert_frequency: null,
					send_alerts: true,
					categories: [],
					platforms: [],
				}
			},
			temps: { // defaults
				alert_frequency: {
					fortnight: 'Fortnightly', 
					week: 'Weekly', 
					halfweek: 'Twice a week',
					day: 'Daily'
				}
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
			this.loaders.alerts = true
			this.$store.dispatch('getSettings', {hub: this.hub, tab: 'alerts'})
				.then(response => {
					this.loaders.alerts = false
					let settings = response.data.data.settings
					// map settings to separate categories and platforms 
					// for form and temps object
					let mappedData = this.mapSettings(settings) 
					Object.assign(this.form, mappedData)
				})
				.catch(error => {
					this.loaders.alerts = false
					console.log(error)
				})
		},

		mapSettings (settings) {
			// map categories that is selected
			let membership = _.pick(settings.membership, [
				'alert_frequency',
				'send_alerts',
				'categories',
				'platforms'
			])

			// hotfix
			membership.categories = membership.categories.map(category => {
				if (!category.pivot) {
					let pivot = {
						is_selected: Boolean(category.is_selected),
						category_id: category.id,
						membership: category.membership_id
					}
					this.$set(category, 'pivot', pivot)
				}
				return _.pick(category, [
					'pivot',
					'id',
					'name'
				])
			})

			// hotfix for platform
			membership.platforms = membership.platforms.map(platform => {
				if (!platform.pivot) {
					let pivot = {
						is_selected: Boolean(platform.is_selected),
						platform_id: platform.id,
						membership: platform.membership_id
					}
					this.$set(platform, 'pivot', pivot)
				}
				return _.pick(platform, [
					'pivot',
					'id',
					'name'
				])
			})

			return {
				email: settings.email,
				membership,
			}
		},

		save () {
			this.loaders.saving = true

			let data = {
				payload: this.mapFormData(this.form),
				hub: this.hub,
				tab: 'alerts'
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
		},

		mapFormData (form) {
			form.membership.categories.map((item) => {
				return {
					id: item.id, 
					is_selected: item.pivot.is_selected
				}
			})
			form.membership.platforms.map((item) => {
				return {
					id: item.id, 
					is_selected: item.pivot.is_selected
				}
			})
			return form
		}
	},
	filters: {
		generateNameId (id, name) {
			return name + '-' + id
		},
	}
}
</script>