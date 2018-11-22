/**
 * avatar.js
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
	var pluginName = 'avatar',
		defaults = {
		};

	// The actual plugin constructor
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

			var $avatar = $(this.element);
			$(window).resize(function() {
				plugin.resize();
			});

			// trigger initial resize
			//plugin.resize();
		},

		resize: function() {
			var plugin = this;
			var $avatar = $(this.element);

			// don't do anything if img tag
			if($avatar.find('img').length) {
				return;
			}

			// get frame dimensions
			var wF = $avatar.parent().width();
			var hF = $avatar.parent().height();

			// calculate
			var ratio =  wF / $avatar.data('wc');
			var bgSize = ratio * $avatar.data('wi');
			var bgLeft = ratio * $avatar.data('xc') * -1;
			var bgTop  = ratio * $avatar.data('yc') * -1;

			$avatar.css('background-image', 'url(' + $avatar.data('image') + ')');
			$avatar.css('background-size', bgSize + 'px');
			$avatar.css('background-position', bgLeft + 'px ' + bgTop + 'px');
		},

		setImage: function(image, cropX, cropY, cropW, cropH) {
			var plugin = this;
			var $avatar = $(this.element);

			// extract values
			var w = null;
			var h = null;
			if(typeof(image) == 'object' && image.url != undefined) {
				w = image.w || null;
				h = image.h || null;
				image = image.url;
			}
			$avatar.data('image', image);

			// if no or incomplete cropping information, just render an img tag
			if(!(!!cropX && !!cropY && !!cropW && !!cropH)) {
				plugin.showImgTag(image);
				return;
			}

			// set crop info
			plugin.setCrop(cropX, cropY, cropW, cropH);
			$avatar.find('img').remove();

			// calculate real image dimensions, this is async so we need to wrap after functionality in a callback
			if(w == null || h == null) {
				plugin.calculateDimensions(image, function(width, height) {
					$avatar.data('wi', width);
					$avatar.data('hi', height);
					plugin.resize();
				});
			} else {
				$avatar.data('wi', w);
				$avatar.data('hi', h);
				plugin.resize();
			}
		},

		calculateDimensions: function(image, callback) {
			var $image = $('<img style="pointer-events:none;opacity:0;position:absolute;top:0;max-width:none;max-height:none;" />').attr('src', image);
			$image.load(function() {
				callback($(this).width(), $(this).height()); // call callback function
				$(this).remove();
			}).appendTo('body');
		},

		setCrop: function(cropX, cropY, cropW, cropH) {
			var plugin = this;
			var $avatar = $(this.element);
			$avatar.data('xc', cropX);
			$avatar.data('yc', cropY);
			$avatar.data('wc', cropW);
			$avatar.data('hc', cropH);

			plugin.resize();
		},

		showImgTag: function(image) {
			var plugin = this;
			var $avatar = $(this.element);

			// clear styles, add img tag
			$avatar.attr('css', '');
			$avatar.append($('<img class="avatar" />').attr('src', image));
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
/**
 * character-counter.js
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
	var pluginName = 'characterCounter',
			defaults = {
			};

	// The actual plugin constructor
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

			// events

			// change input: recalculate
			var $counter = $(this.element);
			var $input = $($counter.data('content'));
			$input.bind('input change blur keyup', function(e) {
				plugin.refresh();
			});
			plugin.refresh();
		},

		refresh: function() {
			var plugin = this;
			var $counter = $(this.element);
			var $input = $($counter.data('content'));
			//var length = $input.val().length;
			var length = $input.realText().length;
			var caption = $counter.data('caption');
			caption = caption.replace('%d', length);
			if(length == 1) {
				caption = caption.replace(' characters ', ' character ');
			}
			$counter.text(caption);
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
/**
 * cover.js
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
	var pluginName = 'cover',
		defaults = {
		};

	// The actual plugin constructor
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

			var $avatar = $(this.element);
			$(window).resize(function() {
				plugin.resize();
			});

			// trigger initial resize
			plugin.resize();
		},

		resize: function() {
			var plugin = this;
			var $avatar = $(this.element);

			// don't do anything if img tag
			if($avatar.find('img').length) {
				return;
			}

			// get frame dimensions
			var wF = $avatar.parent().width();
			var hF = $avatar.parent().height();

			// calculate
			var ratio =  wF / $avatar.data('wc');
			var bgSize = ratio * $avatar.data('wi');
			var bgLeft = ratio * $avatar.data('xc') * -1;
			var bgTop  = ratio * $avatar.data('yc') * -1;

			$avatar.css('background-image', 'url(' + $avatar.data('image') + ')');
			$avatar.css('background-size', bgSize + 'px');
			$avatar.css('background-position', bgLeft + 'px ' + bgTop + 'px');
		},

		setImage: function(image, cropX, cropY, cropW, cropH) {
			var plugin = this;
			var $avatar = $(this.element);

			// extract values
			var w = null;
			var h = null;
			if(typeof(image) == 'object' && image.url != undefined) {
				w = image.w || null;
				h = image.h || null;
				image = image.url;
			}
			$avatar.data('image', image);

			// if no or incomplete cropping information, just render an img tag
			if(!(!!cropX && !!cropY && !!cropW && !!cropH)) {
				plugin.showImgTag(image);
				return;
			}

			// set crop info
			plugin.setCrop(cropX, cropY, cropW, cropH);
			$avatar.find('img').remove();

			// calculate real image dimensions, this is async so we need to wrap after functionality in a callback
			if(w == null || h == null) {
				plugin.calculateDimensions(image, function(width, height) {
					$avatar.data('wi', width);
					$avatar.data('hi', height);
					plugin.resize();
				});
			} else {
				$avatar.data('wi', w);
				$avatar.data('hi', h);
				plugin.resize();
			}
		},

		calculateDimensions: function(image, callback) {
			var $image = $('<img style="pointer-events:none;opacity:0;position:absolute;top:0;max-width:none;max-height:none;" />').attr('src', image);
			$image.load(function() {
				callback($(this).width(), $(this).height()); // call callback function
				$(this).remove();
			}).appendTo('body');
		},

		setCrop: function(cropX, cropY, cropW, cropH) {
			var plugin = this;
			var $avatar = $(this.element);
			$avatar.data('xc', cropX);
			$avatar.data('yc', cropY);
			$avatar.data('wc', cropW);
			$avatar.data('hc', cropH);

			plugin.resize();
		},

		showImgTag: function(image) {
			var plugin = this;
			var $avatar = $(this.element);

			// clear styles, add img tag
			$avatar.attr('css', '');
			$avatar.append($('<img class="avatar" />').attr('src', image));
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
/**
 * dialog.js
 */
