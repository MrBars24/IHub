import Notification from '../../api/notifications'

const state = {
	notifications: []
}

const actions = {
	getNotifications ({commit, rootState}, hub) {
		const _hub = hub || rootState.Hub.selected
		let _notification = new Notification(_hub) // the api
		return new Promise((resolve, reject) => {
			_notification.getNotifications()
				.then(response => {
					// update messages
					commit('setNotifications', {
						notifications: response.data.data.notifications
					})
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	revertNotificationState ({commit}) {
		commit('setNotifications', {
			notifications: []
		})
	}
}

const getters = {
}

const mutations = {
	setNotifications: (state, {notifications}) => state.notifications = notifications,
}

export default {
	state,
	actions,
	mutations,
	getters
}