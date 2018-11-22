const settings = [
	{
		exact: true,
		path: 'account',
		name: 'settings.account',
		meta: { 
			requiresAuth: true,
			tab: 'account',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Account.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'alerts',
		name: 'settings.alerts',
		meta: { 
			requiresAuth: true,
			tab: 'alerts',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Alert.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'messages',
		name: 'settings.messages',
		meta: { 
			requiresAuth: true,
			tab: 'messages',
			bodyClass: 'settings-page'
		},
		components: {
			default: require('../../views/settings/Message.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'profile',
		name: 'settings.profile',
		meta: { 
			requiresAuth: true,
			tab: 'profile',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Profile.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'community',
		name: 'settings.community',
		meta: { 
			requiresAuth: true,
			tab: 'community',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Community.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'influencer',
		name: 'settings.influencer',
		meta: { 
			requiresAuth: true,
			tab: 'influencer',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Influencer.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
	{
		exact: true,
		path: 'hub',
		name: 'settings.hub',
		meta: { 
			requiresAuth: true,
			tab: 'hub',
			bodyClass: 'settings-page'
	 	},
		components: {
			default: require('../../views/settings/Hub.vue'),
			submenu: require('../../views/submenu/Settings.vue')
		}
	},
]

export default settings