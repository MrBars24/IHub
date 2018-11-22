export default {
	computed: {
		hub () {
			return this.$store.state.Hub.selected
		},
		hubs () {
			return this.$store.state.Hub.list
		},
		// this is a temporary fix only. make this async.
		loadInterval () {
			return this.$store.state.isInitialized && this.hub.slug ? 16 : 5000 // check if hub is not null
		}
	}
}