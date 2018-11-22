import store from '../store'
import { bodyClass, title } from '../routes/helper'

export default {
	computed: {
		init() {
			return store.state.isInitialized;
		},

		noop() {
			return () => {}
		},

		// global message
		'$message' () {
			let $message = store.state.route.params.$message
			if (!$message)
				return
			
			let type = $message.type
			$message.class = `alert ${type === 'error' ? 'alert-danger' : 'alert-success'}`;
			return $message
		},

		/**
		 * check if app is running in native applicatin / cordova enabled.
		 * 
		 * @return {Boolean}
		 */
		isNativeApp () {
			return Boolean(window.cordova)
		}
	},

	mounted () {
		// listen for all input's focus and blur events
		this.$nextTick(() => {
			let self = this
			$(self.$el).find('input, textarea, div[contenteditable]')
				.on('blur', self.toggleIosInputFocusBodyClass)
				.on('focus', self.toggleIosInputFocusBodyClass)
		})
	},

	beforeDestroy () {
		let self = this
		$(self.$el).find('input, textarea, div[contenteditable]')
			.off('blur', self.toggleIosInputFocusBodyClass)
			.off('focus', self.toggleIosInputFocusBodyClass)
	},

	methods: {
		/**
		 * adds .ios-input-focus class to body element.
		 */
		toggleIosInputFocusBodyClass (e) {
			e.stopPropagation()
			if (!document.body.classList.contains('platform-ios')) {
				return
			}

			// cannot use toggle because of the bubbling of events
			if (e.type == 'focus') {
				document.body.classList.add('ios-input-focus')
			}
			else if (e.type == 'blur') {
				document.body.classList.remove('ios-input-focus')
			}
		},

		resolveStaticAsset (file) {
			return resolveStaticAsset(file)
		},
		
		getParameters () {
			var query = window.location.search.substring(1);
			var vars = query.split("&");
			var query_string = {};
			
			for (var i = 0; i < vars.length; i++) {
				var pair = vars[i].split("=");
				// If first entry with this name
				if (typeof query_string[pair[0]] === "undefined") {
					query_string[pair[0]] = decodeURIComponent(pair[1]);
					// If second entry with this name
				} else if (typeof query_string[pair[0]] === "string") {
					var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
					query_string[pair[0]] = arr;
					// If third or later entry with this name
				} else {
					query_string[pair[0]].push(decodeURIComponent(pair[1]));
				}
			}

			return query_string;
		},

		getSegment (index) {
			var url = window.location.href.split("/")
			url.shift()
			url.shift()
			url.shift()
			return a[index - 1]
		},

		getSvgPath (name) {
			name = name.toLowerCase()
			return '/images/svg/icon-' + name + '.svg'
		}
	},

	beforeRouteEnter (to, from, next) {
		title(to.meta.title) // set document Title
		bodyClass(to.meta.bodyClass) // set body class -page
		next()
	},

	filters: {
		fromNow(value) {
			if (!value) {
				return
			}
			
			return moment(value).fromNow()
		}
	}
};