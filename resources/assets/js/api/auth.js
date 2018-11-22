import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Auth
{
	constructor ()
	{
		this.urls = {
			STORE: `${API_URL}login/store`,
			MOBILE: `${API_URL}login/mobile`,
			VERIFY_IDENTITY: `${API_URL}onesignal/verify`,
			SEND_PASSWORD: `${BASE_TOKEN}send-password`,
			RESET_PASSWORD: `${BASE_TOKEN}/reset-password`,
			TOKEN: 'oauth/token',
			CHECK: `${BASE_TOKEN}check-slug`
		}
	}

	store (payload) 
	{
		return new Promise((resolve, reject) => {
			axios.post(this.urls.STORE, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	mobile (payload) 
	{
		return new Promise((resolve, reject) => {
			axios.post(this.urls.MOBILE, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	verifiyIdentity (payload)
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.VERIFY_IDENTITY, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	sendPassword (payload)
	{
		return new Promise((resolve, reject) => {
			axios.post(this.urls.SEND_PASSWORD, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	resetPassword (payload)
	{
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.RESET_PASSWORD, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	checkSlug (slug)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.CHECK + '?slug=' + slug
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	token (payload)
	{
		return new Promise((resolve, reject) => {
			axios.post(this.urls.TOKEN, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default Auth