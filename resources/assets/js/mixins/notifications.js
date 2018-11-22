export default {
	computed: {
		notifications() {
			return this.$store.state.Notification.notifications
		},

		newNotifications() {
			return []
		}
	}
}