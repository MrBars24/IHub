import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'

class Settings {
	constructor(hub) {
		// check if undefined or null
		this.hub = hub;
		this.hubUrl = `${API_URL}${this.hub.slug}/`;
		this.urls = {
			SETTINGS: `${this.hubUrl}settings`, // user_slug
			DELETE_CATEGORY: `${this.hubUrl}settings/remove/category/{category}`,
			INFLUENCERS: `${this.hubUrl}settings/influencers`,
			INVITE_INFLUENCER: `${this.hubUrl}settings/influencers/invite`,
			REMOVE_FROM_HUB: `${this.hubUrl}settings/influencers/remove`,
			SEND_MESSAGE: `${this.hubUrl}settings/influencers/message`,
			RESET_POINTS: `${this.hubUrl}settings/influencers/reset`,
			MEMBERSHIP_GROUP: `${this.hubUrl}settings/membership/groups`,
			EXPORT_CSV: `${this.hubUrl}settings/export`,
			LEAVE_HUB: `${this.hubUrl}settings/leave`,
			SETUP_ACCOUNT: `${this.hubUrl}account-setup/{membership}`,
			UPDATE_LINKED_ACCOUNT: `${this.hubUrl}settings/linked-account`,
			TERMS_ACCEPT: `${this.hubUrl}terms/accept`
		}
	}

	/**
	 * GET /api/{hub}/settings/{tab}
	 */
	getSettings(tab) {
		return new Promise((resolve, reject) => {
			let url = `${this.urls.SETTINGS}/${tab}`;
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	/**
	 * POST /api/{hub}/settings/{tab}
	 */
	postSettings(tab, payload) {
		return new Promise((resolve, reject) => {
			let url = `${this.urls.SETTINGS}/${tab}`;
			axios
				.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	getGroups() {
		return new Promise((resolve, reject) => {
			axios
				.get(this.urls.MEMBERSHIP_GROUP)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	setGroup(payload, config = null) {
		let url = `${this.urls.MEMBERSHIP_GROUP}/set`;
		return new Promise((resolve, reject) => {
			axios
				.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	deleteGroup(group_id) {
		return new Promise((resolve, reject) => {
			let url = `${this.urls.MEMBERSHIP_GROUP}/${group_id}`;
			axios
				.delete(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	postGroups(payload) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.MEMBERSHIP_GROUP, payload)
				.then(response => resolve(response))
				.catch(error => reject(error));
		});
	}

	leaveHub() {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.LEAVE_HUB)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	getInfluencers(payload) {
		let url = payload.pagination.next_page_url;

		if (payload.hasNextPage && !url) url = this.urls.INFLUENCERS;

		return new Promise((resolve, reject) => {
			axios
				.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	deleteCategory(category_id, config = null) {
		return new Promise((resolve, reject) => {
			let url = this.urls.DELETE_CATEGORY.replace(/{category}/, category_id);
			axios
				.delete(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	inviteInfluencer(payload, config = null) {
		return new Promise((resolve, reject) => {
			let url = this.urls.INVITE_INFLUENCER
			axios
				.post(url, payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	resetPoints(payload, config = null) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.RESET_POINTS, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	exportCSV() {
		return new Promise((resolve, reject) => {
			axios({
				methods: "get",
				url: this.urls.EXPORT_CSV,
				responseType: "blob"
			})
				.then(response => {
					let blob = new Blob([response.data], {
						type: response.headers["content-type"]
					});
					let filename = (response.headers["content-disposition"] || "").split(
						"filename="
					)[1]
					let linkAnchor = document.createElement("a")
					linkAnchor.href = window.URL.createObjectURL(blob)
					linkAnchor.download = filename
					resolve(linkAnchor)
				})
				.catch(error => reject(error))
		});
	}

	updateLinkedAccount(account_id) {
		return new Promise((resolve, reject) => {
			axios.post(this.urls.UPDATE_LINKED_ACCOUNT, account_id)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	/**
	 * influencer ids
	 */
	removeFromHub(payload, config = null) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.REMOVE_FROM_HUB, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	sendMessage(payload, config = null) {
		return new Promise((resolve, reject) => {
			axios
				.post(this.urls.SEND_MESSAGE, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	setupAccount(payload, membership, config) {
		// create a different instance of axios to set the headers properly
		let url = this.urls.SETUP_ACCOUNT.replace(/{membership}/, membership)
		return new Promise((resolve, reject) => {
			const axiosInstance = axios.create()
			axiosInstance
				.post(url, payload, config)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}

	acceptTerms() {
		let url = this.urls.TERMS_ACCEPT
		return new Promise((resolve, reject) => {
			axios
				.post(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		});
	}
}

export default Settings