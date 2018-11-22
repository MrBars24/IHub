<template>
	<div class="platform-post post-item clearfix">
		<div class="post-meta">
			<div class="post-area">
				<div class="post-info clearfix">
					<a href="#" @click.stop.prevent="noop">
						<img :src="resolveStaticAsset('/images/img-profile-120.gif')" class="pull-left">
					</a>
					<h5 class="post-author">
						<a href="#" @click.stop.prevent="noop">{{ post.params.name }}</a>
						<p class="pull-right post-platform">
							<img :src="platformIcon" :alt="post.platform">
						</p>
					</h5>
					<span class="post-timestamp">
						<a href="#" @click.stop.prevent="noop">
							<span v-if="post.finished_at">{{ post.finished_at | fromNow }}</span>
							<span v-else>Pending</span>
						</a>
					</span>
				</div><!-- /post-info -->
			</div>
		</div><!-- /post-meta -->

		<div class="post-content">
			<p><span v-html="post.params.message"></span></p>
		</div><!-- /post-content -->

		<attachment :attachment="post.attachment" v-if="post.attachment"></attachment>

	</div>
</template>

<script>
import Attachment from '../newsfeed/Attachment.vue'
export default {
	name: 'PlatformPost',

	components: {
		Attachment
	},

	props: {
		post: {
			type: Object,
			required: true
		}
	},

	computed: {
		platformIcon () {
			let platform = this.post.platform === "facebook" ? "fb" : this.post.platform
			return resolveStaticAsset(`/images/icon-${platform}.png`)
		},
	}
}
</script>

