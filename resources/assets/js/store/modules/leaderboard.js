import Leaderboard from '../../api/leaderboard'

// states
const state = {
	list: [] // leaderboard
}

// actions
const actions = {
	getLeaderboard ({commit,rootState}, hub) {
		const _hub = hub || rootState.Hub.selected
		let leaderboard = new Leaderboard(_hub)
		return new Promise((resolve, reject) => {
			leaderboard.getLeaderboard()
			.then(response => {
				commit('setLeaderboard', {
					leaderboard: response.data.data.leaderboard
				}, { root: true })
				resolve(response)
			})
			.catch(error => reject(error))
		})
	},

	revertLeaderboardData ({commit}) {
		commit('setLeaderboard', {
			leaderboard: []
		})
	}
}

// mutations
const mutations = {
	setLeaderboard (state, {leaderboard}) {
		state.list = leaderboard
	}
}

export default {
	state,
	actions,
	mutations
}