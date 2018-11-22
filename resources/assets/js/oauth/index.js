import { Cookies, LocalStorage, Platform } from 'quasar-framework'
import http from 'axios'
import ConfigAuth from '../config/auth'
import AuthService from './service'

class OAuth
{
	constructor ()
	{
		this.storages = {
			Cookies,
			LocalStorage
		}
		this.Session = this.storages[ConfigAuth.default_storage]
	}

	logout ()
	{
		return new Promise((resolve, reject) => {
			AuthService.logout().then(response => {
				AuthService.destroySession()
				let path = '/'
				this.Session.remove("access_token", {path})
				this.Session.remove("refresh_token", {path})
				this.Session.remove("oauth_token", {path})
				
				// fix for mobile.
				if (Platform.is.mobile) {
					// TODO: removing item in localStorage in PhoneGap doesn't seem to be working.
					window.localStorage.removeItem('access_token')
					window.localStorage.removeItem('refresh_token')
					window.localStorage.clear()
					// unsubscribe from the push notification
					window.plugins.OneSignal.deleteTag('email')
					window.plugins.OneSignal.setSubscription(false)
				}
				
				resolve(response)
			})
		})
	}

	guest ()
	{
		return !this.Session.has('access_token')
	}

	isAuthenticated ()
	{
		return this.Session.has('access_token')
	}

	login (username, password) 
	{
		let data = {
			username,
			password,
			grant_type: 'password'
		} 
		let config = ConfigAuth.oauth

		//We merge grant type and client secret stored in configuration
		Object.assign(data, config)
		data.grant_type = 'password' // override the bug
		return new Promise( (resolve, reject) => {
			AuthService.attemptLogin(data)
				.then( response => {
					this.storeSession(response.data)
					this.addAuthHeaders()
					resolve(response)
				}).catch((error) => {
					reject(error)
				})
		})
	}

	getUser (hub)
	{
		if(this.Session.has('access_token')){
			return new Promise( (resolve, reject) => {
				AuthService.currentUser(hub)
					.then( response => {
						resolve(response)
					}).catch(error => {
						reject(error)
					})
			})
		}
		return new Promise( resolve => resolve(null) )
	}

	getAuthHeader () 
	{
		if(this.Session.has('access_token')){
			let access_token = this.getItem('access_token')
			return ConfigAuth.oauth_type+" "+ access_token // Example : Bearer xk8dfwv8783dxddjk232xjshoqpx
		}
		return null
	}
	
	getItem (key, options = {}) 
	{
		if(ConfigAuth.default_storage == 'LocalStorage'){
			return this.Session.get.item(key)
		}
		return this.Session.get(key, options)
	}

	addAuthHeaders () 
	{
		let header = this.getAuthHeader()
		AuthService.addAuthorizationHeader(header)
	}

	getToken () {
		if (this.isAuthenticated()) {
			return this.getItem('access_token')
		}
	}

	storeSession (data)
	{

		let hourInMilliSeconds = 86400;
		let time = data.expires_in / hourInMilliSeconds;

		// immediately store the tokens in LocalStorage if the app is running on a mobile 
		// because mobile platforms doesnt support cookies storage
		if(ConfigAuth.default_storage == 'LocalStorage') {
			this.Session.set('access_token', data.access_token)
			this.Session.set('refresh_token', data.access_token)
		}
		else {

			/************************************* 
			** when the Storage is type Cookies
			** we send the expires property given in days
			**************************************/
			this.Session.set('access_token', data.access_token, {
				expires : time,
				path: '/'
			})
			/*
			** We duplicate the time because,
			** in theory it lasts the double of time access token duration
			*/
			this.Session.set('refresh_token', data.access_token, {
				expires : time * 2,
				path: '/'
			})
		}

	}

	getSegment (index) {
		var url = window.location.href.split("/")
		url.shift()
		url.shift()
		url.shift()
		return url[index - 1]
	}

}

export default OAuth
