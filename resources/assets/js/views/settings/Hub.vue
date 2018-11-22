<template>
	<div class="settings-content" role="tabpanel">
		<h1>Hub</h1>
		<div class="row">
			<div class="col-md-12">
				<div class="detail-box">
					<div class="head">
						<p>Leave Hub</p>
					</div><!-- /head -->
				</div><!-- /detail-box -->
				<input class="btn-full-width btn-gig-save js-branding-button" 
					@click.prevent.stop="leave"
					value="Leave Hub" 
					type="submit">
			</div>
		</div>
	</div><!-- /tab-pane -->
</template>
<script>
import mixinUser from '../../mixins/user'
import mixinHub from '../../mixins/hub'
import SettingsApi from '../../api/settings'

export default {
	name: 'SettingsHub',

	mixins: [mixinUser, mixinHub],
	
	methods: {
		leave () {
			console.log('im leaving')
			const settingsApi = new SettingsApi(this.hub)

			settingsApi.removeFromHub({
				influencer_ids: [this.user.id]
			})
			.then(response => {
				this.$oauth.logout()
				this.$store.dispatch('logout')
				this.$router.replace({name: 'login'})
			})
			.catch(error => {
				console.error(error)
			})
		}
	}
}
</script>