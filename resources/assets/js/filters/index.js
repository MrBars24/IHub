import moment from 'moment'
const fromNow = (value) => { 
	return moment.utc(value).local().fromNow()
}
const imgPlaceholder = (value, size = 50) => {
	return value ? value : `/images/img-profile-${size}.jpg`	 
}
const fixTempPath = (value) => {
	if (value)
		return '/temp/' + value
}

export default {
	fromNow,
	imgPlaceholder,
	fixTempPath
}