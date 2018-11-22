<template>
	<div :class="[classes, 'up-media']">
		<button class="btn-remove" type="button" v-if="!isViewing && !isAttaching && !isUploading" 
			@click="remove">
		</button>

		<div :class="['gig-attachment', generalAttachmentTypeClass, isUploadingClass]">
			<div class="preloader" v-if="isUploading">
				<div class="preloader-icon">
					<i class="preloader-icon fa fa-video-camera" v-if="attachmentType == 'video'"></i>
					<i class="preloader-icon fa fa-image" v-else-if="attachmentType == 'image'"></i>
				</div>
				<div class="preloader-progress" :style="uploadProgressStyle"></div>
			</div>
			<!-- image type -->
			<img :src="attachmentSource" v-if="attachmentSource && attachmentType.match(/image|link/)" />
			<!-- video type but is creating|editing -->
			<img v-else-if="attachmentType === 'video' && !isViewing" 
				:src="resolveStaticAsset('/images/video_thumbnail.png')" />
			<!-- video type -->
			<video controls v-else-if="attachmentType === 'video' && isViewing"> 
				<source :src="attachmentSource" />
				Your browser does not support the video tag.
			</video>

			<!-- upladed video from youtube -->
			<div class="embed-responsive embed-responsive-16by9" 
				v-else-if="attachmentType === 'youtube'">
				<iframe id="youtubePlayer" class="embed-responsive-item"
					type="text/html" height="360" :src="attachment.media_path"
					frameborder="0">
				</iframe>
			</div>

			<!-- upladed video from vimeo -->
			<div class="embed-responsive embed-responsive-16by9"
				v-else-if="attachmentType === 'vimeo'">
				<iframe id="vimeoPlayer" class="embed-responsive-item"
					:src="attachment.media_path" height="366" frameborder="0"
					webkitAllowFullScreen mozallowfullscreen allowFullScreen>
				</iframe>
			</div>

			<!-- support video and link attachment meta -->
			<div class="attachment-meta clearfix" v-if="attachmentType.match(/link|youtube|vimeo/)">
				<h4 class="attachment-title">{{ attachment.title }}</h4>
				<p class="attachment-description">{{ truncatedDescription }}</p>
				<div class="attachment-source">{{ attachment.source }}</div>
			</div><!-- /attachment-meta -->

		</div><!-- /gig-attachment -->
		<div class="gig-attachment-general-type text-right" v-if="showGeneralAttachmentType">
			{{ generalAttachmentType }}
		</div><!-- /gig-attachment-general-type -->
	</div>
</template>
<script>
export default {
	props: {
		index: Number,
		isViewing: {
			type: Boolean,
			default: true
		},
		ignoreClasses: Boolean,
		isAttaching: Boolean,
		attachment: {
			type: Object,
			required: true
		},
		showGeneralAttachmentType: Boolean,
		attachmentsCount: Number
	},
	methods: {
		remove() {
			this.$emit("remove", {
				id: this.isNew ? undefined : this.attachment.id, // make sure to pass an undefined id
				index: this.index
			});
		}
	},

	computed: {
		uploadProgressStyle () {
			if (this.isUploading) {
				return {
					width: this.attachment.upload_progress + '%'
				}
			}
		},

		generalAttachmentTypeClass () {
			return '--' + this.generalAttachmentType
		},
		
		isUploadingClass() {
			return this.isUploading ? '--uploading' : ''
		},

		isUploading() {
			return this.attachment.is_uploading && this.isNew
		},

		isNew() {
			return !this.attachment.id;
		},

		attachmentType() {
			return this.attachment.type;
		},

		generalAttachmentType() {
			if (this.attachmentType === 'vimeo' || this.attachmentType === 'youtube') {
				return 'link'
			}
			return this.attachmentType
		},

		classes() {
			if (this.ignoreClasses) return "";

			let gridClass = this.isViewing
				? this.attachmentsCount > 1 ? "col-xs-6" : "col-xs-12"
				: "col-md-3 col-xs-6 col-sm-4";
			return gridClass;
		},

		attachmentSource() {
			return this.attachmentType === "image" && !this.attachment.id
				? this.attachment.path
				: this.attachment.media_path_thumb;
		},

		truncatedDescription() {
			if (!this.attachment) return;
			return _.truncate(this.attachment.description);
		}
	}
};
</script>