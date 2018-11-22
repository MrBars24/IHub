<template>
	<div id="display-area">
		<div class="container">
			<div class="row" v-if="isHubManager">
				<gig-review-alert />
				<div class="pull-right text-right col-lg-3 col-md-4 visible-lg visible-md create-gig-holder">
					<router-link :to="{name: 'gigs.new'}"
						class="btn-submit js-branding-button">
						<i class="fa fa-plus"></i> Create Gig
					</router-link>
				</div>
				<div class="col-md-12 text-center hidden-md hidden-lg create-gig-holder lower">
					<router-link :to="{name: 'gigs.new'}"
						class="btn-submit js-branding-button button-for-mobile">
						<i class="fa fa-plus"></i> Create Gig
					</router-link>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12 gig-carousel-container">
					<div class="alert alert-danger" v-if="errors.length">
						<span v-for="(error,index) in errors" :key="index">{{ error.message }}</span>
					</div>
					<div class="alert alert-success" v-if="successMessage">
						{{ successMessage.message }}
					</div>
					<section class="center slider row">
						<div class="col-xs-12">
							<div class="text-center" v-if="loaders.gigs || loaders.initializingLayout">
								<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
							</div>

							<p class="no-post text-center" v-if="noGigsFound && !isHubManager">
								You’re all done! There’s nothing for you to share right now.
							</p>
						</div>

						<slick class="gigs-container" ref="gigs" @breakpoint="slickBreakpoint"
							v-show="!loaders.initializingLayout" @afterChange="sliderAfterChange">
							<gig v-for="(gig, index) in gigs" :can-edit="role == 'hubmanager'"
								:key="gig.id" :index="index" @gig-ignored="gigIgnored" :gig="gig"
								:is-carousel-mode="isCarouselMode" @gig-clamp-toggled="gigClampToggled">
							</gig>
						</slick>

					</section>
				</div><!-- /gig-carousel-container -->
			</div>

			<div class="row" v-if="showLoadMoreButton">
				<div class="col-md-12 text-center">
					<button type="button" class="btn-submit js-branding-button"
						@click="loadExpiredGigs" :disabled="loaders.fetching_expired_gigs">
						<i v-if="loaders.fetching_expired_gigs" class="fa fa-spin fa-spinner"></i> Load more
					</button>
				</div>
			</div>

		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import Slick from '../components/SlickSlider.vue'
import SplashScreen from '../components/SplashScreen.vue'
import GigItem from '../components/gigs/carousel/Gig.vue'
import GigReviewAlert from '../components/gigs/ReviewAlert.vue'
import mixinHub from '../mixins/hub'
import mixinGigs from '../mixins/gig'
import mixinUser from '../mixins/user'
import matchHeight from 'jquery-match-height'
import Vue from 'vue'
import imagesLoaded from 'imagesloaded'

