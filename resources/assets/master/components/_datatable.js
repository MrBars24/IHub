/**
 * datatable.js
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
	var pluginName = 'datatable',
		defaults = {
			searchablePrefix: 'searchable',
			orderablePrefix:  'orderable',
			labelClass:       'cell-label',
			pagination:       true,
			paginationBefore: '', // in case you need to move pagination element position next to element
			// Expecting: dataset, columns, row, modifyRow
		};

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

			// orderable/searchable
			$(this.element).find('thead th').each(function(index) {
				var $header = $(this);
				var $cell = $header.addClass('cell');
				var label = $($header.contents()[0]).text().trim();
				var $label = $('<div></div>');
				$label.addClass(plugin.options.labelClass);
				$label.text(label);
				var $orderable  = $header.find(':input[name^=' + plugin.options.orderablePrefix + ']');
				var $searchable = $header.find(':input[name^=' + plugin.options.searchablePrefix + ']');

				// rebuild cell
				$header.text('');
				$cell.prepend($label);
				$cell.prepend($orderable);
				$cell.prepend($searchable);

				// wrap
				if($header.find(':input').length) {
					var $filters = $('<div class="cell-filters"></div>');
					$header.append($filters);
					$header.find(':input').appendTo($filters);
				}

				// insert order triggers
				if($orderable.length) {
					$label.addClass(plugin.options.orderablePrefix);
					var name = $orderable.attr('name');
					$('<a class="order order-asc" href="" data-direction="asc" data-variable="' + name + '"><i class="fa fa-caret-up"></i></a>').appendTo($cell);
					$('<a class="order order-desc" href="" data-direction="desc" data-variable="' + name + '"><i class="fa fa-caret-down"></i></a>').appendTo($cell);
				}

				// insert search triggers
				if($searchable.length) {
					$label.addClass(plugin.options.searchablePrefix);
					$('<a class="search button toggle"><i class="fa fa-search"></i></a>').appendTo($cell);
				}

				// add column classes
				var column = plugin.options.columns[index] || '';
				$header.addClass(column);
			});

			// table header row: toggle searchable field
			$(this.element).on('click', 'thead th .button.search', function(e) {
				e.preventDefault();
				$(this).parents('th').toggleClass(plugin.options.searchablePrefix + '-visible');
				$(this).parents('th').find(':input:visible').focus();
			});

			// table header row: hide searchable field
			$(this.element).on('blur', 'thead th :input', function(e) {
				e.preventDefault();
				$(this).parents('th').removeClass(plugin.options.searchablePrefix + '-visible');
			});

			// table header row: order
			$(this.element).on('click', 'thead th .order', function(e) {
				e.preventDefault();

				// reset other orderables
				$element.find('input[name^=' + plugin.options.orderablePrefix + ']').val('');

				var direction = $(this).data('direction');
				$(this).parents('th').find('input[name^=' + plugin.options.orderablePrefix + ']').val(direction);
				plugin.search(); // execute the actual search
			});

			// table header row for searchable columns
			// any <th> that has a "searchable" class, will then locate input field that will be used to search in data table
			$(this.element).on('input', 'thead th :input', function() {

				// Throttle search to only search after user stops typing
				var waitTime = 200;
				if($element.data('autocomplete') !== null) {
					clearTimeout($element.data('autocomplete'));
				}
				// not fetching data
				if(!$element.hasClass('fetching-data')) {
					var t = setTimeout(function() {
						plugin.search(); // execute the actual search
						$element.data('autocomplete', null);
					}, waitTime);
					$element.data('autocomplete', t);
				}
				// instant search
				else if(instant != undefined && instant == true) {
					plugin.search(); // execute the actual search
					$element.data('autocomplete', null);
				}
			});

			// pagination
			$(this.element).on('click', '.pagination a', function(e) {
				e.preventDefault();
				var page = $(this).attr('href').replace('?', '').replace('page=', '');
				plugin.search({ page: page }); // execute the actual search
			});

			// populate table
			if(this.options.dataset != undefined) {
				plugin.populate(this.options.dataset.data || this.options.dataset);
			}

			// generate pagination
			if(this.options.pagination && this.options.dataset != undefined && this.options.dataset.data != undefined) {
				plugin.paginate(this.options.dataset);
			}

			$(window).resize(function() {
				plugin.resizeRowLinks();
			});

			// indicate activated
			$(this.element).addClass('--interactive');
		},

		populate: function(data) {
			var plugin = this;
			var $element = $(this.element);

			// empty first
			plugin.empty();

			// get all rows
			var rows = [];
			for(var i in data) {
				var item = data[i];
				rows.push(this.options.row(item));
			}

			// build elements
			var $body = $element.find('tbody');
			for(var i in rows) {
				var row = rows[i];
				var item = data[i];
				var $row = $('<tr></tr>').appendTo($body);
				for(var j in row) {
					var cell = row[j];
					var column = this.options.columns[j] || '';
					var $cell = $('<td></td>').appendTo($row);
					$cell.html(cell);
					$cell.addClass(column);
				}

				// modify row if callback is defined
				if(this.options.modifyRow != undefined) {
					var modifiers = this.options.modifyRow(item);
					if(modifiers) {
						for(var m in modifiers) {
							var attr  = m;
							var value = modifiers[m];
							$row.attr(attr, value);
						}
					}
				}
			}

			plugin.resizeRowLinks();
		},

		empty: function() {
			var plugin = this;
			var $element = $(this.element);

			// get tbody
			var $body = $element.find('tbody');
			$body.empty();
		},

		search: function(params) {
			var plugin = this;
			var $element = $(this.element);

			if(params == undefined) {
				params = {};
			}

			// get fields for table header
			params = $.extend(params, $element.find('thead').parse(function(input) {
				return $(input).val() !== '';
			}));

			// get url, this will do for now
			var url = window.location.href;

			$.ajax(url, {
				data: params,
				success: function(json) {
					var dataset = json[plugin.options.datasetKey];
					plugin.populate(dataset.data);
					if(dataset != undefined) {
						plugin.populate(dataset.data);
						plugin.paginate(dataset);
					} else {
						plugin.empty();
					}
				}
			});
		},

		paginate: function(paginated) {
			var plugin = this;
			var $element = (this.paginationBefore === '') ? $(this.element) : $(this.options.paginationBefore);

			var range = 4;
			var step = 3;
			var first = Math.max(paginated.current_page - range, 1);
			var curr  = paginated.current_page;
			var last  = Math.min(paginated.current_page + range, paginated.last_page);

			var i;
			$element.find('.pagination').remove();
			var $pagination = $('<ul class="pagination">').insertAfter($element);

			// prev
			if(curr != first) {
				i = Math.max(curr - step, 1);
				$('<a class="pagination-prev" href="?page=' + i + '">' + '‹' + '</a>').appendTo($pagination);
			}
			// next placeholder
			else {
				$('<span class="pagination-placehold">' + '‹' + '</span>').appendTo($pagination);
			}

			// numbers
			for(i = first; i <= last; i++) {
				var $page = $('<a class="pagination-page" href="?page=' + i + '">' + i + '</a>').appendTo($pagination);
				$page.toggleClass('current', i == curr);
			}

			// next
			if(curr != last) {
				i = Math.min(curr + step, paginated.last_page);
				$('<a class="pagination-next" href="?page=' + i + '">' + '›' + '</a>').appendTo($pagination);
			}
			// next placeholder
			else {
				$('<span class="pagination-placehold">' + '›' + '</span>').appendTo($pagination);
			}
		},

		resizeRowLinks: function() {
			var plugin = this;
			var $element = $(this.element);
			$element.find('a[data-rowlink]').width($element.width());
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