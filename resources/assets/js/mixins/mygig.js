export default {
	computed: {
		postApproval() {
			return this.$store.state.MyGig.approval;
		},
		postRejected() {
			return this.$store.state.MyGig.rejected;
		},
		postScheduled() {
			return this.$store.state.MyGig.scheduled;
		}
	}
};