/*

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'dialog',
		defaults = {
		};

	// The actual plugin constructor
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
			this.setup();

			var plugin = this;

			$(this.element).on('click', '.dialog__trigger', function(e) {
				e.preventDefault();
				var $trigger = $(this);
				var id = $trigger.data('dialog-content');
				var $content = $('#' + id);
				$('.dialog').trigger('show', [$content, id, $trigger]);
			});

			// https://github.com/valtido/jQuery-mutate
			/*var $content = $('<div class="dialog__overlay__content"></div>');
			$(window).mutate('width height', function(el, info) {
				plugin.setOffset($content);
			});*/
		},

		setup: function() {
			var plugin = this;

			// create elements

			var $dialog = $('.dialog');
			var $content = $dialog.find('.dialog__overlay__content');
			if($dialog.length == 0) {
				$dialog = $('<div class="dialog"></div>');
				$content = $('<div class="dialog__overlay__content"></div>');
				var $underlay = $('<div class="dialog__underlay"></div>');
				var $overlay = $('<div class="dialog__overlay"></div>');
				$dialog.append($underlay);
				$dialog.append($overlay);
				$overlay.append($content);
				$(this.element).append($dialog);
			}

			// events
			$dialog.unbind();

			$dialog.on('click', '.dialog__close', function(e) {
				e.preventDefault();
				$('.dialog').trigger('hide');
			});

			$dialog.bind('show', function(e, content, id, trigger, callback) {
				var $dialog = $(this);
				$dialog.find('.dialog__overlay__content').empty().append(content.children());
				var offset = 0;
				if($('body').scrollTop() != 0) {
					offset = Math.abs($('body').scrollTop());
				} else if($('#wrapper').scrollTop() != 0) {
					offset = Math.abs($('#wrapper').scrollTop());
				} else if($('#wrapper').offset().top != 0) {
					offset = Math.abs($('#wrapper').offset().top);
				}
				$dialog.data('offset-top', offset);
				$dialog.addClass('--animating');
				$dialog.addClass('--active');
				$('html').addClass('--dialog-active');
				$dialog.data('dialog-original-content', content);
				plugin.setOffset($('.dialog__overlay__content'));
				var t = setTimeout(function() {
					//plugin.setOffset($('.dialog__overlay__content'));
					$dialog.removeClass('--animating');
				}, 200);

				// touch tabbing that is in the dialog
				$dialog.find('.tabbing').trigger('change');
				$dialog.trigger('show-complete', [content, id, trigger]);
				if(callback) {
					callback.call($dialog.get(0), content, id, trigger);
				}
			});

			$dialog.bind('hide', function(e) {
				var $dialog = $(this);
				$dialog.addClass('--animating');
				$dialog.removeClass('--active');
				$('html').removeClass('--dialog-active');
				if($dialog.data('offset-top') && !isNaN($dialog.data('offset-top'))) {
					$('html, body').animate({ scrollTop: $dialog.data('offset-top') }, 100);
				}
				var t = setTimeout(function() {
					$($dialog.data('dialog-original-content')).append($dialog.find('.dialog__overlay__content').children());
					$dialog.removeClass('--animating');
				}, 200);
			});

			// https://github.com/valtido/jQuery-mutate
			$content.mutate('width height', function(el, info) {
				plugin.setOffset($content);
			});
			$(window).mutate('width height', function(el, info) {
				plugin.setOffset($content);
			});
		},

		setOffset: function(el) {
			var content = $(el).height();
			var overlay = $(el).parents('.dialog__overlay').height();
			var viewport = $(window).height();
			var width = $(window).width();
			var offsetTop = (width > 600) ? ((viewport - Math.min(content, overlay)) / 2) : 0;
			$('.dialog__overlay').css('top', offsetTop);
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function(options) {
		return this.each(function() {
			if(!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName, new Plugin(this, options));
			} else {
				$.data(this, 'plugin_' + pluginName).setup();
			}
		});
	};

})(jQuery, window, document);
/**
 * dropdown.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for dropdown objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'dropdown',
			defaults = {
			};

	// The actual plugin constructor
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

			// init post comments
			//$(this.element).find('.comment-write form').commentwrite();

			$(this.element).not('.--frozen').each(function() {
				var $dropdown = $(this);
				var target = $(this).data('dropdown-target');
				if(target) {
					var $triggers = $(':not(.dropdown) > .dropdown__trigger').filter(function() {
						return $(this).attr('href') == '#' + target;
					});
					$triggers.on('click', function(e) {
						e.preventDefault();
						//e.stopPropagation();
						$dropdown.trigger('toggle');
					});
				}
			});

			$(this.element).not('.--frozen').find('.dropdown__trigger').on('click', function(e) {
				e.preventDefault();
				//e.stopPropagation();
				$(this).parents('.dropdown').trigger('toggle');
			});

			$(this.element).not('.--frozen').bind('toggle', function(e) {
				var $dropdown = $(this);
				$dropdown.toggleClass('--active');

				var target = $(this).data('dropdown-target');
				if(target && !$dropdown.hasClass('--pulldown')) {
					var $trigger = $(':not(.dropdown) > .dropdown__trigger').filter(function() {
						return $(this).attr('href') == '#' + target;
					});
					$trigger.toggleClass('--active', $dropdown.hasClass('--active'));
					if($dropdown.hasClass('--active')) {
						$dropdown.trigger('opened');
					} else {
						$dropdown.trigger('closed');
					}
				}

				// close others in same group
				var group = $dropdown.data('dropdown-group');
				if(group != undefined && group != null) {
					$('.dropdown').filter(function() {
						return $(this).data('dropdown-group') == group && this !== $dropdown.get(0);
					}).trigger('close');
				}

				// opening for pulldowns
				if($dropdown.hasClass('--pulldown')) {
					var height = $dropdown.find('.dropdown__content > *').outerHeight();
					if($dropdown.hasClass('--active')) {
						$dropdown.find('.dropdown__content').height(height);
						var t = setTimeout(function() {
							$dropdown.addClass('--open');
						}, 350);
					} else {
						$dropdown.find('.dropdown__content').height(height);
						$dropdown.removeClass('--open');
						$dropdown.find('.dropdown__content').height(0);
					}
				}
			});

			$(this.element).not('.--frozen').bind('close', function() {
				var $dropdown = $(this);
				var isOpen = $dropdown.hasClass('--active');
				$dropdown.removeClass('--active');

				var target = $(this).data('dropdown-target');
				if(target && !$dropdown.hasClass('--pulldown')) {
					var $trigger = $(':not(.dropdown) > .dropdown__trigger').filter(function() {
						return $(this).attr('href') == '#' + target;
					});
					$trigger.removeClass('--active');
				}

				if($dropdown.hasClass('--pulldown')) {
					$dropdown.find('.dropdown__content').height(0);
				}

				if(isOpen) {
					$dropdown.trigger('closed');
				}
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

/**
 * dropdown.js
 */
jQuery(function($) {

	// hit test for close
	$('body').bind('hit-test', function(e, elements) {
		$('.dropdown:not(.--frozen):not(.--pulldown)').each(function() {
			var $dropdown = $(this);
			var target = $dropdown.data('dropdown-target');
			var $trigger = [];
			if(target) {
				$trigger = $('.dropdown__trigger').filter(function() {
					return $(this).attr('href') == '#' + target && $.inArray(this, elements) !== -1;
				});
			}
			// make note hit test should exempt dialogs
			var inDialog = ($.inArray($('.dialog').get(0), elements) != -1);
			if($.inArray($dropdown.get(0), elements) == -1 && $trigger.length == 0 && !inDialog) {
				$dropdown.trigger('close');
			}
		});
	});
});
/**
 * ellipsis.js
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
	var pluginName = 'ellipsis',
		defaults = {
		};

	// The actual plugin constructor
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

			var $ellipsis = $(this.element);
			$(window).resize(function() {
				plugin.resize($ellipsis);
			});

			// trigger initial resize
			plugin.resize($ellipsis);
		},

		resize: function($ellipsis) {
			$ellipsis.css('width', 0);
			var t = setTimeout(function() {
				var width = $ellipsis.parent().outerWidth();
				$ellipsis.css('width', width);
			}, 10);
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
$.event.special.tap = {
	setup: function() {
		var self = this,
			$self = $(self);

		// Bind touch start
		$self.on('touchstart', function(startEvent) {
			// Save the target element of the start event
			var target = startEvent.target;

			// When a touch starts, bind a touch end handler exactly once,
			$self.one('touchend', function(endEvent) {
				// When the touch end event fires, check if the target of the
				// touch end is the same as the target of the start, and if
				// so, fire a click.
				if (target == endEvent.target) {
					$.event.simulate('tap', self, endEvent);
				}
			});
		});
	}
};

$('body').on('tap', 'a', function() {
	//$('#layout-header').toggleClass('hidden');
});
/**
 * flash.js
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
	var pluginName = 'flash',
		defaults = {
		};

	// The actual plugin constructor
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

			// trigger initial show
			plugin.show();
		},

		show: function() {
			var plugin = this;
			var $element = $(this.element);
			$element.find('.flash').each(function(index) {
				var $item = $(this);
				var lag = index * 400;
				var t = setTimeout(function() {
					$item.addClass('--active');
				}, lag);
			});
		},

		refresh: function(flashes) {
			var plugin = this;
			var $element = $(this.element);
			$element.empty();
			for(var i in flashes) {
				var flash = flashes[i];
				var $item = $('<div class="flash">');
				$item.attr('id', 'flash_' + i);
				$item.addClass('--' + flash.type);
				$item.text(flash.message);
				$element.append($item);
			}
			plugin.show();
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
/**
 * global.js
 */
