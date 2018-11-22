import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class MyGig {
	constructor(hub) {
		// check if undefined or null
		this.hub = hub;
		this.hubUrl = `${API_URL}${this.hub.slug}/`;
		this.urls = {
			SCHEDULED: `${this.hubUrl}mygigs/scheduled`,
			POST_RESCHEDULE: `${this.hubUrl}mygigs/scheduled/{post_id}/reschedule`,
			POST_CANCEL: `${this.hubUrl}mygigs/scheduled/{post_id}/cancel`,
			REJECTED: `${this.hubUrl}mygigs/rejected`,
			APPROVAL: `${this.hubUrl}mygigs/approval`,
			FEED: `${this.hubUrl}mygigs/feed`,
			FEED_MANAGE: `${this.hubUrl}mygigs/feed/manage`,
			FEED_GET: `${this.hubUrl}mygigs/feed/manage/edit/{feed_id}`,
			FEED_UPDATE: `${this.hubUrl}mygigs/feed/manage/edit/{feed_id}`,
			FEED_POST_CONTEXT: `${this.hubUrl}mygigs/feed/post/{feed_id}`,
			TOTAL_COUNT: `${this.hubUrl}mygigs/count`,
			VALIDATE: `${this.hubUrl}mygigs/feed/validate`,
			PLATFORMS: `${this.hubUrl}mygigs/platforms`
		};
	}

	/**
	 * GET /{hub}/mygigs/scheduled
	 * ROUTE: hub::gig.my.scheduled
	 */
	getScheduled(url = null) {
		return new Promise((resolve, reject) => {
			let _url = url ? url : this.urls.SCHEDULED;
			axios
				.get(_url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		})
	}

	validate(url) {
		return new Promise((resolve, reject) => {
			let payload = {
				url
			}
			axios.post(this.urls.VALIDATE, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	getPlatforms () {
		return new Promise((resolve, reject) => {
			axios.get(this.urls.PLATFORMS)
				.then(response => resolve(response))
				.catch(error => reject(error));
		})
	}

	totalCount () {
		return new Promise((resolve, reject) => {
			let url = this.urls.TOTAL_COUNT
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		})
	}

	reSchedulePost (payload) {
		return new Promise((resolve, reject) => {
			let url = this.urls.POST_RESCHEDULE.replace(/{post_id}/, payload.post_id)
			axios.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));      
		})
	}

	cancelPost (payload) {
		return new Promise((resolve, reject) => {
			let url = this.urls.POST_CANCEL.replace(/{post_id}/, payload.post_id)
			axios.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))      
		})
	}

	/**
	 * GET /{hub}/mygigs/rejected
	 * ROUTE: hub::gig.my.rejected
	 */
	getRejected(url = null) {
		return new Promise((resolve, reject) => {
			let _url = url ? url : this.urls.REJECTED;
			axios
				.get(_url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * GET /{hub}/mygigs/approval
	 * ROUTE: hub::gig.my.approval
	 */
	getApproval(url = null) {
		return new Promise((resolve, reject) => {
			let _url = url ? url : this.urls.APPROVAL;
			axios
				.get(_url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * POST /{hub}/mygigs/feed
	 * ROUTE: hub::gig.my.feed [api.php]
	 */
	getGigFeeds(url = null) {
		return new Promise((resolve, reject) => {
			let _url = url ? url : this.urls.FEED;
			axios
				.get(_url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * POST /{hub}/mygigs/feed/manage
	 * ROUTE: hub::gig.my.feed.manage.store [api.php]
	 * @param {*} payload
	 */
	createFeedConfig(payload) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.FEED_MANAGE, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * GET /{hub}/mygigs/feed/manage/edit/{gig_feed}
	 * ROUTE: hub::gig.my.feed.manage.edit [api.php]
	 */
	getFeedConfig(id) {
		return new Promise((resolve, reject) => {
			let url = this.urls.FEED_GET.replace("{feed_id}", id);
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	createFeedPostContext (payload, options = null) {
		let url = this.urls.FEED_POST_CONTEXT.replace("{feed_id}", payload.feed_id)
		return new Promise((resolve, reject) => {
			axios
				.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * GET /{hub}/mygigs/feed/manage/edit/{gig_feed}
	 * ROUTE: hub::gig.my.feed.manage.edit [api.php]
	 */
	updateFeedConfig(payload) {
		return new Promise((resolve, reject) => {
			let url = this.urls.FEED_UPDATE.replace("{feed_id}", payload.id);
			axios
				.patch(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * GET /{hub}/mygigs/manage
	 * ROUTE: hub::gig.my.feed.manage [api.php]
	 */
	getFeedConfigList() {
		return new Promise((resolve, reject) => {
			axios
				.get(this.urls.FEED_MANAGE)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}
}

export default MyGig