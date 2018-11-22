<template>
<div class="settings-content">
	<h1>Profile
		<i v-if="loaders.profile" class="fa fa-spinner fa-pulse fa-fw"></i>
	</h1>
	<div class="row">
		<div class="col-md-12">
			<div class="detail-box">
				<div class="head">
					<p>Profile</p>
				</div><!-- /head -->

				<div class="body">
					<div class="bordered-box form-box">

						<div class="alert alert-success" v-if="messages">
							<p v-for="(message,index) in messages" :key="index">
								{{ message }}
							</p>
						</div>
						
						<p>Customise your profile here and preview your changes live below.</p>
						<div class="profile-form">
							<div class="row">
								<div class="col-md-6">
									<div class="form-field">
										<label>Display Name</label>
										<input v-model="form.name"
											type="text" 
											placeholder="Your name that will show in the app">
									</div><!-- /form-field -->

									<div class="form-field">
										<label>Summary</label>
										<!-- <textarea v-model="form.summary"></textarea> -->
										<text-counter ref="textSummary" :max="1024" 
											:pre-text="form.summary" 
											v-model="form.summary">
										</text-counter>
									</div><!-- /form-field -->

									<div class="form-field">
										<label>Cover Picture</label>
										<file-upload ref="uploadCover"
											modal-title="Crop your Cover Picture"
											:aspect-ratio="2.5"
											:croppable="true"
											name-id="cover-input"
											:is-using-default="!form.cover_picture"
											:original-image="form.original_cover_picture_web_path"
											:crop-box-data="form.cover_picture_cropping | json"
											@cropped-image="croppedCover"
											@rendered-image="renderCover">		
										</file-upload>
									</div><!-- /form-field -->

									<div class="form-field">
										<label>Profile Picture</label>
										<file-upload ref="uploadAvatar"
											modal-title="Crop your Profile Picture"
											:aspect-ratio="1"
											:croppable="true"
											name-id="avatar-input"
											:is-using-default="!form.profile_picture"
											:original-image="form.original_profile_picture_web_path"
											:crop-box-data="form.profile_picture_cropping | json"
											@cropped-image="croppedAvatar"
											@rendered-image="renderAvatar">		
										</file-upload>
									</div><!-- /form-field -->

									<!-- 
									<div class="form-field" hidden>
										<label>Profile picture display style</label>
										<select v-model="form.profile_picture_display" 
											class="custom-select">
											<option value="square">square</option>
											<option value="circle">circle</option>
											<option value="posts-square">square (posts only)</option>
											<option value="posts-circle">circle (posts only)</option>
										</select>
									</div> -->
								</div>

								<div class="col-md-6">
									<div class="profile-form__preview">
										<div class="profile-user">
											<div class="profile-user__cover" ref="previewCover" 
												:style="coverPath">
											</div><!-- /profile-user__cover -->

											<div :class="['profile-user__avatar', avatarClass]">
												<span class="av-block">
													<img class="avatar" ref="previewAvatar" 
														:src="avatarPath" alt="">
												</span>
											</div><!-- /profile-user__avatar -->

											<h3 class="profile-user__name">{{ form.name }}</h3>
											<p class="profile-user__summary">{{ form.summary }}</p>

										</div><!-- /profile-user -->


									</div><!-- /profile-form__preview -->
								</div>
							</div>
						</div><!-- /profile-form -->
					</div><!-- /bordered-box -->
				</div><!-- /body -->
			</div><!-- /detail-box -->

			<div v-show="isHubManager">
				<h1>Branding</h1>
				<div class="detail-box">
					<div class="head">
						<p>App template</p>
					</div><!-- /head -->
					<div class="body">
						<div class="bordered-box form-box">
							<p>Change your brand colours and logos here and preview your changes below.</p>
							<div class="form-area">
								<div class="form-field">
									<label>Header colour</label>
									<color-picker :value="form.branding_header_colour" 
										v-model="form.branding_header_colour">
									</color-picker>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Header secondary colour</label>
									<color-picker :value="form.branding_header_colour_gradient" 
										v-model="form.branding_header_colour_gradient">
									</color-picker>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Header Logo</label>
									<file-upload @rendered-image="renderHeaderLogo" ref="uploadHeaderLogo" name-id="header-logo">		
									</file-upload>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Primary button colour</label>
									<color-picker :value="form.branding_primary_button" 
										v-model="form.branding_primary_button">
									</color-picker>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Primary button text colour</label>									
									<color-picker :value="form.branding_primary_button_text" 
										v-model="form.branding_primary_button_text">
									</color-picker>
								</div><!-- /form-field -->
							</div><!-- /form-area -->
							
							<p>App template preview</p>
							<div class="branding-area">
								<branding-app :branding-data="brandingAppData"
									:user="brandingAppUserData">
								</branding-app>
							</div>
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->

				<div class="detail-box">
					<div class="head">
						<p>Email template</p>
					</div><!-- /head -->
					<div class="body">
						<div class="bordered-box form-box">
							<p>Change your brand colours and logos here and preview your changes below.</p>
							<div class="form-area">
								<div class="form-field">
									<label>Header colour</label>
									<color-picker :value="form.email_header_colour" 
										v-model="form.email_header_colour">
									</color-picker>
									
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Footer colour</label>
									<color-picker :value="form.email_footer_colour" 
										v-model="form.email_footer_colour">
									</color-picker>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Email Logo</label>
									<file-upload ref="uploadEmailLogo" name-id="email-logo" @rendered-image="renderEmailLogo">		
									</file-upload>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Footer text 1</label>
									<color-picker :value="form.email_footer_text_1" 
										v-model="form.email_footer_text_1">
									</color-picker>
								</div><!-- /form-field -->
								<div class="form-field">
									<label>Footer text 2</label>
									<color-picker :value="form.email_footer_text_2" 
										v-model="form.email_footer_text_2">
									</color-picker>
								</div><!-- /form-field -->
							</div><!-- /form-area -->
							<p>Email template preview</p>
							<div class="branding-area">
								<branding-email :branding-data="brandingEmailData"
									:user="brandingAppUserData">
								</branding-email>
							</div>
						</div><!-- /bordered-box -->
					</div><!-- /body -->
				</div><!-- /detail-box -->
			</div>

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
import axios from 'axios'
import FileUpload from '../../components/FileUpload.vue'
import TextCounter from '../../components/TextCounter.vue'
import ColorPicker from '../../components/ColorPicker.vue'
import BrandingApp from '../../components/settings/BrandingApp.vue'
import BrandingEmail from '../../components/settings/BrandingEmail.vue'
import mixinHub from '../../mixins/hub'
import mixinUser from '../../mixins/user'
import autosize from 'autosize'
export default {
	name: 'SettingsProfile',
	
	mixins: [mixinHub, mixinUser],

	components: {
		FileUpload,
		TextCounter,
		ColorPicker,
		BrandingApp,
		BrandingEmail
	},

	data () {
		return {
			form: {
				name: null,
				summary: '',
				cover_picture: null,
				cover_picture_web_path: null,
				cover_picture_cropping: null,
				profile_picture: null,
				profile_picture_medium: null,
				profile_picture_cropping: null,
				profile_picture_display: 'square',
			},
			loaders: {
				profile: false,
				saving: false
			},
			rendering: {
				avatar: false,
				cover: false
			},
			temps: {
				avatar: {
					path: null,
					type: 'string' // string|crop|render
				},
				cover: {
					path: null,
					type: 'string' // string|crop|render
				}
			},
			messages: null,
			original: {
				cover_picture: null,
				cover_picture_cropping: null,
				profile_picture: null,
				profile_picture_cropping: null
			}
		}
	},

	mounted () {
		if (this.init) {
			this.getProfile()
		}
	},

	watch: {		
		'$route': 'getProfile',
		init (value) {
			if (value) {
				this.getProfile()
			}
		},
	},

	methods: {
		getProfile () {
			this.loaders.profile = true
			this.$store.dispatch('getSettings', {hub: this.hub, tab: 'profile'})
			.then(response => {
				let data = response.data.data
		    this.form = Object.assign({}, this.form, data.settings)
				// cached the original data
				this.original = Object.assign({}, this.original, _.pick(data.settings, [
					'cover_picture',
					'cover_picture_cropping',
					'profile_picture',
					'profile_picture_cropping'
				]))

				this.loaders.profile = false
			})
			.catch(error => {
				this.loaders.profile = false
				console.error(error)
			})
		},

		save () {
			// NOTE: redo this. specially the upload
			this.loaders.saving = true
			Promise.all([
				this.$refs.uploadAvatar.upload(), 
				this.$refs.uploadCover.upload(),
				this.$refs.uploadHeaderLogo.upload(),
				this.$refs.uploadEmailLogo.upload()
			]).then(response => {
					// reset renderers
					this.resetRenderers()

					let avatar = null // avatar is first in the Promise.all
					let cover = null // then cover
					let branding_header_logo = null
					let email_logo = null

					if (response[0]) {
						avatar = response[0].data.data.file.path
					}

					if (response[1]) {
						cover = response[1].data.data.file.path
					}

					if (this.isHubManager) {
						if (response[2]) {
							branding_header_logo = response[2].data.data.file.path
						}

						if (response[3]) {
							email_logo = response[3].data.data.file.path
						}

						this.form.branding_header_logo = branding_header_logo ? branding_header_logo : this.form.branding_header_logo
						this.form.email_logo = email_logo ? email_logo : this.form.email_logo
					}					

					this.form.cover_picture = cover ? cover : this.form.cover_picture
					this.form.profile_picture = avatar ? avatar : this.form.profile_picture

					let data = {
						payload: this.form,
						hub: this.hub,
						tab: 'profile'
					}
					this.$store.dispatch('updateSettings', data)
						.then(response => {
							let settings = response.data.data.settings
							this.messages = Array.isArray(settings) ? null : settings
							this.loaders.saving = false
							this.getProfile()
						})
				})
				.catch(error => {
					console.error(error)
					this.loaders.saving = false
					this.resetRenderers()
				})
		},

		croppedAvatar (croppedData) {
			this.form.profile_picture_cropping = JSON.stringify(croppedData.settings)
			this.renderAvatar({
				type: 'crop',
				path: croppedData.path
			}, true)
		},

		renderAvatar (source, rendering = false) {
			this.rendering.avatar = rendering
			this.temps.avatar = source			
		},

		croppedCover (croppedData) {
			this.form.cover_picture_cropping = JSON.stringify(croppedData.settings)
			this.renderCover({
				type: 'crop',
				path: croppedData.path
			}, true)
		},

		renderCover (source, rendering = false) {
			this.rendering.cover = rendering
			this.temps.cover = source
		},

		renderHeaderLogo (source, rendering = false) {
			this.form.branding_header_logo = source.path
			this.form.branding_header_logo_web_path = source.path
		},

		renderEmailLogo (source, rendering = false) {
			this.form.email_logo = source.path
			this.form.email_logo_web_path = source.path
		},

		resetRenderers () {
			if (this.temps.cover) {
				this.temps.cover.path = null
				this.temps.cover.type = null
			}
			
			if (this.temps.avatar) {
				this.temps.avatar.path = null
				this.temps.avatar.type = null
			}
			this.rendering.cover = false
			this.rendering.avatar = false
		}
	},

	computed: {
		avatarClass () {
			// let display = this.form.profile_picture_display @temp
			let display = 'circle'
			return '--display-' + display
		},
		coverPath () {
			// do not override the rendered image blob
			let path = this.form.cover_picture_web_path
			if (this.rendering.cover)
				path = this.temps.cover.path

			return {
				backgroundImage: Boolean(path) ? `url(${path})` : 'none'
			}
		},
		avatarPath () {
			return !this.rendering.avatar ? 
				this.form.profile_picture_medium : 
				this.temps.avatar.path
		},

		brandingAppData () {
			return _.pick(this.form, [
				"branding_header_colour",
				"branding_header_colour_gradient",
				"branding_header_logo",
				"branding_header_logo_web_path",
				"branding_primary_button",
				"branding_primary_button_text"
			])
		},

		brandingAppUserData () {
			return {
				name: this.form.name,
				avatar: this.avatarPath
			}
		},

		brandingEmailData () {
			return _.pick(this.form, [
				'email_logo',
				'email_logo_web_path',
				'email_header_colour',
				'email_footer_colour',
				'email_footer_text_1',
				'email_footer_text_2',	
				'branding_primary_button',
				'branding_primary_button_text'
			])
		}
	},

	filters: {
		json (data) {

			if (data && !_.isObject(data)) {
				return JSON.parse(data)
			}
			return data
		}
	}
}
</script>