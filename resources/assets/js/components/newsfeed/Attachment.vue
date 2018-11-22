<template>
	<div class="post-attachment">
		<!-- remove attachment -->
		<slot name="remove-attachment"></slot>

		<div class="attachment-body" :data-type="attachmentType">
			<!-- image -->
			<img :src="attachment.media_path_large" 
				v-if="attachmentType.match(/image|link/) && attachment.media_path_large"
				:alt="attachment.title" />
			
			<!-- video type -->
			<video width="320" controls v-else-if="attachmentType === 'video'"> 
				<source :src="attachment.media_path" />
				Your browser does not support the video tag.
			</video>
			<!-- upladed video from youtube -->
			<iframe id="youtubePlayer"
				v-else-if="attachmentType === 'youtube'"
				type="text/html" 
				height="360"
				:src="attachment.media_path"
				frameborder="0">
			</iframe>

			<!-- upladed video from vimeo -->
			<iframe id="vimeoPlayer"
				v-else-if="attachmentType === 'vimeo'"
				:src="attachment.media_path" 
				height="366" 
				frameborder="0"
				webkitAllowFullScreen 
				mozallowfullscreen 
				allowFullScreen>		
			</iframe>
		</div>
		<!-- support video and link attachment meta -->
		<div class="attachment-meta clearfix" 
			v-if="attachmentType.match(/link|youtube|vimeo/)">
			<h4 class="attachment-title">{{ attachment.title }}</h4>
			<p class="attachment-description">{{ truncatedDescription }}</p>
			<span class="pull-right attachment-source">{{ attachment.source }}</span>
		</div><!-- /attachment-meta -->

		<a target="_blank" :class="attachmentLinkCLass" 
			v-if="!attachmentType.match(image|video)"
			:href="attachment.url" 
			:title="attachment.title"></a>
	</div><!-- /post-attachment -->
</template>
<script>
export default {
	name: 'PostAttachment',

	props: {
		attachment: {
			type: Object
		}
	},

	computed: {    
		attachmentType() {
			if (!this.attachment)
				return ''
			return this.attachment.type
		},
		
		attachmentLinkCLass() {
			if (this.attachmentType.match(/image|video/))
				return

			let classes = ['attachment-link']
			if (this.attachmentType.match(/youtube|vimeo/)) {
				classes.push('--videos')
			}
			return classes.join(' ')
		},
		
		truncatedDescription() {
			return _.truncate(this.attachment.description)
		},
		
		/**
		 * get the general attachment type
		 *
		 * @return string link|video|image
		 */
		generalAttachmentType() {
			return this.attachmentType.match(/youtube|vimeo/)
				? "link"
				: this.attachmentType;
		},
	}
}
</script>
