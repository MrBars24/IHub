import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class User
{
	constructor (hub)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			PROFILE: `${this.hubUrl}`, // user_slug,
			SEARCH: `${this.hubUrl}entity/search?query=`
		}
	}
	
	/**
	 * GET /api/{hub}/{user}
	 */
	getProfile (user)
	{
		return new Promise((resolve, reject) => {
			let url = `${this.hubUrl}${user.slug}`
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	searchEntity (params)
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.SEARCH + params)
				.then(response => resolve(response))
				.catch(error => reject(error));
		})
	}

}

export default User