jQuery(function($) {
	// Hit detection for clicks
	$('body').click(function(e) {
		var $heirarchy = $(e.target).parents();
		$(this).trigger('hit-test', [$heirarchy]);
	});
});
/**
 * increment.js
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
	var pluginName = 'increment',
		defaults = {
		};

	// The actual plugin constructor
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
			var element = this.element;

			// set element status
			if($(element).css('position') == 'static') {
				$(element).css('position', 'relative');
			}

			// handle clicks
			$(element).on('increment', function(e) {
				e.stopPropagation(); // make sure parent items don't receive this event

				var $incr = $(this);
				var points = $incr.data('points');
				var incremented = typeof($incr.data('incremented')) != 'undefined' ? $incr.data('incremented') : ':not(:visible)';

				// check if first incremented
				if($incr.is(incremented) || typeof(points) == 'undefined') {
					return;
				}

				// insert points
				var $points = $('<span class="incrementer__points">+' + points + '</span>');
				$incr.append($points);

				// animate and remove
				var t = setTimeout(function() {
					$points.addClass('--incrementing');
				}, 35);
				var t2 = setTimeout(function() {
					$points.remove();
				}, 1000);
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
/**
 * like.js
 */
jQuery(function($) {
	$('body').on('click', '.like', function(e) {
		e.preventDefault();
		$(this).trigger('increment');
		$(this).toggleClass('--active');
	});
});
/**
 * liveform.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for live-link objects
 *
 * Dependencies:
 * _parse.js
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'livelink',
		defaults = {
		};

	// The actual plugin constructor
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
			var loadCallback = function(html, status, xhr) {
				var $info = $(html).find('#mainContainer').parents('#wrapper');
				var url = $info.data('url');
				var options = xhr.livelink !== undefined ? xhr.livelink : {};

				// close any dialogs/splashes
				$('.dialog').trigger('hide');
				$('.splash').trigger('hide');

				// update history
				// support checking xhr.history to see if we should not alter history
				if(window.location.protocol !== 'file:' && window.location.hostname !== '') {
					if(xhr.history !== false) {
						history.pushState({href: url}, null, url);
					} else {
						history.replaceState({href: url}, null, url);
					}
				}

				// load layout
				// header
				var $lh = $('#layout-header').css('background-color', $(html).find('#layout-header').css('background-color'));
				var $h = $(html).find('#headerContainer');
				$lh.toggleClass('--hidden', $info.is('[no-header=true]'));
				if($h.length) {
					$('#headerContainer').html($h.html());
				}

				// flash
				var $fl = $(html).find('#flash-container');
				$fl.toggleClass('--hidden', $info.is('[no-flash=true]'));
				if($fl.length) {
					$('#flash-container').replaceWith($fl);
				}

				// footer
				var $lf = $('#layout-footer');
				var $f = $(html).find('#footerContainer');
				$lf.toggleClass('--hidden', $info.is('[no-footer=true]'));
				if($f.length) {
					$('#footerContainer').css('background-color', $f.css('background-color')).html($f.html());
				}

				// content
				$('#layout-content').attr('class', $(html).find('#layout-content').attr('class'));

				// load container contents into main container
				$('#mainContainer').html($(html).find('#mainContainer').html());

				// load body class attribute
				var bodyClass = $info.data('page-template');
				if(typeof(options.bodyClass) !== 'undefined') {
					bodyClass = options.bodyClass;
				}

				// get member role
				var memberRole = $info.data('member-role');

				bodyClass = 'layout ' + bodyClass + ' ' + memberRole;
				$('body').attr('class', bodyClass);

				// run page init code
				for(var page in app) {
					var pageCallback = app[page];
					pageCallback($);
				}

				// scroll page
				if(xhr.history !== false) {
					$('html, body').animate({ scrollTop: 0 }, 100);
				}
			};

			// link click
			$(this.element).on('click', 'a', function(e) {
				if(!($(this).data('internal') !== undefined && $(this).data('internal') == true)) {
					return;
				}

				e.preventDefault();
				var href = $(this).attr('href');

				// check dirty forms
				var $dirty = $('form').filter('.dirty');
				if($dirty.size() > 0) {
					var r = confirm($dirty.data('areyousure-message'));
					if(r !== true) {
						return;
					}
				}

				// close menu
				$('#layout-header .menu').trigger('close');

				// link options
				var options = {};
				options['bodyClass'] = $(this).data('body-class') !== undefined ? $(this).data('body-class') : undefined;

				console.log('link click: ' + href);
				var xhr = $.ajax(href, {
					type: 'GET',
					success: loadCallback
				});
				xhr.livelink = options;
			});

			// form submission
			$(this.element).on('submit', 'form', function(e) {
				if(!($(this).data('internal') !== undefined && $(this).data('internal') == true)) {
					return;
				}

				e.preventDefault();
				var action = $(this).attr('action');
				var method = $(this).attr('method');

				// get params
				var params = $(this).parse(); // call dependent plugin function
				if($(this).data('form-submit-action') !== undefined) {
					params['action'] = $(this).data('form-submit-action');
				}

				console.log('form submit: ' + action);
				var xhr = $.ajax(action, {
					type: method,
					data: params,
					success: loadCallback
				});
				xhr.livelink = {};
			});

			// redirect via window.location
			$(this.element).on('redirect', function(e, url) {
				console.log('redirect: ' + url);
				var xhr = $.ajax(url, {
					type: 'GET',
					success: loadCallback
				});
				xhr.livelink = {};
			});

			// history back
			$(this.element).on('back', function(e, url) {
				console.log('back: ' + url);
				console.log('-----');
				var xhr = $.ajax(url, {
					type: 'GET',
					success: loadCallback
				});
				xhr.history = false;
				xhr.livelink = {};
			});

			// external links (phonegap only)
			// http://stackoverflow.com/questions/17887348/phonegap-open-link-in-browser
			if(is_phonegap) {
				$(this.element).on('click', 'a[target=_blank]', function(e) {
					e.preventDefault();
					var href = $(this).attr('href');
					window.open(href, '_system', 'location=yes');
					//return false;
				});
			}
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
/**
 * loadmore.js
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
	var pluginName = 'loadmore',
			defaults = {
				injectMethod: 'append', // prepend, append
				dataType: 'html',
				callback: $.noop,
				listType: 'linear' // linear, grouped
			};

	// The actual plugin constructor
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
			var element = this.element;
			var collection = this.options.collection;
			var dataType = this.options.dataType;
			var injectMethod = this.options.injectMethod;
			var callback = this.options.callback;
			var listType = this.options.listType;

			// trigger
			var trigger = this.options.trigger;
			var $trigger = $(trigger);

			// handle clicks
			$trigger.on('click', function (e, $trigger) {
				e.preventDefault();

				// if already loading, do nothing
				if($(this).hasClass('--loading')) {
					return;
				}
				$(this).addClass('--loading');

				// load results
				var href = $(this).attr('href');
				$.ajax(href, {
					dataType: dataType,
					success: function(data) {
						var $loaded = $(data).find(collection).children();
						var $current = $(element).children();

						// insert each element - linear
						if(listType == 'linear') {
							$loaded.each(function() {
								var $new = $(this);
								var id = $new.data('id');

								// find old element
								var $match = $current.filter(function() {
									return $(this).data('id') == id;
								});

								// remove old element
								$match.remove();

								// insert
								if(injectMethod == 'append') {
									$(element).append($new);
								} else {
									$(element).append($new);
								}
							});
						} else if(listType == 'grouped') {
							$loaded.each(function() {
								var $new = $(this);

								// insert
								if(injectMethod == 'append') {
									$(element).append($new);
								} else {
									$(element).append($new);
								}
							});

							// get final list and make groups unique
							// collect all keys
							var $groups = $(element).children();
							var groups = {};
							var prev = '';
							var i = -1;
							var j = -1;
							$groups.each(function() {
								j++;
								var curr = $(this).data('key');
								if(prev != curr) {
									prev = curr;
									i++;
									groups[i] = [];
								} else {
									groups[i].push(j);
								}
							});
							for(var key in groups) {
								var sub = groups[key];
								for(var key2 in sub) {
									var s = sub[key2];
									var $target = $($groups.get(key));
									var $source = $($groups.get(s));
									$source.find('.itemlist__item').appendTo($target.find('ul'));
									$source.remove();
								}
							}
						}

						// trigger
						var $loadedTrigger = $(data).find(trigger);
						var loadMore = $loadedTrigger.hasClass('load-more');

						// set pagination
						plugin.setPagination(loadMore);

						// trigger callback
						callback($loaded, $(element));
					},
					complete: function() {
						// trigger
						var $trigger = $(trigger);
						$trigger.removeClass('--loading');
					}
				});
			});

			// refresh
			$(this.element).on('refresh', function() {
				plugin.setPagination();
			});
		},

		setPagination: function(loadMore) {
			loadMore = (loadMore !== false);

			// trigger
			var trigger = this.options.trigger;
			var $trigger = $(trigger);

			// collection
			var element = this.element;

			var perpage = $trigger.data('perpage');
			var total = $(element).is('.loadmore__container') ?
				$(element).find('> .itemlist__item').size() :
				$(element).find('.loadmore__container > .itemlist__item').size();
			var nextPage = Math.ceil((total + 1) / perpage);

			// set href
			var href = $trigger.attr('href');
			href = href.replace(/page=[0-9]+/, 'page=' + nextPage);
			$trigger.attr('href', href);

			// load more
			if(loadMore === false) {
				$trigger.css('display', 'none');
			} else if(loadMore === true) {
				$trigger.css('display', 'inline-block');
			}
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
/**
 * menu.js
 */
