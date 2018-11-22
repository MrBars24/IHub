import Vue from 'vue'
import Vuex from 'vuex'

// import globals
import state from './state'
import actions from './actions'
import mutations from './mutations'

// import modules
import Gig from './modules/gigs'
import Hub from './modules/hubs'
import Leaderboard from './modules/leaderboard'
import Messages from './modules/message'
import Notification from './modules/notification'
import Post from './modules/post'
import Profile from './modules/profile'
import Authoring from './modules/authoring'
import MyGig from './modules/mygigs'
import Report from './modules/report'

Vue.use(Vuex);

/* eslint-disable no-new */
const store = new Vuex.Store({
	state,
	actions,
	mutations,
	modules: {
		Gig,
		Hub,
		Leaderboard,
		Messages,
		Notification,
		Post,
		Profile,
		Authoring,
		MyGig,
		Report
	}
});

export default store