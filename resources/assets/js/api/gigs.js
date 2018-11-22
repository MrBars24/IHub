import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'
import store from '../store'

class Gig
{
	constructor (hub)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			GIGS: `${this.hubUrl}gigs`,
			GIGS_EXPIRED: `${this.hubUrl}gigs/expired`,
			REVIEW: `${this.hubUrl}gig/review`,
			ACCEPT: `${this.hubUrl}gig/accept`,
			REJECT: `${this.hubUrl}gig/reject`,
			CREATE: `${this.hubUrl}gig/create`,
			EDIT: `${this.hubUrl}gig/edit`,
			VIEW: `${this.hubUrl}gig/{gig}`,
			DELETE: `${this.hubUrl}gig/{gig}`,
			IGNORE: `${this.hubUrl}gig/ignore/{gig}`,
			DELETE_ATTACHMENT: `${this.hubUrl}gig/{gig}/remove/attachment`,
			DELETE_REWARD: `${this.hubUrl}gig/{gig}/remove/reward`
		}
	}

	getGigs ()
	{
		return new Promise((resolve, reject) => {
			axios.get(this.urls.GIGS)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getExpiredGigs(url = null)
	{
		url = url ? url : this.urls.GIGS_EXPIRED
		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	gigIgnore(gig_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.IGNORE.replace(/{gig}/, gig_id)
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * GET hub::gig.review
	 */
	getReviews () {
		return new Promise((resolve, reject) => {
			axios.get(this.urls.REVIEW)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getOldReviews (url)
	{
		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST hub::gig.reject
	 */
	postReject (payload, config = null) {
		return new Promise((resolve, reject) => {
			axios.post(this.urls.REJECT, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST hub::gig.accept
	 */
	postAccept (payload, config = null) {
		return new Promise((resolve, reject) => {
			axios.post(this.urls.ACCEPT, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getGig (gig_id) 
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.DELETE_ATTACHMENT.replace(/{gig}/, gig_id)
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getCreate () {
		return new Promise((resolve, reject) => {
			axios.get(this.urls.CREATE)
				.then(response => resolve(response))
				.catch(error =>reject(error))
		})
	}

	store (gig, config = null) {
		return new Promise((resolve, reject) => {
			axios.post(this.urls.CREATE, gig, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	edit (gig, config = null) {
		return new Promise((resolve, reject) => {
			axios.get(`${this.urls.EDIT}/${gig}`)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	deleteGig (gig_id, config = null) {
		return new Promise((resolve, reject) => {
			let url = this.urls.DELETE.replace(/{gig}/, gig_id)
			axios.delete(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
	/**
	 * GET /api/{hub}/gig/{gig}
	 * ROUTE hub::gig.view [api.php]
	 * 
	 * The hub gig page (specific gig)
	 * 
	 * @param Gig gig
	 * @return Promise
	 */
	view (gig) {
		return new Promise((resolve, reject) => {
			let url = this.urls.VIEW.replace('{gig}', gig)
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	deleteAttachment (gig, attachment, config) {
		return new Promise((resolve, reject) => {
			let url = this.urls.DELETE_ATTACHMENT.replace(/{gig}/, gig)
			axios.delete(`${url}/${attachment}`)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	deleteReward (gig, reward, config) {
		return new Promise((resolve, reject) => {
			let url = this.urls.DELETE_REWARD.replace(/{gig}/, gig)
			axios.delete(`${url}/${reward}`)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	update(gig, config = null) {
		return new Promise((resolve, reject) => {
			axios.patch(`${this.urls.EDIT}/${gig.id}`, gig, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}
}

export default Gig