jQuery(function($) {
	$('#layout-header').on('tap click', '.logo', function(e) {
		e.preventDefault();
		$('#layout-header .menu').toggleClass('--active'); // fix selector
	});

	$('#layout-header').on('tap click', '.menu .menu__underlay, .menu .logo', function(e) {
		e.preventDefault();
		$('#layout-header .menu').trigger('close'); // fix selector
	});

	$('#layout-header').on('close', '.menu', function() {
		$(this).removeClass('--active');
	});
});
/**
 * orientate.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Function for orientating images
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		orientate: function(orientation) {
			var img = this;
			var angle = 0;
			var flip = false;
			switch(orientation) {
				case 2:
					flip = true;
					break;
				case 3:
					angle = 180;
					break;
				case 4:
					angle = 180;
					flip = true;
					break;
				case 5:
					angle = 270;
					flip = true;
					break;
				case 6:
					angle = 270;
					break;
				case 7:
					angle = 90;
					flip = true;
					break;
				case 8:
					angle = 90;
					break;
			}
			$(img).rotateLeft(angle);
		}
	});

})(jQuery, window, document);
/**
 * preview.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * {
 *   'parent': selector | object | data-update,
 *   'map': {
 *     'field_name': function(source, target, value){ //DOM manipulation } | function(source, target, value){ $(target).text(value) }
 *   }
 * }
 *
 * data-update="something"
 *
 * 1. find element with id "something"
 * 2. find element with name "something"
 * 3. find element with class "something"
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'preview',
		defaults = {
			parent: null, // will resolve to called object's "update" value by default
			map: {}, // override fefault syncing behaviour per input field
			defaultFn: function(source, target, value, e){ $(target).text(value); } // called when input field is modified
		};

	// The actual plugin constructor
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

			// monitor input elements
			$(this.element).on('change keyup keydown file-select', ':input', function(e, fileUrl) {
				var $source = $(this);
				var name    = $source.attr('name');
				var value   = $source.val();
				var $target = plugin.resolveTargets($source);

				// file consolidating
				var files = e.target.files;
				if(files !== undefined && files !== null) {
					for(var i = 0, f; f = files[i]; i++) {
						var URLObject = window.URL || window.webkitURL;
						var objectUrl = URLObject.createObjectURL(f);
						value = objectUrl;
					}
				}
				if(typeof(fileUrl) !== 'undefined') {
					value = fileUrl;
				}

				// Get map key
				var map = plugin.resolveMap(name);
				var vars = plugin.resolveVars([$source, $target, value, e], name, map);

				var $fn = plugin.options.map[map] || plugin.options.defaultFn || null;
				if($target != null && $fn != null) {
					$fn.apply(this, vars);
				}
			});

			// disable interactivity in preview pane
			var $parent = this.resolveParent();
			$parent.on('click', '*', function(e) {
				e.preventDefault();
			});
		},

		resolveMap: function(sourceName) {
			var plugin = this;
			var currSource = sourceName;
			if(sourceName in plugin.options.map) {
				return sourceName;
			}

			// Get elements for source and all mappings
			var sourceMatches = currSource.match(/(\[[0-9a-zA-Z\-\_]+\])/g); // ["[0]", "[file]", "[test]", "[test33]"]

			var mapMatches = [];
			var j = 0;
			for(var key in plugin.options.map) {
				var matches = key.match(/\[((\{\$)?[0-9a-zA-Z\-\_]+(\})?\])/g); // ["[{$index}]", "[file]", "[{$test}]", "[{$test2}]"]
				if(matches != null && sourceMatches != null && matches.length === sourceMatches.length) {
					mapMatches[j] = {score: ',', matches: matches, name: key};
				}
				j++;
			}

			// score matches
			// - exact matches: 2, partial matches: 1, no matches: 0 (will be disqualified)
			// - elements closer to root will take precedence
			for(var i in sourceMatches) {
				var sourceMatch = sourceMatches[i];
				for(var j in mapMatches) {
					var mapMatch = mapMatches[j].matches[i];

					if(mapMatches[j].score == ',0') {
						continue;
					}
					// if exact match
					else if(mapMatch === sourceMatch) {
						mapMatches[j].score += 2;
					}
					// if partial match (such as a variable)
					else if(/\{\$[0-9a-zA-Z\-\_]+\}/.test(mapMatch) === true) {
						mapMatches[j].score += 1;
					}
					// if no match
					else {
						mapMatches[j].score = ',0';
					}
				}
			}

			// sort and get first result
			if(mapMatches.length) {
				mapMatches.sort(function(a, b) {
					return a.score.replace(',', '') < b.score.replace(',', '');
				});
				var match = mapMatches.shift();
				if(match.score != ',0') {
					return match.name;
				}
			}
			return sourceName;
		},

		resolveVars: function(vars, sourceName, mapName) {
			var sourceVars = sourceName.match(/\[([0-9a-zA-Z\-\_]+\])/g);
			var mapVars    = mapName.match(/\[((\{\$)?[0-9a-zA-Z\-\_]+(\})?\])/g);
			for(var i in sourceVars) {
				if(sourceVars[i] !== mapVars[i]) {
					vars.push(sourceVars[i].replace('[', '').replace(']', ''));
				}
			}
			return vars;
		},

		resolveParent: function() {
			var selector = this.options.parent || $(this.element).data('update');
			if(selector) {
				var parent = $('#' + selector).get(0) || $('[name=' + selector + ']').get(0) || $('.' + selector).get(0) || null;
				return $(parent);
			}
			return null;
		},

		resolveTargets: function($source) {
			var $parent = this.resolveParent();
			var selector = $source.data('update');
			if($parent != null && selector && selector.length > 0) {
				var target = $parent.find('#' + selector);
				if(!target.length) {
					target = $parent.find('[name=' + selector + ']');
				}
				if(!target.length) {
					target = $parent.find('.' + selector);
				}
				if(!target.length) {
					target = null;
				}
				return target;
			}
			return null;
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
/**
 * slider.js
 */
