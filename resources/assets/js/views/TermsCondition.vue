<template>
<div class="terms">
	<div id="display-area">
		<div class="container">
			<div class="terms__typography">
				<p>To join this Hub, please confirm that you agree to the Community Conditions set out below:</p>
				<h2>{{ hub.name }} Community Conditions</h2>
				<p>{{ hub.community_conditions }}</p>
				<p class="terms__updated-at">Last updated: {{ getLastUpdated() }}</p>
			</div>

			<div class="terms__controls--center-block">
				<div class="row">
					<div class="col-xs-6">
						<a href="#" class="terms__button--full-width terms__button--active" @click="accept()">I agree</a>
					</div>
					<div class="col-xs-6">
						<a href="#" class="terms__button--full-width terms__button--inactive" @click="decline()">I don't agree</a>
					</div>
				</div>
			</div>
		</div>
	</div><!-- /display-area -->
</div>
</template>
<script>
import mixinHub from "../mixins/hub";
import mixinUser from "../mixins/user";

export default {
	name: "TermsCondition",
	mixins: [mixinHub, mixinUser],
	mounted: function() {

	},
	watch: {

	},
	methods: {
		getLastUpdated() {
			if (this.hub.conditions_updated_at !== null) {
				return moment(this.hub.conditions_updated_at).format("Do MMMM YYYY, h:mm a");
			}
		
			return moment(this.hub.created_at).format("Do MMMM YYYY, h:mm a");
		},
		accept() {
			this.$store.dispatch('acceptTerms', {
				hub: this.hub
			}).then(response => {
				if(response.data.success) {
					this.$router.replace({
						name: "hub",
						params: { hub_slug: this.hub.slug },
						query: { redirect: this.$route.query.redirect }
					});
				}
			})
		},
		decline() {
			this.$oauth.logout()
				.then(response => {	
					this.$store.dispatch('logout')
					this.$router.replace({name: 'login'})
				})
		}
	}
};
</script>