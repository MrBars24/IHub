const mutations = {
	// set User
	setUser(state, user) {
		// fix the membership data structure
		// because data structure of a hub user is different
		if (typeof user.membership === "undefined" && user.object_class === "Hub") {
			user.membership = user.original.membership;
		}
		state.user = user;
	},
	updateSettings(state, { data }) {
		state.user = Object.assign({}, state.user, data);
	},
	// update profile picture
	updateProfilePicture: (state, path) => (state.user.profile_picture = path),
	// update cover photo
	updateCoverPhoto: (state, path) => (state.user.cover_picture = path),
	// set if AUthenticated
	setAuthenticated: (state, isAuth) => (state.isAuthenticated = isAuth),
	// set if app is initialized
	setInit: (state, isInit) => (state.isInitialized = isInit),

	updateLoading: (state, isLoading) => state.isLoading = isLoading,

	setUserTerms: (state, isAccepted) => {
		console.log("state");
		console.log(state.user.membership)
		state.user.membership.accepted_conditions = isAccepted
	},
		

};

export default mutations