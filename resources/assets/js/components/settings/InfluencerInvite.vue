<template>
	<div class="influencer-invite-container">

		<!-- MODALS -->
		<div class="modal fade" ref="modalConfirmAction" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<h5>
							{{ modalTitle }}
						</h5>
					</div>
					<div class="modal-footer">
						<button type="button" 
							@click="importEmails" 
							:disabled="loaders.importing" 
							class="btn btn-danger">
							<i v-show="loaders.importing" 
								class="fa fa-spinner fa-pulse fa-fw"></i>Yes, continue
						</button>
						<button type="button" 
							class="btn btn-default"
							data-dismiss="modal">
							CANCEL
						</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<p>Use the form below to invite a single influencer to your hub.</p>
		<div class="form-field">
			<label>Email address</label>
			<input placeholder="Email Address" v-model="form.email" type="email">
		</div><!-- /form-field -->

		<div class="alert alert-success" v-if="messages" v-html="messages"></div>

		<div class="action-container text-center">
			<button class="btn-submit js-branding-button" @click.prevent.stop="invite" 
				:disabled="loaders.inviting || !form.email">
				<i v-show="loaders.inviting"
					class="fa fa-spinner fa-pulse fa-fw"></i> Invite
			</button>
			<!-- <span class="separator">or</span> -->
			<button class="btn-submit js-branding-button" :disabled="loaders.importing"
				@click.prevent.stop="$refs.import.click()">
				<i v-show="loaders.importing" 
					class="fa fa-spinner fa-pulse fa-fw"></i> IMPORT INFLUENCERS
			</button>
			<input type="file" @change="processCsv" class="hidden" ref="import" accept="text/csv">
		</div>
	</div>
</template>
<script>
import SettingsApi from '../../api/settings'
import mixinHub from '../../mixins/hub'
import Papa from 'papaparse'

export default {
	mixins: [mixinHub],
	data () {
		return {
			loaders: {
				inviting: false,
				importing: false
			},
			form: {
				email: null,
				emails: []
			},
			messages: null
		}
	},
	mounted () {
		$(this.$refs.modalConfirmAction).on('hidden.bs.modal', () => this.form.emails = [])
	},
	beforeDestroy () {
		$(this.$refs.modalConfirmAction).off('hidden.bs.modal', () => {})
	},
	methods: {
		flashMessage (res) {
			if (res.success) {
				if (!_.isArray(res.data.invited)) {
					this.messages = `An Invitation link has been sent to <strong>${this.form.email}</strong>.`
					this.$bus.$emit('invitation-sent', res.data.invited)
				}
				else {
					let emailText = this.form.emails.length > 1 ? 'emails' : 'email'
					if (res.data.invited.length) {
						this.messages = `An Invitation link has been sent to the ${emailText}.`
						this.$bus.$emit('invitation-sent', res.data.invited)
					}
					else {
						this.messages = `The ${emailText} you have imported was already a member of this Hub.`
					}
				}
			}
			else {
				this.messages = `The user associated with the email <strong>${this.form.email}</strong> was already a member of this Hub.`
			}
		},
		invite() {
			this.loaders.inviting = true
			this.messages = null
			const settingsApi = new SettingsApi(this.hub)
			let payload = {
				payload: this.form.email
			}
			settingsApi.inviteInfluencer(payload)
				.then(response => {
					this.flashMessage(response.data)
					this.loaders.inviting = false
					this.form.email = null
				})
				.catch(error => {
					console.error(error)
					this.loaders.inviting = false
				})
		},
		processCsv ($event) {
			if (!this.$refs.import.files.length) 
				return

			let file = this.$refs.import.files[0]

			Papa.parse(file, {
				complete: result => {
					let filtered = result.data.filter(item => {
						if (!_.isEmpty(item[0]))
							return item[0]
					})
					this.form.emails = _.flatten(filtered)
					if (this.form.emails.length)
						$(this.$refs.modalConfirmAction).modal('show')
				}
			})
		},
		importEmails () {
			this.loaders.importing = true
			this.messages = null

			const settingsApi = new SettingsApi(this.hub)
			let payload = {
				payload: this.form.emails
			}
			settingsApi.inviteInfluencer(payload)
				.then(response => {
					this.loaders.importing = false
					this.flashMessage(response.data)
					this.$refs.import.value = null
					$(this.$refs.modalConfirmAction).modal('hide')
				})
				.catch(error => {
					console.error(error)
					$(this.$refs.modalConfirmAction).modal('hide')
					this.loaders.importing = false
				})
		}
	},
	computed: {
		modalTitle () {
			return `You are about to import ${this.form.emails.length} influencer/s, continue?`
		}
	}
}
</script>