jQuery(function($) {
	/**
	 * options
	 * - orientation: horizontal|vertical (default: horizontal)
	 * - min: integer (default: 0)
	 * - max: integer (default: 100)
	 * - increment: integer (default: 1)
	 * - spacing: integer (default: 0)
	 * keyboard events
	 * mouse events
	 * touch events
	 * custom events (triggers)
	 */

	var slider_parent        = '.slider';
	var slider_track         = '.slider__track';
	var slider_progress      = '.slider__track__progress';
	var slider_handle        = '.slider__handle';
	var slider_value         = '.slider__value';
	var slider_handle_active = '.--last-active';

	//$(slider_parent).on('mousedown', slider_handle, function(e) {
	$('body').on('mousedown', slider_parent + ' ' + slider_handle, function(e) {
		$(this).data('anchor', e.clientX);
		$(this).data('startpos', $(this).position().left); // try remove this dependency
		$(slider_handle).removeClass('--last-active');
		$(this).addClass('--last-active');
		$('body').addClass('dragging');
	});

	$('body').bind('mouseup', function() {
		$('body').removeClass('dragging');
	});

	// move handle by mouse
	$('body').bind('mousemove', function(e) {
		var $handle = $(slider_handle + ':active').not('.--readonly');
		if($handle.length == 0) {
			return;
		}

		// get movement vector
		// negative value = moving left, positive value = moving right
		var delta = e.clientX - $handle.data('anchor');
		moveByDelta($handle, delta);
	});

	// move handle by keyboard
	$('body').bind('keydown', function(e) {
		var $handle = $(slider_handle + slider_handle_active).not('.--readonly');
		if($handle.length == 0) {
			return;
		}

		// get key
		var direction = 0;
		var magnitude = e.shiftKey ? 3 : 1;
		var LEFT_KEY  = 37;
		var RIGHT_KEY = 39;
		switch(e.which) {
			case LEFT_KEY:
				direction = -1;
				break;
			case RIGHT_KEY:
				direction = 1;
				break;
		}
		moveByIncrements($handle, direction * magnitude);
	});

	// move handle by track click
	//$(slider_parent).on('mousedown', slider_track, function(e) {
	$('body').on('mousedown', slider_parent + ' ' + slider_track, function(e) {
		var $track = $(this);

		// only handle explicit track clicks
		var $handle = $track.parents(slider_parent).find(slider_handle).not('.--readonly');
		if(e.target != $track.get(0) || $handle.length == 0) {
			return;
		}

		// get closest handle to click area
		var pos, deltaMin, deltaMax, delta, direction;
		pos = e.clientX;
		deltaMin = $handle.filter('.--min').length ? pos - $handle.filter('.--min').offset().left : null;
		deltaMax = $handle.filter('.--max').length ? pos - $handle.filter('.--max').offset().left : null;

		if(deltaMax === null || deltaMin !== null && Math.abs(deltaMin) < Math.abs(deltaMax)) {
			$handle = $handle.filter('.--min');
			delta = deltaMin;
		} else {
			$handle = $handle.filter('.--max');
			delta = deltaMax;
		}
		//direction = delta < 0 ? -1 : 1;

		// centre handle onto mouse
		var handleOffset = $handle.width() / 2;

		//moveByIncrements($handle, direction);
		moveByOffset($handle, delta - handleOffset);
	});

	$('body').bind('mousedown', function(e) {
		$('body').data('mousedown-target', e.target);
	});

	$('body').bind('mouseup', function(e) {
		var originalTarget = $('body').data('mousedown-target');
		if(!$(originalTarget).is(slider_handle)) {
			$(slider_handle).removeClass('--last-active');
		}
	});

	function getSnapSpacing($slider) {
		// spanning increment
		var minVal = $slider.data('min') || 0;
		var maxVal = $slider.data('max') || 100;
		var incVal = $slider.data('increment') || 1; // advisable to choose a number that evenly divides minVal and maxVal
		var increment = 100 / ((maxVal - minVal) / incVal);
		return increment;
	}

	function moveByIncrements($handle, units) {
		var $slider = $handle.parents(slider_parent);
		var increment = getSnapSpacing($slider);

		var width = $handle.parents(slider_parent).width();
		var movePercent = increment * units;
		var startPercent = $handle.position().left * 100 / width;
		console.log('start percent: ' + startPercent + ', move percent: ' + movePercent);

		var nextPosition = startPercent + movePercent;
		console.log('next percent: ' + nextPosition);

		moveByPercent($handle, nextPosition);
	}

	function moveByDelta($handle, delta) {
		// translate vector into percentage of total width of slider
		var width = $handle.parents(slider_parent).width();
		var movePercent = delta * 100 / width;
		var startPercent = $handle.data('startpos') * 100 / width;
		//console.log('start percent: ' + startPercent + ', move percent: ' + movePercent);

		var nextPosition = startPercent + movePercent;
		//console.log('next percent: ' + nextPosition);

		moveByPercent($handle, nextPosition);
	}

	function moveByOffset($handle, offset) {
		var width = $handle.parents(slider_parent).width();
		var movePercent = offset * 100 / width;
		var startPercent = $handle.position().left * 100 / width;
		//console.log('start percent: ' + startPercent + ', move percent: ' + movePercent);

		var nextPosition = startPercent + movePercent;
		//console.log('next percent: ' + nextPosition);

		moveByPercent($handle, nextPosition);
	}

	function moveByValue($handle, value) {
		var $slider = $handle.parents(slider_parent);
		var minVal = $slider.data('min') || 0;
		var maxVal = $slider.data('max') || 100;
		var nextPosition = (value - minVal) * 100 / (maxVal - minVal);
		moveByPercent($handle, nextPosition);
	}

	function moveByPercent($handle, percent) {
		var nextPosition = percent;
		var $slider = $handle.parents(slider_parent);
		var $progress = $handle.prev(slider_progress);
		var increment = getSnapSpacing($slider);
		var width = $slider.width();

		// snap to next increment
		var mod = nextPosition % increment;
		var snapping = mod >= (increment / 2) ? increment - mod : -mod;
		nextPosition += snapping;
		//console.log('next percent (snapped): ' + nextPosition);

		// bounds checking
		var $min = $handle.not('.--min') ? $handle.siblings(slider_handle + '.--min') : null;
		var $max = $handle.not('.--max') ? $handle.siblings(slider_handle + '.--max') : null;
		var min = $min.length ? $min.position().left * 100 / width : 0;
		var max = $max.length ? $max.position().left * 100 / width : 100;

		// bounds check
		// - can't go lower than 0 or min
		// - can't go higher than 100 or max
		nextPosition = Math.max(nextPosition, min);
		nextPosition = Math.min(nextPosition, max);
		//console.log('next percent (bounds check): ' + nextPosition);

		// spacing
		var spacing = $slider.data('spacing') || 0;
		if(spacing > 0) {
			nextPosition = $min.length ? Math.max(nextPosition, min + increment * spacing) : nextPosition;
			nextPosition = $max.length ? Math.min(nextPosition, max - increment * spacing) : nextPosition;
			//console.log('next percent (spacing): ' + nextPosition);
		}

		var currPosition = $handle.position().left;

		// update ui
		$handle.css('left', nextPosition + '%');
		$progress.css('width', nextPosition + '%');

		var $input = $slider.find('input[name*=\\[' + ($handle.is('.--min') ? 'min' : 'max') + '\\]]');
		if($input.length == 0) {
			$input = $slider.find('input');
		}
		var $value = $input.prev(slider_value);

		// update values
		var minVal = $slider.data('min') || 0;
		var maxVal = $slider.data('max') || 100;
		var constant = minVal;
		var linear   = (maxVal - minVal) * nextPosition / 100;
		var value = linear + constant;
		//console.log('value: ' + value);

		// round to 2 decimal places (see below)
		value = roundToTwo(value);

		// trigger custom event handlers
		// returning false will stop the value getting saved
		var returnValue = $slider.triggerHandler('before-change', [$handle, $input, $value, value]);
		if(returnValue !== undefined) {
			value = returnValue;
		}
		if(value !== false) {
			$input.val(value);
			$value.text(value);

			$slider.trigger('change', [$handle, $input, $value, value]);

			// center value
			var margin = $value.outerWidth() / 2 * -1 + 2;
			$value.css('left', nextPosition + '%');
			$value.css('margin-left', margin);
		}
	}

	// init function
	sliderInit = function($sliders) {
		$sliders.find(slider_handle).each(function() {
			var $handle = $(this);
			var $input = $handle.parents(slider_parent).find('input[name*=\\[' + ($handle.is('.--min') ? 'min' : 'max') + '\\]]');
			if($input.length == 0) {
				$input = $handle.parents(slider_parent).find('input');
			}
			moveByValue($handle, $input.val());
		});

		// https://github.com/valtido/jQuery-mutate
		$sliders.find(slider_value).mutate('show', function(el, info) {
			var $value = $(el);
			var $handle = $value.next(slider_handle);

			// center value
			var margin = $value.outerWidth() / 2 * -1 + 2;
			$value.css('left', $handle.css('left') + '%');
			$value.css('margin-left', margin);
		});
	}
});
var sliderInit;

function roundToTwo(num) {
	return +(Math.round(num + "e+2")  + "e-2");
}

/**
 * splash.js
 */
