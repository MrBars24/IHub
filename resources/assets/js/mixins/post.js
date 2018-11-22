export default {
	computed: {
		posts () {
			return this.$store.state.Post.posts
		},
		post () {
			return this.$store.state.Post.post // current
		}
	}
}