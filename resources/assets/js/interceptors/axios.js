import http from 'axios'
import OAuth from '../oauth'
import router from '../routes'
import store from '../store'
import { Platform } from 'quasar-framework'

let oAuth = new OAuth();

// Add a request interceptor
http.interceptors.request.use(function (config) {
	// config.headers['X-CSRF-TOKEN'] = Laravel.csrfToken
	config.headers['Authorization'] = oAuth.getAuthHeader() //Example: Bearer asf3132dfsfddffd
	config.headers['X-Requested-With'] = !Platform.is.mobile ? 'XMLHttpRequest' : 'com.influencerhub.app'

	return config;
}, function (error) {
	// Do something with request error
	return Promise.reject(error)
});

// Add a response interceptor
http.interceptors.response.use(function (response) {
	// Do something with response data
	return response;
}, function (error) {
	const currentRoute = router.history.current
	const requiresAuth = currentRoute.meta && currentRoute.meta.requiresAuth
	if (requiresAuth && error.response && error.response.status === 401 && error.response.data.error !== "invalid_credentials") {
		// should do a 3 retries here before logging out the user
		oAuth.logout()
			.then(response => {
				store.dispatch('logout')
					.then(response => {
						let redirectRoute = currentRoute.name

						let route = {
							name: "login",
							query: {
								redirect: redirectRoute
							},
							params: {
								$message: {
									type: 'error',
									from: currentRoute,
									text: 'Your session has expired. Please log in again to continue.'
								}
							}
						}

						if (redirectRoute == 'index' || redirectRoute == 'login') {
							route.query.redirect = 'gigs.carousel'
						}
						console.log(route)
						store.commit('setAuthenticated', false)
						router.replace(route)
					})
			})

	}

	return Promise.reject(error)
});