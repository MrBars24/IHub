
import { Platform } from 'quasar-framework'
import AuthApi from '../api/auth'

export default {
	data () {
		return {
			isInAppBrowser: false,
			refInAppBrowser: null,
			safariViewController: null,
			pushNotification: {
				isSubscribed: false,
				isAuthorized: false
			}
		}
	},

	methods: {
		/**
		 * Cordova methods
		 * NOTE: all cordova fuctions should be placed inside the influencer-hub-pg project repo not here.
		 * move to global function as a link opener for external links.
		 */
		openInAppBrowser(link) {
			if (Platform.is.mobile && this.cordova == undefined)
				return

			link += `&session_user=${this.accountUserId}`

			// open in SafariViewController
			if (SafariViewController) {
				SafariViewController.isAvailable(available => {
					if (available) {
						this.openSafariView(link)
					}
					else {
						this.openInApp(link)
					}
				})
			}
			else {
				// open in inAppBrowser
				this.openInApp(link)
			}
		},

		/**
		 * opens the SafariViewController
		 */
		openSafariView(link) {
			// callbacks
			let success = result => {
				if (result.event === 'loaded') {
					console.log('Loaded: ')
				}
			}
			let failed = message => {
				console.error('SafariViewController error: ' + message)
			}
			// configurations
			const SafariViewOptions = {
				url: link,
				animated: true,
				// tintColor: "#d13f26", // default is ios blue
				// barColor: "#d13f26", // on iOS 10+ you can change the background color as well
				// controlTintColor: "#d13f26"
			}

			// open the SafariViewController
			SafariViewController.show(
				SafariViewOptions,
				result => success(result),
				message => failed(message)
			)
		},


		/**
		 * opens the inAppBrowser cordova plugin
		 */
		openInApp(link) {
			// clearsessioncache=yes,clearcache=yes
			this.refInAppBrowser = cordova.InAppBrowser.open(link, '_blank', 'toolbarcolor=#d13f26,clearsessioncache=yes,clearcache=yes')
			this.refInAppBrowser.addEventListener('loadstop', this.inAppLoadStop)
		},

		inAppLoadStop(event) {
			// @note: we are expecting a url from social callback like this one: "https://ihubapp2.dev.bodecontagion.com/social/{platform}/callback"

			if (!event.url.includes(`/callback`)) {
				return
			}

			let loop = setTimeout(() => {
				this.refInAppBrowser.executeScript({
					code: "JSON.parse(localStorage.getItem( 'linked_account' ))"
				}, values => {
					let linked_account = values[0] // values always return an array
					// If the tokens were set, clear the interval and close the InAppBrowser.
					if (linked_account.data && !linked_account.error) {
						// store the tokens to global window variable
						window.linked_account = linked_account.data
						clearInterval(loop)
						this.refInAppBrowser.close()
						// add the linked_account to platforms array.
						this.addToPlatforms(linked_account.data)
					}
				})
			})
		},

		/**
		 * OneSignal push notification plugin codes.
		 */
		initializeOneSignal () {
			// window.plugins.OneSignal.userProvidedPrivacyConsent // doesn't seem to work on android devices.
				// ios settings
			let iosSettings = {
				kOSSettingsKeyAutoPrompt: true, // Auto prompt user for notification permissions
				kOSSettingsKeyInAppLaunchURL: false // Launch notifications with a launch URL as an in app webview.
			}

			window.plugins.OneSignal
				.startInit(App.oneSignalAppId)
				.handleNotificationOpened(this.onNotificationOpened)
				.iOSSettings(iosSettings)
				.inFocusDisplaying(window.plugins.OneSignal.OSInFocusDisplayOption.Notification)
				.endInit()
			
			window.plugins.OneSignal.setSubscription(true)
			this.oneSignalSetTags()

			// add subscription observer to get the subscription states
			window.plugins.OneSignal.addSubscriptionObserver(this.addSubscriptionObserver)
		},

		/**
		 * listen for push notification subscription changes in the app.
		 * these method is triggered when:
		 * - Getting a push token from Apple / Google.
		 * - Getting a player / user id from OneSignal
		 * - OneSignal.setSubscription is called
		 * - User disables or enables notifications
		 * 
		 * NOTE: find a better OneSignal SDK methods to get and store the playerID to our database.
		 * NOTE: i think it shouldn't be here in this method..
		 */
		addSubscriptionObserver (state) {
			// from unsubscribed to subscribed
			if (state.to.subscribed) {
				this.mobileLogin(state.to.userId)
			}
		},

		oneSignalSetSubscription(subscribe = true) {
			window.plugins.OneSignal.setSubscription(subscribe)
		},

		oneSignalSetTags(email = undefined, hub_slug = undefined) {
			email = email ? email : this.$store.state.user.email
			// hub_slug = hub_slug ? hub_slug : this.$store.state.Hub.selected.slug
			if (email) {
				window.plugins.OneSignal.sendTags({
					email,
					// hub
				})
			}
		},

		/**
		 * save the userId to database.
		 * 
		 * @param {any} userId
		 * @return {void} 
		 */
		mobileLogin (userId) {
			let payload = {
				oauth_token: this.$oauth.getToken(),
				device_token: userId,
				device_os: Platform.is.platform
			}
			// update the database.
			const authApi = new AuthApi()
			authApi.mobile(payload)
				.then(response => {
					console.log('device info stored.')
				})
				.catch(error => {
					console.error(error)
				})
		},

		// checkPermissionAndSubscriptionState () {
		// 	// get permission and subscription state.
		// 	window.plugins.OneSignal.getPermissionSubscriptionState(status => {
		// 		// status.permissionStatus.status: iOS only: Integer: 0 = Not Determined, 1 = Denied, 2 = Authorized
		// 		// status.permissionStatus.state: Android only: Integer: 1 = Authorized, 2 = Denied

		// 		let checker = { // default is for iOS
		// 			status: 'status', 
		// 			authorized: 2
		// 		}
		// 		if (Platform.is.android) {
		// 			checker.status = 'state'
		// 			checker.authorized = 1
		// 		}
		// 		let authorized = status.permissionStatus[checker.status] == checker.authorized
				
		// 		this.pushNotification.isAuthorized = authorized
		// 		this.pushNotification.isSubscribed = status.subscriptionStatus.subscribed

		// 		// prompt user to use notification. 
		// 		// note: move this to observer.
		// 		if (!authorized) {
		// 			alert('would you like Influencer HUB to send you Push Notifications?')
		// 		}
		// 	})

		// 	// add a permission observer
		// 	window.plugins.OneSignal.addPermissionObserver(function (state) {
		// 		alert('Notification permission state changed: ' + state.hasPrompted);
		// 		alert('Notification permission status: ' + state.status);
		// 	})
		// },

		// subscribeUserToNotification() {

		// },

		/**
		 * see the response data structure here: https://documentation.onesignal.com/docs/cordova-sdk#section--osnotificationopenedresult-
		 *
		 * @param {Object} data 
		 */
		onNotificationOpened (data) {
			let notification = data.notification.payload.additionalData
			let path = notification.link.replace(App.baseUrl, '/')
			this.$router.replace(path)
		}
	},

	mounted() {
		this.cordova.on('deviceready', () => {
			this.isInAppBrowser = true
		})
	}
}