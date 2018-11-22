import { BASE_TOKEN, API_URL } from '../config/auth'

export default {
	TOKEN_URL: BASE_TOKEN + 'oauth/token',
	CURRENT_USER_URL : API_URL + "user",
	LOGOUT: API_URL + 'logout'
}