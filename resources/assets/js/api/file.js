import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'
import store from '../store'

class File
{
	constructor ()
	{
		this.urls = {
			UPLOAD: `${API_URL}attachment/upload`,
			SCRAPE: `${API_URL}attachment/scrape`,
			COPY: `${API_URL}attachment/copy`,
		}
	}

	upload (file, config = null) 
	{
		return new Promise((resolve, reject) => {
			const formData = new FormData()
			formData.append('file', file)
			axios.post(this.urls.UPLOAD, formData, config)
				.then(response => {
					// add resource attribute as path vallue
					Object.assign(response.data.data.file, {
						'resource': response.data.data.file.path
					})
					resolve(response)
				})
				.catch(error => reject(error))
		})
	}

	scrape ({url, context = 'post'}, config = null)
	{
		return new Promise((resolve, reject) => {
			axios.post(this.urls.SCRAPE, {url, context}, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default File