jQuery(function($) {
	var $splash = $('<div class="splash"></div>');
	$('body').append($splash);

	var st = null;
	$splash.on('show', function() {
		$splash.addClass('--inactive');
		var t = setTimeout(function() {
			$splash.addClass('--active');
		}, 50);
		st = setInterval(function() {
			$splash.addClass('--large');
			var t2 = setTimeout(function() {
				$splash.removeClass('--large');
			}, 250);
		}, 3000);
	});

	$splash.on('hide', function() {
		$splash.removeClass('--active --large');
		var t = setTimeout(function() {
			$splash.removeClass('--inactive');
		}, 200);
		clearInterval(st);
		st = null;
	});
});
/**
 * tabbing.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * (no options required)
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'tabbing',
			defaults = {
			};

	// The actual plugin constructor
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

			// ignore
			if($(this.element).data('tabbing-ignore') != undefined) {
				return;
			}

			// init
			$(this.element).find('.tabbing__nav').each(function() {
				var $indicator = $('<div class="tabbing__nav__indicator"></div>');
				$(this).append($indicator);

				var $tabbing = $(this).parents('.tabbing');
				var target = $tabbing.data('tabbing-target');
				var $triggers = $(':not(.tabbing) > .tabbing__nav__tab').filter(function() {
					return $(this).attr('href') == '#' + target;
				});
				$triggers.on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$tabbing.trigger('before-change', [$(this)]);
				});

				// arbitrary triggers
				var $extTriggers = $('.tabbing__trigger');
				$extTriggers.on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					// find the matching trigger tab
					var $extTrigger = $(this);
					var $trigger = $('.tabbing__nav__tab, .tabbing__trigger').filter(function() {
						return $(this).attr('href') == $extTrigger.attr('href');
					});;
					$tabbing.trigger('before-change', [$trigger]);
				});
			});

			// handle clicks
			$(this.element).on('click', '.tabbing__nav__tab', function(e, $trigger) {
				e.preventDefault();
				$(this).parents('.tabbing').trigger('before-change', [$(this)]);
			});

			// event entry
			$(this.element).on('before-change', function(e, $trigger) {
				e.preventDefault();

				// update history
				if(history.pushState) {
					if($trigger.parents('.tabbing').data('scheme')) {
						var tab = $trigger.attr('href').replace('#tab_', '');
						history.pushState({href: $trigger.attr('href')}, null, $trigger.parents('.tabbing').data('scheme').replace('{tab}', tab));
					}
				}

				$(this).trigger('change', [$trigger]);
			});

			// monitor tab changes
			$(this.element).on('change', function(e, $trigger) {
				if(!$trigger) {
					$trigger = $(this).find('.tabbing__nav .--active');
				}
				var $indicator = $trigger.parents('.tabbing__nav').find('.tabbing__nav__indicator');
				var init = !$indicator.is(':visible');
				if($trigger.is('.--disabled') && !init) {
					return;
				}

				$trigger.parents('.tabbing__nav').find('.tabbing__nav__tab').removeClass('--active');
				$trigger.addClass('--active');

				// update content
				$(this).trigger('update-content');

				plugin.resizeTabs($(this));
			});

			// update content
			$(this.element).on('update-content', function(e, $trigger) {
				if(!$trigger) {
					$trigger = $(this).find('.tabbing__nav__tab.--active');
				}

				var target = $trigger.attr('href');
				var $panel = $(target);
				$panel.siblings('.tabbing__panel').removeClass('--active');
				$panel.addClass('--active');
			});

			// handle window resizes
			var $tabbing = $(this.element);
			$(window).resize(function() {
				$tabbing.each(function() {
					plugin.resizeTabs($(this));
				});
			});

			// init state
			$(this.element).trigger('change');
		},

		resizeTabs: function($tabbing) {
			var $trigger = $tabbing.find('.tabbing__nav .--active');
			if($trigger.length) {
				var $indicator = $trigger.parents('.tabbing__nav').find('.tabbing__nav__indicator').addClass('--active');
				var width = $trigger.parents('li').outerWidth();
				var offset = $trigger.parents('li').position().left;
				$indicator.css('width', width);
				$indicator.css('left', offset);
			}
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
/**
 * autosuggest.js
 */
jQuery(function($) {
	// Auto suggest field
	var autosuggest_parent       = '.field';
	var autosuggest              = '.field__autosuggest';
	var autosuggest_value        = '.field__autosuggest__value';
	var autosuggest_results      = '.field__autosuggest__results';
	var autosuggest_results_item = '.field__autosuggest__results__item';

	$(autosuggest_parent + ' ' + autosuggest).each(function() {
		var $autosuggest = $(this);
		$autosuggest.after('<div class="field__autosuggest__results"></div>');
	});

	$(autosuggest_parent).on('keyup keydown', autosuggest, function(e) {
		var $autosuggest = $(this);
		var value = $autosuggest.val();

		if(value.length >= 3) {
			$autosuggest.trigger('update', [QUESTIONS, value]);
		} else {
			$autosuggest.parents(autosuggest_parent).trigger('close');
		}
	});

	$(autosuggest_parent).on('click', autosuggest, function(e) {
		var $field = $(this).parents(autosuggest_parent);
		$field.trigger('open');
	});

	$(autosuggest_parent).on('update', autosuggest, function(e, data, keyword) {
		var $autosuggest = $(this);
		var $results = $autosuggest.next(autosuggest_results);
		$results.addClass('--active');

		// populate rows, using question as an example
		var $list = $('<div class="itemlist"><ul></ul></div>');
		$results.empty();
		$results.append($list);
		for(var i in data) {
			var row = data[i];
			//var text = row.question + '|' + row.answer;

			var regex = new RegExp('(' + keyword + ')', 'gi');
			var text = row.question.replace(regex, '<strong>$1</strong>');

			var $row = $('<li class="field__autosuggest__results__item"></li>')
					.html('<div class="question"><span class="question__search">' + text + '</span>' +
						'<span class="question__id">#' + htmlEntities(row.id) + '</span></div>');
			$row.appendTo($list.find('ul'));
		}
	});

	$(autosuggest_parent).bind('open', function() {
		$(this).find(autosuggest_results).addClass('--active');
	});

	$(autosuggest_parent).bind('close', function() {
		$(this).find(autosuggest_results).removeClass('--active');
	});

	// hit test for close
	$('body').bind('hit-test', function(e, elements) {
		$('.field:has(.field__autosuggest)').each(function() {
			var $field = $(this);
			if($.inArray($field.get(0), elements) == -1) {
				$field.trigger('close');
			}
		});
	});
});

