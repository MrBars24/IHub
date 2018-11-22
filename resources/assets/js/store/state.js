const state = {
	isAuthenticated: false,
	isInitialized: false,
	user: {
		// set the defaults 
		id: null,
		name: null,
		profile_picture: null,
		membership: {
			points: 0,
			role: ''
		},
		accounts: [],
		object_class: ''
	},
	isLoading: true
}

export default state