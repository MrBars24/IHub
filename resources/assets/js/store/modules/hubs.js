import Hub from '../../api/hub'
import {LightenDarkenColor} from '../../routes/helper'
let apiHub = new Hub()
// states
const state = {
	selected: { // hub
		id: null,
		name: null, 
		profile_picture: null,
		slug: null
	},
	list: [], // hubs
}

// actions
const actions = {
	getHubList({ dispatch, commit, state }) {
		return new Promise((resolve, reject) => {
			apiHub
				.getHubs()
				.then(response => {
					let hubs = response.data.data.hubs;
					commit("setHubList", {
						hubs
					});
					if (hubs.length && state.selected.id === null) {
						commit("selectHub", { hub: hubs[0] });

						let brandingData = _.pick(hubs[0], [
							"branding_header_colour",
							"branding_header_colour_gradient",
							"branding_header_logo",
							'branding_header_logo_web_path',
							"branding_primary_button",
							"branding_primary_button_text"
						]);
						dispatch("brandingSetup", brandingData);
					}
					resolve(response);
				})
				.catch(error => reject(error));
		});
	},

	getHub({ commit, dispatch }, hub) {
		// dispatch('hideSplashScreen', false, {
		//   root: true
		// })
		return new Promise((resolve, reject) => {
			apiHub
				.select(hub.slug)
				.then(response => {
					let data = response.data.data;
					commit("selectHub", data);

					let brandingData = _.pick(data.hub, [
						"branding_header_colour",
						"branding_header_colour_gradient",
						"branding_header_logo",
						'branding_header_logo_web_path',
						"branding_primary_button",
						"branding_primary_button_text"
					]);
					dispatch("brandingSetup", brandingData);
					resolve(response);
				})
				.catch(error => reject(error));
		});
	},

	brandingSetup({ dispatch, commit, state }, brandingData = null) {
		let $brandingHeaderImage = document.getElementById('js-branding-header-logo')

		// just to fix the inconsistency of data
		let brandingHeader = brandingData.branding_header_logo_web_path;
		if (!brandingHeader) 
			brandingHeader = state.selected.branding_header_logo_web_path
		
		if ($brandingHeaderImage)
			$brandingHeaderImage.src = brandingHeader;

		createStyle()

		// build branding style
		function createStyle () {
			let styleTag = document.createElement('style'),
				head = document.head || document.getElementsByTagName('head')[0]

			styleTag.type = 'text/css'
			styleTag.id = 'branding-style'

			let styles = `
			.js-branding-button {
				color: ${brandingData.branding_primary_button_text} !important;
				background: ${brandingData.branding_primary_button} !important;
			}
			.js-branding-button[disabled] {
				background: ${LightenDarkenColor(brandingData.branding_primary_button, 60)} !important; 
			}
			#js-branding-header-colour {
				background: linear-gradient(to right, ${brandingData.branding_header_colour}, ${brandingData.branding_header_colour_gradient}) !important;
			}
			.assets-gallery .item-wrapper.--selected .gig-attachment {
				box-shadow: 0 0 5px ${brandingData.branding_primary_button} !important;
			}`;

			if (styleTag.styleSheet) {
				styleTag.styleSheet.cssText = styles;
			} else {
				styleTag.appendChild(document.createTextNode(styles));
			}

			if (document.getElementById("branding-style")) {
				head.removeChild(document.getElementById("branding-style"))
			}
			head.appendChild(styleTag);

			dispatch('hideSplashScreen', true, {
				root: true
			})
		}
	},

	revertHubState ({commit}) {
		commit('setHubList', {
			hubs: []
		})
		commit('selectHub', {
			hub: { 
				id: null,
				name: null, 
				profile_picture: null,
			}
		})
	}
};

const getters = {
}

// mutations
const mutations = {
	setHubList (state, {hubs}) {
		state.list = hubs
	},
	selectHub (state, {hub}) {
		state.selected = hub
	}
}

export default {
	state,
	actions,
	mutations,
	getters
}