function htmlEntities(str) {
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
/**
 * collection.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for collection objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'collection',
		defaults = {
		};

	// The actual plugin constructor
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

			// Fancy collection editor
			var collection_parent  = '.field';
			var collection         = '.field__collection';
			var collection_item    = '.field__collection__item';
			var collection_remove  = '.field__collection__item__remove';
			var collection_undo    = '.field__collection__item__undo';
			var collection_action  = '.field__collection__item__action';
			var collection_add     = '.field__collection__add';
			var collection_input   = 'input';
			var collection_deleted = '.field__collection__item__deleted';

			$(this.element).each(function() {
				var $collection = $(this);
				var name = $collection.data('field-name');
				$collection.find(collection_item).each(function() {
					var $item = $(this);
					$('<button type="button" class="field__collection__item__remove fa fa-times" title="Remove"></button>').appendTo($item);
					$('<button type="button" class="field__collection__item__undo fa fa-undo" title="Undo"></button>').appendTo($item);
					if($item.find(collection_input).val().length == 0 || $item.hasClass('--new')) {
						$item.data('clean', true);
					}
				});

				// add default collection's fresh callback
				$collection.data('fresh-callback', function($item) {
					var fresh = true;
					$item.find(':input').each(function() {
						var $input = $(this);
						var checkbable = $input.is('[type=checkbox], [type=radio]');
						if((checkbable && $input.prop('checked') == true) || ($input.val() != '' && !checkbable)) {
							fresh = false;
						}
					});
					return fresh;
				});
			});

			// enter key on collection inputs
			$(this.element).on('keyup keydown', collection_input, function(e) {

				// is last input field?
				var $curr = $(this);
				var $last = $curr.parents(collection_item).find(collection_input + ':last');

				// add
				if(e.which == 13) {
					e.preventDefault();
					// if not last, do nothing
					if($curr.get(0) == $last.get(0)) {
						$curr.parents(collection).trigger('additem');
					}
				}
				// remove
				else {
					$curr.parents(collection).trigger('reduce');
				}
			});

			$(this.element).on('click', collection_remove, function(e) {
				e.preventDefault();
				var $item = $(this).parents(collection_item);

				// manage deleted resource
				if($item.data('id') != undefined) {
					var deleted = $item.data('id');
					var $deleted = $item.parents(collection).siblings(collection_deleted);
					if($deleted.val().length == 0) {
						$deleted.val(',');
					}
					var value = $deleted.val();
					$deleted.val(value + deleted + ',');
				}

				// update view
				if($item.data('clean') != undefined && $item.data('clean') === true) {
					$item.remove();
				} else {
					$item.addClass('--removed');
				}
			});

			$(this.element).on('click', collection_undo, function(e) {
				e.preventDefault();
				var $item = $(this).parents(collection_item);

				// manage deleted resource
				if($item.data('id') != undefined) {
					var deleted = $item.data('id');
					var $deleted = $item.parents(collection).siblings(collection_deleted);
					var value = $deleted.val();
					$deleted.val(value.replace(',' + deleted + ',', ','));
				}

				// update view
				$item.removeClass('--removed');
			});

			$(this.element).on('click', collection_action, function(e) {
				e.preventDefault();
				var href = $(this).attr('href');
				var internal = !!$(this).data('internal');

				if(internal) {
					$('body').trigger('redirect', [href]);
				} else {
					window.location = href;
				}
			});

			$(this.element).next(collection_add).on('click', function(e) {
				e.preventDefault();
				var $collection = $(this).prev(collection);
				$collection.trigger('additem');
			});

			// actions

			$(this.element).on('additem', function(e) {
				var $collection = $(this);

				// get limit
				var limit = $(this).data('collection-limit') || false;
				var $items = $collection.find(collection_item + ':not(.--template)');

				// check limit against collection
				if(limit !== false && $items.size() >= limit) {
					return false;
				}

				// detect fresh
				var $fresh = $items.filter(function() {
					var fn = $collection.data('fresh-callback');
					if(fn == undefined || fn == null) {
						return true;
					}
					return fn($(this));
				});
				if($fresh.length) {
					// select it
					$fresh.first().trigger('_select');
					return;
				}

				var $new = $collection.find(collection_item + '.--template').clone();
				$new.removeClass('--template');
				$new.addClass('--new');
				$new.data('clean', true);

				// change template to new
				$new.find(':input').each(function() {
					if($(this).attr('name') != undefined) {
						$(this).attr('name', $(this).attr('name').replace(/\[template\]/, '[new]'));
					}
				});

				$new.appendTo($collection);

				// reindex
				$collection.trigger('reindex');

				// select
				$new.trigger('_select');
			});

			$(this.element).on('_select', collection_item, function(e) {
				var $item = $(this);
				$item.find(collection_input + ':visible:first').focus();
			});

			$(this.element).on('reduce', function(e) {
				// detect fresh
				var $collection = $(this);
				var $fresh = $collection.find(collection_item + ':not(.--template)').filter(function() {
					var fn = $collection.data('fresh-callback');
					if(fn == undefined || fn == null) {
						return true;
					}
					return fn($(this));
				});
				if($fresh.size() > 1) {
					$fresh.last().remove();
					$collection.trigger('reduce');
				}
			});

			$(this.element).on('reindex', function(e) {
				var $collection = $(this);
				//var $items = $collection.find(collection_item + ':not(.--template)');
				var $items = $collection.find(collection_item + '.--new');

				$items.each(function(index) {
					// generate id
					var id = index;
					$(this).find(':input').each(function() {
						if($(this).attr('name') != undefined) {
							$(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/, '[' + id + ']'));
						}
					});
				});
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
/**
 * formchanged.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for detecting changes in forms
 */
/*;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'formchanged',
		defaults = {
			leaveMessage: 'Are you sure you want to leave this page?'
		};

	// The actual plugin constructor
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
			var $form = $(this.element);
			$form.data('snapshot', plugin.snapshot());

			// detect submit
			$form.on('submit', function() {
				$form.data('submitting', true);
			});

			// leave page
			window.addEventListener("beforeunload", function(e) {
				console.log(e);
				var snapshot = plugin.snapshot();
				if($form.data('snapshot') != snapshot && ($form.data('submitting') == undefined || $form.data('submitting') !== true)) {
					var txt;
					var r = confirm("Press a button!");
					if(r == true) {
						txt = "You pressed OK!";
					} else {
						txt = "You pressed Cancel!";
					}
				}
			});
		},

		snapshot: function() {
			var plugin = this;
			var serialised = '';
			$(this.element).find(':input').each(function() {
				serialised += '&' + $(this).attr('name') + '=' + $(this).val();
			});
			return serialised;
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

})(jQuery, window, document);*/
/**
 * liveform.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for live-form objects
 *
 * Dependencies:
 * _parse.js
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'liveform',
		defaults = {
			data:  {}, // the data source for querying
			query: function(form_params, data_source) {}, // the query function
			view:  function(results) {} // the view rendering function
		};

	// The actual plugin constructor
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

			// form submission
			$(this.element).on('submit', function(e) {
				// form submissions will be disabled.
				e.preventDefault();
			});

			// input change
			$(this.element).on('change keyup', ':input', function() {
				plugin.update();
			});
		},

		update: function() {
			var plugin = this;

			// get params
			var params = $(this.element).parse(); // call dependent plugin function

			// get result set
			var result = plugin.options.query(params, plugin.options.data);

			// update view
			plugin.options.view(result);
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
/**
 * parse.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Function for parsing form objects into JSON
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		parse: function() {

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
			};

			// method
			return (function(form) {
				var config = {};
				$(form).serializeArray().map(function(item) {
					var props = item.name.replace(/\]\[/g, '~').replace(/\[/g, '~').replace(/\]/g, '').split('~');
					config = buildProp(config, props, item.value);
				});
				return config;
			})(this);
		}
	});

})(jQuery, window, document);
/**
 * realval.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Function getting the real value of an input field, supports html5 contenteditor attribute
 */
;(function($, window, document, undefined) {

	// Extend
	$.fn.extend({
		// used in actual form submission
		realVal: function() {
			var $input = $(this);
			var isHtml = !$input.is(':input');
			var val = $input.html();

			// method
			if(isHtml) {
				// 1. new lines instead of div/p rows (wrapped)
				// 2. new lines instead of div/p rows (unwrapped)
				// 3. remove div/p end tags
				// 4. trim off first new line
				var html = $input.html().replace(/<\/div><div>/g, "\n").replace(/<\/p><p>/g, "\n").replace('<div>', "\n").replace('<p>', "\n").replace('</div>', '').replace('</p>', '').replace("\n", '');
				return html;
			}
			return isHtml ? $input.text() : $input.val();
		},
		// used in counting number of characters
		realText: function() {
			var $input = $(this);
			var isHtml = !$input.is(':input');
			var val = $input.html();

			// method
			if(isHtml) {
				// 1. new lines between div/p rows (wrapped)
				// 2. new lines between div/p rows (unwrapped)
				// 3. trim off first new line
				var html = $input.html().replace(/<\/div><div>/g, "</div>\n<div>").replace(/<\/p><p>/g, "</p>\n<p>").replace('<div>', "\n<div>").replace('<p>', "\n<p>").replace("\n", '');
				var $html = $('<div></div>').html(html);
				return $html.text();
			}
			return isHtml ? $input.text() : $input.val();
		},
		// used in calculating textarea height
		realHtml: function() {
			var $input = $(this);
			var isHtml = !$input.is(':input');

			// method
			return isHtml ? $input.html() : $input.val();
		}
	});

})(jQuery, window, document);
/**
 * textarea.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for textarea objects
 */
function getSelectRange() {
	if(window.getSelection) {
		sel = window.getSelection();
		if(sel.rangeCount) {
			range = sel.getRangeAt(0);
		}
	} else if(document.selection && document.selection.createRange) {
		range = document.selection.createRange();
	}
	return range;
}
function getCaretInfo() {
	var sel;
	if(window.getSelection) {
		sel = window.getSelection();
	} else if(document.selection) {
		sel = document.selection;
	}

	// build
	var node = sel.anchorNode;
	var parent = node ? $(node).parent().get(0) : null;
	return {
		startPos: sel.anchorOffset,
		node: node,
		parent: parent,
	};
}
function getCaretPosition(editableDiv) {
	var caretPos = 0, sel, range;
	if(window.getSelection) {
		sel = window.getSelection();
		if(sel.rangeCount) {
			range = sel.getRangeAt(0);
			if (range.commonAncestorContainer.parentNode == editableDiv) {
				caretPos = range.endOffset;
			}
		}
	} else if(document.selection && document.selection.createRange) {
		range = document.selection.createRange();
		if(range.parentElement() == editableDiv) {
			var tempEl = document.createElement("span");
			editableDiv.insertBefore(tempEl, editableDiv.firstChild);
			var tempRange = range.duplicate();
			tempRange.moveToElementText(tempEl);
			tempRange.setEndPoint("EndToEnd", range);
			caretPos = tempRange.text.length;
		}
	}
	return caretPos;
}
function getCaretObject(editableDiv)
{
	var caretObj, sel, range;
	if(window.getSelection) {
		sel = window.getSelection();
		if(sel.rangeCount) {
			range = sel.getRangeAt(0);
			caretObj = range.commonAncestorContainer.parentNode;
		}
	} else if(document.selection && document.selection.createRange) {
		range = document.selection.createRange();
		caretObj = range.parentElement();
	}
	return caretObj;
}
function focusTextNode(node)
{
	var sel = window.getSelection();
	var range = sel.getRangeAt(0);
	range.collapse(false);
	range = range.cloneRange();
	range.selectNodeContents(node.get(0));
	range.collapse(false);
	sel.removeAllRanges();
	sel.addRange(range);
}
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'textarea',
		defaults = {
		};

	// The actual plugin constructor
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

			// medium
			if($(this.element).is('[contenteditable=true]')) {
				new Medium({
					element: this.element,
					mode: Medium.richMode, // partialMode
					placeholder: $(this.element).data('placeholder') || $(this.element).attr('placeholder') || '',
					tags: {
						'break': 'br',
						'horizontalRule': '',
						'paragraph': 'p',
						'outerLevel': [], // 'pre', 'blockquote', 'figure'
						'innerLevel': ['span', 'img'] // 'a', 'b', 'u', 'i', 'img', 'strong'
					},
					attributes: {
						remove: ['style'] // 'style', 'class'
					}
				});
			}

			// Textarea Auto Resize
			var $hiddenDiv = $('.hiddendiv').first();
			if(!$hiddenDiv.length) {
				$hiddenDiv = $('<div class="hiddendiv common"></div>');
				$('body').append($hiddenDiv);
			}

			$(this.element).each(function() {
				var $textarea = $(this);
				//if($textarea.val().length) {
				//if($textarea.realHtml().length) {
					plugin.resize($textarea);
				//}
			});

			$(this.element).on('keyup keydown refresh paste pastecomplete', function(e) {
				if($(this).is('[contenteditable=true]') && (e.type == 'keydown' || e.type == 'paste')) {
					$(this).trigger('caret');
					plugin.cleanupObjects(e, $(this));
				}
				plugin.resize($(this));
			});

			// https://github.com/valtido/jQuery-mutate
			$(this.element).mutate('show width', function(el, info) {
				plugin.resize($(el));
			});

			// object click / toggle
			if($(this.element).is('[contenteditable=true]')) {
				// clicking on text are is 1 trigger for caret change
				$(this.element).bind('click', function(e) {
					$(this).trigger('caret');
				});
				$(this.element).bind('caret', function(e) {
					$(this).find('.o_').removeClass('a_ l_ _l');
					var $obj = $(getCaretObject(this)); // caret position
					if($obj.length > 0 && $obj.is('.o_')) {
						var pos = getCaretPosition($obj.get(0));
						if(pos == $obj.text().length) {// caret end of object
							$obj.addClass('l_');
						}
						else if(pos == 0) {// caret start of object
							$obj.addClass('_l');
						}
						$obj.addClass('a_');
					}
				});
			}
		},

		cleanupObjects: function(e, $textarea) {
			var plugin = this;
			var obj = plugin.getObjects($textarea);
			var moveKeys = [35,36,37,38,39,40]; // 35 = end, 36 = home, 37 = left, 38 = up, 30 = right, 40 = down
			var deleteKeys = [8,46]; // 8 = backspace, 46 = delete
			for(var i in obj) {
				var o = obj[i];
				// 8 = backspace, 46 = delete
				if(o.is('.a_') && deleteKeys.indexOf(e.which) != -1) {
					var $tn = null;
					var $c = $(o);

					// find nearest text node before delete target
					var $pC = $(o).parents('[contenteditable] > *');
					var $pB = $pC.prev();
					var $tn = null;
					var c = $pC.contents();
					var index = $(c).index(o);
					for(var j = 0; j < index; j++) {
						if($(c).get(j).nodeType == 3) {
							$tn = $($(c).get(j));
						}
					}
					if($tn == null) {
						$tn = $pB;
					}

					// now delete node
					// will the parent element be empty once deleted?
					if($(o).parent().contents().length == 1) {
						$(o).parent().remove();
					} else {
						o.remove();
					}

					// focus on text node
					if($tn != null) {
						focusTextNode($tn);
					}
					e.preventDefault();

				} else if(o.hasClass('a_')) {
					// 32 = space, start of object
					if(e.which == 32 && o.hasClass('_l')) {
						// create text node before or add space to end of previous text node
						e.preventDefault();
						var $tn = $('<span>&nbsp;</span>');
						var txt = $($tn.get(0).childNodes[0]);
						o.before(txt);
						focusTextNode(txt);
						$textarea.find('.o_').removeClass('a_ l_ _l');
					}
					// 13 = enter, start of object
					else if(e.which == 13 && o.hasClass('_l')) {
						// create text node after or add space to start of next text node
						e.preventDefault();
						var $tn = $('<div><br /></div>');
						//var p = o.parents('[contenteditable] *').last(); // get top most node that is child of contenteditable
						//p.before($tn);
						o.before($tn);
						focusTextNode($tn);
						$textarea.find('.o_').removeClass('a_ l_ _l');
					}
					// 32 = space, end of object
					else if(e.which == 32 && o.hasClass('l_')) {
						// create text node after or add space to start of next text node
						e.preventDefault();
						var $tn = $('<span>&nbsp;</span>');
						var txt = $($tn.get(0).childNodes[0]);
						o.after(txt);
						focusTextNode(txt);
						$textarea.find('.o_').removeClass('a_ l_ _l');
					}
					// 13 = enter, end of object
					else if(e.which == 13 && o.hasClass('l_')) {
						// create text node after or add space to start of next text node
						e.preventDefault();
						var $tn = $('<div><br /></div>');
						//var p = o.parents('[contenteditable] *').last(); // get top most node that is child of contenteditable
						//p.after($tn);
						o.after($tn);
						focusTextNode($tn);
						$textarea.find('.o_').removeClass('a_ l_ _l');
					}
					// 35 = end, 36 = home, 37 = left, 38 = up, 30 = right, 40 = down, 8 = backspace, 46 = delete
					else if(moveKeys.indexOf(e.which) == -1 && deleteKeys.indexOf(e.which) == -1) {
						e.preventDefault(); // prevent action
					}
				}
			}

			var t = setTimeout(function() {
				// remove partial objects
				obj = plugin.getObjects($textarea);
				for(i in obj) {
					var o = obj[i];
					if(o.data('text') !== o.text()) {
						o.remove();
					}
				}
				// strip out style tags
				$textarea.find('*[style]:not(.o_)').each(function() {
					$(this).get(0).removeAttribute('style');
				});
			}, 0);
		},

		getObjects: function($textarea) {
			var obj = [];
			$textarea.find('.o_').each(function() {
				obj.push($(this));
			});
			return obj;
		},

		resize: function($textarea) {
			var plugin = this;

			if(!$(this.element).is('[contenteditable=true]')) {
				var $hiddenDiv = $('.hiddendiv').first();

				$hiddenDiv.html($textarea.realHtml());
				var content = $hiddenDiv.html().replace(/\n/g, '<br>&nbsp;');
				$hiddenDiv.css('line-height', $textarea.css('line-height'));
				$hiddenDiv.css('font-size', $textarea.css('font-size'));
				$hiddenDiv.css('padding-top', $textarea.css('padding-top'));
				$hiddenDiv.css('padding-bottom', $textarea.css('padding-bottom'));
				$hiddenDiv.html(content);
				$hiddenDiv.css('width', $textarea.width());
				$textarea.css('height', $hiddenDiv.outerHeight());
			}
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
//# sourceMappingURL=components.js.map
