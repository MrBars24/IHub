/**
 * tabs.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'tabs',
		defaults = {};

	// The actual plugin constructor
	// Expected element: <div class="datatable">
	function Plugin(element, options) {
		this.element = element;

		// merge options
		this.options = $.extend({}, defaults, options);

		this._defaults = defaults;
		this._name = pluginName;

		this.init();
	}

	Plugin.prototype = {

		// Place initialization logic in here
		init: function() {
			var plugin = this;
			var $element = $(this.element);

			// table header row: toggle searchable field
			$(this.element).on('click', '.tab-pane__nav__link', function(e) {
				e.preventDefault();

				var $this = $(this);
				var $link = $this.find('a');
				$element.find('.tab-pane__nav__link').removeClass('--active');
				$this.addClass('--active');

				$element.find('.tab-pane__content').removeClass('--active');
				$element.find('.tab-pane__content').filter($link.attr('href')).addClass('--active');
			});
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function(options) {
		return this.each(function() {
			if(!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName, new Plugin(this, options));
			}
		});
	};

})(jQuery, window, document);