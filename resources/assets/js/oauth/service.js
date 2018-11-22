import Http from 'axios'
import API from '../api/routes'  //It will changed to config OAuth
import { BASE_TOKEN, API_URL } from '../config/auth'
export default {
	user : null,
	destroySession () {
		this.user = null
	},

	currentUser (hub) {
		return new Promise( (resolve, reject) => {
			let url = API_URL + 'entity'

			if (hub.slug)
				url = url + '?hub='+hub.slug
			
			Http.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	},

	logout () {
		return new Promise((resolve, reject) => {
			Http.get(API.LOGOUT)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	},

	attemptLogin (credentials) {
		return new Promise( (resolve, reject) => {
			Http.post(API.TOKEN_URL, credentials)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	},

	addAuthorizationHeader(header){
		Http.defaults.headers.common['Authorization'] = header
	}
}