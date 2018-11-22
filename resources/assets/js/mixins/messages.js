import mixinUser from './user'
export default {
	mixins: [mixinUser],
	computed: {
		conversations() {
			return this.$store.state.Messages.conversations
		},
		conversation() {
			return this.$store.state.Messages.conversation
		},

		// get whos' this user is talking to
		talkingTo() {
			const picker = ['id', 'object_class', 'name', 'profile_picture', 'slug']
			const pick = ['id', 'object_class']
			const receiver = _.pick(this.conversation.receiver, picker)
			const sender = _.pick(this.conversation.sender, picker)
			const user = _.pick(this.user, picker)
			// check if auth user is euqal to sender, return receiver
			return _.isEqual(_.pick(user, pick), _.pick(sender, pick)) ?
				receiver : sender
		},

		/**
		 * get the new messages
		 */
		newMessages() {
			return []
		}
	}
}