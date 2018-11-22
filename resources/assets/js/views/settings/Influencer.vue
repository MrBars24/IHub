<template>
<div class="tab-pane fade in active" id="settings-influencers" role="tabpanel">
	
	<h1>Influencers</h1>
	<div class="row">
		<div class="col-md-12">
			<div class="detail-box">
				<div class="head">
					<p>Influencers</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<div class="alert alert-success" v-if="messages">
							<p v-for="message in messages" :key="message.id">
								{{ message }}
							</p>
						</div>
						<div class="form-area">
							<influencer-list></influencer-list>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Invite influencers to your hub</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<div class="form-area">
							<influencer-invite></influencer-invite>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Member groups</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<div class="form-area">
							<member-group></member-group>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Custom fields</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<p>Define any additional fields that you wish influencers to fill out in their profiles. The information filled out in these fields by influencers will only be visible to hub managers. Limited to 10 fields.</p>
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<div class="custom-field-holder add-field">
										<ul v-if="form.custom_fields">
											<li :class="['custom-field-item add-field-item', generateDeleted(custom_field.deleted)]" 
												v-for="(custom_field,index) in form.custom_fields"
												:key="index">
												<input placeholder="Add Custom Field" 
													v-model="custom_field.name" type="text">
												<a role="button" href="#" class="btn-remove"
													@click.prevent.stop="markForDeletion($event, custom_field, index)">	
													<i class="fa fa-times" v-if="!custom_field.deleted"></i>
													<i class="fa fa-undo" v-else></i>
												</a>
											</li>
										</ul>
									</div><!-- /custom-field-holder -->

									<input type="submit" @click.prevent.stop="addCustomField" 
										value="Add Custom Field" 
										class="js-branding-button btn-submit --full-width">
								</div><!-- /form-field -->
							</form>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->
			<button class="btn-full-width btn-gig-save js-branding-button"
				:disabled="loaders.importing"
				@click.prevent.stop="save">
				<i v-show="loaders.saving" 
					class="fa fa-spinner fa-pulse fa-fw"></i> SAVE
			</button>
		</div>
	</div>
</div><!-- /tab-pane -->
</template>
<script>
import mixinHub from '../../mixins/hub'
import SettingsApi from '../../api/settings'
import InfluencerList from '../../components/settings/InfluencerList.vue'
import InfluencerInvite from '../../components/settings/InfluencerInvite.vue'
import MemberGroup from '../../components/settings/MemberGroup.vue'

export default {
	name: 'SettingsInfluencer',

	components: {
		InfluencerList,
		InfluencerInvite,
		MemberGroup
	},

	mixins: [mixinHub],

	data () {
		return {
			loaders: {
				fetching: false,
				saving: false
			},
			form: {
				custom_fields: [],
			},
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
		save () {
			this.loaders.saving = true

			let data = {
				payload: this.form,
				hub: this.hub,
				tab: 'influencer'
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

		generateDeleted (isDeleting) {
			return isDeleting ? '--deleting' : ''
		},

		getSettings () {
			this.loaders.fetching = true
			this.$store.dispatch('getSettings', {hub: this.hub, tab: 'influencer'})
				.then(response => {
					this.loaders.fetching = false
					let settings = response.data.data.settings
					if (settings.custom_fields === null) {
						settings.custom_fields = []
					}

					if (Array.isArray(settings.custom_fields)) {
						let mappedCustomFields = settings.custom_fields.map(name => {
							return {
								name,
								new: false,
								deleted: false
							}
						})
						settings.custom_fields = mappedCustomFields
					}
					Object.assign(this.form, settings)
				})
				.catch(error => {
					this.loaders.fetching = false
					console.log(error)
				})
		},

		markForDeletion ($event, custom_field, index) {
			custom_field.deleted = !custom_field.deleted
			if (custom_field.new && custom_field.deleted) {
				this.form.custom_fields.splice(index, 1)
			}
		},

		addCustomField () {
			let lastCustomField = _.last(this.form.custom_fields)
			if ((lastCustomField && lastCustomField.name === null) || this.form.custom_fields.length >= 10) {
				return
			}
			let customField = {
				name: null,
				new: true,
				deleted: false
			}
			this.form.custom_fields.push(customField)
		}
	}
}
</script>