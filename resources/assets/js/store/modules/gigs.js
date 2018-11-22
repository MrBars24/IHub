import Gig from '../../api/gigs'

// states
const state = {
	list: [], // gigs
	gig: {
		id: null,
		attachments: [],
		categories: [],
		platforms: []
	},
	posts: [], // the gig post that is for review,
	postsPagination: {
		total: null
	}
}

// actions
const actions = {
	getGigs ({commit, rootState}, hub) {
		const _hub = hub || rootState.Hub.selected
		let gig = new Gig(_hub)
		return new Promise((resolve, reject) => {
			gig.getGigs()
			.then(response => {
				commit('setGigs', {
					gigs: response.data.data.gigs
				})
				resolve(response)
			})
			.catch(error => reject(error))
		})
	},

	getExpiredGigs ({commit, rootState}, {hub, url}) {
		const _hub = hub || rootState.Hub.selected
		let gig = new Gig(_hub)
		return new Promise((resolve, reject) => {
			gig.getExpiredGigs(url)
			.then(response => {
				let paginatedData = response.data.data.gigs
				// commit('appendExpiredGigs', paginatedData.data)

				// pagination
				let pagination = _.omit(paginatedData, 'data')
				resolve(paginatedData)
			})
			.catch(error => reject(error))
		})
	},

	getReviews ({commit, rootState}, {hub, pagination}) {
		const _hub = hub || rootState.Hub.selected
		let gigApi = new Gig(_hub)
		return new Promise((resolve, reject) => {
			gigApi.getReviews()
				.then(response => {
					let posts = response.data.data.posts
					commit('setReviews', posts.data)
					let pagination = _.omit(posts, 'data')
					commit('setReviewsPagination', pagination)
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	getOldReviews ({commit, state, rootState}, hub) {
		let pagination = state.postsPagination
		if (!pagination.next_page_url) 
			return Promise.reject('No next page')

		const _hub = hub || rootState.Hub.selected
		let gigApi = new Gig(_hub)
		
		return new Promise((resolve, reject) => {
			gigApi.getOldReviews(pagination.next_page_url)
				.then(response => {
					let posts = response.data.data.posts
					commit('updateReviewPosts', posts.data)
					let pagination = _.omit(posts, 'data')
					commit('setReviewsPagination', pagination)
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	getGig ({commit, state, rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		let gig = new Gig(_hub)
		return new Promise((resolve, reject) => {
			const gig = _.find(state.list, gig => gig.id === payload.gig_id)
			if (gig !== undefined) {
				resolve(gig)
			}
			else {
				gig.getGig(payload.gig_id)
					.then(response => {
						let gig = response.data.data.gig
						commit('setGig', gig)
						resolve(gig)
					})
					.catch(error => reject(error))
			}
		})
	},

	revertGigsState({commit}, payload) {
		
		commit('setGigs', {
			gigs: []
		})

		commit('setGig', {
			id: null,
			attachments: [],
			categories: [],
			platforms: []
		})

		commit('updateReviewPosts', [])
		commit('setReviewsPagination', {
			total: null
		})
	}
}

const getters = {
}

// mutations
const mutations = {
	setGigs (state, {gigs}) {
		state.list = gigs
	},

	appendExpiredGigs (state, gigs) {
		gigs.forEach(gig => {
			state.list.push(gig)
		})
	},

	setGig (state, gig) {
		state.gig = gig
	},

	/**
	 * mutator to the GigPost for the review
	 * @param {[type]} state [description]
	 * @param {[type]} posts [description]
	 */
	setReviews (state, posts) {
		state.posts = posts
	},

	updateReviewPosts (state, posts) {
		posts.forEach(post => state.posts.push(post))
	},

	setReviewsPagination (state, pagination) {
		state.postsPagination = pagination
	}
}

export default {
	state,
	actions,
	mutations,
	getters
}