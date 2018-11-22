import Vue from 'vue'
import VueRouter from 'vue-router'
import { SelectHub } from './helper'
import {Platform} from 'quasar-framework'
import store from '../store'
Vue.use(VueRouter)

// import modules
import GigModule from './modules/gigs'
import MessageModule from './modules/message'
import ProfileModule from './modules/profile'
import SettingsModule from './modules/settings'
import MyGigsModule from './modules/my-gigs'
import PostModule from './modules/post'

const router = new VueRouter({
	mode: Platform.is.mobile ? 'hash' : 'history',
	linkActiveClass: 'selected', // override the default '.link-active-class'
	scrollBehavior () {
		return { x: 0, y: 0 }
	},
	routes: [
		{ 
			// general path
			path: '/login', 
			name: 'login', 
			component: require('../views/Login.vue'),
			meta: { 
				requiresAuth: false, 
				title: 'Login',
				bodyClass: 'auth-page'
			}
		},
		{ 
			path: '/forgot', 
			name: 'forgot', 
			component: require('../views/Password.vue'),
			meta: { 
				requiresAuth: false,
				title: 'Reset your password',
				bodyClass: 'auth-page'
			}
		},
		{
			path: '/reset-password/:token',
			name: 'reset.password',
			component: require('../views/ResetPassword.vue'),
			meta: { 
				requiresAuth: false,
				title: 'Reset your password',
				bodyClass: 'auth-page'
			}
		},
		{ 
			path: '/account-setup', 
			name: 'signup', 
			component: require('../views/Register.vue'),
			meta: { 
				requiresAuth: false,
				title: 'Register',
				bodyClass: 'auth-page'
			}
		},
		{
			path: '/master',
			name: 'master',
			meta: {
				requiresAuth: true,
				title: 'Master Page'
			},
			component: require('../views/Master.vue')
		},
		{
			path: '/',  // will redirect to hub
			name: 'index',
			meta: { 
				requiresAuth: true
			},
			// NOTE: remove this and let the main app handle the route redirects
			// redirect: to => {
			// //   // if (store.state.isInitialized && store.state.Hub.selected.id) {
			// //   //   return {
			// //   //     name: 'gigs.carousel',
			// //   //     params: {
			// //   //       hub_slug: store.state.Hub.selected.slug
			// //   //     }
			// //   //   }
			// //   // }
			//   return {
			//     name: 'hub',
			//     params
			//   }
			// }
		},
		{
			// the web route of hub.
			path: '/:hub_slug',
			name: 'hub',
			meta: {
				title: 'Hub',
			},
			component: require('../views/Hub.vue'),
			props: SelectHub, // select hub
			redirect: to => {
				console.log(to)
				const { query, name } = to
				if (query.redirect) {
					return {
						name: query.redirect,
						query: {
							redirect: undefined
						}
					}
				}
				return {
					name: 'gigs.carousel'
				}
			},
			children: [
				// gigs (default or landing)
				{
					path: 'gigs',
					name: 'gigs',
					meta: { 
						requiresAuth: true,
						title: 'Gigs'
					},
					component: require('../views/Gig.vue'),
					redirect: { name: 'gigs.carousel' },
					children: GigModule
				},
				// my-gigs 
				{
					path: 'my-gigs',
					name: 'my.gigs',
					meta: { 
						requiresAuth: true,
						title: 'My Gigs'
					},
					component: require('../views/MyGigs.vue'),
					redirect: { name: 'my.gigs.scheduled' },
					children: MyGigsModule
				},
				// term & condition
				{
					path: 'terms-condition',
					name: 'terms.condition',
					meta: { 
						requiresAuth: true,
						title: 'Terms and Condition'
					},
					component: require('../views/TermsCondition.vue'),
				},
				// reportings
				{
					exact: true,
					path: 'reporting',
					name: 'reporting',
					meta: { 
						requiresAuth: true,
						title: 'Reporting' // NOTE: dynamic 
					},
					redirect: {
						name: 'report.gigs'
					},
					component: require('../views/Reporting.vue'),
					children: [
						{
							path: 'gigs',
							name: 'report.gigs',
							meta: { 
								requiresAuth: true,
								title: 'Gigs Report'
							},
							components: {
								default: require('../views/ReportGig.vue'),
								submenu: require('../views/submenu/Reports.vue')
							}
						},
						{
							path: 'influencers',
							name: 'report.influencers',
							meta: { 
								requiresAuth: true,
								title: 'Influencers Report'
							},
							components: {
								default: require('../views/ReportInfluencer.vue'),
								submenu: require('../views/submenu/Reports.vue')
							}
						},
						{
							path: 'alerts',
							name: 'report.alerts',
							meta: { 
								requiresAuth: true,
								title: 'Influencer Alerts'
							},
							components: {
								default: require('../views/ReportAlert.vue'),
								submenu: require('../views/submenu/Reports.vue')
							}
						},
						{
							path: 'social',
							name: 'report.social',
							meta: { 
								requiresAuth: true,
								title: 'Influencer Social Media'
							},
							components: {
								default: require('../views/ReportSocialMedia.vue'),
								submenu: require('../views/submenu/Reports.vue')
							}
						},
						{
							path: 'history',
							name: 'report.history',
							meta: { 
								requiresAuth: true,
								title: 'Report History'
							},
							components: {
								default: require('../views/ReportHistory.vue'),
								submenu: require('../views/submenu/Reports.vue')
							}
						}
					]
				},
				// messages (inbox)
				{
					exact: true,
					path: 'messages',
					name: 'messages',
					meta: { 
						requiresAuth: true,
						title: 'Messages'
					},
					component: require('../views/Messages.vue'),
					redirect: { name: 'messages.inbox' },
					children: MessageModule
				},
				// conversation
				{
					path: 'message',
					name: 'message',
					meta: { requiresAuth: true },
					redirect: to => {
						if (!to.params.conversation_id) {
							return {
								name: 'message.new',
							}
						}
						return {
							name: 'message.old'
						}
					},
					component: require('../views/ConversationHome.vue'),
					children: [
						{
							path: 'new',
							name: 'message.new',
							meta: {
								requiresAuth: true,
								title: 'Send new message',
								bodyClass: 'conversation-page'
							},
							component: require('../views/ConversationNew.vue')
						},
						{
							path: ':conversation_id',
							name: 'message.old',
							meta: {
								requiresAuth: true,
								title: 'Conversation', // dynamic
								bodyClass: 'conversation-page'
							},
							component: require('../views/Conversation.vue')
						}
					]
				},
				// settings 
				{
					path: 'settings',
					name: 'settings',
					meta: { 
						requiresAuth: true,
						title: 'Settings'
					},
					component: require('../views/Settings.vue'),
					redirect: { name: 'settings.account' },
					children: SettingsModule
				},
				// newsfeed
				{
					path: 'newsfeed',
					name: 'newsfeed',
					exact: true,
					meta: { 
						requiresAuth: true,
						title: 'Newsfeed',
						bodyClass: 'newsfeed-page'
					},
					component: require('../views/Newsfeed.vue')
				},
				// leaderboard
				{
					path: 'leaderboard',
					name: 'leaderboard',
					meta: { 
						requiresAuth: true,
						title: 'Leaderboard',
						bodyClass: 'leaderboard-page'
					},
					component: require('../views/Leaderboard.vue')
				},
				// write or post-authoring
				{
					path: 'write/:post_id?', // we'll prioritize the post instead of gig when post-authoring
					name: 'write',
					meta: { 
						requiresAuth: true,
						title: 'Post Authoring', // NOTE: dynamic 
						bodyClass: 'post-authoring-page'
					},
					component: require('../views/PostAuthoring.vue')
				},
				// posts
				{
					exact: true,
					path: 'post/:post_id',
					name: 'post',
					meta: { 
						requiresAuth: true,
						title: 'Post' // NOTE: dynamic
					},
					component: require('../views/PostHome.vue'),
					redirect: {
						name: 'post.view'
					},
					children: PostModule
				},
				// profile
				{
					exact: true,
					path: ':user_slug',
					name: 'profile',
					meta: {
						requiresAuth: true,
						title: 'Profile', // NOTE: dynamic 
					},
					component: require('../views/Profile.vue'),
					redirect: {
						name: 'profile.home' 
					},
					children: ProfileModule
				},
			]
		},
		// { 
		// 	// general path
		// 	path: '*', 
		// 	name: '404', 
		// 	component: require('../views/errors/NotFound.vue'),
		// 	meta: { 
		// 		requiresAuth: false,
		// 		title: 'Page not found',
		// 		bodyClass: 'error-page'
		// 	}
		// },
	]
})
export default router