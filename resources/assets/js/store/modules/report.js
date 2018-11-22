import Report from '../../api/report'

// states
const state = {
	gigReport: {
		chart_gig_numbers: null,
		chart_gig_participation_platforms: null,
		chart_gig_participation_categories: null,
		chart_gig_punctuality_first_post: null,
		chart_gig_punctuality_completion: null,
		list_high_gigs: null,
		list_low_gigs: null,
		start_date: "",
		end_date: "",
	}
}

// actions
const actions = {
	fetchGigReport ({commit,rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		const report = new Report(_hub)
		return new Promise((resolve, reject) => {
			report.getGigsReport(payload.startDate, payload.endDate)
			.then(response => {
				let data = response.data;
				commit('updateReport', data);
				resolve(data)
			})
			.catch(error => reject(error))
		})
	},

	fetchInfluencersReport ({commit,rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		const report = new Report(_hub)
		return new Promise((resolve, reject) => {
			report.getInfluencersReport(payload.startDate, payload.endDate)
			.then(response => {
				let data = response.data;
				commit('updateReport', data);
				resolve(data)
			})
			.catch(error => reject(error))
		})
	},

	fetchAlertsReport ({commit,rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		const report = new Report(_hub)
		return new Promise((resolve, reject) => {
			report.getAlertsReport(payload.startDate, payload.endDate)
			.then(response => {
				let data = response.data;
				commit('updateReport', data);
				resolve(data)
			})
			.catch(error => reject(error))
		})
	},

	fetchSocialReport ({commit,rootState}, payload) {
		const _hub = payload.hub || rootState.Hub.selected
		const report = new Report(_hub)
		return new Promise((resolve, reject) => {
			report.getSocialReport(payload.startDate, payload.endDate)
			.then(response => {
				let data = response.data;
				commit('updateReport', data);
				resolve(data)
			})
			.catch(error => reject(error))
		})
	},

	fetchGeneratedReport ({commit,rootState}, payload) {
		return new Promise((resolve, reject) => {
			const report = new Report(payload.hub)
			report.getGeneratedReport(payload.url)
			.then(response => {
				resolve(response)
			})
			.catch(error => reject(error))
		})
	},

	fetchReportHistory ({commit,rootState}, payload) {
		return new Promise((resolve, reject) => {
			const report = new Report(payload.hub)
			report.getReportHistory(payload.startDate, payload.endDate, payload.screen)
			.then(response => {
				resolve(response)
			})
			.catch(error => reject(error))
		})
	}
}

const mutations = {
	updateReport(state, report) {
		state.gigReport = report
	}
}

export default {
	state,
	actions,
	mutations
}