<template>
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h3>Master Page</h3>

					<button @click="logout" class="btn-submit">
						<span>LOGOUT</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {

	mounted () {
		this.$store.dispatch('hideSplashScreen')
	},

	methods: {
		
		// logout
		logout () {
			this.$oauth.logout()
				.then(response => {	
					this.$store.dispatch('logout')
					this.$router.replace({name: 'login'})
				})
		}
	}
}
</script>

