import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Notifications
{
	constructor (hub)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			NOTIFICATIONS: `${this.hubUrl}message/notifications`
		}
	}
	
	/**
	 * GET /api/{hub}/message/notifications
	 */
	getNotifications ()
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.NOTIFICATIONS)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

}

export default Notifications