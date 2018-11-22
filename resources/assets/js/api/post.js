import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Post {
	constructor(hub) {
		// check if undefined or null
		this.hub = hub;
		this.hubUrl = `${API_URL}${this.hub.slug}/`;
		this.urls = {
			POSTS: `${this.hubUrl}newsfeed`,
			POST: `${this.hubUrl}post/`,
			POST_INSTAGRAM: `${this.hubUrl}post/{post}/instagram/{item}`,
			WRITE: `${this.hubUrl}post/write`,
			TAG: `${this.hubUrl}post/tag/{account}`,
			SHARES: `${this.hubUrl}post/{post}/shares`,
			TOGGLE_HIDDEN: `${this.hubUrl}post/{post}/toggle-hidden`,
			REPORT: `${this.hubUrl}post/{post}/report`,
			UNPUBLISH: `${this.hubUrl}post/{post}/unpublish`,
		};
	}

	/**
	 * GET /api/{hub}/message/notifications
	 */
	getPosts() {
		return new Promise((resolve, reject) => {
			axios
				.get(this.urls.POSTS)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	/**
	 * GET /api/{hub}/post/{post}/instagram/{item}
	 * ROUTE hub::post.instagram
	 */
	getInstagramPost(post_id, item_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.POST_INSTAGRAM.replace('{post}', post_id).replace('{item}', item_id)
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/post/{post}/toggle-hidden
	 * ROUTE hub::post.toggle-hidden [api.php]
	 */
	toggleHidden(post_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.TOGGLE_HIDDEN.replace('{post}', post_id)
			axios.post(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/post/{post}/report
	 * ROUTE hub::post.report [api.php]
	 */
	report(post_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.REPORT.replace('{post}', post_id)
			axios.post(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/post/{post}/unpublish
	 * ROUTE hub::post.unpublish [api.php]
	 */
	unpublish(post_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.UNPUBLISH.replace('{post}', post_id)
			axios.post(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * POST /api/{hub}/post/{post}/instagram/{item}
	 * ROUTE hub::post.instagram.sharing
	 */
	instagramPostSharing(post_id, item_id)
	{
		return new Promise((resolve, reject) => {
			let url = this.urls.POST_INSTAGRAM.replace('{post}', post_id).replace('{item}', item_id)
			axios.post(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getSharesList(postId, url = null)
	{
		return new Promise((resolve, reject) => {
			let baseUrl = this.urls.SHARES.replace('{post}', postId)
			let _url = url ? url : baseUrl
			axios.get(_url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getOldPosts(url) {
		return new Promise((resolve, reject) => {
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	getTaggableUsers(payload) {
		return new Promise((resolve, reject) => {
			let url = this.urls.TAG.replace('{account}', payload.account_id)
			axios
				.get(`${url}?query=${payload.query}&platform=${payload.platform}`)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	getPost(post_id) {
		let url = `${this.urls.POST}${post_id}`;
		return new Promise((resolve, reject) => {
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	comment(post_id, payload) {
		let url = `${this.urls.POST}${post_id}`;
		return new Promise((resolve, reject) => {
			axios
				.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	// hub::post.create
	create(payload) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.WRITE, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	// hub::post.write
	getWrite(queryString) {
		return new Promise((resolve, reject) => {
			let url = `${this.urls.WRITE}${queryString}`;
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	like(post_id) {
		let url = `${this.urls.POST}${post_id}/like`;
		return new Promise((resolve, reject) => {
			axios
				.post(url)
				.then(response => {
					let like = response.data.data.like;
					// map like object if it's a new like
					if (like.liker !== undefined) {
						let liker = like.liker;

						// new data is not returning the whole namespace
						if (!liker.object_class.match("App")) {
							liker.object_class = "App\\" + liker.object_class;
						}

						Object.assign(like, {
							entity_id: liker.id,
							entity_type: liker.object_class,
							entity_name: liker.name
						});
						response.data.data.like = like;

						response.data.data.like = _.omit(like, ["liker", "content"]);
					}
					resolve(response);
				})
				.catch(error => reject(error));
		});
	}
}

export default Post