import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

const HUB_URL = API_URL + 'hub/'
export const URL = {
	LIST: HUB_URL + 'list',
	SELECT: HUB_URL + 'select'
}

class Hub
{
	constructor (slug = null) 
	{
		this.slug = slug
	}

	getHubs ()
	{
		return new Promise((resolve, reject) => {
			axios.get(URL.LIST)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	select (hub)
	{
		return new Promise((resolve, reject) => {
			axios.get(`${URL.SELECT}/${hub}`)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default Hub