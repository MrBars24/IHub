import Post from '../../api/post'

// states
const state = {
	posts: [], // posts
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
		likes: [],
		comments: [],
	},
	pagination: {
		current_page:1,
		from:0,
		last_page:0,
		next_page_url:null,
		per_page:0,
		prev_page_url:null,
		to: 0,
		total: 0
	}
}

// actions
const actions = {
	getPosts ({commit,rootState}, hub) {
		const _hub = hub || rootState.Hub.selected
		let post = new Post(_hub)
		return new Promise((resolve, reject) => {
			post.getPosts()
			.then(response => {
				const posts = response.data.data.posts
				// update posts
				commit('setPosts', {
					posts: posts.data
				})
				// update pagination
				let pagination = _.omit(posts, 'data')
				commit('setPagination', pagination)

				resolve(response)
			})
			.catch(error => reject(error))
		})
	},

	getOldPosts ({commit, state, rootState}, hub) {
		let pagination = state.pagination
		if (!pagination.next_page_url) 
			return Promise.reject('No next page')
		const _hub = hub || rootState.Hub.selected
		let post = new Post(_hub)
		return new Promise((resolve, reject) => {
			post.getOldPosts(pagination.next_page_url)
				.then(response => {
					let data = response.data.data
					commit('updatePosts', data.posts.data)
					let pagination = _.omit(data.posts, 'data')
					commit('setPagination', pagination)
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	getPost ({commit,state, rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		let _post = new Post(_hub)
		return new Promise((resolve, reject) => {
			const post = _.find(state.posts, post => post.id === payload.post_id)
			if (post !== undefined)  {
				commit('setPost', post)
				resolve(post)
			}
			else {
				_post.getPost(payload.post_id)
					.then(response => {
						commit('setPost', response.data.data.post)
						resolve(response)
					})
					.catch(error => reject(error))
			}
		})
	},

	updatePostComments({commit, state, rootState}, comment) {
		const postIndex = _.findIndex(state.posts, (post) => post.id === comment.post_id)
		commit('addPostComments', {
			postIndex,
			comment,
			inPost: rootState.route.name == 'post.view'
		})
	},

	updatePostLikes({commit, state, rootState}, payload) {
		let inPost = rootState.route.name === 'post.view'
		let postIndex = _.findIndex(state.posts, post => post.id === payload.post_id)
		let likeIndex = -1
		
		if (postIndex > -1) {
			 likeIndex = _.findIndex(state.posts[postIndex].likes, like => {
				return like.entity_id === payload.like.entity_id &&
					like.entity_type === payload.like.entity_type
			})
		}

		if (inPost) {
			likeIndex = _.findIndex(state.post.likes, like => {
				return like.entity_id === payload.like.entity_id &&
					like.entity_type === payload.like.entity_type
			})
		}

		commit('updatePostLikes', {
			postIndex,
			likeIndex,
			inPost,
			like: payload.like,
			postId: payload.post_id,
		})
	},

	revertPostsState ({commit}) {
		commit('setPost', {
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
			likes: [],
			comments: [],
		})

		commit('setPosts', {
			posts: []
		})

		commit('setPagination', {
			current_page:1,
			from:0,
			last_page:0,
			next_page_url:null,
			per_page:0,
			prev_page_url:null,
			to: 0,
			total: 0
		})
	},

	removePostFromList({ commit, state, rootState}, payload) {
		let index = _.findIndex(state.posts, post => post.id === payload.post.id)

		commit('removePostFromList', {
			post: payload.post,
			index
		})
	},

	updatePostHideInList({ commit, state, rootState }, payload) {
		let inPost = rootState.route.name === 'post.view'
		let index = _.findIndex(state.posts, post => post.id === payload.post.id)

		commit('updatePostHideInList', {
			post: payload.post,
			post_hide: payload.post_hide,
			index,
			inPost
		})
	},

	updatePostReportInList({ commit, state, rootState }, payload) {
		let inPost = rootState.route.name === 'post.view'
		let index = _.findIndex(state.posts, post => post.id === payload.post.id)

		commit('updatePostReportInList', {
			post: payload.post,
			report: payload.report,
			index,
			inPost
		})
	},
}

// mutations
const mutations = {
	removePostFromList (state, payload) {
		state.posts.splice(payload.index, 1)
	},
	updatePostReportInList (state, payload) {
		if (!payload.inPost) {
			if (payload.report.is_reported) {
				state.posts[payload.index].reports.push(payload.report)
			}
			else {
				state.posts[payload.index].reports.splice(0, 1)
			}
		}

		if (state.post.id == payload.post.id) {
			if (payload.report.is_reported) {
				state.post.reports.push(payload.report)
			}
			else {
				state.post.reports.splice(0, 1)
			}
		}
	},
	updatePostHideInList (state, payload) {
		if (!payload.inPost) {
			if (payload.post_hide.is_hidden) {
				state.posts[payload.index].hidden_posts.push(payload.post_hide)
			}
			else {
				state.posts[payload.index].hidden_posts.splice(0, 1)
			}
		}

		if (state.post.id == payload.post.id) {
			if (payload.post_hide.is_hidden) {
				state.post.hidden_posts.push(payload.post_hide)
			}
			else {
				state.post.hidden_posts.splice(0, 1)
			}
		}
	},
	setPosts (state, {posts}) {
		state.posts = posts
	},
	updatePosts (state, posts) {
		posts.forEach(post => state.posts.push(post))
	},
	setPagination (state, pagination) {
		state.pagination = pagination
	}, 
	setPost (state, post) {
		state.post = post
	},
	addPostComments (state, payload) {
		if (!payload.inPost)
			state.posts[payload.postIndex].comments.push(payload.comment)
		 
		if (state.post.id == payload.comment.post_id) {
			state.post.comments.push(payload.comment)
		}
	},
	updatePostLikes (state, payload) {
		if (!payload.inPost) {
			// add new post
			if (payload.likeIndex === -1) {
				state.posts[payload.postIndex].likes.push(payload.like)
			}
			else {
				// the .is_liked is always false
				// but for the safety check. ensure if it's unliked
				if (!payload.like.is_liked) {
					state.posts[payload.postIndex].likes.splice(payload.likeIndex, 1)
				}
			}
		}

		// update the state.post object as well
		if (state.post.id === payload.postId) {
			if (payload.likeIndex === -1) {
				state.post.likes.push(payload.like)
			}
			else {
				if (!payload.like.is_liked) {
					state.post.likes.splice(payload.likeIndex, 1)
				}
			}
		}
	}
}

export default {
	state,
	actions,
	mutations
}