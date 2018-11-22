import Message from '../../api/message'
import Vue from 'vue'
const state = {
	conversations: [],
	conversation: { // the selected conversation
		messages: [],
		sender: {
			id: null
		},
		receiver: {
			id: null
		},
		pagination: null,
	}
}

const actions = {
	getMessages ({commit, rootState}, hub) {
		const _hub = hub || rootState.Hub.selected
		let _message = new Message(_hub) // the api
		return new Promise((resolve, reject) => {
			_message.getMessages()
				.then(response => {
					// update messages
					commit('setMessages', {
						conversations: response.data.data.conversations
					})
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	getConversation ({dispatch,commit, rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		let _message = new Message(_hub) // the api
		
		return new Promise((resolve, reject) => {
			_message.getConversation(payload.conversation_id)
				.then(response => {
					let data = response.data.data
					// update conversation
					data.conversation['messages'] = [] // NOTE: hack
					commit('setConversation', {
						conversation: data.conversation
					})
					dispatch('updateMessages', {
						type: 'old',
						data: data.messages.data
					})
					let pagination = _.omit(data.messages, 'data')
					commit('updateConversationPagination', pagination)
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	fetchOldMessages ({dispatch, commit, state, rootState}, payload) {
		let pagination = state.conversation.pagination
		if (!pagination.next_page_url) 
			return Promise.reject('No Next Page')

		const _hub = payload.hub || rootState.Hub.selected
		let _message = new Message(_hub) // the api
		return new Promise((resolve, reject) => {
			_message.getOldMessages(pagination.next_page_url)
				.then(response => {
					let data = response.data.data
					dispatch('updateMessages', {
						type: 'old',
						data: data.messages.data
					})
					let pagination = _.omit(data.messages, 'data')
					commit('updateConversationPagination', pagination)

					resolve(response)
				})
				.catch(error => reject(error))
		})
	},
	// type = String: old | new
	// data = Array, Object
	updateMessages ({commit, rootState}, payload) {
		const method = payload.type == 'old' ? 'prependMessage' : 'appendMessage'
		if (Array.isArray(payload.data)) {
			payload.data.forEach(message => commit(method, message))
		}
		else {
			commit(method, payload.data)
		}
	},

	// revert state
	revertMessagesState ({commit}) {
		commit('setMessages', {
			conversations: []
		})
		commit('setConversation', {
			conversation: {
				messages: [],
				sender: {
					id: null
				},
				receiver: {
					id: null
				},
				pagination: null,
			}
		})
	}
}

const mutations = {
	setMessages: (state, {conversations}) => state.conversations = conversations,
	addConversationMessages: (state, conversation) => {
		state.conversations.unshift(conversation)
	},
	setConversation: (state, {conversation}) => {
		state.conversation = conversation
	},
	updateConversationMessages: (state, messages) => {
		Vue.set(state.conversation, 'messages', messages)
	},
	updateConversationPagination: (state, pagination) => {
		Vue.set(state.conversation, 'pagination', pagination)
	},
	prependMessage: (state, message) => {
		state.conversation.messages.unshift(message)
	},
	appendMessage: (state, message) => {
		state.conversation.messages.push(message)
	}
}

const getters = {
	
}

export default {
	state,
	actions,
	mutations,
	getters
}