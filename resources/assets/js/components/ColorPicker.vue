<template>
	<div class="color-picker-container">
		<input type="text" ref="text" @input="input" :value="text" maxlength="7">
		<input type="color" id="color-picker" ref="picker" />
	</div>
</template>
<script>
require('spectrum-colorpicker')

export default {
	props: {
		value: String
	},

	data () {
		return {
			text: null,
			$picker: null
		}
	},

	computed: {
		isValidHex () {
			return this.isValid()
		},
	},

	beforeDestroy () {
		this.destroy()
		// this.$refs.text.removeEventListener('focus', this.toggleIosInputFocusBodyClass)
		// this.$refs.text.removeEventListener('blur', this.toggleIosInputFocusBodyClass)
	},

	mounted () {
		this.create()
		// this.$nextTick(() => {
		// 	this.$refs.text.addEventListener('focus', this.toggleIosInputFocusBodyClass)
		// 	this.$refs.text.addEventListener('blur', this.toggleIosInputFocusBodyClass)
		// })
	},

	methods: {
		isValid () {
			return /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(this.text)
		},
		create () {
			const $picker = $(this.$refs.picker)
			$picker.on('change', this.change)
			$picker.spectrum({
				color: this.value,
				showButtons: false
			})

			this.$picker = $picker
		},

		destroy () {
			const $picker = $(this.$refs.picker)
			$picker.on('change', this.change)      
			$picker.spectrum("destroy")

			this.$picker = null
		},

		change (e, color) {
			this.text = color.toHexString()
			this.$emit('input', this.text)      
		},

		input () {
			let text = this.$refs.text.value
			this.text = text

			if (this.isValidHex)
				this.$picker.spectrum("set", this.text)
				
			this.$emit('input', this.text)
		}
	},

	watch: {
		value (value) {
			if (value && !this.text) {
				this.text = value
				this.$picker.spectrum("set", value)
			}
		}
	}
}
</script>

<style src="spectrum-colorpicker/spectrum.css"></style>
<style>
.color-picker-container {
	position: relative;
}
.color-picker-container .sp-replacer {
	position: absolute;
	top: 12px;
	right: 0;
	padding: 2px;
}
</style>

