<template>
	<div>
		<slot name="preview" :attachment="form.file"></slot>
		<input type="file" @change="change" :accept="accept" :multiple="multiple" ref="uploader"
			:class="{ hidden : inputHidden}">
	</div>
</template>

<script>
/**
 * New ABSTRACT version of file uploader component 
 * that we will use across the app and in other projects as well.
 * and will have the following features:
 * - indeterminate/determinate progress for all file uploads (loading status).
 * - multiple uploads
 * - slotted template for loading status
 * - file validation including size, type
 * - pre-rendering of image type upload
 * - API agnostic :)
 * - image processing like image resizing before uploading it to server?.. (useful for turtle net users.)
 * - cancellable uploads.
 */
</script>

<script>
export default {
	name: 'FileUploader',

	props: {
		maxSize: {
			type: [String, Number],
			default: Number.MAX_SAFE_INTEGER
		},
		accept: String,
		multiple: Boolean,
		change: {
			type: Function,
			required: true
		},
		inputHidden: {
			type: Boolean,
			default: true
		},
		cancellable: Boolean
	},

	data () {
		return {
			form: {
				file: {
					type: null
				}
			}
		}
	},

	methods: {
		/** 
		 * NOTE: make this an event
		 */
		processFile ($event) {
			let target = $event.target;
			if (!target.files.length && !target.files[0]) {
				return
			}

			const fileApi = new FileApi()

			for (let i = 0; i < target.files.length; i++) {

			}
		},

		/**
		 * opens the file browser
		 */
		open () {
			this.$refs.uploader.click()
		}
	}
}
</script>

