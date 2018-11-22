<template>
	<div class="modal fade" ref="modal" tabindex="-1" role="dialog" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" 
					class="close" 
					@click="cancel"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">{{ modalTitle }}</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="cropper-image-container">
							<img id="image" ref="image">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" @click="cancel" class="btn-post --default">Close</button>
				<button type="button" @click="getCroppedCanvas" class="btn-post js-branding-button">Use this Image</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</template>
<script>
import Cropper from 'cropperjs'
export default {
	props: {
		options: {
			type: Object,
			default () {
				return {
					aspectRatio: 1,
					viewMode: 1
				}
			}
		},
		modalTitle: {
			type: String,
			required: true
		},
		aspectRatio: {
			type: Number,
			default: 1
		}
	},

	data () {
		return {
			showModal: false,

			cropper: null,
			cropBoxData: null,
			cachedCropBoxData: null
		}
	},

	mounted () {
		this.$nextTick(() => {
			$(this.$el).on('shown.bs.modal', this.initCropper)
				.on('hidden.bs.modal', this.hideModal)
		})
	},

	beforeDestroy () {
		$(this.$el).off('shown.bs.modal', this.initCropper)
			.off('hidden.bs.modal', this.hideModal)
	},

	methods: {
		getCroppedCanvas () {
			if (!this.cropper) 
				return
			
			// cropped canvas
			let croppedImage = this.cropper.getCroppedCanvas().toDataURL('image/jpeg')

			// crop settings
			let cropSettings = this.cropper.getData()
			let imageData = _.pick(this.cropper.getImageData(), ['naturalWidth', 'naturalHeight'])
			Object.assign(cropSettings, imageData)
			
			this.$emit('cropped-image', {
				path: croppedImage, 
				settings: _.mapValues(cropSettings, Math.round)
			})
			$(this.$el).modal('hide')
		},

		cancel () {
			this.$emit('cropped-image', null)
			$(this.$el).modal('hide')
		},

		showCropper (imageSrc, settings = null) {
			this.$refs.image.src = imageSrc
			this.cropBoxData = settings
			$(this.$el).modal('show')
		},

		initCropper () {
			let img = this.$refs.image
			this.cropper = new Cropper(img, {
				aspectRatio: this.aspectRatio,
				background: true,
				autoCropArea: 1,
				viewMode: 3,
				zoomable: false,
				rotatable: false,
				scalable: false,
				dragMode: 'move',
				checkCrossOrigin: false,
				checkOrientation: false,
				ready: () => {
					if (this.cropBoxData) {
						this.cropper.setData(this.cropBoxData)
					}
				}
			})
		},

		reInit () {
			this.destroy()
			this.initCropper()
		},

		// Methods
		hideModal () {
			this.destroy()
		},

		destroy () {
			this.cropper.destroy()
			this.$refs.image.src = null
			this.cropBoxData = null
			// $(this.$el).modal('hide')
		},
	}
}
</script>
<style>
.cropper-image-container img {
	max-width: 100% !important;
}
</style>