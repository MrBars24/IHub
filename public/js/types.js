/**
 * brand-greylist.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for brand greylist
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'brandgreylist',
		defaults = {
			brands: []
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

			// activate
			$(this.element).on('click', function(e) {
				e.preventDefault();

				// submit form
				plugin.showBrands();
			});
		},

		showBrands: function() {
			var plugin = this;
			var options = this.options;
			var element = this.element;

			// get dialog object, we'll open this later
			var $dialog = $('#' + $(this.element).data('dialog-content'));
			if($dialog.data('loaded')) {
				plugin.initDialog($dialog);
				return;
			}

			if(options.brands == undefined) {
				return;
			}

			// mark dialog as loaded
			// this should throttle back future api requests
			$dialog.data('loaded', true);

			// populate select boards dialog
			var $list = $dialog.find('#brand-greylist-selection-list');
			$list.find('li:not(.--template)').remove();
			for(var i in options.brands) {
				var $template = $list.find('li.--template').clone();
				var brand = options.brands[i];
				$template.removeClass('--template');
				$template.data('id', brand.id);
				//$template.find('.entity__icon').attr('src', brand.profile_picture_tiny);
				$template.find('.entity__icon').attr('src', brand.profile_picture_small);
				$template.find('label').text(brand.name);
				$list.append($template);
			}
			plugin.initDialog($dialog);
		},

		initDialog: function($dialog) {
			var $original = $(this.element);

			// show dialog
			$('.dialog').trigger('show', [$dialog]);

			if($dialog.data('initialised')) {
				return;
			}
			$dialog.data('initialised', true);

			// selecting boards
			$('.dialog').on('click', '.entity', function(e) {
				var val = $('.dialog').find('input[name=greylist]').val();
				var id = $(this).data('id');
				if($(this).hasClass('--active')) { // remove
					val = val.replace(',' + $(this).data('id') + ',', ',');
					if(val == ',') {
						val = '';
					}
				} else { // add
					if(val.indexOf(',' + id + ',') == -1) {
						val = (val.length == 0 ? ',' : val);
						val = val + id + ',';
					}
				}
				$('.dialog').find('input[name=greylist]').val(val);
				$(this).toggleClass('--active');
			});

			// selecting boards
			$('.dialog').on('click', '.confirm-selection', function(e) {
				// populate list
				var $brands = $('#brand-greylist-selection-list').find('li.\--active').clone();
				$brands.removeClass('--active');
				$('#brand-greylist-current-selection').find('li').remove();
				$('#brand-greylist-current-selection').append($brands);

				var $input = $('.dialog').find('input[name=greylist]').clone();
				$original.find('input[name=greylist]').remove();
				$original.append($input);
				$original.trigger('select');
			});

			// cancel
			$('.dialog').on('click', '.cancel-selection', function(e) {
				$original.find('input[name=greylist]').remove();
				$original.trigger('deselect');
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
 * notifications.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for notifications
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'notifications',
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
			var $dropdown = $(this.element);

			// dropdown
			$(this.element).dropdown();

			// mark as read
			$(this.element).on('click', '.dropdown__trigger', function(e) {
				if($dropdown.find('.account__counter__value').text() != '0') {
					// ajax it up
					$.ajax($dropdown.data('action'), {
						method: 'POST',
						data: { _token: $dropdown.data('token') },
						success: function(json) {
							var reset = $dropdown.find('.account__counter__value').data('reset-value');
							$dropdown.find('.itemlist .notification').removeClass('--unread');
							$dropdown.find('.account__counter__value').text(reset);
						}
					});
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
 * comment-write.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for comment-write objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'commentwrite',
		defaults = {
			injectTarget: function(element) {
				var $ele = $(element);
				return $($ele.data('target'));
			},
			injectMethod: 'append',
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

			// auto suggest entities
			$(this.element).find('textarea').tagger();

			// post submission
			$(this.element).on('submit', function(e) {
				e.preventDefault();

				// submit form
				plugin.submitPost();
			});
		},

		submitPost: function() {
			var plugin = this;

			// validation
			// - requires text
			if($(this.element).find('[name=message]').val().trim().length == 0) {
				return;
			}

			// ajax it up
			$.ajax($(this.element).attr('action'), {
				method: $(this.element).attr('method') || 'POST',
				data: $(this.element).serialize(),
				success: function(html) {
					// clear form
					plugin.resetForm();

					// inject new post
					plugin.inject(html);
				}
			});
		},

		inject: function(html) {
			var plugin = this;

			var $target = plugin.getInjectTarget();
			var $item   = $(html).addClass('--injected');
			if(plugin.options.injectMethod == 'prepend') {
				$target.prepend($item);
			} else if(plugin.options.injectMethod == 'append') {
				$target.append($item);
			}

			// remove injected class
			var t = setTimeout(function() {
				$item.removeClass('--injected');
			}, 100);

			// init incrementer, making sure all child elements have it too
			$item.add($item.find('.incrementer')).increment();
			$item.trigger('increment');
		},

		resetForm: function() {
			// list of fields to reset
			var resetFields = [
				'message'
			];
			var $reset = $(this.element).find(':input').filter(function() {
				return $.inArray($(this).attr('name'), resetFields) !== -1;
			});
			$reset.each(function() {
				if($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
					$(this).prop('checked', false);
				} else {
					$(this).val('');
				}
			});
		},

		// helpers

		getInjectTarget: function() {
			var plugin = this;

			var target = plugin.options.injectTarget;
			var $target = null;

			if(typeof(target) == 'function') {
				$target = target(this.element);
			} else if(typeof(target) == 'string') {
				$target = $(plugin.options.injectTarget);
			}
			return $target;
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
 * gig-accept.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for comment-write objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'gigaccept',
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

			// post submission
			$(this.element).on('submit', function(e) {
				e.preventDefault();

				// submit form
				plugin.submitPost();
			});
		},

		submitPost: function() {
			var plugin = this;

			// ajax it up
			$.ajax($(this.element).attr('action'), {
				method: $(this.element).attr('method') || 'POST',
				data: $(this.element).serialize(),
				success: function(json) {
					// go to the engagement page
					//window.location = json.redirect;
					//$('.dialog').trigger('hide');
					$('body').trigger('redirect', json.redirect);
				}
			})
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
 * post-share.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for post-share objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'postauthoring',
		defaults = {
			injectTarget: function(element) {
				var $ele = $(element);
				return $($ele.data('target'));
			},
			ajax: 'default', // 'default' = ajax if file field not present
			injectMethod: 'prepend',
			success: $.noop,
			requireSocialMedia: false,
			autosave: false
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

			this.isTwitterInvalid = false;
			this.isYoutubeInvalid = false;
			this.newlyActivatedPlatformPost = '';

			plugin.renderSelectedAccount();
			plugin.pastedLink();
			plugin.fileUploadingProcess();
			plugin.gigAssetSelection();
			plugin.toggleContinueButton();
			plugin.toggleSubmitButton();
			plugin.updateAnchorDownState();

			// adjust flash container
			$('.flash-container').css('top', '56px');

			// toggle state based on current attachment (if any)
			var attachment = $('.postauthoring__attachment-wrapper').find('[name=attachment\\[type\\]]').val();
			plugin.togglePlatformState(attachment);

			// init character counter
			$(plugin.element).find('.character-counter').characterCounter();

			//  body click event - close account list when clicked outside element
			$(document).click(function(event) {
				if(!$(event.target).closest('.account-list').length) {
					$('.account-list').removeClass('--active');
				}
			});

			// Open each account list
			$(plugin.element).find('.account-list').find('i').click(function() {
				if($(this).closest('.account-list').hasClass('--active')) {
					$(this).closest('.account-list').removeClass('--active');
				} else {
					$(plugin.element).find('.account-list').removeClass('--active');
					$(this).closest('.account-list').addClass('--active');
				}
			});

			// toggle account selection
			$(plugin.element).find('.all-account-wrapper').find('.account').click(function() {
				var parentElement = $(this).closest('.all-account-wrapper'),
					accountPlatformElement = $(this).closest('.account-list'),
					dataPlatform = $(this).data('platform'),
					dataName = $(this).find('span.name').text();

				// get default values
				var defaults = $('[name=platform_defaults]').val() || '[]';
				var prepopulate = $('[name=prepopulate]').val() || '[]';
				defaults = JSON.parse(defaults);
				prepopulate = JSON.parse(prepopulate);

				// remove / create post element
				if($(this).hasClass('--active')) {
					$(this).removeClass('--active');
					plugin.removeRenderedPlatformPost(dataPlatform, dataName);
				} else {
					$(this).addClass('--active');
					plugin.setNewlyActivatedPlatformPost($(this));
					plugin.renderPlatformPostingItem(defaults, prepopulate);
				}

				if(parentElement.find('.account.--active').length > 0) {
					accountPlatformElement.addClass('--enabled');
				} else {
					accountPlatformElement.removeClass('--enabled');
				}

				plugin.renderSelectedAccount();
				plugin.toggleContinueButton();
				plugin.updateAnchorDownState();
			});

			// Remove attachment process
			$(plugin.element).on('click', '.postauthoring__attachment-wrapper__close-button', function() {
				plugin.removeAttachment();
				plugin.updateAnchorDownState();
			});

			// Copy textarea at first step data to variable
			$(plugin.element).find('textarea#textarea_message_write').on('change', function(e) {
				$('.post-panel-item__content__textbox').val($(this).val());
				plugin.updateAnchorDownState();
			});

			// Continue to platform posting if theres any linked account checked
			// Submit form if no linked account
			$('.postauthoring__post-footer__continue-button').click(function() {

				// stop any playing videos
				$('.postauthoring video').each(function() {
					this.pause();
				});
				$('.postauthoring iframe').each(function() {
					var src = $(this).attr('src');
					$(this).attr('src', src);
				});

				if(!$.isEmptyObject(plugin.getLinkedAccountSelectedInformation())) {
					var defaults = $('[name=platform_defaults]').val() || '[]';
					var prepopulate = $('[name=prepopulate]').val() || '[]';
					defaults = JSON.parse(defaults);
					prepopulate = JSON.parse(prepopulate);
					plugin.toggleSubmitButton();
					$(plugin.element).parents('.postauthoring__wrapper').removeClass('--step-one').addClass('--step-two');
					$(plugin.element).addClass('--selected');
				} else {
					//$(plugin.element).find('form').submit();
					//plugin.submitPost();
					plugin.startPostSubmission();
				}
			});

			// Go back to write post
			$('.postauthoring__post-heading__informational-block__back-button').click(function() {
				$(plugin.element).parents('.postauthoring__wrapper').removeClass('--step-two').addClass('--step-one');
				$(plugin.element).removeClass('--selected');
				plugin.toggleContinueButton();
			});

			// Open close panel at step2
			$('.postauthoring__post-panels').on('click', '.post-panel-item__heading', function() {
				$(this).parent('.post-panel-item').toggleClass('--active');
			});

			// Textarea at platform post on:keyup
			$('.postauthoring__post-panels').on('keyup', '.post-panel-item__content__textbox', function() {

				var postPanelItemElement = $(this).closest('.post-panel-item');

				if($(this).text() != $(plugin.getPostText()).text()) {
					postPanelItemElement.addClass('--changed');
				} else {
					postPanelItemElement.removeClass('--changed');
				}
				plugin.renderCharacterCounter(postPanelItemElement);
				plugin.toggleSubmitButton();
				plugin.updateAnchorDownState();
			});

			// Cancel changes button at platform text clicked
			$('.postauthoring__post-panels').on('click', '.post-panel-item__footer span', function() {
				plugin.revertPlatformText($(this).closest('.post-panel-item'));
			});

			// Submit form using post-button
			$('.postauthoring__post-footer__post-button').click(function() {
				// render tags and messages
				$('.postauthoring').find('[contenteditable]').trigger('render-tags');
				// add form fields
				$('.post-panel-item__content__textbox').each(function() {
					var name = $(this).attr('name');
					var value = $(this).realVal();
					var $i = $('<input type="hidden" name="' + name + '">');
					$i.val(value);
					$(this).after($i);
				});
				//$(plugin.element).find('form').submit();
				//plugin.submitPost();
				plugin.startPostSubmission();
			});

			// Remove css rules: html - overflow:hidden (this was found at _dialog.css)
			$('html').css({
				'overflow': 'initial'
			});

			// Youtube post options on:keyup
			$('.postauthoring__post-panels').on('change', '.post-panel-item__option-item :input', function() {
				plugin.invalidEmptyYoutubeOptions($(this));
				plugin.checkAllYoutubeIfInvalid();
				plugin.toggleSubmitButton();
				plugin.updateAnchorDownState();
			});

			// checklist - populate post
			$('#engagementPublishTagsList').on('click', 'a', function() {
				var $publish = $('#form_post_authoring').find('textarea');
				var value = $publish.val();
				var tag = $(this).data('content');
				$publish.val(value + ' ' + tag);

				// resize textarea
				$publish.trigger('refresh');
			});

			// prepopulation
			var prepopulate = $('[name=prepopulate]').val() || '[]';
			prepopulate = JSON.parse(prepopulate);
			for(var i in prepopulate) {
				var row = prepopulate[i];
				var accountId = row.linked_id;
				var subId = row.sub_id;
				var $account = $('.all-account-wrapper').find('.account').filter(function() {
					return $(this).data('linked-id') == accountId && ($(this).data('sub-id') == subId || (subId == '' && !$(this).data('sub-id')));
				});

				// select accounts
				$account.trigger('click');
			}

			// platform_defaults
			var platform_defaults = $('[name=platform_defaults]').val() || '[]';
			platform_defaults = JSON.parse(platform_defaults);
			for(var key in platform_defaults) {
				var val = platform_defaults[key];

			}

			// anchor down
			$('.postauthoring__anchordown').click(function(e) {
				e.preventDefault();
				var offset = $('.postauthoring__post-text-social-platform').offset().top;
				$('body').animate({ scrollTop: offset });
				$(this).removeClass('--active');
				$(this).addClass('expired');
			});
		},
		startPostSubmission: function() {
			var plugin = this;

			// check for dialog check list
			var $checklist = $('#engagement-checklist');
			if($checklist.length) {
				$('.dialog').trigger('show', [$checklist, 'engagement-checklist', null, function() {

					// extract tags and update dialog check list, if the check list even exists
					var $checklist = $('#checklist');
					if($checklist.length) {
						$checklist.off('change', 'input[type=checkbox]');
						$checklist.on('change', 'input[type=checkbox]', function() {
							// if all boxes are checked, then show publish button
							var $checklist = $(this).parents('#checklist');
							if($checklist.find('input[type=checkbox]:not(:checked)').length > 0 && $checklist.find('input[type=checkbox]').length > 0) {
								$('.submit-post').addClass('--disabled');
							} else {
								$('.submit-post').removeClass('--disabled');
							}
						});
						$checklist.find('input[type=checkbox]:first').triggerHandler('change');
					} else {
						$('.submit-post').removeClass('--disabled');
					}

					// submit action
					$('.dialog').find('.submit-post').one('click', function(e) {
						e.preventDefault();

						// disable button
						var $button = $(this);
						$button.addClass('--disabled');

						// additional fields
						var data = $('.dialog').find('form').parse();
						var fields = $('<div class="dialog_additional_fields"></div>');
						for(var name in data) {
							var val = data[name];
							var $field = $('<input type="hidden" name="' + name + '">').val(val);
							$(fields).append($field);
						}
						var $form = $(plugin.element).find('form');
						$form.find('.dialog_additional_fields').remove();
						$form.append($(fields));

						var t = setTimeout(function() {
							plugin.submitPost();
						}, 250);
						$('.dialog').trigger('hide');
					});
				}]);
			}
			// no checks needed, submit post
			else {
				plugin.submitPost();
			}
		},
		submitPost: function() {

			// ajax it up
			var $form = $(this.element).find('form');
			$.ajax($form.attr('action'), {
				method: $form.attr('method') || 'POST',
				data: $form.serialize(),
				success: function(json) {
					// redirect
					if(json.redirect_url) {
						$('body').trigger('redirect', [json.redirect_url]);
					}
				}
			});
		},
		renderSelectedAccount: function() {
			var plugin = this,
				selectedAccountTotal = $(plugin.element).find('.account-list').find('.account.--active').length,
				selectedAccountElement = $(plugin.element).find('.selected-account'),
				caption = selectedAccountElement.data('caption');

			caption = caption.replace('%d', selectedAccountTotal);
			if(selectedAccountTotal > 1) {
				caption = caption.replace('account', 'accounts')
			}
			
			selectedAccountElement.text(caption);

			if(selectedAccountTotal > 0) {
				$(plugin.element).find('.postauthoring__post-panels').addClass('--active');
				$(plugin.element).find('.postauthoring__post-text-social-platform').addClass('--active');
			} else {
				$(plugin.element).find('.postauthoring__post-panels').removeClass('--active');
				$(plugin.element).find('.postauthoring__post-text-social-platform').removeClass('--active');
			}
			plugin.updateAnchorDownState();
		},
		renderPlatformPostingItem: function(defaults, prepopulate) {
			var plugin = this;

			// remove and replace post panels
			var $panels = $('.postauthoring__post-panels');

			// youtube categories
			var categories = $('[name=youtube_categories]').val();
			if(categories.length) {
				categories = JSON.parse(categories);
			}

			// closure for finding records in prepopulated
			var findDataBy = function(data, id) {
				for(var i in data) {
					if(data[i].linked_id == id) {
						return data[i];
					}
				}
			};

			// create post panels
			//var index = 0;
			var index = $('.postauthoring__post-panels .post-panel-item:last').data('index');
			if(isNaN(index)) {
				index = 0;
			} else {
				index++;
			}
			$.each(plugin.getLinkedAccountSelectedInformation(), function(e, val) {
				var itemOption = "";
				var hasInvalidClass = '';
				var row = findDataBy(prepopulate, val.linkedId);
				//var message = (row && row.message) ? row.message : plugin.getPostText();
				var defaultVal = defaults[val.platform];

				// message hierarchy: 1) prepopulate (messages previously saved), 2) platform default, 3) text form step 1, 4) blank
				var message = '';
				message = plugin.getPostText() || message;
				message = defaultVal || message;

				// message_store and tags
				if(row && row.message_store) {
					message = row.message_store;
					if(row.tags) {
						for(var i = 0 in row.tags) {
							var tagRow = row.tags[i].raw || '';
							message = message.replace('[~' + i + ']', tagRow);
						}
					}
				}

				// youtube validation
				if(val.platform == 'youtube') {

					// populate drop down
					var catList = categories[val.linkedId];
					var options = '<option value=""></option>';
					for(var key in catList) {
						options += '<option value="' + htmlEntities(key) + '">' + htmlEntities(catList[key]) + '</option>';
					}

					hasInvalidClass = '--invalid';
					itemOption = '<div class="post-panel-item__option">'+
						'<div class="post-panel-item__option-title">OPTIONS</div>'+
						'<div class="post-panel-item__option-items">'+
							'<div class="post-panel-item__option-item --invalid">'+
								'<div class="option-item-label">Video Title*</div>'+
								'<div class="option-item-field youtube-options-title"><input type="text" name="platform_post['+ index +'][youtube_title]"></div>'+
							'</div>'+
							'<div class="post-panel-item__option-item --invalid">'+
								'<div class="option-item-label">Video Category*</div>'+
								'<div class="option-item-field youtube-options-category"><select name="platform_post['+ index +'][youtube_category]">' + options + '</select></div>'+
							'</div>'+
						'</div>'+
					'</div>';

					plugin.isYoutubeInvalid = true;
				}

				// twitter validation
				if(val.platform == 'twitter') {
					var charLengthValue = $('<div>').html(message).realText().length;
					//var charLengthValue = message.length;
					if(charLengthValue > 140) {
						hasInvalidClass = '--invalid';
						plugin.isTwitterInvalid = true;
					}
				}

				// params:
				// all (need?): name, platform, linked_id
				// +facebook: pages (eg: 1,2,3)
				// +pinterest: boards (eg: 1,2,3)
				// +youtube: title, category
				var $panel = $('<div class="post-panel-item '+ val.platform +' --active '+ hasInvalidClass +'" data-index="' + index + '" data-account-id="' + val.linkedId + '" data-platform="' + val.platform + '" data-account-name="'+val.name+'">'+
					'<div class="post-panel-item__heading">'+
						'<div class="post-panel-item__heading__icon '+ val.platform +'"><i class="'+ val.platformClass +'"></i></div>'+
						'<div class="post-panel-item__heading__name">'+
							'<span>'+ val.name +'</span>'+
						'</div>'+
						'<div class="post-panel-item__heading__character">'+ plugin.getPostTextCharacterLength() +'</div>'+
					'</div>'+
					'<div class="post-panel-item__content-wrapper">'+
						'<div class="tagger">'+
							'<div class="post-panel-item__content">'+
								'<div contenteditable="true" name="platform_post['+ index +'][message]" class="post-panel-item__content__textbox">'+ message +'</div>'+
							'</div>'+
						'</div>'+
						itemOption+
					'</div>'+
					'<div class="post-panel-item__footer"><span>Cancel changes</span></div>'+
					'<input name="platform_post_id['+ index +']" class="hidden" value="'+ val.linkedId +'">'+
					'<input name="platform_post['+ index +'][name]" class="hidden" value="'+ val.name +'">'+
					'<input name="platform_post['+ index +'][platform]" class="hidden" value="'+ val.platform +'">'+
					'<input name="platform_post['+ index +'][linked_id]" class="hidden" value="'+ val.linkedId +'">'+
					'<input name="platform_post['+ index +'][sub_id]" class="hidden" value="'+ val.subId +'">'+
					'<input name="platform_post['+ index +'][token]" class="hidden" value="'+ val.token +'">'+
				'</div>').appendTo('.postauthoring__post-panels');

				// trigger character counter handler and change detection
				$panel.find('[contenteditable]').trigger('keyup');

				// increment
				index++;
			});

			var taggerSupport = [
				'facebook', // only supports tagging of pages, and needs facebook review
				'twitter',
				'instagram'
			];

			// assign tagger url
			var taggerUrl = $(this.element).data('tagger-url');
			$('.post-panel-item').each(function() {
				var accountId = $(this).data('account-id');
				if(taggerSupport.indexOf($(this).data('platform')) != -1) {
					$(this).find('[contenteditable]').data('tagger-url', taggerUrl + '/' + accountId);
				}
			});

			// init text area
			var tagCallback = function(json) {
				var $wrapper = $(this).parents('.post-panel-item__content-wrapper .tagger');
				$wrapper.find('.post-panel-item__tags').remove();
				var $item = $(this).parents('.post-panel-item');
				if(json.items != undefined && json.items.length > 0) {
					// append tags
					var $items = $('<div class="post-panel-item__tags tagger__results --active">').appendTo($wrapper);
					// items
					for(var i in json.items) {
						var item = json.items[i];
						var html = '<div class="post-panel-item__tags__item suggestion">'+
							'<img class="post-panel-item__tags__item__avatar" src="' + htmlEntities(item.avatar) + '" alt="">'+
							'<span class="post-panel-item__tags__item__name">' + htmlEntities(item.display_name) + '</span>'+
							'<i class="fa ' + $item.data('platform') + ' ' + plugin.getPlatformIconClass($item.data('platform')) + '"></i>'+
						'</div>';
						$(html)
							.data('profile-id', item.native_id)
							.data('display-name', item.display_name)
							.data('platform', $item.data('platform'))
							.appendTo($items);
					}
				}
			};

			// text area
			$panels.find('textarea, [contenteditable]').textarea();

			// tagger
			$panels.find('textarea, [contenteditable]').socialtagger({
				callback: tagCallback
			});

			// blur event to generate input elements for each tag
			$panels.find('textarea, [contenteditable]').on('blur', function() {
				$(this).trigger('render-tags');
			});
			$panels.find('textarea, [contenteditable]').on('render-tags', function() {
				$(this).parents('.tagger').find('.tags-container').remove();
				var $c = $('<div class="tags-container">');
				var id = $(this).parents('.post-panel-item').data('account-id');
				var index = $(this).parents('.post-panel-item').data('index');
				var i = 0;
				$(this).find('.o_.ot_').each(function() {
					var html = $(this).clone().wrap('<p>').parent('p').html();
					$c.append('<input type="hidden" name="platform_post[' + index + '][tags][' + i + '][id]" value="' + $(this).data('id') + '">');
					$c.append('<input type="hidden" name="platform_post[' + index + '][tags][' + i + '][value]" value="' + $(this).data('value') + '">');
					$c.append('<input type="hidden" name="platform_post[' + index + '][tags][' + i + '][text]" value="' + '@' + $(this).data('text') + '">');
					$c.append('<input type="hidden" name="platform_post[' + index + '][tags][' + i + '][raw]" value="' + htmlEntities(html) + '">');
					i++;
				});
				$(this).after($c);
				$(this).trigger('render-real-value');
			});
			$panels.find('textarea, [contenteditable]').on('render-real-value', function() {
				var $c = $(this).siblings('.tags-container');
				$c.find('textarea').remove();

				var $value = $(this).clone();
				var index = $(this).parents('.post-panel-item').data('index');
				$value.find('.o_[data-value]').each(function(index) {
					var item = $value.find('.o_[data-value]').get(0);
					$(item).replaceWith('[~' + index + ']'); // just store tag reference
				});

				$c.append('<textarea tabindex="-1" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" name="platform_post[' + index + '][message_store]">' + htmlEntities($value.realHtml()) + '</textarea>');
				$(this).after($c);
			});
		},
		removeRenderedPlatformPost: function(platform, name) {
			var plugin = this;

			$(plugin.element).find('.post-panel-item.'+platform+'[data-account-name="'+name+'"]').remove();
		},
		attachmentPreview: function(html) {
			var plugin = this;
			var element = this.element;

			var attachmentWrapper = '<div class="postauthoring__attachment-wrapper">'+
				'<div class="postauthoring__attachment-wrapper__close-button"><i class="fa fa-times"></i></div>'+
				html+
				'</div>';
			// register content change
			$(element).trigger('content-change');

			// replace attachment
			$(element).find('.postauthoring__attachment-wrapper').remove();
			$(element).find('.postauthoring__post').children('.postauthoring__post-text-wrapper').after(attachmentWrapper);
			var attachmentTypeElement = $(element).find('.postauthoring__attachment-wrapper').find('.attachment');
			var attachmentType = "";

			if(attachmentTypeElement.hasClass('--video')) {
				attachmentType = 'video';
			} else if(attachmentTypeElement.hasClass('--image')) {
				attachmentType = 'image';
			} else if(attachmentTypeElement.hasClass('--link')) {
				attachmentType = 'link';
			} else if(attachmentTypeElement.hasClass('--youtube')) {
				attachmentType = 'youtube';
			} else if(attachmentTypeElement.hasClass('--vimeo')) {
				attachmentType = 'vimeo';
			}

			// toggle enable / disable for each platform
			plugin.togglePlatformState(attachmentType);
			plugin.updateAnchorDownState();
		},
		scrapeUrl: function(url) {
			var plugin = this;
			var element = this.element;

			// ajax to return url info
			if($(this.element).data('attachment-analyser').length) {
				$.ajax({
					url: $(element).data('attachment-analyser'),
					method: 'POST',
					data: {
						_token: $(element).find(':input[name=_token]').val(),
						a: url
					},
					success: function(html) {
						plugin.attachmentPreview(html);
					}
				});
			}
		},
		pastedLink: function() {
			var plugin = this;

			// paste link as attachment
			$(this.element).find('textarea').on('paste', function(e) {
				var element = $(this);

				// get clipboard data
				var data = null;
				if(e.originalEvent.clipboardData !== undefined) {
					try {
						data = e.originalEvent.clipboardData.getData('Text') || null;
					}
					catch(e) {
						data = null;
					}
				} else if(window.clipboardData !== undefined) {
					try {
						data = window.clipboardData.getData('Text') || null;
					}
					catch(e) {
						data = null;
					}
				}

				// get alternate value if clipboard is not accessible
				if(data == null) {
					var t = setTimeout(function() {
						var dataAlt = element.val();
						//alert('fallback method');
						element.trigger('pastecomplete', [dataAlt]);
					}, 220);
				} else {
					//alert('normal method');
					element.trigger('pastecomplete', [data]);
				}
			});

			// textarea paste complete
			$(this.element).find('textarea').on('pastecomplete', function(e, data) {
				// get value of current element if data is null
				if(data == null) {
					data = $(this).val();
				}

				// get first detected url in string and load attachment from it
				var urlRegex = '(?!mailto:)(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?';
				var url = new RegExp(urlRegex, 'ig');
				var matches = data.match(url);

				// create attachment
				if(matches) {
					plugin.scrapeUrl(matches[0]);
				}
			});
		},
		gigAssetSelection: function() {
			var plugin = this;

			var $gigAssets = $('#action-gig-assets');
			if($gigAssets.length == 0) {
				return;
			}

			// asset selection
			$gigAssets.find('.gig-assets').on('click', '.gig-asset-item', function() {
				$(this).siblings('.gig-asset-item').removeClass('--active');
				$(this).addClass('--active');
				$('.dialog').find('button').removeClass('--disabled');

				plugin.updateAnchorDownState();
			});

			// asset confirm
			$gigAssets.find('form').on('submit', function(e) {
				e.preventDefault();
				// get image
				var $media = $(this).find('.gig-asset-item.--active img');
				if($media.length == 0) {
					$media = $(this).find('.gig-asset-item.--active video source');
				}

				// add attachment preview
				var resource = $media.attr('src');
				var type = resource.split('.').pop();

				var videos = [
					'mp4',
					'avi',
					'mov',
					'ogg'
				];
				var images = [
					'png',
					'jpg',
					'gif',
					'jpeg'
				];

				// get file type
				if(videos.indexOf(type.toLowerCase()) !== -1) {
					type = 'video';
				} else if(images.indexOf(type.toLowerCase()) !== -1) {
					type = 'image';
				} else {
					type = 'file';
				}

				$(plugin.element).find('.postauthoring__attachment-wrapper').remove();
				//$(plugin.element).find('input[name=message_file]').remove();
				$(plugin.element).find('input[name=message_file]').attr('name', ''); // remove name attribute instead

				// create preview container
				var $template = null;
				var $preview = $('<div class="attachment-container">' +
					'<input name="attachment[type]" type="hidden">' +
					'<input name="attachment[resource]" type="hidden">' +
					'<input name="attachment[description]" type="hidden">' +
					'<input name="attachment[title]" type="hidden">' +
					'<input name="attachment[source]" type="hidden">' +
					'<input name="attachment[url]" type="hidden">' +
					'<input name="message_existing_file" type="hidden">' +
					'</div>');
				var $wrapper = $('<div class="postauthoring__attachment-wrapper">');
				// add close button and preview
				$wrapper.append('<div class="postauthoring__attachment-wrapper__close-button"><i class="fa fa-times"></i></div>');
				$wrapper.append($preview);
				//$(plugin.element).find('.postauthoring__post').append($wrapper);
				$(plugin.element).find('.postauthoring__post .postauthoring__post-text-wrapper').after($wrapper);

				switch(type) {
					case 'image':
						var $template = $('<div class="attachment --image">' +
							'<div class="attachment__content">' +
							'<img src="" alt="" />' +
							'</div>' +
							'</div>');

						// generate thumbnail
						$template.find('img').attr('src', resource);
						$preview.find('input[name=attachment\\[type\\]]').val('image');
						$preview.find('input[name=message_existing_file]').val(resource);
						break;
					case 'video':
						var $template = $('<video width="100%" controls>' +
							'<source src="" type="">' +
							'Your browser does not support HTML5 video.' +
							'</video>');

						// generate thumbnail
						var mimetype = '?';
						$template.find('source').attr('src', resource);
						//$template.find('source').attr('type', mimetype);
						$preview.find('input[name=attachment\\[type\\]]').val('video');
						$preview.find('input[name=message_existing_file]').val(resource);
						break;
				}
				$template.appendTo($preview);

				plugin.togglePlatformState(type);
				plugin.updateAnchorDownState();

				// close dialog
				$('.dialog').trigger('hide');
			});

			// select file dialog
			$(this.element).find('.action-select-file').on('click', function(e, selectOnOpen) {
				e.preventDefault();
				$('.dialog').trigger('show', [$gigAssets, 'action-gig-assets', null, function() {
					if(selectOnOpen) {
						var $items = $('.dialog').find('.gig-asset-item');
						$items.first().trigger('click');
						// only item? Then select and close
						if($items.size() == 1) {
							$('#form_gig_assets').trigger('submit');
						}
					}
				}]);
			});

			// open dialog if no asset selected and gig assets are available
			if($(this.element).find('.action-select-file').length > 0 && $('.attachment-container').length == 0) {
				var t = setTimeout(function() {
					$('.action-select-file').triggerHandler('click', [true]); // selectOnOpen
				}, 100);
			}
		},
		fileUploadingProcess: function() {
			var plugin = this;

			// post options attachments
			// http://www.html5rocks.com/en/tutorials/getusermedia/intro/
			$(this.element).find('.action-upload-file').on('click', function(e) {
				e.preventDefault();
				var target = $(this).data('target');
				var $file = $(target);

				// phonegap or HTML input?
				if(is_phonegap) {
					pg_getMedia($(this), $file, function(url, $trigger, $file) {
						pg_uploadMedia(url, $trigger, $file);
					});
				} else {
					$file.trigger('click');
				}
			});

			$(this.element).on('change', '.postauthoring__file-input', function(e) {
				var $file = $(this);
				if($file.attr('name') == '') {
					$file.attr('name', $file.data('field-name'));
				}

				var files = e.target.files;
				for(var i = 0, f; f = files[i]; i++) {
					$(this).addClass('--has-file');
				}

				// if live upload attributes found, upload
				if($file.data('fileupload-url')) {
					$file.trigger('upload', [files[0]]);
				}
			});

			$(this.element).on('upload', '.postauthoring__file-input', function(e, file) {
				var $file = $(this);
				name = file.name;
				size = file.size;
				type = file.type;

				var types = [
					'image/png',
					'image/jpg',
					'image/gif',
					'image/jpeg',
					'video/mp4',
					'video/avi',
					'video/mov',
					'video/quicktime',
					'video/ogg'
				];

				// file present?
				if(file.name.length < 1) {
				}
				// check size
				else if(file.size > 40 * 1024 * 1024) {
					alert("File is too big");
				}
				// check file type
				else if(types.indexOf(file.type) === -1) {
					alert('File format "' + file.type + '" not supported');
				}
				// ok to upload
				else {
					var formData = new FormData();
					formData.append('file', file);
					formData.append('_token', $file.parents('form').find('input[name=_token]').val());

					// insert thumbnail preview
					$file.trigger('placehold');

					// disable form submit
					var $form = $file.parents('form');
					$form.find('button').addClass('--disabled');
					$file.data('working', true);

					// perform the actual file upload
					$.ajax({
						url : GLOBAL_URLPREFIX + $file.data('fileupload-url'),
						type : 'POST',
						data : formData,
						processData: false,  // tell jQuery not to process the data
						contentType: false,  // tell jQuery not to set contentType
						success : function(json) {
							$file.parents('.postauthoring__post-text-wrapper').find('.action-upload-file').trigger('upload-success', [file, json]);
						},
						error: function() {
							// indicate not busy
							$file.trigger('upload-complete');
						},
						xhr: function() {  // custom xhr
							myXhr = $.ajaxSettings.xhr();
							if(myXhr.upload){ // if upload property exists
								myXhr.upload.addEventListener('progress', function(e) {
									var percent = Math.ceil((e.loaded / e.total) * 100);
									console.log(percent);
								}, false); // progressbar
							}
							return myXhr;
						}
					});
				}
			});

			// 'upload' event abstract out 'success' callback so phonegap upload can tap into app functions
			$(this.element).on('upload-success', '.action-upload-file', function(e, file, json) {
				// register content change
				$(plugin.element).trigger('content-change');

				//var target = $(this).attr('href');
				var target = $(this).data('target');
				var $file = $(target);

				var inputName = $file.siblings('input[type=hidden]').size() > 0 ? $file.siblings('input[type=hidden]').attr('name') : $file.attr('name');
				$file.val(null); // reset file input
				$file.attr('name', '');
				$file.data('field-name', inputName);
				$file.siblings('input[type=hidden]').remove();
				$('<input type="hidden" readonly="readonly" name="' + inputName + '" value="' + json.file[0].path + '" />').insertAfter($file);

				// indicate not busy
				$file.trigger('upload-complete');

				// insert thumbnail preview
				var f = {
					absolute: json.file[0].full,
					type: file.type
				};
				$file.trigger('preview', [f, json.file[0].orientation]);

				plugin.updateAnchorDownState();
			});

			// do stuff after upload complete
			$(this.element).on('upload-complete', '.postauthoring__file-input', function(e) {
				var $file = $(this);

				// indicate not busy
				$file.data('working', false);

				// check form now
				var $form = $file.parents('form');
				var working = $form.find('input[type=file]').filter(function() {
					return $(this).data('working') === true;
				});
				if(working.size() <= 0) {
					// re-enable form submit button
					$form.find('button[type=submit]').removeClass('--disabled');
				}

				plugin.updateAnchorDownState();
			});

			// add upload preview event (after successful upload)
			$(this.element).on('preview', '.postauthoring__file-input', function(e, file, orientation) {

				// remove previous attachment
				plugin.removeAttachment();

				// create preview container
				var $template = null;
				var $preview = $('<div class="attachment-container">' +
					'<input name="attachment[type]" type="hidden">' +
					'<input name="attachment[resource]" type="hidden">' +
					'<input name="attachment[description]" type="hidden">' +
					'<input name="attachment[title]" type="hidden">' +
					'<input name="attachment[source]" type="hidden">' +
					'<input name="attachment[url]" type="hidden">' +
					'<input name="attachment[shortened_url]" type="hidden">' +
				'</div>');
				var $wrapper = $('<div class="postauthoring__attachment-wrapper">');
				// add close button and preview
				$wrapper.append('<div class="postauthoring__attachment-wrapper__close-button"><i class="fa fa-times"></i></div>');
				$wrapper.append($preview);
				//$(plugin.element).find('.postauthoring__post').append($wrapper);
				$(plugin.element).find('.postauthoring__post .postauthoring__post-text-wrapper').after($wrapper);

				// create preview element by type
				switch(file.type) {
					case 'image/png':
					case 'image/jpg':
					case 'image/gif':
					case 'image/jpeg':
						$template = $('<div class="attachment --image">' +
							'<div class="attachment__content">' +
								'<img src="" alt="" />' +
							'</div>' +
						'</div>');

						// generate thumbnail
						if(file.absolute !== undefined) {
							$template.find('img').attr('src', file.absolute);
						}
						// use local file
						else {
							// generate thumbnail
							var URLObject = window.URL || window.webkitURL;
							var objectUrl = URLObject.createObjectURL(file);

							$template.find('img').attr('src', objectUrl);
							if(orientation != undefined) {
								var img = $template.find('img').get(0);
								img.onload = function() {
									$template.find('img').orientate(orientation);
								};
							}
						}
						$preview.find('input[name=attachment\\[type\\]]').val('image');
						plugin.togglePlatformState('image');
						break;
					case 'video/mp4':
					case 'video/avi':
					case 'video/mov':
					case 'video/quicktime':
					case 'video/ogg':
						$template = $('<video width="100%" controls>' +
							'<source src="" type="">' +
							'Your browser does not support HTML5 video.' +
						'</video>');

						// generate thumbnail
						if(file.absolute !== undefined) {
							$template.find('source').attr('src', file.absolute);
							$template.find('source').attr('type', file.type);
						}
						// use local file
						else {
							// generate thumbnail
							var URLObject = window.URL || window.webkitURL;
							var objectUrl = URLObject.createObjectURL(file);
							var objectType = file.type;

							$template.find('source').attr('src', objectUrl);
							$template.find('source').attr('type', objectType);
						}
						$preview.find('input[name=attachment\\[type\\]]').val('video');
						plugin.togglePlatformState('video');
						break;
				}
				$template.appendTo($preview);

				plugin.updateAnchorDownState();
			});
		},
		removeAttachment: function() {
			var plugin = this;
			$(plugin.element).find('.postauthoring__attachment-wrapper').remove();
			plugin.togglePlatformState('delete');
			plugin.updateAnchorDownState();
		},
		togglePlatformState: function(attachmentType) {
			var plugin = this;
			var $items = $(plugin.element).find('.postauthoring__post-footer__account-lists .account-list');

			// disable all
			$items.addClass('--disabled');

			// all platforms
			var platforms = ['facebook','twitter','linkedin','pinterest','youtube','instagram'];
			var exclude = [];
			var allowed = [];
			var denied = [];

			// check allowed platforms
			var $allowed = $('[name=allowed_platforms]');
			if($allowed.length) {
				allowed = $allowed.val().split(',');
				exclude = $(platforms).not(allowed).get();
			}
			// check attachment
			switch(attachmentType) {
				case 'image':
					denied = ['pinterest', 'youtube', 'instagram'];
					allowed = ['pinterest', 'instagram'];
					denied = $(denied).not(allowed).get();
					exclude = $(exclude).add(denied).get();
					break;
				case 'video':
					denied = ['pinterest', 'youtube', 'instagram'];
					allowed = ['youtube', 'instagram'];
					denied = $(denied).not(allowed).get();
					exclude = $(exclude).add(denied).get();
					break;
				default:
					denied = ['pinterest', 'youtube', 'instagram'];
					exclude = $(exclude).add(denied).get();
					break;
			}

			// filter and allow
			$items.filter(function() {
				return exclude.indexOf($(this).data('platform')) == -1;
			}).removeClass('--disabled');

			// deselect accounts that are in the now disabled list
			$items.filter('.--disabled').each(function() {
				$(this).find('.account.--active').triggerHandler('click');
			});
		},
		getPostText: function() {
			var plugin = this;

			var content = htmlEntities($(plugin.element).find('#textarea_message_write').val());
			var parts = content.replace(/\r\n/g, "\n").replace(/\r/g, "\n").split("\n");
			for(var i in parts) {
				parts[i] = '<p>' + (parts[i].length ? parts[i] : '<br />') + '</p>';
			}
			content = parts.join("\n");

			return content;
		},
		getPostTextCharacterLength: function() {
			return $('.postauthoring__post-heading__informational-block__character-wrapper__character-counter').text();
		},
		revertPlatformText: function(element) {
			var plugin = this;

			element.removeClass('--changed').find('.post-panel-item__content__textbox').html(plugin.getPostText());
			element.find('.post-panel-item__heading__character').text(plugin.getPostTextCharacterLength());
			element.find('.post-panel-item__content__textbox').trigger('render-tags');
		},
		setNewlyActivatedPlatformPost: function(element) {
			var plugin = this;

			plugin.newlyActivatedPlatformPost = element;
		},
		getNewlyActivatedPlatformPost: function() {
			var plugin = this;

			return plugin.newlyActivatedPlatformPost;
		},
		getLinkedAccountSelectedInformation: function() {
			var plugin = this,
				accountSelectedArray = {};

			$(plugin.getNewlyActivatedPlatformPost()).each(function(i) {
				var platform = $(this).data('platform');
				var iconClass = plugin.getPlatformIconClass(platform);

				accountSelectedArray[i] = {
					name: $(this).children('span.name').text(),
					platform: platform,
					platformClass: 'fa ' + iconClass,
					linkedId: $(this).data('linked-id'),
					// other params
					subId: $(this).data('sub-id') || '', // for facebook pages and pinterest boards
					token: $(this).data('access-token') || ''
				};
			});

			return accountSelectedArray;
		},
		getPlatformIconClass: function(platform) {
			var platformMap = {
				facebook:  'fa-facebook-official',
				twitter:   'fa-twitter',
				linkedin:  'fa-linkedin',
				pinterest: 'fa-pinterest',
				youtube:   'fa-youtube',
				instagram: 'fa-instagram'
			};
			return platformMap[platform];
		},
		renderCharacterCounter: function(element) {
			var plugin = this;
			var length = element.find('.post-panel-item__content__textbox').realText().length;
			var characterLength = (length != 1) ? length + ' characters' : length + ' characters';

			// For twitter platform post, if the character exceed 140, invalid and cant post
			if(element.hasClass('twitter')) {
				if(length > 140) {
					element.addClass('--invalid');
				} else {
					element.removeClass('--invalid');
				}
			}

			element.find('.post-panel-item__heading__character').text(characterLength);
			plugin.checkAllTwitterIfInvalid();
		},
		invalidEmptyYoutubeOptions: function(element) {
			if(element.val().length < 1) {
				element.closest('.post-panel-item__option-item').addClass('--invalid');
			} else {
				element.closest('.post-panel-item__option-item').removeClass('--invalid');
			}

			var youtubeInvalid = 2;

			element.closest('.post-panel-item__option-items').children().each(function() {
				if(!$(this).hasClass('--invalid')) {
					youtubeInvalid -= 1;
				}
			});

			if(youtubeInvalid > 0) {
				element.closest('.post-panel-item').addClass('--invalid');
			} else {
				element.closest('.post-panel-item').removeClass('--invalid');
			}
		},
		checkAllYoutubeIfInvalid: function() {
			var plugin = this,
				youtubeInvalidLimit = 0;

			$('.post-panel-item.youtube').each(function() {
				if($(this).hasClass('--invalid')) {
					youtubeInvalidLimit++;
				}
				plugin.isYoutubeInvalid = (youtubeInvalidLimit > 0);
			});
		},
		checkAllTwitterIfInvalid: function() {
			var plugin = this,
				twitterInvalidLimit = 0;

			$('.post-panel-item.twitter').each(function() {
				if($(this).hasClass('--invalid')) {
					twitterInvalidLimit++;
				}
				plugin.isTwitterInvalid = (twitterInvalidLimit > 0);
			});
		},
		toggleContinueButton: function() {
			var plugin = this;

			// require social
			var $requireSocial = $('.postauthoring [name=require_social]');
			var require_social = $requireSocial.length > 0;
			var hasSocial = $('.postauthoring .account-list .account.--active').length > 0;

			// button
			var $button = $('.postauthoring__post-footer .postauthoring__post-footer__continue-button');
			// state
			$button.toggleClass('--disabled', !(!require_social || (require_social && hasSocial)));
			// text
			var buttonText = $('.postauthoring [name=buttons]').val();
			if(!buttonText) {
				return;
			}
			var key = hasSocial ? 'continue' : 'continue_empty';
			$button.find('span').text(JSON.parse(buttonText)[key]);
		},
		toggleSubmitButton: function() {
			var plugin = this;
			// button
			var $button = $('.postauthoring__post-footer .postauthoring__post-footer__post-button');
			// state
			$button.toggleClass('--disabled', (plugin.isYoutubeInvalid || plugin.isTwitterInvalid));
			// text
			var buttonText = $('.postauthoring [name=buttons]').val();
			if(!buttonText) {
				return;
			}
			var key = 'post';
			$button.find('span').text(JSON.parse(buttonText)[key]);
		},
		updateAnchorDownState: function() {
			var plugin = this;
			var $anchor = $('.postauthoring__anchordown');
			var viewport = $('body').height();
			var canvas   = $('.postauthoring__wrapper').height();
			var buffer = 100;
			var hasAttachment = $('.postauthoring__attachment-wrapper').size() > 0;
			var hasAccounts = $('.postauthoring__post-panels .post-panel-item').size() > 0;
			var isExpired = $anchor.hasClass('expired');
			// show anchor down if:
			// - canvas is at least 100px taller than the viewport; and
			// - if at least 1 account is selected; and
			// - an attachment is added; and
			// - doesn't have class 'expired'
			if(canvas > (viewport + buffer) && hasAccounts && hasAttachment && !isExpired) {
				$anchor.addClass('--active');
			} else {
				$anchor.removeClass('--active');
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
 * facebook.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for facebook
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'facebook',
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
			$(this.element).data('platform', pluginName);

			var plugin = this;

			// activate
			$(this.element).on('click', function(e) {
				e.preventDefault();

				// mark as loading to ensure it doesn't load twice
				var loading = $(plugin.element).data('facebook-loading');
				if(loading === true) {
					return;
				}
				$(plugin.element).data('facebook-loading', true);

				// submit form
				plugin.showPages();
			});
		},

		showPages: function() {
			var plugin = this;
			var element = this.element;

			// get dialog object, we'll open this later
			var $dialog = $('#' + $(this.element).data('dialog-content'));
			if($dialog.data('loaded')) {
				$(plugin.element).data('facebook-loading', false);
				plugin.initDialog($dialog, element);
				return;
			}

			// ajax to return facebook pages
			if($(this.element).data('get-facebook-pages').length) {
				$.ajax($(this.element).data('get-facebook-pages'), {
					method: 'GET',
					success: function(json) {
						if(json.pages == undefined) {
							return;
						}

						// mark dialog as loaded
						// this should throttle back future api requests
						$dialog.data('loaded', true);

						// populate select pages dialog
						var $list = $dialog.find('#facebook-pages-selection-list');
						$list.find('li:not(.--template)').remove();
						for(var i in json.pages) {
							var $template = $list.find('li.--template').clone();
							var page = json.pages[i];
							$template.removeClass('--template');
							$template.data('id', page.id);
							$template.data('type', page.type);
							$template.data('token', page.access_token);
							$template.find('label').text(page.name);
							$list.append($template);
						}
						plugin.initDialog($dialog, element);
					},
					complete: function() {
						// mark as loading to ensure it doesn't load twice
						$(plugin.element).data('facebook-loading', false);
					}
				});
			}
		},

		initDialog: function($dialog, element) {

			// show dialog
			$('.dialog').trigger('show', [$dialog]);
			$('.dialog').data('facebook-pages-trigger', $(element));

			if($dialog.data('initialised')) {
				return;
			}
			$dialog.data('initialised', true);

			// selecting pages
			$('.dialog').on('click', '#facebook-pages-selection-list .entity', function(e) {
				var val = $('.dialog').find('input[name=social\\[facebook\\]\\[pages\\]]').val();
				var id = $(this).data('id') + '|' + $(this).data('type') + '|' + $(this).data('token');
				if($(this).hasClass('--active')) { // remove
					val = val.replace(',' + id + ',', ',');
					if(val == ',') {
						val = '';
					}
				} else { // add
					if(val.indexOf(',' + id + ',') == -1) {
						val = (val.length == 0 ? ',' : val);
						val = val + id + ',';
					}
				}
				$('.dialog').find('input[name=social\\[facebook\\]\\[pages\\]]').val(val);

				// button state
				$('.dialog').find('.button.confirm-selection').toggleClass('--disabled', val.length == 0);

				$(this).toggleClass('--active');
			});
			$('.dialog').find('.button.confirm-selection').addClass('--disabled');

			// selecting pages
			$('.dialog').on('click', '.confirm-selection', function(e) {
				if($(this).parents('.dialog').find('#facebook-pages-selection-list').length == 0) {
					return;
				}
				var $trigger = $('.dialog').data('facebook-pages-trigger');
				var $input = $('.dialog').find('input[name=social\\[facebook\\]\\[pages\\]]').clone();
				$trigger.find('input[name=social\\[facebook\\]\\[pages\\]]').remove();
				$trigger.append($input);
				$trigger.trigger('select');
			});

			// cancel
			$('.dialog').on('click', '.cancel-selection', function(e) {
				if($(this).parents('.dialog').find('#facebook-pages-selection-list').length == 0) {
					return;
				}
				var $trigger = $('.dialog').data('facebook-pages-trigger');
				$trigger.find('input[name=social\\[facebook\\]\\[pages\\]]').remove();
				$trigger.trigger('deselect');
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
 * pinterest.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for pinterest
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'pinterest',
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
			$(this.element).data('platform', pluginName);

			var plugin = this;

			// activate
			$(this.element).on('click', function(e) {
				e.preventDefault();

				// mark as loading to ensure it doesn't load twice
				var loading = $(plugin.element).data('pinterest-loading');
				if(loading === true) {
					return;
				}
				$(plugin.element).data('pinterest-loading', true);

				// submit form
				plugin.showBoards();
			});
		},

		showBoards: function() {
			var plugin = this;
			var element = this.element;

			// get dialog object, we'll open this later
			var $dialog = $('#' + $(this.element).data('dialog-content'));
			if($dialog.data('loaded')) {
				$(plugin.element).data('pinterest-loading', false);
				plugin.initDialog($dialog, element);
				return;
			}

			// ajax to return pinterest boards
			if($(this.element).data('get-pinterest-boards').length) {
				$.ajax($(this.element).data('get-pinterest-boards'), {
					method: 'GET',
					success: function(json) {
						if(json.boards == undefined) {
							return;
						}

						// mark dialog as loaded
						// this should throttle back future api requests
						$dialog.data('loaded', true);

						// populate select boards dialog
						var $list = $dialog.find('#pinterest-boards-selection-list');
						$list.find('li:not(.--template)').remove();
						for(var i in json.boards) {
							var $template = $list.find('li.--template').clone();
							var board = json.boards[i];
							$template.removeClass('--template');
							$template.data('id', board.id);
							$template.find('label').text(board.name);
							$list.append($template);
						}
						plugin.initDialog($dialog, element);
					},
					complete: function() {
						// mark as loading to ensure it doesn't load twice
						$(plugin.element).data('pinterest-loading', false);
					}
				});
			}
		},

		initDialog: function($dialog, element) {

			// show dialog
			$('.dialog').trigger('show', [$dialog]);
			$('.dialog').data('pinterest-boards-trigger', $(element));

			if($dialog.data('initialised')) {
				return;
			}
			$dialog.data('initialised', true);

			// selecting boards
			$('.dialog').on('click', '#pinterest-boards-selection-list .entity', function(e) {
				var val = $('.dialog').find('input[name=social\\[pinterest\\]\\[boards\\]]').val();
				var id = $(this).data('id');
				if($(this).hasClass('--active')) { // remove
					val = val.replace(',' + $(this).data('id') + ',', ',');
					if(val == ',') {
						val = '';
					}
				} else { // add
					if(val.indexOf(',' + id + ',') == -1) {
						val = (val.length == 0 ? ',' : val);
						val = val + id + ',';
					}
				}
				$('.dialog').find('input[name=social\\[pinterest\\]\\[boards\\]]').val(val);

				// button state
				$('.dialog').find('.button.confirm-selection').toggleClass('--disabled', val.length == 0);

				$(this).toggleClass('--active');
			});
			$('.dialog').find('.button.confirm-selection').addClass('--disabled');

			// selecting boards
			$('.dialog').on('click', '.confirm-selection', function(e) {
				if($(this).parents('.dialog').find('#pinterest-boards-selection-list').length == 0) {
					return;
				}
				var $trigger = $('.dialog').data('pinterest-boards-trigger');
				var $input = $('.dialog').find('input[name=social\\[pinterest\\]\\[boards\\]]').clone();
				$trigger.find('input[name=social\\[pinterest\\]\\[boards\\]]').remove();
				$trigger.append($input);
				$trigger.trigger('select');
			});

			// cancel
			$('.dialog').on('click', '.cancel-selection', function(e) {
				if($(this).parents('.dialog').find('#pinterest-boards-selection-list').length == 0) {
					return;
				}
				var $trigger = $('.dialog').data('pinterest-boards-trigger');
				$trigger.find('input[name=social\\[pinterest\\]\\[boards\\]]').remove();
				$trigger.trigger('deselect');
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
 * post-share.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for post-share objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'postshare',
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
			var $sharer = $(this.element);

			// find special buttons
			$sharer.find('.entity').each(function() {
				// facebook
				if($(this).hasClass('load-facebook-select-pages')) {
					$(this).facebook();
				}
				// pinterest
				if($(this).hasClass('load-pinterest-select-boards')) {
					$(this).pinterest();
				}
				// youtube
				if($(this).hasClass('load-youtube-options')) {
					$(this).youtube();
				}
				// facebook
				if($(this).hasClass('open-facebook-messagebox')) {
					$(this).on('click', function() {
						// check if there's a message box to scroll to
						if($('#textarea_message_write_fb').length > 0 && !$('#facebook_dropdown').hasClass('--active') && !$(this).hasClass('--active')) {
							$('#facebook-dropdown-trigger').trigger('click');
							$('html, body').animate({ scrollTop: 0 }, 100);
						}
					});
				}
				// twitter
				if($(this).hasClass('validate-twitter-post')) {
					$(this).twitter();
				}
			});

			// selecting accounts
			$sharer.on('click', '.entity', function(e) {
				if($(this).hasClass('--active')) { // remove
					$(this).trigger('deselect');
				} else { // add
					$(this).trigger('select');
				}
			});

			// toggle on
			var $form = plugin.getForm();
			$sharer.on('select', '.entity', function(e) {
				var val = $sharer.find('input[name=accounts]').val();
				var id = $(this).data('id');
				if(val.indexOf(',' + id + ',') == -1) {
					val = (val.length == 0 ? ',' : val);
					val = val + id + ',';
				}
				$sharer.find('input[name=accounts]').val(val);
				$(this).addClass('--active');
				$form.trigger('check-socialmedia-state');
			});

			// toggle off
			$sharer.on('deselect', '.entity', function(e) {
				var val = $sharer.find('input[name=accounts]').val();
				var id = $(this).data('id');
				val = val.replace(',' + $(this).data('id') + ',', ',');
				if(val == ',') {
					val = '';
				}
				$sharer.find('input[name=accounts]').val(val);
				$(this).removeClass('--active');
				$form.trigger('check-socialmedia-state');
			});

			// share
			$form.on('click', '.button-holder .button.--sharer', function(e) {
				e.preventDefault();

				var $form = plugin.getForm();

				// platform specific validation
				// platforms post-submit-before event
				var outcome = true;

				var image   = $form.find('.attachment__content img').attr('src');
				var message = $form.find('[name=message]').val();
				var data    = $form.parse();

				outcome = plugin.submitBefore(image, data.message, data.attachment, data.social);

				if(!$.isEmptyObject(outcome)) {
					// show first dialog for now
					var error = outcome.shift();
					var dialog = error.dialog;
					var $content = $('#' + dialog);
					var callback = error.callback;
					var social = (data.social !== undefined) ? data.social[error.platform] : undefined;
					var $trigger = $form.find('.entity.--active').filter(function() {
						return $(this).data('platform') == error.platform;
					});
					if($content.length) {
						$('.dialog').trigger('show', [$content, dialog, $trigger]);
						callback($('.dialog'), $trigger, image, data.message, data.attachment, social);
						return;
					}
				}

				// submission
				$.ajax($form.attr('action'), {
					method: 'POST',
					data: $form.parse(),
					success: function(json) {
						//var media   = (json.post.attachment !== null) ? json.post.attachment.resource : null;
						var media   = null;
						//var message = json.post.message_original;
						var message = null;

						// increment?
						$form.find('.button-holder:has(button)').toggleClass('increment', json.points_increment);

						// submit after event
						plugin.submitAfter(media, message, true); // true: show success message

						// success
						plugin.reset();
					}
				});
			});

			// drop down open
			$sharer.on('opened', '.dropdown', function() {
				$('body').addClass('sharer-dropdown-active');
			});

			// drop down close
			$sharer.on('closed', '.dropdown', function() {
				$('body').removeClass('sharer-dropdown-active');
			});
		},

		getForm: function() {
			var $sharer = $(this.element);
			var $form = $sharer.find('form');
			if($form.size() == 0) {
				$form = $sharer.parents('form');
			}
			return $form;
		},

		reset: function() {
			var plugin = this;

			// get proper parent
			var $form = plugin.getForm();

			// list of fields to reset
			var resetFields = [
				'message',
				'accounts'
			];
			var $reset = $form.find(':input').filter(function() {
				return $.inArray($(this).attr('name'), resetFields) !== -1;
			});
			$reset.each(function() {
				if($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
					$(this).prop('checked', false);
				} else {
					$(this).val('');
				}
			});

			// remove active states on all entities
			$(this.element).find('.entity').removeClass('--active');

			// close dropdown
			var ddTarget = $(this.element).find('.dropdown').data('dropdown-target');
			var $trigger = $('.post-options__action.dropdown__trigger').filter(function() {
				return $(this).attr('href') == '#' + ddTarget;
			});
			if($(this.element).find('.dropdown').hasClass('--active')) {
				$trigger.trigger('click');
			}
		},

		submitAfter: function(image, message, showSuccess) {
			var $sharer = $(this.element);
			var $selected = $sharer.find('.entity.--active');
			var shared = [];
			$selected.each(function() {
				var key = $(this).find('.field__label').text();

				if($(this).hasClass('after-instagram-publish')) {
					console.log(' > instagram');
					pg_instagram(image, message); // this will open a dialog
				}
				// all other cases
				else {
					shared.push(key);
				}
			});

			// if no extra actions to be taken, show "shared" message
			if($selected.size() > 0 && $selected.size() == shared.length && showSuccess === true) {
				alert('This post has been shared.');
			}

			// increment
			var $form = $sharer.find('form');
			if($form.size() == 0) {
				$form = $sharer.parents('form');
			}
			$form.find('.button-holder').trigger('increment');
		},

		submitBefore: function(image, message, attachment, social) {
			var plugin = this;
			var $sharer = $(this.element);

			var validation = [];
			$sharer.find('.entity.--active').each(function() {
				var platform = $(this).data('platform');
				var socialData = (social !== undefined) ? social[platform] : undefined;
				var results = plugin.validatePlatform(this, image, message, attachment, socialData);
				if(!$.isEmptyObject(results)) {
					validation.push(results);
				}
			});

			return validation;
		},

		validatePlatform: function(trigger, image, message, attachment, social) {
			var $platform = $(trigger);
			var platform = $platform.data('platform');
			var results = {};
			if($platform.data('plugin_' + platform) !== undefined && $platform.data('plugin_' + platform).validate !== undefined) {
				var results = $platform.data('plugin_' + platform).validate(image, message, attachment, social);
				if(!$.isEmptyObject(results)) {
					results = $.extend({ platform : platform }, results);
				}
			}
			return results;
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
 * post-write.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for post-write objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'postwrite',
		defaults = {
			injectTarget: function(element) {
				var $ele = $(element);
				return $($ele.data('target'));
			},
			ajax: 'default', // 'default' = ajax if file field not present
			injectMethod: 'prepend',
			success: $.noop,
			requireSocialMedia: false,
			autosave: false,
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

			// auto suggest entities
			$(this.element).find('textarea').tagger();

			// are you sure
			if($(this.element).data('areyousure-message') != undefined) {
				$(this.element).areYouSure({
					message: $(this.element).data('areyousure-message')
				});
			}

			// init dropdowns
			$(this.element).find('.dropdown').dropdown();

			// init post share
			$(this.element).find('.post-options__sharing').postshare();

			// init character counter
			$(this.element).find('.character-counter').characterCounter();

			// post options attachments
			// http://www.html5rocks.com/en/tutorials/getusermedia/intro/
			$(this.element).find('.post-options__file-trigger').on('click', function(e) {
				e.preventDefault();
				var target = $(this).attr('href');
				var $file = $(target);

				// phonegap or HTML input?
				//if(typeof(pg_getMedia) !== 'undefined') {
				if(is_phonegap) {
					pg_getMedia($(this), $file, function(url, $trigger, $file) {
						pg_uploadMedia(url, $trigger, $file);
					});
				} else {
					$file.trigger('click');
				}
			});

			$(this.element).on('change', '.post-options__file-input', function(e) {
				var $file = $(this);
				if($file.attr('name') == '') {
					$file.attr('name', $file.data('field-name'));
				}

				var files = e.target.files;
				for(var i = 0, f; f = files[i]; i++) {
					$(this).addClass('--has-file');
				}

				// if live upload attributes found, upload
				var $file = $(this);
				if($file.data('fileupload-url')) {
					$file.trigger('upload', [files[0]]);
				}
			});

			$(this.element).on('upload', '.post-options__file-input', function(e, file) {
				var $file = $(this);
				name = file.name;
				size = file.size;
				type = file.type;

				var types = [
					'image/png',
					'image/jpg',
					'image/gif',
					'image/jpeg',
					'video/mp4',
					'video/avi',
					'video/mov',
					'video/quicktime',
					'video/ogg'
				];

				// file present?
				if(file.name.length < 1) {
				}
				// check size
				else if(file.size > 40 * 1024 * 1024) {
					alert("File is too big");
				}
				// check file type
				else if(types.indexOf(file.type) === -1) {
					alert('File format "' + file.type + '" not supported');
				}
				// ok to upload
				else {
					var formData = new FormData();
					formData.append('file', file);
					formData.append('_token', $file.parents('form').find('input[name=_token]').val());

					// insert thumbnail preview
					$file.trigger('placehold');

					// disable form submit
					var $form = $file.parents('form');
					$form.find('button').addClass('--disabled');
					$file.data('working', true);

					// perform the actual file upload
					$.ajax({
						url : GLOBAL_URLPREFIX + $file.data('fileupload-url'),
						type : 'POST',
						data : formData,
						processData: false,  // tell jQuery not to process the data
						contentType: false,  // tell jQuery not to set contentType
						success : function(json) {
							$file.parents('.post-options__group').find('.post-options__file-trigger').trigger('upload-success', [file, json]);
						},
						error: function() {
							// indicate not busy
							$file.trigger('upload-complete');
						},
						xhr: function() {  // custom xhr
							myXhr = $.ajaxSettings.xhr();
							if(myXhr.upload){ // if upload property exists
								myXhr.upload.addEventListener('progress', function(e) {
									var percent = Math.ceil((e.loaded / e.total) * 100);
									console.log(percent);
								}, false); // progressbar
							}
							return myXhr;
						}
					});
				}
			});

			// 'upload' event abstract out 'success' callback so phonegap upload can tap into app functions
			$(this.element).on('upload-success', '.post-options__file-trigger', function(e, file, json) {
				// register content change
				$(plugin.element).trigger('content-change');

				var target = $(this).attr('href');
				var $file = $(target);

				var inputName = $file.siblings('input[type=hidden]').size() > 0 ? $file.siblings('input[type=hidden]').attr('name') : $file.attr('name');
				$file.val(null); // reset file input
				$file.attr('name', '');
				$file.data('field-name', inputName);
				$file.siblings('input[type=hidden]').remove();
				$('<input type="hidden" readonly="readonly" name="' + inputName + '" value="' + json.file[0].path + '" />').insertAfter($file);

				// indicate not busy
				$file.trigger('upload-complete');

				// insert thumbnail preview
				//$file.trigger('preview', [file, json.file[0].orientation]);
				var f = {
					absolute: json.file[0].full,
					type: file.type
				};
				$file.trigger('preview', [f, json.file[0].orientation]);
			});

			// do stuff after upload complete
			$(this.element).on('upload-complete', '.post-options__file-input', function(e) {
				var $file = $(this);

				// indicate not busy
				$file.data('working', false);

				// check form now
				var $form = $file.parents('form');
				var working = $form.find('input[type=file]').filter(function() {
					return $(this).data('working') === true;
				});
				if(working.size() <= 0) {
					// re-enable form submit button
					$form.find('button[type=submit]').removeClass('--disabled');
				}
			});

			// add upload preview event (after successful upload)
			$(this.element).on('preview', '.post-options__file-input', function(e, file, orientation) {
				$(this).parents('.post-options').siblings('.attachment-container').remove();

				var $preview = $('<div class="attachment-container">' +
					'<input name="attachment[type]" type="hidden">' +
					'<input name="attachment[resource]" type="hidden">' +
					'<input name="attachment[description]" type="hidden">' +
					'<input name="attachment[title]" type="hidden">' +
					'<input name="attachment[source]" type="hidden">' +
					'<input name="attachment[url]" type="hidden">' +
					'<input name="attachment[shortened_url]" type="hidden">' +
				'</div>').insertAfter($(this).parents('.post-options').siblings('.post-write__message'));

				switch(file.type) {
					case 'image/png':
					case 'image/jpg':
					case 'image/gif':
					case 'image/jpeg':
						var $template = $('<div class="attachment --image">' +
							'<div class="attachment__content">' +
								'<img src="" alt="" />' +
							'</div>' +
						'</div>');

						// generate thumbnail
						if(file.absolute !== undefined) {
							$template.find('img').attr('src', file.absolute);
						}
						// use local file
						else {
							// generate thumbnail
							var URLObject = window.URL || window.webkitURL;
							var objectUrl = URLObject.createObjectURL(file);

							$template.find('img').attr('src', objectUrl);
							if(orientation != undefined) {
								var img = $template.find('img').get(0);
								img.onload = function() {
									$template.find('img').orientate(orientation);
								};
							}
						}
						$preview.find('input[name=attachment\\[type\\]]').val('image');
						break;
					case 'video/mp4':
					case 'video/avi':
					case 'video/mov':
					case 'video/quicktime':
					case 'video/ogg':
						var $template = $('<video width="100%" controls>' +
							'<source src="" type="">' +
							'Your browser does not support HTML5 video.' +
						'</video>');

						// generate thumbnail
						if(file.absolute !== undefined) {
							$template.find('source').attr('src', file.absolute);
							$template.find('source').attr('type', file.type);
						}
						// use local file
						else {
							// generate thumbnail
							var URLObject = window.URL || window.webkitURL;
							var objectUrl = URLObject.createObjectURL(file);
							var objectType = file.type;

							$template.find('source').attr('src', objectUrl);
							$template.find('source').attr('type', objectType);
						}
						$preview.find('input[name=attachment\\[type\\]]').val('video');
						break;
				}
				$template.appendTo($preview);
			});

			// post require social media
			var $form = $(this.element);
			$(this.element).on('check-socialmedia-state', function() {
				if(plugin.options.requireSocialMedia === false) {
					return;
				}
				var $entities = $form.find('.entity.--active');
				$form.find('[type=submit]').toggleClass('--disabled', $entities.size() == 0);
				$('.gig-post-submit').toggleClass('--disabled', $entities.size() == 0);
			});
			$(this.element).on('click', '.entity', function(e) {
				e.preventDefault();
				$form.trigger('check-socialmedia-state');
			});
			$form.trigger('check-socialmedia-state');

			// post submission
			$(this.element).on('submit', function(e) {
				//var plugin = this;
				var element = plugin.element;

				// do ajax if file inputs aren't present
				var doAjax = plugin.options.ajax;
				if(doAjax == 'default') {
					//doAjax = $(element).find('input[type=file]').size() == 0;
				}

				if(doAjax === false) {
					var outcome = plugin.preSubmit();
					if(outcome === false) {
						e.preventDefault();
						e.stopPropagation();
					}
					return;
				}
				e.preventDefault();

				// submit form
				plugin.submitPost();
			});

			// paste link as attachment
			$(this.element).find('textarea').on('paste', function(e) {
				var $ele = $(this);

				// get clipboard data
				var data = null;
				/*if(e.originalEvent.clipboardData !== undefined) {
					try {
						data = e.originalEvent.clipboardData.getData('Text') || null;
					}
					catch(e) {
						data = null;
					}
				} else if(window.clipboardData !== undefined) {
					try {
						data = window.clipboardData.getData('Text') || null;
					}
					catch(e) {
						data = null;
					}
				}*/
				data = getClipboardData(e);

				// get alternate value if clipboard is not accessible
				if(data == null) {
					var t = setTimeout(function() {
						var dataAlt = $ele.val();
						//alert('fallback method');
						$ele.trigger('pastecomplete', [dataAlt]);
					}, 220);
				} else {
					//alert('normal method');
					$ele.trigger('pastecomplete', [data]);
				}
			});
			$(this.element).find('textarea').on('pastecomplete', function(e, data) {
				// get value of current element if data is null
				if(data == null) {
					data = $(this).val();
				}
				//alert(data);

				// get first detected url in string and load attachment from it
				var urlRegex = '(?!mailto:)(?:(?:http|https|ftp)://)(?:\\S+(?::\\S*)?@)?(?:(?:(?:[1-9]\\d?|1\\d\\d|2[01]\\d|22[0-3])(?:\\.(?:1?\\d{1,2}|2[0-4]\\d|25[0-5])){2}(?:\\.(?:[0-9]\\d?|1\\d\\d|2[0-4]\\d|25[0-4]))|(?:(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)(?:\\.(?:[a-z\\u00a1-\\uffff0-9]+-?)*[a-z\\u00a1-\\uffff0-9]+)*(?:\\.(?:[a-z\\u00a1-\\uffff]{2,})))|localhost)(?::\\d{2,5})?(?:(/|\\?|#)[^\\s]*)?';
				var url = new RegExp(urlRegex, 'ig');
				var matches = data.match(url);

				// create attachment
				if(matches) {
					//alert('url "' + matches[0] + '" detected');
					plugin.addAttachment(matches[0]);
				}
			});

			// register content change
			$(this.element).on('content-change', function() {
				var $write = $(this);

				// attach autosave event
				if(plugin.options.autosave == true) {
					console.log('invoke autosave waiter');

					var t = null;
					if($write.data('autosave-waiter') == undefined || $write.data('autosave-waiter') == null) {
						if($write.data('first-autosave') == undefined) {
							console.log('60 seconds');
							// delay first autosave for 60 seconds
							t = setTimeout(function() {
								$write.data('autosave-waiter', null);
								$write.data('first-autosave', true);
								$write.trigger('autosave');
								console.log('perform autosave');
							}, 60000);
						} else {
							console.log('10 seconds');
							t = setTimeout(function() {
								$write.data('autosave-waiter', null);
								$write.data('first-autosave', false);
								$write.trigger('autosave');
								console.log('perform autosave');
							}, 10000);
						}
						$write.data('autosave-waiter', t);
					}
				}
			});

			if(plugin.options.autosave == true) {

				// autosave
				$(this.element).on('autosave', function() {
					plugin.autosave();
				});
			}

			$(this.element).find('textarea').on('input', function() {
				// register content change
				$(plugin.element).trigger('content-change');
			});
		},

		addAttachment: function(url) {
			var plugin = this;
			var element = this.element;

			// ajax to return url info
			if($(this.element).data('attachment-analyser').length) {
				$.ajax($(this.element).data('attachment-analyser'), {
					method: 'POST',
					data: {
						_token: $(this.element).find(':input[name=_token]').val(),
						a: url
					},
					success: function(html) {
						// register content change
						$(element).trigger('content-change');

						// replace attachment
						$(element).find('.attachment-container').remove();
						$(element).find('.post-write__message').after(html);

						// remove any media uploads from attachments
						var $ff = $(element).find('input[name=message_file]');
						if($ff.is('[type=hidden]')) {
							$ff.remove();
						} else {
							$ff.wrap('<form>').closest('form').get(0).reset();
							$ff.unwrap();
						}
					}
				});
			}
		},

		autosave: function() {
			var plugin = this;
			var $write = $(this.element);

			// don't continue if autosave url not specified
			if($write.data('autosave-url') == null || $write.data('autosave-url').length <= 10) {
				console.log('Autosave URL not specified');
				return false;
			}

			// get autosave url
			var autosaveUrl = $write.data('autosave-url');

			// ajax it up
			$.ajax(autosaveUrl, {
				method: $(this.element).attr('method') || 'POST',
				data: $(this.element).serialize(),
				success: function(html) {
				}
			});
		},

		// validate post before submitting
		preSubmit: function() {
			var plugin = this;

			// validation
			// - requires text; or
			// - requires attachment
			if($(this.element).find('[name=message]').val().trim().length == 0 &&
				$(this.element).find('.attachment-container').length == 0) {
				return false;
			}

			// platform specific validation
			// platforms post-submit-before event
			var outcome = true;
			if($(plugin.element).find('.post-options__sharing').length) {
				var $form   = $(plugin.element);
				var image   = $form.find('.attachment__content img').attr('src');
				var message = $form.find('[name=message]').val();
				var data    = $form.parse();

				var sharePlugin = $form.find('.post-options__sharing').data('plugin_postshare');
				outcome = sharePlugin.submitBefore(image, data.message, data.attachment, data.social);

				if(!$.isEmptyObject(outcome)) {
					// show first dialog for now
					var error = outcome.shift();
					var dialog = error.dialog;
					var $content = $('#' + dialog);
					var callback = error.callback;
					var social = (data.social !== undefined) ? data.social[error.platform] : undefined;
					var $trigger = $form.find('.entity.--active').filter(function() {
						return $(this).data('platform') == error.platform;
					});
					if($content.length) {
						$('.dialog').trigger('show', [$content, dialog, $trigger]);
						callback($('.dialog'), $trigger, image, data.message, data.attachment, social);
						return false;
					}
				}
			}
			return true;
		},

		submitPost: function() {
			var plugin = this;

			var outcome = plugin.preSubmit();
			if(outcome === false) {
				return;
			}

			// ajax it up
			$.ajax($(this.element).attr('action'), {
				method: $(this.element).attr('method') || 'POST',
				data: $(this.element).serialize(),
				success: function(html) {
					// clear form
					plugin.resetForm();

					// platforms post-submit-after event
					if($(plugin.element).find('.post-options__sharing').length) {
						var image   = $(html).find('.attachment__content img').attr('src');
						var message = $(html).find('.post__message__content').text();
						$(plugin.element).find('.post-options__sharing').data('plugin_postshare').submitAfter(image, message);
					}

					// sharing options
					if($(plugin.element).find('.post-options__sharing').length) {
						$(plugin.element).find('.post-options__sharing').data('plugin_postshare').reset();
					}

					// inject new post
					var $item = plugin.inject(html);

					// init post
					$item.post();

					// call callbacks
					plugin.options.success($item);
				}
			});
		},

		inject: function(html) {
			var plugin = this;

			var $target = plugin.getInjectTarget();
			var $item   = $(html).addClass('--injected');
			if(plugin.options.injectMethod == 'prepend') {
				$target.prepend($item);
			} else if(plugin.options.injectMethod == 'append') {
				$target.append($item);
			}

			// remove injected class
			var t = setTimeout(function() {
				$item.removeClass('--injected');
			}, 100);

			// init incrementer, making sure all child elements have it too
			$item.add($item.find('.incrementer')).increment();
			$item.trigger('increment');

			return $item;
		},

		resetForm: function() {
			// list of fields to reset
			var resetFields = [
				'message'
			];
			var $reset = $(this.element).find(':input').filter(function() {
				return $.inArray($(this).attr('name'), resetFields) !== -1;
			});
			$reset.each(function() {
				if($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
					$(this).prop('checked', false);
				} else {
					$(this).val('');
				}
			});

			// reset character counters
			var $counters = $(this.element).find('.character-counter');
			$counters.each(function() {
				if($(this).data('plugin_characterCounter')) {
					$(this).data('plugin_characterCounter').refresh();
				}
			});

			// remove attachment container
			$(this.element).find('.attachment-container').remove();

			// remove social media specific fields
			$(this.element).find('input[name^=social\\[]').remove();
		},

		// helpers

		getInjectTarget: function() {
			var plugin = this;

			var target = plugin.options.injectTarget;
			var $target = null;

			if(typeof(target) == 'function') {
				$target = target(this.element);
			} else if(typeof(target) == 'string') {
				$target = $(plugin.options.injectTarget);
			}
			return $target;
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
 * post.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for post objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'post',
		defaults = {
			openComments: false
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
			$(this.element).find('.comment-write form').commentwrite();

			// post options
			$(this.element).find('.dropdown').dropdown();

			// post share
			$(this.element).find('.post-options__sharing').postshare();

			// open comments
			if(this.options.openComments === true) {
				$(this.element).find('.post-options__action.dropdown__trigger.comments').trigger('click');
			}

			// like
			$(this.element).on('click', '.like', function(e) {
				e.preventDefault();
				var $post = $(this).parents('.post');

				// ajax it up
				$.ajax($(this).data('url'), {
					method: 'POST',
					data: {
						_token: $('input[name=_token]').val()
					},
					success: function(json) {
						// update counter
						$post.find('.like__counter').text(json.counter);
					}
				});
			});

			// other options
			$(this.element).on('click', '.post-options__drop .dialog-action, .post-action', function(e) {
				e.preventDefault();
				var $trigger = $(this);
				var $post = $trigger.parents('.post');
				var dialog = $trigger.attr('href');
				var action = $trigger.data('action-url');

				// close current drop down
				$('body').triggerHandler('click');

				// open dialog
				var $content = $(dialog);
				$('.dialog').trigger('show', [$content, dialog, $trigger,
					function(content, id, trigger) {
						// reset input fields
						$('.dialog').find(':input.action-param').val('');
						// bind data to dialogs
						var data = $(trigger).data('dialog-data');
						for(var key in data) {
							var val = data[key];
							var $obj = $(':input[data-bind=' + key + ']');
							if($obj.length == 0) {
								$('[data-bind=' + key + ']').text(val);
							} else {
								$obj.val(val);
							}
						}
						// confirmation of action
						var callback = function(e) {
							e.preventDefault();
							var action = $(this).data('action-url');
							var title = $('.dialog').find('[name=action-complete-title]').val();
							var message = $('.dialog').find('[name=action-complete-message]').val();
							var close = $('.dialog').find('[name=action-complete-close]').val();
							var $data = $('.dialog').find(':input.action-param');
							var data = {};
							$data.each(function() {
								var name = $(this).attr('name');
								data[name] = $(this).val();
							});
							data._token = $('input[name=_token]').val();
							// ajax it up
							$.ajax(action, {
								method: 'POST',
								data: data,
								success: function(json) {
									// hide current popup
									$('.dialog').trigger('hide');
									// show action complete dialog
									/*var t = setTimeout(function() {
										var $success = $('#action-complete-dialog');
										$('.dialog').trigger('show', [$success, 'action-complete', null, function() {
											$(this).find('[data-bind=action-complete-title]').text(title);
											$(this).find('[data-bind=action-complete-message]').text(message);
											$(this).find('.action-complete-close').text(close);
										}]);
									}, 2600);*/
									// hide post in ui
									if(id == '#hide-post') {
										$post.addClass('--status-hidden');
									} else if(id == '#unhide-post') {
										$post.removeClass('--status-hidden');
									}
								},
								error: function() {
									alert('error performing this action');
									// rebind event
									$('.dialog').find('.confirm-action').one('click', callback);
								}
							});
						};
						$('.dialog').find('.confirm-action').data('action-url', action).one('click', callback);
					}]);
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
 * tagger.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for tagger objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'socialtagger',
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
			var options = this.options;
			var $input = $(this.element);

			// fallback to 'tagger-url' attribute
			if(options.url == undefined) {
				options.url = $input.data('tagger-url');
			}
			if(options.url == undefined || options.url.length == 0) {
				console.log('tagger: could not resolve fetch URL');
				return;
			}

			// tagging suggestions
			$input.on('keyup', function(e) {
				var value = $input.realVal();
				var info = getCaretInfo();
				var caret  = info.startPos;
				var parent = info.parent;
				var node   = info.node;

				// get between start of string and caret
				//var before = value.substr(0, caret);
				var before = node.data.substr(0, caret);

				// get last '@' in this substring
				var tagStart = before.lastIndexOf('@');
				var term = before.substr(tagStart + 1, caret - tagStart);
				var isNewline = (term.indexOf("\n") != -1 || term.indexOf("\r") != -1);

				// checkpoint:
				// - @ present before caret
				// - no new line character between @ and caret
				// - at least 1 character in term
				if(isNewline || term.length == 0 || tagStart == -1) {
					return;
				}

				// nullify throttle
				if($input.data('throttle')) {
					clearTimeout($input.data('throttle'));
					$input.data('throttle', null);
				}

				// start new throttle
				var t = setTimeout(function() {
					var url = options.url;

					// ajax
					$.ajax(url, {
						data: {
							term: term
						},
						success: function(json) {
							options.callback.apply($input, [json]);
						}
					});
				}, 250);

				// keep track
				$input.data('throttle', t);
			});

			$input.on('keyup keydown keypress click paste undo input change', function(e) {
				var info = getCaretInfo();
				$input.data('caret-info', info);
				$input.data('caret-node', info.node);
				$input.data('caret-start', info.startPos);
			});

			// selecting suggestion
			$input.parents('.tagger').on('click', '.suggestion', function(e) {
				var $item = $(this);

				// get information and value of current text node
				var caret = $input.data('caret-start');
				var node  = $input.data('caret-node');
				var value = node.data;

				// get between start of string and caret
				var before = value.substr(0, caret);

				// get last '@' in this substring
				var tagStart = before.lastIndexOf('@');

				// split string into 3 parts, replace second part, then join
				var first = value.substr(0, tagStart); // text in text node leading up to @
				var third = value.substr(caret); // text in text node after tag injection
				var render = function(data) {
					var shortMap = {
						facebook:  'fb',
						twitter:   'tw',
						linkedin:  'li',
						pinterest: 'pi',
						youtube:   'yt',
						instagram: 'ig'
					};
					var short = shortMap[data.platform];
					return '<span class="o_ ot_ ofa_ a_ ' + short + '_" data-id="' + htmlEntities(data.profileId) + '" data-text="' + htmlEntities(data.displayName) + '" data-value="[@' + short + ':' + data.profileId + ']">' + htmlEntities(data.displayName) + '</span>';
				};
				var insertHtml = render($item.data());

				// get final value
				value = first + insertHtml + third;

				// get new caret position
				caret = (first + insertHtml).length;

				// set value
				// we'll wrap the text node to achieve html injection (node.data assignment won't work as it's plain text)
				var $wrap = $(node).wrap('<b>').parent();
				$wrap.replaceWith(value + '&nbsp;'); // replace entire element
				var $o = $input.find('.o_.a_'); // a_ is used to paint the new tag, so we can easily select it
				var c = $o.parent().contents();
				var index = $(c).index($o) + 1; // text node to focus is 1 ahead of the injected tag
				node = $o.parent().contents()[index];
				$o.removeClass('a_');

				// set caret to next text node after injected element
				var t = setTimeout(function() {
					focusTextNode($(node));
				}, 1);
			});

			$(document).click(function() {
				$input.parents('.tagger').find('.tagger__results').remove();
			})
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
 * tagger.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for tagger objects
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'tagger',
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
			var options = this.options;
			var $input = $(this.element);

			// fallback to 'tagger-url' attribute
			if(options.url == undefined) {
				options.url = $input.data('tagger-url');
			}
			if(options.url == undefined || options.url.length == 0) {
				console.log('tagger: could not resolve fetch URL');
				return;
			}

			// tagging suggestions
			$input.on('keyup', function(e) {
				var value = $input.val();
				var caret = this.selectionStart;

				// get between start of string and caret
				var before = value.substr(0, caret);

				// get last '@' in this substring
				var tagStart = before.lastIndexOf('@');
				var term = before.substr(tagStart + 1);
				var isNewline = (term.indexOf("\n") != -1 || term.indexOf("\r") != -1);

				// checkpoint:
				// - @ present before caret
				// - no new line character between @ and caret
				// - at least 1 character in term
				if(isNewline || term.length == 0 || tagStart == -1) {
					return;
				}

				// nullify throttle
				if($input.data('throttle')) {
					clearTimeout($input.data('throttle'));
					$input.data('throttle', null);
				}

				// start new throttle
				var t = setTimeout(function() {
					var url = options.url;

					// ajax
					$.ajax(url, {
						data: {
							term: term
						},
						success: function(json) {
							var $list = $input.parents('.tagger').find('.tagger__results');
							$list.addClass('--active');
							$list.find('li:not(.--template)').remove();
							for(var i in json.entities) {
								var $template = $list.find('li.--template').clone();
								var entity = json.entities[i];
								$template.removeClass('--template');
								$template.data('id', entity.id);
								$template.data('slug', entity.slug);
								//$template.find('.entity__icon').attr('src', entity.profile_picture_tiny);
								$template.find('.entity__icon').attr('src', entity.profile_picture_small);
								$template.find('label').text(entity.name);
								$list.append($template);
							}
						}
					});
				}, 250);

				// keep track
				$input.data('throttle', t);
			});

			$input.on('blur', function() {
				$input.data('caret-start', this.selectionStart);
			});

			// selecting suggestion
			$input.parents('.tagger').find('.tagger__results').on('click', '.entity', function(e) {
				var value = $input.val();
				var slug = $(this).data('slug');

				var caret = $input.data('caret-start');

				// get between start of string and caret
				var before = value.substr(0, caret);

				// get last '@' in this substring
				var tagStart = before.lastIndexOf('@');

				// split string into 3 parts, replace second part, then join
				var first = value.substr(0, tagStart + 1);
				var term = before.substr(tagStart + 1);
				var third = value.substr(caret);

				// get final value
				value = first + slug + third;

				// get new caret position
				caret = (first + slug).length;

				// set value and caret
				$input.val(value);
				plugin.setCaretPosition($input.get(0), caret);
			});

			$(document).click(function() {
				$input.parents('.tagger').find('.tagger__results').removeClass('--active');
			})
		},

		setCaretPosition: function(elem, caretPos) {
			var range;

			if (elem.createTextRange) {
				range = elem.createTextRange();
				range.move('character', caretPos);
				range.select();
			} else {
				elem.focus();
				if (elem.selectionStart !== undefined) {
					elem.setSelectionRange(caretPos, caretPos);
				}
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
 * twitter.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for pinterest
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'twitter',
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
			$(this.element).data('platform', pluginName);
		},

		validate: function(image, message, attachment, social) {
			var plugin = this;
			var fullMessage = plugin.getMessageFromParams(image, message, attachment, social);

			// get max character count based on criteria:
			// - subtract 24 characters if attachments present
			// - subtract 3 characters if links present
			var charCount = 140;
			if(image !== undefined && attachment !== undefined && (attachment.type == 'image' || attachment.type == 'video')) {
				//charCount -= 24; // SP: Commented out as part of changing Twitter's policy for media uploads to its platform
			}
			if(message.indexOf('http://') != -1 || message.indexOf('https://') != -1) {
				//charCount -= 3; // SP: Commented out as part of changing Twitter's policy for media uploads to its platform
			}

			// if gone over, show error dialog
			if(fullMessage.length > charCount) {
				return {
					error: 'Twitter character count breached',
					dialog: 'twitter-correct-post',
					callback: function($dialog, $trigger, image, message, attachment, social) {

						// store trigger
						$('.dialog').data('twitter-post-trigger', $trigger);

						// populate textarea
						var fullMessage = plugin.getMessageFromParams(image, message, attachment, social);
						$dialog.find('textarea').val(fullMessage);

						// set character count
						$('.dialog').data('twitter-max-length', charCount);

						// toggle submit
						$dialog.find('.confirm-selection').addClass('--disabled');

						// below here will only trigger once
						var $content = $('#twitter-correct-post');
						if($content.data('initialised')) {
							// update character counter
							$('.dialog').find('#textarea_message_write_tw').trigger('input');
							return;
						}
						$content.data('initialised', true);

						// updating twitter post
						$('.dialog').on('click', '.confirm-selection', function(e) {
							if($(this).parents('.dialog').find('#textarea_message_write_tw').length == 0) {
								return;
							}
							var $input = $('<input name="social[twitter][message]" type="hidden" />');
							$input.val($('.dialog').find('textarea').val());
							var $trigger = $('.dialog').data('twitter-post-trigger');
							$trigger.find('input[name=social\\[twitter\\]\\[message\\]]').remove();
							$trigger.append($input);
							$trigger.trigger('select');

							// submit form again to chain validation or submit
							var $button = $trigger.parents('form').find('.button[type=submit]');
							if($button.length) {
								$button.trigger('click');
							} else {
								$trigger.parents('form').trigger('submit');
							}
						});

						// checking character count
						$('.dialog').on('input change blur', '#textarea_message_write_tw', function(e) {
							var message = $(this).val();
							var counter = $('.dialog').data('twitter-max-length') || 140;
							$('.dialog').find('.confirm-selection').toggleClass('--disabled', message.length > counter);
						});

						// init character counter
						$dialog.find('.character-counter').characterCounter();
						$dialog.find('.character-counter').data('plugin_characterCounter').refresh();
					}
				};
			}
		},

		getMessageFromParams: function(image, message, attachment, social) {
			var appendedAttachment = '';
			var fullMessage = message;
			if(social == undefined) {
				if(attachment !== undefined && ((attachment.url !== undefined && message.indexOf(attachment.url) === -1) || (attachment.shortened_url !== undefined && message.indexOf(attachment.shortened_url) === -1))) {
					fullMessage = fullMessage.replace(' ' + attachment.url, '').replace(attachment.url, '');
					appendedAttachment = attachment.shortened_url || '';
					appendedAttachment = appendedAttachment.length == 0 ? attachment.url : appendedAttachment;
					fullMessage += ' ' + appendedAttachment;
				}
				else if(image !== undefined && message.indexOf(image) === -1) {
					//fullMessage += ' ' + image;
				}
			}
			else {
				fullMessage = social.message;
			}
			return fullMessage;
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
 * youtube.js
 */

//https://github.com/jquery-boilerplate/jquery-patterns/blob/master/patterns/jquery.basic.plugin-boilerplate.js
/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */
/**
 * Plugin for youtube
 */
;(function($, window, document, undefined) {

	// Create the defaults once
	var pluginName = 'youtube',
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
			$(this.element).data('platform', pluginName);

			var plugin = this;

			// activate
			$(this.element).on('click', function(e) {
				e.preventDefault();

				// mark as loading to ensure it doesn't load twice
				var loading = $(plugin.element).data('youtube-loading');
				if(loading === true) {
					return;
				}
				$(plugin.element).data('youtube-loading', true);

				// submit form
				plugin.showOptions();
			});
		},

		showOptions: function() {
			var plugin = this;
			var element = this.element;

			// get dialog object, we'll open this later
			var $dialog = $('#' + $(this.element).data('dialog-content'));
			if($dialog.data('loaded')) {
				$(plugin.element).data('youtube-loading', false);
				plugin.initDialog($dialog, element);
				return;
			}

			// ajax to return youtube options
			if($(this.element).data('get-youtube-categories').length) {
				$.ajax($(this.element).data('get-youtube-categories'), {
					method: 'GET',
					success: function(json) {
						if(json.categories == undefined) {
							return;
						}

						// mark dialog as loaded
						// this should throttle back future api requests
						$dialog.data('loaded', true);

						// populate categories
						var $list = $dialog.find('#social_youtube_category');
						$list.empty();
						for(var v in json.categories) {
							var text = json.categories[v];
							$list.append('<option value="' + v + '">' + text + '</option>');
						}
						plugin.initDialog($dialog, element);
					},
					complete: function() {
						// mark as loading to ensure it doesn't load twice
						$(plugin.element).data('youtube-loading', false);
					}
				});
			}
		},

		initDialog: function($dialog, element) {

			// show dialog
			$('.dialog').trigger('show', [$dialog]);
			$('.dialog').data('youtube-categories-trigger', $(element));

			if($dialog.data('initialised')) {
				return;
			}
			$dialog.data('initialised', true);

			// selecting options
			$('.dialog').on('click', '.confirm-selection', function(e) {
				var $trigger = $('.dialog').data('youtube-categories-trigger');
				var $name     = $('<input type="hidden" name="social[youtube][title]" />').val($('.dialog').find('input[name=social\\[youtube\\]\\[title\\]]').val());
				var $category = $('<input type="hidden" name="social[youtube][category]" />').val($('.dialog').find('select[name=social\\[youtube\\]\\[category\\]]').val());
				$trigger.find('input[name=social\\[youtube\\]\\[name\\]]').remove();
				$trigger.find('input[name=social\\[youtube\\]\\[category\\]]').remove();
				$trigger.append($name);
				$trigger.append($category);
				$trigger.trigger('select');
			});

			// cancel
			$('.dialog').on('click', '.cancel-selection', function(e) {
				var $trigger = $('.dialog').data('youtube-categories-trigger');
				$trigger.find('input[name=social\\[youtube\\]\\[title\\]]').remove();
				$trigger.find('input[name=social\\[youtube\\]\\[category\\]]').remove();
				$trigger.trigger('deselect');
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
//# sourceMappingURL=types.js.map
