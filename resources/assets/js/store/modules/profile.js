import User from '../../api/user'

// states
const state = {
	user: {
		name: null,
		slug: null,
		id: null,
		profile_picture: null,
		membership: {
			points: 0,
			role: ''
		},
		accounts: [],
	},
	accounts: [], // ? depreciate
	pagination: {
		current_page:1,
		from:1,
		last_page:1,
		next_page_url:null,
		per_page:12,
		prev_page_url:null,
		to: 6,
		total: 6
	}
}

// actions
const actions = {
	fetchUserData ({commit,rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		const user = new User(_hub)
		return new Promise((resolve, reject) => {
			user.getProfile(payload.user)
			.then(response => {
				let data = response.data.data
				// NOTE && TODO: i think this should be entity..
				commit('updateUser', data.user ? data.user : data.hub) // update user

				// NOTE: we should commit the global posts not user.posts
				commit('setPosts', {
					posts: data.posts.data
				}, {
					root: true
				})
				let pagination = _.omit(data.posts.data, 'pagination')
				commit('setPagination', pagination, {
					root: true
				}) // update posts pagination
				resolve(response)
			})
			.catch(error => reject(error))
		})
	},
}

// mutations
const mutations = {
	updateUser (state, user) {
		state.user = user
	}
}

export default {
	state,
	actions,
	mutations
}