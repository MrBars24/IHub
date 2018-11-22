// temporary fix for BASE_URL on different environment
// let's default this to staging..
export const STATUS = window.App != undefined ? window.App.baseEnv : 'staging'

const URL = window.App != undefined ? window.App.baseUrl : 'https://ihubapp2.staging.bodecontagion.com/'
export const API_URL = URL + 'api/'
export const BASE_TOKEN = URL

const CLIENT_SECRET = window.App != undefined ? window.App.secret : 'berg709KSLpaqgXS6yQJyBqVeoqz4rBWlNrYzlXc'

export default {
	oauth : {
		grant_type : 'password',
		client_id : '3',
		client_secret : CLIENT_SECRET,
		scope : '*'
	},
	default_storage : 'Cookies', //Supported Types 'Cookies', 'LocalStorage',
	oauth_type: 'Bearer'
}