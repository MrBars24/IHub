import Hub from '../api/hub'
let _hub = new Hub()
import store from '../store'
import Color from 'color'

export const SelectHub = route => {
	let slug = route.params.hub_slug || store.state.Hub.selected.slug
	if (slug && route.params.hub_slug !== store.state.Hub.selected.slug) {
		store.dispatch("getHub", { slug }).then(response => {
			return response.data.data.hub
		})
	}
}

export const bodyClass = value => {
	let bodyClasses = removeClass(document.body.className, '-page')
	let finalClass = bodyClasses

	if (value !== undefined)
		finalClass = addClass(bodyClasses, value)
		
	document.body.className = finalClass
}

export const title = title => {
	document.title = title == undefined ? 'Influencer HUB' : title + ' - Influencer HUB'
}


function addClass(classStack, value) {
	if (!value) return
	let bodyClassList = classStack.split(' ')
	
	if (!bodyClassList.filter(item => value == item).length)
		bodyClassList.push(value)

	return bodyClassList.join(' ')
}

function removeClass(classes, pattern) {
	if (!pattern) return
	let classList = stripAndCollapse(classes).split(' ')
	let newClassList = _.remove(classList, item => !item.match(new RegExp(pattern)))
	return newClassList.join(' ')
}

/**
 * Strip and collapse whitespace according to HTML spec
 * https://html.spec.whatwg.org/multipage/infrastructure.html#strip-and-collapse-whitespace
 */

function stripAndCollapse( value ) {
	var tokens = value.match( /[^\x20\t\r\n\f]+/g ) || [];
	return tokens.join( " " )
}

export const LightenDarkenColor = (color, percent = 60) => {
	percent = percent * .01
	let newColor = Color(color).fade(percent)
	return newColor
}