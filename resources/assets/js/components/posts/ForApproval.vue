<template>
	<div class="gig-post-scheduled">
		<div class="detail-box">
			<div class="body">
				<button class="btn-submit btn-cancel" @click="cancelPost"
					:disabled="loaders.cancelling">
					<i v-show="loaders.cancelling" 
						class="fa fa-spinner fa-pulse fa-fw"></i> Cancel Post
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import ApiMyGig from '../../api/mygigs'
import mixinHub from '../../mixins/hub'
import moment from 'moment'

export default {
	mixins: [mixinHub],

	props: {
		post: {
			type: Object,
			required: true
		}
	},

	data () {
		return {
			loaders: {
				cancelling: false
			}
		}
	},

	methods: {
		cancelPost () {
			this.loaders.cancelling = true

			const apiMyGig = new ApiMyGig(this.hub)

			let payload = {
				post_id: this.post.id,
				gig_id: this.post.gig_id
			}

			apiMyGig.cancelPost(payload)
				.then(response => {
					this.loaders.cancelling = false
					this.$emit('post-cancelled', response.data.data.gig_post)
				})
				.catch(error => {
					console.error(error)
					this.loaders.cancelling = false
				})
		}
	},
}
</script>