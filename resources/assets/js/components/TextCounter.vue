<template>
	<div class="counter-container">
		<textarea class="input-field" 
			:readonly="readonly"
			@input="input($event)" 
			@paste="input($event)"
			ref="text" 
			:placeholder="placeholder" 
			:value="text"
			v-if="!contentEditable">
		</textarea>
		<div class="input-field contenteditable" contenteditable="true"
			@input="input($event)"
			@paste="input($event)"
			ref="text"
			:readonly="readonly"
			:placeholder="placeholder"
			:v-html="text"
			v-else>
		</div>
		<span v-if="showCounter" :class="textAlign">
			<span class="small" v-if="!target">{{ counterText }}</span>
			<portal v-else :target-el="target">
				{{ counterText }}
			</portal>
		</span>
	</div>
</template>
<script>
import PortalVue from 'portal-vue'
import Vue from 'vue'
import autosize from 'autosize'
Vue.use(PortalVue)
export default {
	name: 'TextCounter',
	props: {
		variableSize: {
			type: Boolean,
			default: true
		},
		readonly: Boolean,
		contentEditable: Boolean,
		max: {
			type: Number,
			default: 255
		},
		textAlign: {
			type: String,
			default: 'text-left'
		},
		autosize: {
			type: Boolean,
			default: true
		},
		placeholder: {
			type: String
		},
		target: String,
		counterSuffix: {
			type: String,
			default: " characters left"
		},
		type: {
			type: String,
			default: 'decrement'
		},
		showCounter: {
			type: Boolean,
			default: true
		},
		preText: String, // set explicitly,
		onComplete: {
			type: Function,
			default: function () {

			}
		},
		parentText: String
	},
	data () {
		return {
			text: '',
			preTextSet: false,
			isChanged: false,
			validTargets: {
				'#wi-linkedin-counter' : 'ideas_linkedin',
				'#wi-pin-counter' : 'ideas_pinterest',
				'#wi-yt-counter' : 'ideas_youtube',
				'#wi-in-counter' : 'ideas_instagram',
				'#wi-twitter-counter' : 'ideas_twitter',
				'#wi-facebook-counter' : 'ideas_facebook',
			}
		}
	},
	watch: {
		preText (value) {
			if (!this.preTextSet && value && !this.text) {
				this.text = value
				this.preTextSet = true

				this.$nextTick( () => {
					this.update()
				})				
			}
		},
		parentText (value) {
			if(!this.isChanged){
				this.$refs.text.innerText = value;
				this.text = value;
			}
		},

		text (value) {
			if(this.$parent.$options._componentTag === "social-post" && this.$parent.$parent.originalText == this.getPreFillText(this.$parent.platformName)) {
				this.$parent.form.message = value;
			}

			if(this.$parent.name === "CreateGig") {
				if(Object.keys(this.validTargets).indexOf(this.target) > -1 && !this.isChanged) {
					let platform = this.validTargets[this.target];
					this.$parent.form[platform] = value;
				}
			}
		}
	},

	beforeDestroy() {
		// this.$refs.text.removeEventListener('focus', this.toggleIosInputFocusBodyClass)
		// this.$refs.text.removeEventListener('blur', this.toggleIosInputFocusBodyClass)
	},

	mounted () {
		this.$nextTick(() => {
			// listen for global events to fix the ios issue: https://stackoverflow.com/questions/20963742/positionfixed-footer-in-iphone-ios
			// this.$refs.text.addEventListener('focus', this.toggleIosInputFocusBodyClass)
			// this.$refs.text.addEventListener('blur', this.toggleIosInputFocusBodyClass)

			if (this.autosize) {
				autosize(this.$refs.text, {
					append: false
				})
				
				if (this.variableSize && this.$refs.text)
					this.$refs.text.style.height = '42px'
				
				this.text = this.preText
				
				if(this.$parent.name === "CreateGig") {
					if(Object.keys(this.validTargets).indexOf(this.target) > -1) {
						if(this.text == this.$parent.form.ideas){
							this.isChanged = false;
						}else{
							this.isChanged = true;
						}
					}
				}

				if (this.contentEditable) {
					this.$refs.text.innerText = this.preText

					if(this.$parent.$options._componentTag === "social-post") {
						if (this.isPrefillMatch()) { 
							this.isChanged = false;

							// bind changes on main box
							this.$refs.text.innerText = this.parentText;
							this.text = this.parentText;
						}else{ 
							this.isChanged = true;
						}
					}
				}
				else {
					if (this.$refs.text) {
						this.$refs.text.value = this.preText
					}
				}

				// Call the update method to recalculate the size:
				this.update()
			}
		})
	},
	methods: {
		input ($event) {
			const target = $event.target
			// if it's a content editable div. get $.text() instead of .value
			const value = this.contentEditable ? target.innerText : target.value
			this.text = value

			if (this.showCounter && (this.text && this.text.length > this.max)) {
				this.text = value.slice(0,-1)
				this.$emit('input', this.text)
				return false
			}

			this.checkChange();

			this.$emit('input', this.text)
		},
		clear() {
			this.text = null
		},
		update () {
			autosize.update(this.$refs.text)
		},
		getPreFillText (platform) {
			let gig = this.$store.state.Authoring.gig
			return gig ? gig["ideas_" + platform] : null
		},
		checkChange () { 
			if(this.$parent.$options._componentTag === "social-post" && this.$parent.$parent.originalText == this.getPreFillText(this.$parent.platformName)) {
				if(this.text == this.$parent.current.message) { 
					this.isChanged = false; 
				}else{ 
					this.isChanged = true; 
				} 
			}

			if(this.$parent.name === "CreateGig") {
				if(Object.keys(this.validTargets).indexOf(this.target) > -1) {
					if(this.text == this.parentText){
						this.isChanged = false;
					}else{
						this.isChanged = true;
					}
				}
			}
		},
		isPrefillMatch () {
			if (this.$parent.$parent.originalText == this.getPreFillText(this.$parent.platformName)) { 
				return true;
			}

			return false;
		}
	},
	computed: {
		counterText () {
			if (this.type === 'increment')
				return this.text ? this.text.length : 0

			let current = this.max - (this.text ? this.text.length : 0)
			return `${current}${this.counterSuffix}`
		}
	}
}
</script>
<style>
.counter-container textarea {
	position: relative;
	resize: vertical;
}
.counter-container span {
	position: relative;
	right: 0;
	bottom: 0;
	color: #999999;
}
</style>