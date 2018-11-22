// states
const state = {
	current: {
		message: null,
		attachment: null,
		attachments: [],
		platforms: [], // the selected platforms
	},
	linked_accounts: null, // the accoutns linked to user
	gig: {
		id: null,
		attachments: [],
		categories: [],
		platforms: []
	},
	context: 'newsfeed',
	post: {
		author: {
			profile_picture: null,
			name: null
		},
		attachment: {
			description: null,
			hub_id: null,
			id: null,
			post_id: null,
			resource: null,
			shortened_url: null,
			source: null,
			title: null,
			type: null,
			url: null,
		},
		attachments: [],
		likes: [],
		comments: [],
	},
	platform_fields: {
		youtube: null
	}
}

// actions
const actions = {
	updateAuthoringPlatform ({commit, state}, account) {
		let index = _.findIndex(state.current.platforms, item => item.native_id === account.native_id)
		// if found, remove foundPlatform in list
		commit('updateAuthoringPlatform', {
			index,
			account
		})

		return Promise.resolve(index)
	},
	
	// revert post authoring state
	revertPostAuthoringState({commit}) {
		commit('updateAuthoringPlatformFields', {
			youtube: null
		})
		commit('updateAuthoringPost', {
			author: {
				profile_picture: null,
				name: null
			},
			attachment: {
				description: null,
				hub_id: null,
				id: null,
				post_id: null,
				resource: null,
				shortened_url: null,
				source: null,
				title: null,
				type: null,
				url: null,
			},
			attachments: [],
			likes: [],
			comments: [],
		})
		commit('updateAuthoringGig', {
			id: null,
			attachments: [],
			categories: [],
			platforms: []
		})
		commit('resetCurrent')
		commit('updateAuthoringContext', 'newsfeed')
		commit('updateLinkedAccounts', null)
	}
}

// mutations
const mutations = {
	updateAuthoringPlatformFields (state, platform_fields) {
		state.platform_fields = Object.assign(state.platform_fields, {}, platform_fields)
	},
	updateAuthoringPost (state, post) {
		state.post = post
	},
	updateAuthoringGig (state, gig) {
		state.gig = gig
	},
	updateAuthoringAttachment (state, attachment) {
		state.current.attachment = attachment
	},
	updateAuthoringAttachments (state, attachments) {
		state.current.attachments = attachments
	},
	updateAuthoringPlatform (state, payload) {
		if (payload.index >= 0) {
			state.current.platforms.splice(payload.index, 1)
		}
		else {
			state.current.platforms.push(payload.account)
		}
	},
	resetCurrent (state, payload) {
		let defaults = {
			message: null,
			attachment: null,
			attachments: [],
			platforms: [],
		}
		Object.assign(state.current, defaults)
	},
	updateAuthoringPlatformMessage (state, payload) {
		let platformIndex = _.findIndex(state.current.platforms, item => item.native_id === payload.native_id)
		if (platformIndex > -1) {
			Object.assign(state.current.platforms[platformIndex], payload)
		}
	},
	updateAuthoringMessage (state, payload) {
		state.current.message = payload
	},
	updateAuthoringContext (state, payload) {
		state.context = payload
	},
	updateLinkedAccounts (state, accounts) {
		state.linked_accounts = accounts
	}
}

export default {
	state,
	actions,
	mutations
}