$(document).ready(function() {
	var $body = $('body')
	if ($body.hasClass('platform-ios')) { // should be platform specific ie: .contains('platform-ios)
		$(document)
			.on('focus', 'input, textarea', function(e) {
				$body.addClass('ios-input-focus')
			})
			.on('blur', 'input, textarea', function(e) {
				$body.removeClass('ios-input-focus')
			})
	}
})