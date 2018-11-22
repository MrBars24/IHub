<template>
<div id="display-area" class="post-instagram">
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
				<div class="text-center" v-if="loaders.fetching && !loaders.checked">
					<i class="fa fa-spinner fa-pulse"></i>
				</div>

				<div v-if="!loaders.fetching && instagram.ready && instagram.data">
					<div class="inactive-post" v-if="instagram.data.inactive">
						<h2>Link no longer active</h2>
						<p>
							This link is no longer active. <router-link :to="{ name: 'newsfeed' }">Back to newsfeed</router-link>
						</p>
					</div>

					<div class="instagram-installed" v-else>
						<div v-if="!instagram.installed">
							<h2>Instagram not installed</h2>
							<p>You must use a mobile device with Instagram installed to post to Instagram.</p>
						</div>
						<div v-else>
							<h2>Open Instagram</h2>
							<p>You are about to share this post to Instagram. Tap <strong>"Open"</strong> to open the Instagram app. You must have the Instagram app installed on your device.</p>
							<p>The post content will be copied to your device clipboard so you can paste it in Instagram.</p>
							<button class="btn-submit" @click="shareToInstagram" :disabled="loaders.sharing">
								<i v-show="loaders.sharing" class="fa fa-spinner fa-pulse"></i> Open
							</button>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div><!-- /container -->
</div><!-- /display-area -->
</template>

<script>
	// TODO:
	// fetch the post here that will dispatch to Instagram.
	// detect if instagram is installed on the device.
	// 
import ApiPost from '../api/post'
import mixinHub from '../mixins/hub'

export default {
	name: 'PostInstagram',

	mixins: [mixinHub],

	data () {
		return {
			loaders: {
				fetching: true,
				sharing: false,
				checked: false
			},
			instagram: {
				ready: false,
				installed: false,
				error: null,
				data: null
			}
		}
	},

	watch: {
		'$route': 'fetchInstagramPost',
		init (value) {
			if (value) {
				this.fetchInstagramPost()
			}
		}
	},

	mounted () {
		if (this.init) {
			this.fetchInstagramPost()
		}
		
	},

	methods: {
		checkInstagram () {
			// BUG?: Instagram object is always undefined.
			if (typeof(Instagram) == 'undefined') {
				return
			}

			// check if installed
			setTimeout(() => { // trick use by other developers to make it async.
				Instagram.isInstalled((error, installed) => {
					this.loaders.checked = true // show the instagram installed status
					this.instagram.installed = Boolean(installed) // string representation of installed Instagram version or null
					this.instagram.error = Boolean(error) // "Application not installed" or null
				})
			}, 0)
		},

		/**
		 * Invoke instagram plugin
		 */
		shareToInstagram () {
			if (this.instagram.installed) {
				this.loaders.sharing = true
				// copy the content to clipboard
				let data = this.instagram.data
				cordova.plugins.clipboard.copy(data.content)

				let media = data.queue.attachment.media_path
				// convert to base64 image to pass on Instagram bridge, then share the image to Instagram via cordova bridge
				this.getDataUri(media, this.share)
				
			}
		},

		attemptShareInstagram () {
			const apiPost = new ApiPost(this.hub)
			let post_id = this.$route.params.post_id
			let item_id = this.$route.params.item_id
			apiPost.instagramPostSharing(post_id, item_id)
				.then(response => {
					console.log('marked the instagram attempts to 100')
				})
				.catch(error => console.error(error))
		},

		share (dataUrl) {
			// https://github.com/vstirbu/InstagramPlugin/issues/31
			Instagram.share(dataUrl, error => { // callback executes too soon.
				this.loaders.sharing = false

				// attempt to mark the PostDIspatchQueueItem to set attempts to 100 regardless of the Instagram.share callback response.
				this.attemptShareInstagram()

				// error callback parameter is always "Share Cancelked."

				setTimeout(() => {	
					this.$router.replace({
						name: 'gigs.carousel'
					})
				}, 5000)
				
				// if (error) {
				// 	// determine what type of error, if failed posting to instagram, 
				// 	// then flag the postdispatchqueueitem as failed?.. 
				// }
				// else {
				// 	// flag the postdispatchqueueitem as success.
				// }
			})
		},

		getDataUri (url, cb) {
			let image = new Image()

			image.onload = function() {
				let self = this
				let canvas = document.createElement('canvas')
				canvas.width = self.naturalWidth
				canvas.height = self.naturalHeight
				canvas.getContext('2d').drawImage(self, 0, 0)
				let dataUrl = canvas.toDataURL('image/png')
				cb(dataUrl)
			}
			image.src = url
		},

		/**
		 * fetch the instagram post
		 */
		fetchInstagramPost () {
			this.instagram.ready = false
			this.loaders.fetching = true

			const apiPost = new ApiPost(this.hub)

			let post_id = this.$route.params.post_id
			let item_id = this.$route.params.item_id

			apiPost.getInstagramPost(post_id, item_id)
				.then(response => {
					this.loaders.fetching = false
					this.instagram.ready = true
					this.instagram.data = response.data
					// only check the instagram if installed once the PostDispatchQueueItem is fetched.
					this.checkInstagram() // should move in the pgapp.js of pg repo.
				})
				.catch(error => {
					this.loaders.fetching = false
				})
		}
	}
}
</script>