const profile = [
	{
		exact: true,
		path: '',
		name: 'profile.home',
		meta: { 
			requiresAuth: true,
			title: 'Profile', // NOTE: dynamic 
			bodyClass: 'profile-page'
		},
		components: {
			default: require('../../views/ProfileHome.vue'),
			submenu: require('../../views/submenu/Profile.vue')
		}
	},
	{
		exact: true,
		path: 'posts',
		name: 'profile.posts',
		meta: { 
			requiresAuth: true,
			title: 'Posts', // NOTE: dynamic 
			bodyClass: 'profile-page'
		},
		components: {
			default: require('../../views/ProfilePost.vue'),
			submenu: require('../../views/submenu/Profile.vue')
		}
	}
]
export default profile