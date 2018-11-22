export default {
	computed: {
		gigs() {
			return this.$store.state.Gig.list;
		},

		reviewPosts() {
			return this.$store.state.Gig.posts;
		},

		gigId () {
			let slug = this.$route.params.gig_slug
			return slug ? slug.split('-', 1)[0] : undefined
		}
	}
};