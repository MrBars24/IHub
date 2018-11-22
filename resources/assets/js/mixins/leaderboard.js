export default {
	computed: {
		leaderboard () {
			return this.$store.state.Leaderboard.list
		}
	}
}