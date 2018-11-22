/**
 * This script is for phonegap 
 */

window.handleOpenURL = function(url) {
	// wrap inside setTimeout
	setTimeout(() => {
		if (SafariViewController) {
			SafariViewController.hide()
		}
		//alert('recieved url: ' + url)
		
		// parse url
		// url: influencerhub://{[account-linked|account-loggedin|app-redirect]}?some=query&string=here
		var urlData = null
		try {
			urlData = url.split("://").pop().split('?')	
		} catch (error) {
			alert('Something went wrong. Please reload the app.')
		}

		if (urlData) {
			var callbackType = urlData[0]
			var queryString = urlData[1]
			
				// emit the event for the app to handle the redirect.
			pg_emitEvent(callbackType, queryString)
		}
	}, 0)
}

function pg_emitEvent(eventType, data) {
	// make sure it's object
	if(typeof(data) !== "object") {
		data = queryStringToJSON(data)
	}

	var evt = new CustomEvent('influencerhub-'+eventType, {
		detail: data
	})
	document.dispatchEvent(evt)
}

// https://stackoverflow.com/a/32204670/3750775
function queryStringToJSON(qs) {
	var pairs = qs.split('&')
	var result = {}
	pairs.forEach(function(p) {
		var pair = p.split('=')
		var key = pair[0];
		var value = decodeURIComponent(pair[1] || '')

		if( result[key] ) {
			if( Object.prototype.toString.call( result[key] ) === '[object Array]' ) {
				result[key].push( value )
			} else {
				result[key] = [ result[key], value ]
			}
		} else {
			result[key] = value
		}
	})

	return JSON.parse(JSON.stringify(result))
}