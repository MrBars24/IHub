<template>
	<div class="uploader">
		<label for="fileUpload" @click.stop.prevent="$refs.inputFile.click()">
			<i class="btn-upload" title="Upload Attachment" slot="label">
				<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('clipboard')" width="13px" height="13px" />
			</i>
		</label>

		<input type="file" id="fileUpload" :accept="accept" ref="inputFile" class="hidden" 
			@change="change">
	</div>		
</template>
<script>
import FileApi from '../api/file'
export default {
	props: {
		accept: {
			type: String,
			default: 'image/*'
		},
		immediateRender: {
			type: Boolean,
			default: false
		},
		immediateUpload: {
			type: Boolean,
			default: true
		}
	},
	data () {
		return {
			File: null,
			isUploading: false,
			progress: 0,
			colorFill: '#636b6f'
		}	
	},
	mounted () {
		this.$bus.$on('image-preview-removed', this.imagePreviewRemoved)
	},
	beforeDestroy () {
		this.$bus.$off('image-preview-removed', this.imagePreviewRemoved)
	},
	methods: {
		imagePreviewRemoved () {
			this.File = null
			this.$refs.inputFile.value = null
		},
		change ($e) {
			let inputFile = $e.target			
			if (!inputFile.files.length && !inputFile[0]) {
				this.File = null
				if (this.immediateRender)
					this.$emit('rendered-image', null)
				return
			}

			this.File = _.head(inputFile.files)

			if (!this.isFileAccepted)	return

			if (this.immediateRender) {
				this.render(this.File)
		 			.then(response => {
		 				let result = response.target.result
		 				this.$emit('rendered-image', result) // encoded image
		 			})
			}

			if (this.immediateUpload) {
				this.upload()
			}
		},

		upload () {
			this.isUploading = true
			this.progress = 0

			const fileApi = new FileApi()
			return new Promise((resolve, reject) => {
				fileApi.upload(this.File, {
					onUploadProgress: this.onUploadProgress
				})
				.then(response => {
					this.isUploading = false
					let file = response.data.data.file
					// clean mime type
					let type = file.type.split('/')[0]
					file.type = type
					file.media_path = file.full_path
					// fix the media_path
					file.media_path = file.media_path;
					file.media_path_large = file.media_path;
					file.media_path_thumb = file.media_path;
					this.$emit('uploaded', file)
				})
			})
		},

		// upload hook events
		onUploadProgress (progressEvent) {
			// emit??
			let percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
			this.$emit('uploading', percentCompleted)
			this.progress = percentCompleted
		},	

		// helpers
		render (file) {
			return new Promise((resolve, reject) => {
				const Reader = new FileReader()
				Reader.onload = (e) => resolve(e)
				Reader.onerror = (e) => reject(e)
				Reader.readAsDataURL(file)
			})
		}
	},
	computed: {
		isFileAccepted () {
			let acceptedMimeTypes = this.accept.split(';')
			let fileMime = this.File.type

			let isAccepted = acceptedMimeTypes.filter(mime => fileMime.match(mime))
			return Boolean(isAccepted)
		}
	}
}
</script>