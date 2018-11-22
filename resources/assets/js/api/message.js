import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Conversation
{
	constructor (hub)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			INBOX: `${this.hubUrl}message/inbox`,
			CONVERSATION: `${this.hubUrl}conversation`,
			NEW: `${this.hubUrl}conversation/new`,
			USER: `${this.hubUrl}message`
		}
	}
	
	/**
	 * GET /api/{hub}/message/inbox
	 */
	getMessages ()
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.INBOX)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * GET /api/{hub}/message/{user}
	 * talking to
	 */
	getUser (user_id) 
	{
		return new Promise((resolve, reject) => {
			axios.get(`${this.urls.USER}/${user_id}`)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * GET /api/{hub}/conversation/{conversation}
	 */
	getConversation (conversation_id)
	{
		let url = `${this.urls.CONVERSATION}/${conversation_id}`
		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * GET /api/{hub}/conversation/new/{entity}
	 * ROUTE hub::message.write
	 */
	getWrite (entity)
	{
		let url = `${this.urls.NEW}/${entity}`
		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/conversation/{conversation}
	 */
	send ({conversation_id}, payload, config = null)
	{
		let url = `${this.urls.CONVERSATION}/${conversation_id}`
		return new Promise((resolve, reject) => {
			axios.post(url, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/conversation/new/{entity}
	 * ROUTE hub::message.write
	 */
	sendNew (entity, payload, config = null)
	{
		return new Promise((resolve, reject) => {
			let url = `${this.urls.NEW}/${entity}`
			axios.post(url, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getOldMessages (url, config) {
		return new Promise((resolve, reject) => {
			axios.get(url, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default Conversation