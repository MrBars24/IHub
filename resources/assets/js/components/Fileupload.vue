<template>
	<div class="form-field">
		<j-cropper ref="cropper" :modal-title="modalTitle" :aspect-ratio="aspectRatio" 
			@cropped-image="onCroppedImage" v-if="croppable">
		</j-cropper>
		<div class="custom-file">
			<input type="file" 
				:accept="accept"
				:id="nameId" 
				ref="inputFile"
				@change="onChange"
				class="inputfile"/>
			<label :for="nameId">
				<span class="input-label">{{ inputLabel }} <span v-if="!File">&hellip;</span></span>
				<span @click.prevent.stop="editCrop" 
					v-if="showCropEdit" class="crop-button pull-right" title="Crop Picture">
					<i class="fa fa-crop"></i>
				</span>
			</label>
		</div>
		<slot name="progress">
			<transition name="fade">
				<div class="progress" v-show="isUploading">
					<div class="progress-bar progress-bar-striped active"
						ref="progressBar"
						role="progressbar" 
						:aria-valuenow="progress" 
						aria-valuemin="0" 
						aria-valuemax="100">
						<span class="sr-only">{{ progress }}% Complete</span>
					</div>
				</div>
			</transition>
		</slot>
	</div>
</template>
<script>
import axios from 'axios'
import JCropper from './JCropper.vue'
import FileApi from '../api/file'
export default {
	props: {
		label: {
			type: String,
			default: 'Choose a file'
		},
		uploadUrl: {
			type: String,
			required: false
		},
		accept: {
			type: String,
			default: 'image/*'
		},
		size: {
			type: [String, Number],
			default: Number.MAX_SAFE_INTEGER
		},
		isUsingDefault: Boolean,
		croppable: {
			type: Boolean,
			default: false
		},
		nameId: {
			type: String,
			required: true
		},
		modalTitle: {
			type: String
		},
		aspectRatio: {
			type: Number,
			default: 1
		},
		originalImage: {
			type: String,
			default: null
		},
		cropBoxData: {
			type: [Object, String]
		}
	},

	components: {
		JCropper
	},

	data () {
		return {
			File: null,
			progress: 0,
			loaders: {
				uploading: false
			},
			uploadSuccessful: true,
			// cached rendered image
			renderedImage: {
				path: null,
				type: 'blob'
			},
			// croppedData
			croppedData: null
		}
	},

	methods: {
		/// Events

		onChange ($e) {
			let inputFile = $e.target
			
			if (!inputFile.files.length) {
				this.File = null
				// revert to original file
				this.$emit('rendered-image', {
					path: this.originalImage, // could be nulled value
					type: 'string'
				})
				this.clear() 
				return
			}

			this.File = _.head(inputFile.files)

			if (!this.isFileAccepted) return // validate mime type

	 		this.render(this.File)
	 			.then(response => {
	 				let result = response.target.result
	 				let image = {
	 					path: result,
	 					type: 'render',
	 				}
					this.renderedImage = image

					// show cropper if croppable
	 				if (this.croppable) {
						this.showCropper(result)
						return
					}

	 				this.$emit('rendered-image', image, true)
	 			})
		},

		editCrop () {
			// prioritized the browser cached image
			if (this.renderedImage.path) {
				this.showCropper(this.renderedImage.path, this.cropBoxData)
			}
			else {
				this.showCropper(this.originalImage, this.cropBoxData)
			}
		},

		showCropper (result, settings = null) {
			this.$refs.cropper.showCropper(result, settings)
		},

		onCroppedImage (croppedImage) {
			// if `use this image`. use the cropped image
			if (croppedImage) {
				this.croppedData = croppedImage
				this.$emit('cropped-image', this.croppedData)
				this.croppedData = null
			}
			else {
				// if cancelled, use the cached image even if it's null
				this.$emit('rendered-image', !this.originalImage ? {
					path: null,
					type: 'string'
				} : this.renderedImage)
				// clear the cached browser image
				this.clear()
			}
		},

		// Methods
		upload () {
			if (!this.File) 
				return
			
			this.loaders.uploading = true
			this.uploadSuccessful = true

			this.progress = 0

			const _file = new FileApi()

			const config = {
				onUploadProgress: (progressEvent) => {
					var percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total )
					this.progress = percentCompleted
				}
			}

			return new Promise((resolve, reject) => {
				_file.upload(this.File, config)
					.then(response => {
						this.loaders.uploading = false
						this.clear()
						resolve(response)
					})
					.catch(error => {
						this.uploadSuccessful = false
						this.clear()
						reject(error)
					})
			})
		},

		clear () {
			this.File = null
			this.$refs.inputFile.value = null
			this.renderedImage = {
				path: null,
				type: 'blob'
			}
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

	watch: {
		progress (value) {
			this.$refs.progressBar.style.width = value + '%'
		},
		uploadSuccessful (value) {
			let progressBar = this.$refs.progressBar
			if (!progressBar.classList.contains('progress-bar-danger') && !value) {
				progressBar.classList.add('progress-bar-danger')
				progressBar.classList.remove('progress-bar-striped')
				progressBar.classList.remove('active')
			}
			else {
				progressBar.classList.remove('progress-bar-danger')
				progressBar.classList.add('active')
				progressBar.classList.add('progress-bar-striped')
			}
		}
	},

	computed: {
		inputLabel () {
			return this.File ? this.File.name : this.label
		},
		isFileAccepted () {
			if (!this.File)
				return false

			let acceptedMimeTypes = this.accept.split(';')
			let fileMime = this.File.type

			let isAccepted = acceptedMimeTypes.filter(mime => fileMime.match(mime))
			return Boolean(isAccepted)
		},
		showCropEdit () {
			return this.croppable && Boolean(this.File || this.originalImage) && !this.isUsingDefault
		}
	}
}
</script>
<style scoped type="sass">
	.progress {
		margin-top: 10px;
	}
</style>