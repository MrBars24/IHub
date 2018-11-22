/**
 * value.js
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		nested: function(property, alternate) {
			alternate = alternate || undefined;
			var curr = $(this).get(0);
			property = property.replace(/\:/g, '.:');
			property = property.replace(/^\./, '');
			var parts = property.split('.');
			for(var i in parts) {
				if(curr[parts[i]] !== undefined && curr[parts[i]] !== null) {
					curr = curr[parts[i]];
				} else {
					// read special case parts
					switch(parts[i]) {
						case ':first':
							curr = curr[0] || undefined;
							break;
						default:
							curr = alternate;
							break;
					}
				}
				if(curr == undefined || curr == null || (typeof(curr) == 'string' && curr.replace(/^\s+/, '').replace(/\s+$/, '').length == 0)) {
					curr = alternate;
					break;
				}
			}
			return curr;
		},

		pluck: function(property, alternate) {
			return $.map(this, function(o) { return o[property]; });
		}
	});

})(jQuery, window, document);