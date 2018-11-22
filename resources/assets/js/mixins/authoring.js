export default {
	computed: {
		current () {
			return this.$store.state.Authoring.current
		},
		context () {
			return this.$store.state.Authoring.context
		},
		linkedAccounts () {
			return this.$store.state.Authoring.linked_accounts
		}
	}
}