export default {
	name: 'GigCarousel',
	
	components: {
		Gig: GigItem,
		Slick,
		GigReviewAlert,
		SplashScreen
	},

	mixins: [mixinHub, mixinGigs, mixinUser],

	data () {
		return {
			loaders: {
				gigs: false,
				fetching_expired_gigs: false,
				initializingLayout: false
			},
			errors: [],

			lastGigAttachmentLoaded: false,
			matchHeightOptions: {
				property: 'height',
				byRow: true
			},

			expiredPagination: {
				next_page_url: null
			},
			hasMoreGigs: false,
			reachedLast: false,

			isCarouselMode: false, // default is desktop mode,
			layoutMode: 'grid',
			lastLayoutMode: 'grid', // grid | carousel
			imagesLoad: null, // images loaded
		}
	},

	mounted () {
		$.fn.matchHeight._beforeUpdate = this.handleMatchHeightBeforeUpdate
		$.fn.matchHeight._afterUpdate = this.handleMatchHeightAfterUpdate
		$.fn.matchHeight._throttle = 80
		$.fn.matchHeight._maintainScroll = true;
		
		if(this.init && this.hub.id) {
			this.fetchGigs()
		}

		this.isCarouselMode = window.innerWidth < 768
	},

	beforeRouteLeave (to, from, next) {
		$.fn.matchHeight._beforeUpdate = this.noop
		$.fn.matchHeight._afterUpdate = this.noop
		this.clearGigs()
		if (this.imagesLoad) {
			this.imagesLoad.off('always', this.allImagesLoaded)
		}
		next()
	},

	destroy () {
		$.fn.matchHeight._beforeUpdate = this.noop
		$.fn.matchHeight._afterUpdate = this.noop
		this.clearGigs()
		if (this.imagesLoad) {
			this.imagesLoad.off('always', this.allImagesLoaded)
		}
	},

	watch: {
		'$route' (value) {
			this.loaders.gigs = true
			// only unslick on change hub
			// clear the hubs on store ?
			this.clearGigs()
			let refGig = this.$refs.gigs
			if (refGig && $(refGig.$el).hasClass('slick-initialized')) {
				refGig.destroy()
			}
			console.log(`change hub: ${value.params.hub_slug}`)
			this.fetchGigs()
		},

		init (value) {
			if (value) {
				this.fetchGigs()
			}
		},

		successMessage (value) {
			if (value) {
				setTimeout (() => {
					this.successMessage = false
				}, 2000)
			}
		},
		isCarouselMode (isCarouselMode) {
			this.layoutMode = isCarouselMode ? 'carousel' : 'grid'
		}
	},

	computed: {
		noGigsFound () {
			return !this.gigs.length && !this.loaders.gigs && this.init && !this.loaders.initializingLayout
		},
		
		successMessage: {
			get ( value ) {
				if (!this.$route.params.success) return false
				return {
					message: this.$route.params.success.message
				}
			},
			set (value) {
				delete this.$route.params.success
				return
			}
		},

		showLoadMoreButton () {
			return this.isHubManager && 
				!this.reachedLast && 
				this.hasMoreGigs &&
				!this.loaders.initializingLayout &&
				!this.loaders.gigs
		},
	},
	
	methods: {
		clearGigs () {
			this.$store.commit('setGigs', {
				gigs: []
			})
		},

		/** 
		 * fired before any jquery events
		 * here we can apply any animations because the gigs is 
		 * matching its height from each other
		 * 
		 * @param jQuery.event              jqEvent
		 * @param $.fn.matchHeight._groups  group
		 * @return void
		 */
		handleMatchHeightBeforeUpdate (event, group) {
			this.loaders.initializingLayout = true
		},

		handleMatchHeightAfterUpdate (event, group) {
			this.loaders.initializingLayout = false
			this.isCarouselMode = window.innerWidth < 768
			// only change when needed
			if (event && event.type === 'resize') {
				// let canChangeLayout = isCarouselMode
				// if (!this.isCarouselMode) {
				// 	this.updateHeights('handleMatchHeightAfterUpdate')
				// }
				// else {
				// 	this.resetLayout()
				// 	this.initializeLayout() // remove matchheight bindings instantly
				// }
			}
		},

		/**
		 * do something on change break point here.
		 */
		slickBreakpoint (event, slick, breakpoint) {
			// if (breakpoint >= 768) { 
			// 	this.initializeLayout()
			// }
		},

		/**
		 * @param {Event}  event
		 * @param {Object} slick object that contains slick DOM
		 * @param {Number} currentSlide
		 * @param {Number} nextSlide
		 */
		sliderAfterChange(event, slick, currentSlide, nextSlide) {
			// get the gig id of current slide
			let $current = slick.$slides.filter('.slick-current')
			let gigId = $current.length ? $current.find('.gig-item').data().gigId : undefined
			this.$bus.$emit('gig-carousel-change', gigId)
		},
		
		gigIgnored (index) {
			this.gigs.splice(index, 1)

			if (this.isCarouselMode) {
				let currentSlide = this.$refs.gigs.currentSlide()
				this.$refs.gigs.goTo(currentSlide)
				this.$refs.gigs.remove(currentSlide)
				console.log(currentSlide, index)
			}
			else {
				setTimeout(() => {
					this.updateHeights()
				}, 500) // gig animation duration
			}
		},

		/**
		 * re-initialize the layout once the images has been completely loaded
		 */
		allImagesLoaded (instance ) {
			if (this.isCarouselMode) {
				this.$refs.gigs.reSlick()
			}
			else {
				this.updateHeights()
			}
		},

		gigClampToggled () {
			// update heights in desktop mode
			if (!this.isCarouselMode) {
				this.updateHeights()
			}
			else {
				// update height property of .slick-list
				let $slickList = $(this.$refs.gigs.$el).find('.slick-list')
				$slickList.height('auto')
			}
		},

		fetchGigs () {
			this.loaders.gigs = true
			let hub = {
				slug: this.$route.params.hub_slug
			}
			if (!hub.slug) {
				hub = this.hub
			}

			// if it's still null, then wait until it's called again by watchers
			if (!hub.slug) {
				return
			}

			this.$store.dispatch('getGigs', hub)
				.then(response => {
					this.loaders.gigs = false
					this.loaders.initializingLayout = true

					if (this.isHubManager) {
						this.hasMoreGigs = response.data.data.more_gigs_count > 0
					}
					// initialize layout
					setTimeout(() => {
						this.initializeLayout()
					}, 500)
				})
				.catch(error => {
					this.errors.push(error)
					this.loaders.gigs = false
				})
		},

		// append the expired gigs for hubmanager.
		loadExpiredGigs () {
			this.loaders.fetching_expired_gigs = true

			if (this.isCarouselMode) {
				this.loaders.initializingLayout = true
			}

			let payload = {
				hub: this.hub,
				url: this.expiredPagination.next_page_url
			}
			this.$store.dispatch('getExpiredGigs', payload)
				.then(response => {
					this.loaders.fetching_expired_gigs = false
					let pagination = _.omit(response, 'data')
					this.expiredPagination = pagination

					// destroy the slick slider first
					if (this.isCarouselMode) {
						this.$refs.gigs.destroy()
					}

					this.$store.commit('appendExpiredGigs', response.data)

					this.$nextTick(() => {
						// since we can't do a slickAdd bacause we can't use Vue.compile 
						// without having to use the Vue full build. we will just re-initialize the slick slider.
						// https://github.com/staskjs/vue-slick/issues/16
						this.initializeLayout()
					})

					if (!pagination.next_page_url && !this.reachedLast) {
						this.reachedLast = true
					}
				})
				.catch(error => {
					console.error(error)
					this.loaders.fetching_expired_gigs = false
				})
		},
		
		// /**
		//  * append the gigs to existing layout
		//  * 
		//  * @param {array} gigs
		//  */
		// appendExpiredGigsToLayout (gigs) {
		// 	if (this.isCarouselMode) {
		// 		Vue.compile(`<gig :can-edit="${role == 'hubmanager'}">

		// 		</gig>`)
		// 	}
		// },

		/** 
		 * determine which layout will the app use.
		 */
		initializeLayout () {
			this.imagesLoad = imagesLoaded('.gig-carousel-container .gig-item')
			this.imagesLoad.on('always', this.allImagesLoaded)
			if (this.isCarouselMode) {
				this.initializeCarouselLayout()
			}
			else {
				this.initializeGridLayout()
			}
		},

		initializeCarouselLayout () {
			setTimeout(() => {
				this.$refs.gigs.create()
				this.loaders.initializingLayout = false
				let hasGigs = this.gigs.length > 0
				this.$bus.$emit('gig-carousel-initialized', hasGigs)
			}, 500)
		},

		/**
		 * initialize grid layout, trigger matchHeight plugins
		 */
		initializeGridLayout () {
			let refGig = this.$refs.gigs

			// initialize matchHeight plugin
			let $gigs = $(refGig.$el).find('.gig-item-inner')
			$gigs.matchHeight(this.matchHeightOptions)

			// wait .5 seconds before updating the heights.
			setTimeout(() => {
				this.updateHeights()
				this.loaders.initializingLayout = false
				let hasGigs = this.gigs.length > 0
				this.$bus.$emit('gig-carousel-initialized', hasGigs)
			}, 500)
		},

		/**
		 * intialize heights of gig elements
		 * @param {boolean} initialized
		 */
		updateHeights () {
			let refGig = this.$refs.gigs
			let $gigs = $(refGig.$el).find('.gig-item-inner')
			$.fn.matchHeight._apply($gigs.find('.js-gig-title'), this.matchHeightOptions)
			$.fn.matchHeight._apply($gigs.find('.js-gig-body'), this.matchHeightOptions)
			$.fn.matchHeight._apply($gigs, this.matchHeightOptions)
			$.fn.matchHeight._update()
		},

		resetLayout () {
			let refGig = this.$refs.gigs
				// destroy the matchHeight plugin
			let $gigs = $(refGig.$el).find('.gig-item-inner')
			$gigs.matchHeight({ remove: true })
		}
	},
}
</script>