export default {
	computed: {
		user () {
			return this.$store.state.user
		},
		membership () {
			return this.user.membership
		},
		role () {
			if (this.membership)
				return this.membership.role
		},
		isHubManager () {
			if (this.membership)
				return this.role === 'hubmanager'
		},
		isAuthenticated () {
			return this.$store.state.isAuthenticated
		},
		accounts () {
			let accounts = this.$store.state.user.accounts
			return accounts
		},
	}
}