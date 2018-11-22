import oauth from '../oauth'
import { BASE_TOKEN } from './auth'
const oAuth = new oauth()

// default the values from staging config.
const PUSHER_KEY = window.App != undefined ? window.App.pusherKey : 'cf16e40419031d332777'
const PUSHER_CLUSTER = window.App != undefined ? window.App.pusherCluster : 'ap1'

export default {
	broadcaster: 'pusher',
	authEndpoint: BASE_TOKEN + 'broadcasting/auth',
	key: PUSHER_KEY,
	cluster: PUSHER_CLUSTER,
	auth: {
		headers: {
			'Authorization': oAuth.getAuthHeader()
		}
	}
}