import OAuth from '../oauth'
import router from '../routes'
import AuthApi from '../api/auth'
import SettingsApi from '../api/settings'
import Settings from '../api/settings';
const oAuth = new OAuth()

const actions = {
	// initialize application
	initApp: (state) =>  {
		return Promise.all([
			state.dispatch('checkIfAuthenticated'),
		])
	},

	storeToken (state, payload) {
		return new Promise((resolve, reject) => {
			const authApi = new AuthApi()
			authApi.store(payload)
				.then(response => resolve(response))
				.catch(error => reject(error))
		})
	},

	getSettings ({dispatch, commit, rootState}, {hub, tab = ''}) {
		const _hub = hub || rootState.Hub.selected
		let settingsApi = new SettingsApi(_hub)
		return new Promise((resolve, reject) => {
			settingsApi.getSettings(tab)
				.then(response => {
					let data = response.data.data.settings
					commit('updateSettings', {tab, data})
					
					if (tab === 'profile' && rootState.user.membership.role === "hubmanager") {
						let brandingData = _.pick(data, [
							"branding_header_colour",
							"branding_header_colour_gradient",
							"branding_header_logo",
							"branding_header_logo_web_path",
							"branding_primary_button",
							"branding_primary_button_text"
						]);
						dispatch("brandingSetup", brandingData, {
							root: true
						});
					}
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	hideSplashScreen ({dispatch, commit, rootState}, hide = true) {
		// use jquery to fadeout the splash screen
		if (hide)
			$('#js-splash-screen').fadeOut()
		else 
			$('#js-splash-screen').show()
	},

	updateSettings ({dispatch, commit, rootState}, {hub, payload, tab = ''}) {
		const _hub = hub || rootState.Hub.selected
		let settingsApi = new SettingsApi(_hub)
		return new Promise((resolve, reject) => {
			settingsApi.postSettings(tab, payload)
				.then(response => {
					// NOTE: temporary fix just to update/commit the settings
					dispatch('getSettings', {
						hub: _hub,
						tab
					})
					resolve(response)
				})
				.catch(error => reject(error))
		})
	},

	updateLinkedAccounts({dispatch, commit, rootState}, {hub, account_ids}) {
		const _hub = hub || rootState.Hub.selected
		let settingsApi = new SettingsApi(_hub)

		let payload = { account_ids }
		
		return new Promise((resolve, reject) => {
			settingsApi.updateLinkedAccount(payload)
				.then(response => {
					dispatch("getSettings", { hub: _hub });
					resolve(response)
				})
				.catch(error => reject(error))
		});
	},
	
	logout (state) {
		return new Promise((resolve, reject) => {

			state.commit("setAuthenticated", false, { root: true });

			state.commit("setInit", false, { root: true });

			// mutate the user object
			state.commit("setUser", { // set the defaults
					id: null, 
					name: null, 
					profile_picture: null, 
					membership: { 
						points: 0, 
						role: "" 
					}, 
					accounts: [], 
					object_class: "" 
			}, { root: true });

			// revert hub states
			state.dispatch("revertHubState", null, { root: true });

			// revert gigs state
			state.dispatch("revertGigsState", null, { root: true });

			// revert posts state
			state.dispatch("revertPostsState", null, { root: true });

			// revert my gigs state
			state.dispatch("revertMyGigsState", null, { root: true });

			// revert notifications state
			state.dispatch("revertNotificationState", null, { root: true });

			// revert messages state
			state.dispatch("revertMessagesState", null, { root: true });

			// revert leaderboard state
			state.dispatch("revertLeaderboardData", null, { root: true });

			state.dispatch("revertPostAuthoringState", null, { root: true });

			// revert branding style
			if (document.getElementById("branding-style")) {
				let head = document.head || document.getElementsByTagName("head")[0];
				head.removeChild(document.getElementById("branding-style"));
			}

			if (document.getElementById("js-branding-header-logo")) {
				document.getElementById("js-branding-header-logo").src = resolveStaticAsset("/images/logo.png")
			}
			resolve(true)
		})
	},

	// getAuthenticatedUser
	getAuthenticatedUser: ({ dispatch, commit, rootState, state }) => {
		return new Promise((resolve, reject) => {
			let hub = state.Hub.selected
			if (!hub.slug) {
				hub = {
					slug: oAuth.getSegment(1)
				}
			}

			oAuth.getUser(state.Hub.selected)
			.then(response => {
				let data = response.data.data
				if (data.hub) {

				}
				
				commit('setUser', data.entity)
				resolve(response)
			})
			.catch(error => {
				reject(error)
			})
		})
	},

	// checkIfAuthenticated
	checkIfAuthenticated: ({commit}) => {
		let isAuth = oAuth.isAuthenticated()
		commit('setAuthenticated', isAuth)
	},

	// checkInit
	checkInit: ({commit}) => {
		return new Promise(resolve => {
			commit('setInit', true)
			resolve(true)
		})
	},

	acceptTerms ({commit,rootState}, payload) {
		return new Promise((resolve, reject) => {
			const settings = new Settings(payload.hub)

			settings.acceptTerms().
				then(response => {
					commit('setUserTerms', response.data.success)
					resolve(response)
				})
				.catch(error => {
					reject(error)
				})
		})
	},

	resetTerms ({commit,rootState}) {
		commit('setUserTerms', false)
	}
}

export default actions