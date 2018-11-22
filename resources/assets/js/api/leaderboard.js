import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Leaderboard
{
	constructor (hub)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			LEADERBOARD: `${this.hubUrl}leaderboard`
		}
	}

	getLeaderboard ()
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.LEADERBOARD)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default Leaderboard