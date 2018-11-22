<template>
<div class="settings-content">
	<div class="row">
		<div class="col-md-12">		
			<h1>Community 
				<i v-if="loaders.community" class="fa fa-spinner fa-pulse fa-fw"></i>
			</h1>
			<div class="detail-box">
				<div class="head">
					<p>Conditions</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<div class="alert alert-success" v-if="messages">
							<p v-for="(message,index) in messages" :key="index">
								{{ message }}
							</p>
						</div>
						<p>Set the default conditions that influencers need to agree to before participating in a gig.</p>
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<label>Community Conditions</label>
									<text-counter
										:pre-text="form.community_conditions" 
										v-model="form.community_conditions"
										ref="refCommunityConditions"
										:show-counter="false"
										placeholder="What are the community conditions">
									</text-counter>
								</div><!-- /form-field -->

								<div class="form-field">
									<div class="checkbox-area">
										<input class="styled-checkbox enabled-alerts" 
											id="styled-checkbox-community-condition" 
											type="checkbox" 
											value="value1" 
											checked
											v-model="form.force_reaccept">
										<label for="styled-checkbox-community-condition">Force influencers to re-accept community conditions</label>
									</div><!-- /checkbox-area -->
									<p class="community-condition-txt">Check this field if you have made major changes to your community conditions.</p>
								</div><!-- /form-field -->

								<div class="form-field">
									<label>Default Gig Conditions</label>
									<text-counter ref="refGigConditions"
										:pre-text="form.default_gig_conditions" 
										v-model="form.default_gig_conditions"		
										:show-counter="false"
										placeholder="What are the conditions for all of your gigs?">
									</text-counter>
								</div><!-- /form-field -->
							</form>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Onboarding</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<p>Set information around the influencer onboarding experience.</p>
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<label>Email invitation message</label>
									<text-counter ref="refEmailTextInvite"
										:pre-text="form.email_invite_text" 
										v-model="form.email_invite_text"		
										:show-counter="false"
										placeholder="Add a small message to your influencer invitation emails">
									</text-counter>
								</div><!-- /form-field -->
							</form>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Posting and Sharing</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<p>Set information around the posting and sharing experience.</p>
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<label>Linkedin Image/Video Meta title</label>
									<text-counter ref="refLinkedinCaption"
										:pre-text="form.sharing_meta_linkedin" 
										v-model="form.sharing_meta_linkedin"
										:show-counter="false"
										placeholder="Add a caption for Linkedin sharing metadata">
									</text-counter>
								</div><!-- /form-field -->
							</form>
						</div><!-- /form-area -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div class="detail-box">
				<div class="head">
					<p>Gig Management</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<div class="checkbox-area">
										<input class="styled-checkbox enabled-alerts" 
											id="styled-checkbox-gig-management" 
											type="checkbox" 
											v-model="form.default_gig_require_approval"
											:checked="form.default_gig_require_approval">
										<label for="styled-checkbox-gig-management">
											Gigs require approval by hub manager
										</label>
									</div><!-- /checkbox-area -->
								</div><!-- /form-field -->
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
						<p>Categories that describe the hub's content.</p>
						<div class="form-area">
							<form action="#">
								<div class="form-field">
									<div class="category-hub add-field">
										<ul class="list-states">
											<li v-for="(category,index) in form.categories" :key="category.id"
												:class="['category-hub-item add-field-item', categoryClass(category)]">
												<input type="text" max="40" v-model="category.name" 
													placeholder="Category Name">
												<a href="#" tabindex="-1" role="button" class="btn-remove" 
													@click.prevent.stop="removeCategory(category, index)">		
													<i class="fa fa-times icon-state" v-if="!category.removing"></i>
													<i class="fa fa-undo icon-state" v-else></i>
												</a>
											</li>
										</ul>
									</div><!-- /category-hub -->

									<input type="submit" @click.prevent.stop="addCategory" 
										value="Add Category" 
										class="js-branding-button btn-submit --full-width">

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
				Save
			</button>
		</div>
	</div>
</div><!-- /tab-pane -->
</template>
<script>
import mixinHub from '../../mixins/hub'
import mixinUser from '../../mixins/user'
import SettingsApi from '../../api/settings'
import FileUpload from '../../components/FileUpload.vue'
import TextCounter from '../../components/TextCounter.vue'

export default {
	name: 'SettingsCommunity',

	mixins: [mixinHub, mixinUser],

	components: {
		TextCounter,
		FileUpload
	},

	data () {
		return {
			loaders: {
				community: true,
				saving: false
			},
			form: {
				community_conditions: '',
				default_gig_conditions: '',
				sharing_meta_linkedin: '',
				email_invite_text: '',
				force_reaccept: false,
				default_gig_require_approval: false,
				categories: [],
			},
			messages: null
		}
	},

	mounted () {
		if (this.init) {
			this.getSettings()
		}
	},

	watch: {
		'$route': 'getSettings',
		'user': 'mapForm',
		init (value) {
			if (value) {
				this.getSettings()
			}
		}
	},

	methods: {		
		getSettings () {
			this.loaders.community = true
			this.$store.dispatch('getSettings', {hub: this.hub, tab: 'community'})
				.then(response => {
					this.loaders.community = false
				})
				.catch(error => {
					this.loaders.community = false
					console.error(error)
				})
		},

		// only get the required data for this component
		mapForm() {
			let data = _.pick(this.user, _.keys(this.form))
			if (!data || !data.categories) {
				return
			}
			
			// map category data
			data.categories = data.categories.map(category => {
				this.$set(category, 'removing', false)
				return category
			})
			Object.assign(this.form, {}, data)
			this.loaders.saving = false
		},

		save () {
			this.loaders.saving = true
			let data = {
				payload: this.form,
				hub: this.hub,
				tab: 'community'
			}
			this.$store.dispatch('updateSettings', data)
				.then(response => {
					let settings = response.data.data.settings
					this.messages = Array.isArray(settings) ? null : settings
				})
				.catch(error => {
					console.error(error)
					this.loaders.saving = false
				})
		},

		removeCategory (category, index) {
			if (category.id !== undefined) {
				category.removing = !category.removing
			}
			else {
				this.form.categories.splice(index, 1)
			}
		},

		addCategory () {
			let categories = this.form.categories
			if (categories.length) {
				if (!_.last(categories).name) 
					return				
			}

			const category = {
				'name': null
			}
			this.form.categories.push(category)
		},

		categoryClass (category) {
			return category.removing ? '--removing' : ''
		}
	}
}
</script>