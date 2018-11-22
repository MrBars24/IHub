<template>
	<div class="slick-slider" ref="slider">
		<slot></slot>
	</div>
</template>
<script>
require('slick-carousel')
export default {
	props: {
		options: {
			type: Object,
			default() {
				return {
					mobileFirst: true, // set to mobile first
					variableWidth: true,
					centerMode: true,
					centerPadding: '60px',
					slidesToScroll: 1,
					arrows: false,
					adaptiveHeight: true,
					responsive: [
						{
							breakpoint: 767, // 991 - screen-sm
							settings: "unslick"
						}
					],
				};
			},
		},
	},

	// beforeDestroy() {
	//   this.destroy()
	// },

	methods: {
		// resizeSlick () {
		//   if (window.innerWidth < 769) {
		//     if (!this.$refs.slider.classList.contains('slick-initialized')) {
		//       this.reSlick()
		//     }
		//   }
		// },

		reSlick() {
			if (this.$el.classList.contains('slick-initialized')) {
				this.destroy()
				this.create()
			}
		},

		create() {
			const $slick = $(this.$el).not('.slick-initialized');
			$slick.on('afterChange', this.onAfterChange);
			$slick.on('beforeChange', this.onBeforeChange);
			$slick.on('breakpoint', this.onBreakpoint);
			$slick.on('destroy', this.onDestroy);
			$slick.on('edge', this.onEdge);
			$slick.on('init', this.onInit);
			$slick.on('reInit', this.onReInit);
			$slick.on('setPosition', this.onSetPosition);
			$slick.on('swipe', this.onSwipe);
			$slick.on('lazyLoaded', this.onLazyLoaded);
			$slick.on('lazyLoadError', this.onLazyLoadError);
			$slick.on('breakpoint', this.breakpoint);
			$slick.slick(this.options);
		},
		
		destroy() {
			const $slick = $(this.$el);
			$slick.off('afterChange', this.onAfterChange);
			$slick.off('beforeChange', this.onBeforeChange);
			$slick.off('breakpoint', this.onBreakpoint);
			$slick.off('destroy', this.onDestroy);
			$slick.off('edge', this.onEdge);
			$slick.off('init', this.onInit);
			$slick.off('reInit', this.onReInit);
			$slick.off('breakpoint', this.breakpoint);
			$slick.off('setPosition', this.onSetPosition);
			$slick.off('swipe', this.onSwipe);
			$slick.off('lazyLoaded', this.onLazyLoaded);
			$slick.off('lazyLoadError', this.onLazyLoadError);
			$slick.slick('unslick');
		},

		next() {
			$(this.$el).slick('slickNext');
		},
		prev() {
			$(this.$el).slick('slickPrev');
		},
		pause() {
			$(this.$el).slick('slickPause');
		},
		play() {
			$(this.$el).slick('slickPlay');
		},
		goTo(index, dontAnimate) {
			$(this.$el).slick('slickGoTo', index, dontAnimate);
		},
		currentSlide() {
			return $(this.$el).slick('slickCurrentSlide');
		},
		add(element, index, addBefore) {
			$(this.$el).slick('slickAdd', element, index, addBefore);
		},
		remove(index, removeBefore) {
			$(this.$el).slick('slickRemove', index, removeBefore);
		},
		filter(filterData) {
			$(this.$el).slick('slickFilter', filterData);
		},
		unfilter() {
			$(this.$el).slick('slickUnfilter');
		},
		getOption(option) {
			$(this.$el).slick('slickGetOption', option);
		},
		setOption(option, value, refresh) {
			$(this.$el).slick('slickSetOption', option, value, refresh);
		},
		setPosition() {
			$(this.$el).slick('setPosition');
		},
		// Events
		onAfterChange(event, slick, currentSlide) {
			this.$emit('afterChange', event, slick, currentSlide);
		},
		breakpoint(event, slick, breakpoint) {
			this.$emit('breakpoint', event, slick, breakpoint)
		},
		onBeforeChange(event, slick, currentSlide, nextSlide) {
			this.$emit('beforeChange', event, slick, currentSlide, nextSlide);
		},
		onBreakpoint(event, slick, breakpoint) {
			this.$emit('breakpoint', event, slick, breakpoint);
		},
		onDestroy(event, slick) {
			this.$emit('destroy', event, slick);
		},
		onEdge(event, slick, direction) {
			this.$emit('edge', event, slick, direction);
		},
		onInit(event, slick) {
			setTimeout(() => {
				slick.slickGoTo(0)
			}, 100)
			this.$emit('init', event, slick);
		},
		onReInit(event, slick) {
			this.$emit('reInit', event, slick);
		},
		onSetPosition(event, slick) {
			this.$emit('setPosition', event, slick);
		},
		onSwipe(event, slick, direction) {
			this.$emit('swipe', event, slick, direction);
		},
		onLazyLoaded(event, slick, image, imageSource) {
			this.$emit('lazyLoaded', event, slick, image, imageSource);
		},
		onLazyLoadError(event, slick, image, imageSource) {
			this.$emit('lazyLoadError', event, slick, image, imageSource);
		},
	},
}
</script>