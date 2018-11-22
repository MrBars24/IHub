import MyGig from '../../api/mygigs'
const state = {
	scheduled: [],
	approval: [],
	rejected: [],
	feeds_list: [], // configurations
	gig_feeds: [],
	total: {
		scheduled: 0,
		pending: 0, // approval
		rejected: 0,
		feeds_list: 0,
		fetched: false
	},
	paginationScheduled: null,
	paginationApproval: null,
	paginationRejected: null,
}

const actions = {
	getTotalMyGigs({ commit, state, rootState }, { hub }) {
		const _hub = hub || rootState.Hub.selected;
		let myGig = new MyGig(_hub);

		return new Promise((resolve, reject) => {
			myGig
				.totalCount()
				.then(response => {
					commit("updateTotal", response.data.data);
					resolve(response);
				})
				.catch(error => reject(error));
		});
	},

	getScheduled({ commit, state, rootState }, { hub, fetchNew }) {
		const _hub = hub || rootState.Hub.selected;
		let myGig = new MyGig(_hub);
		let url = null;
		let pagination = state.paginationScheduled;
		if (!fetchNew && pagination) {
			if (!pagination.next_page_url) return Promise.reject("No Next Page");
			else url = pagination.next_page_url;
		}
		return new Promise((resolve, reject) => {
			myGig
				.getScheduled(url)
				.then(response => {
					let posts = response.data.data.posts
					commit("setScheduled", { posts: posts.data, fetchNew })
					pagination = _.omit(posts, "data")
					commit("setPagination", {
						pagination,
						type: "Scheduled"
					})
					commit('updateTotal', {
						scheduled: pagination.total
					})
					resolve(response);
				})
				.catch(error => reject(error))
		});
	},

	getRejected({ commit, state, rootState }, { hub, fetchNew }) {
		const _hub = hub || rootState.Hub.selected;
		let myGig = new MyGig(_hub);
		let url = null;
		let pagination = state.paginationRejected;
		if (!fetchNew && pagination) {
			if (!pagination.next_page_url) return Promise.reject("No Next Page");
			else url = pagination.next_page_url;
		}
		return new Promise((resolve, reject) => {
			myGig
				.getRejected(url)
				.then(response => {
					let posts = response.data.data.posts;
					commit("setRejected", { posts: posts.data, fetchNew })
					pagination = _.omit(posts, "data")
					commit("setPagination", {
						pagination,
						type: "Rejected"
					})
					commit("updateTotal", { rejected: pagination.total })
					resolve(response);
				})
				.catch(error => reject(error));
		});
	},

	getApproval({ commit, state, rootState }, { hub, fetchNew }) {
		const _hub = hub || rootState.Hub.selected;
		let myGig = new MyGig(_hub);
		let url = null;
		let pagination = state.paginationApproval;

		if (!fetchNew && pagination) {
			if (!pagination.next_page_url) return Promise.reject("No Next Page");
			else url = pagination.next_page_url;
		}
		return new Promise((resolve, reject) => {
			myGig
				.getApproval(url)
				.then(response => {
					let posts = response.data.data.posts;
					commit("setApproval", { posts: posts.data, fetchNew })
					pagination = _.omit(posts, "data")
					commit("setPagination", {
						pagination,
						type: "Approval"
					})
					
					commit("updateTotal", { pending: pagination.total })
					resolve(response);
				})
				.catch(error => reject(error));
		});
	},

	updateFeedConfigList({ commit }, { feeds, isNew = false }) {
		commit("setGigFeedList", {
			feeds,
			isNew
		});
	},

	// revert state
	revertMyGigsState({commit}) {
		commit('setScheduled', {
			fetchNew: true,
			posts: []
		})
		commit('setRejected', {
			fetchNew: true,
			posts: []
		})
		commit('setApproval', {
			fetchNew: true,
			posts: []
		})
		commit('setGigFeedList', {
			isNew: false,
			feeds: []
		})
		commit('setPagination', {
			type: 'Scheduled',
			pagination: null
		})
		commit('setPagination', {
			type: 'Approval',
			pagination: null
		})
		commit('setPagination', {
			type: 'Rejected',
			pagination: null
		})
	}
};

const mutations = {
	updateTotal: (state, total) => {
		state.total = Object.assign(state.total, {}, total)

		if (!state.total.fetched) {
			state.total.fetched = true
		}
	},
	setScheduled: (state, {fetchNew = false, posts}) => {
		if (!fetchNew) posts.forEach(push => state.scheduled.push(push));
		else state.scheduled = posts;
	},
	setRejected: (state, {fetchNew = false, posts}) => {
		if (!fetchNew) posts.forEach(push => state.rejected.push(push));
		else state.rejected = posts;
	},
	setApproval: (state, {fetchNew = false, posts}) => {
		if (!fetchNew) posts.forEach(push => state.approval.push(push));
		else state.approval = posts;
	},
	setPagination(state, { pagination, type }) {
		state[`pagination${type}`] = pagination;
	},
	setGigFeedList (state, {feeds, isNew}) {
		if (isNew) {
			if (_.isArray(feeds)) feeds.forEach(feed => state.feeds_list.push(feed))
			else state.feeds_list.push(feeds)
		}
		else state.feeds_list = feeds
	}
};

const getters = {
	
}

export default {
	state,
	actions,
	mutations,
	getters
}