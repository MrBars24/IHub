import axios from 'axios'
import { BASE_TOKEN, API_URL } from '../config/auth'
import store from '../store'

class Report
{
	constructor (hub = null)
	{
		// check if undefined or null
		this.hub = hub
		this.hubUrl = `${API_URL}${this.hub.slug}/`
		this.urls = {
			GIGS_REPORT: `${this.hubUrl}reporting/gigs`,
			INFLUENCERS_REPORT: `${this.hubUrl}reporting/influencers`,
			ALERTS_REPORT: `${this.hubUrl}reporting/alerts`,
			SOCIAL_REPORT: `${this.hubUrl}reporting/social`,
			REPORT_HISTORY: `${this.hubUrl}reporting/history`,
		}
	}

	getGigsReport (startDate = "", endDate = "")
	{
		let url = (startDate == "" && endDate == "") ? this.urls.GIGS_REPORT : this.urls.GIGS_REPORT + "?start_date=" + startDate + "&end_date=" + endDate;

		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getInfluencersReport (startDate = "", endDate = "")
	{
		let url = (startDate == "" && endDate == "") ? this.urls.INFLUENCERS_REPORT : this.urls.INFLUENCERS_REPORT + "?start_date=" + startDate + "&end_date=" + endDate;

		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getAlertsReport (startDate = "", endDate = "")
	{
		let url = (startDate == "" && endDate == "") ? this.urls.ALERTS_REPORT : this.urls.ALERTS_REPORT + "?start_date=" + startDate + "&end_date=" + endDate;

		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getSocialReport (startDate = "", endDate = "")
	{
		let url = (startDate == "" && endDate == "") ? this.urls.SOCIAL_REPORT : this.urls.SOCIAL_REPORT + "?start_date=" + startDate + "&end_date=" + endDate;

		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	}

	getGeneratedReport (url)
	{
		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response =>{
					require("downloadjs")(response.data.snapshot.download_url);
					resolve(response);
				})
				.catch(error => reject(error))
		})
	}

	getReportHistory (startDate = "", endDate = "", screen = "")
	{
		let url = (startDate == "" && endDate == "") ? this.urls.REPORT_HISTORY + ((screen == "") ? "" : "?screen=" + screen) : this.urls.REPORT_HISTORY + "?start_date=" + startDate + "&end_date=" + endDate + "&screen=" + screen;

		console.log(this.urls.REPORT_HISTORY);

		return new Promise((resolve, reject) => {
			axios.get(url)
				.then(response =>{
					resolve(response)
				})
				.catch(error => reject(error))
		})
	}

	downloadReport (downloadUrl)
	{
		return new Promise((resolve, reject) => {
			axios.get(downloadUrl)
				.then(response => {
					resolve(response);
				})
				.catch(error => reject(error))
		})
	}
}

export default Report