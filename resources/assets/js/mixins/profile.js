export default {
	computed: {
		user () {
			return this.$store.state.Profile.user
		},
		posts () {
			return this.$store.state.Profile.posts
		}
	}
}