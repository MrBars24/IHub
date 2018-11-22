import OAuth from '../oauth'
import router from '../routes'
import store from '../store'

let oAuth = new OAuth()

router.beforeEach((to,from,next) => {
	console.log(to)
	if (!to.meta.requiresAuth && oAuth.isAuthenticated()) {
		return next({
			path: '/'
		})
	}

	if(to.meta.requiresAuth && oAuth.guest()){
		return next({
			name: 'login',
			query: {
				redirect: to.name
			}
		})
	}

	// hideMenu
	document.body.classList.remove('pushmenu-push-toright')
	if (document.querySelector('#nav_list')) 
	document.querySelector('#nav_list').classList.remove('active')
	if (document.querySelector('#pushmenu'))
		document.querySelector('#pushmenu').classList.remove('pushmenu-open')

		// hideDropdown
	if (document.querySelector('#menu-dropdown'))
		document.querySelector('#menu-dropdown').classList.remove('open')

	return next()
})