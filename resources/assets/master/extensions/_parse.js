/**
 * parse.js
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		parse: function(preFilter) {

			// helper
			var buildProp = function(obj, props, value) {
				var plugin = this;
				var prop = props.shift();
				if(props.length) {
					if(prop == '') {
						var i = -1;
						for(var prop in obj) {
							if(/^[0-9]+$/g.test(prop) === true) {
								i = Math.max(i, prop);
							}
						}
						obj[++i] = {};
						buildProp(obj[i], props, value)
					} else if(obj[prop]) {
						buildProp(obj[prop], props, value)
					} else {
						obj[prop] = {};
						buildProp(obj[prop], props, value)
					}
				} else {
					obj[prop] = value;
				}
				return obj;
			}

			var r20 = /%20/g,
				rbracket = /\[\]$/,
				rCRLF = /\r?\n/g,
				rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
				rsubmittable = /^(?:input|select|textarea|keygen)/i;
			var rcheckableType = (/^(?:checkbox|radio)$/i);

			// method
			return (function(element) {
				var config = {};
				// we are no longer using jQuery's "serializeArray" method, since we want to be able to invoke "parse" on arbitrary elements, not just forms
				$(element).find(':input')
					// standard input filter
					.filter(function() {
						var type = this.type;
						// Use .is(":disabled") so that fieldset[disabled] works
						return this.name && !$(this).is(':disabled') &&
							rsubmittable.test(this.nodeName) && !rsubmitterTypes.test(type) &&
							(this.checked || !rcheckableType.test(type));
					})
					// custom filter function
					.filter(function() {
						if(preFilter != undefined) {
							if(preFilter(this) === false) {
								return false;
							}
						}
						return true;
					})
					.map(function(item) {
						var item = this;
						var props = item.name.replace(/\]\[/g, '~').replace(/\[/g, '~').replace(/\]/g, '').split('~');
						config = buildProp(config, props, item.value);
					});
				return config;
			})(this);
		}
	});

})(jQuery, window, document);