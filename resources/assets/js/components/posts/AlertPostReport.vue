<template>
	<div class="modal fade" id="modalPostReport" ref="modalPostReport" 
		tabindex="-1" role="dialog" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">Influencer HUB</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 shares-container">
							<h3>Are you sure you want to report this post?</h3> 
							<p>The hub manager will be notified.</p>
						</div>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn-post js-branding-button" @click="report"
						:disabled="loaders.submitting">
						<i v-if="loaders.submitting" class="fa fa-spinner fa-pulse fa-fw"></i> Confirm
					</button>
					<button type="button" class="btn-post --default" data-dismiss="modal"
						:disabled="loaders.submitting">
						Cancel
					</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</template>

<script>
import ApiPost from "../../api/post"
import mixinHub from '../../mixins/hub'

export default {

	mixins: [mixinHub],

	data () {
		return {
			loaders: {
				submitting: false
			},
			postId: -1
		}
	},
	
	mounted () {
		this.$bus.$on('post:confirm-report', this.showModal)
		$(this.$el).on('hide.bs.modal', this.hideModal)
	},

	beforeDestroy () {
		this.$bus.$off('post:confirm-report', this.showModal)
		$(this.$el).modal('hide')
		$(this.$el).off('hide.bs.modal', this.hideModal)
	},

	beforeRouteLeave () {
		this.$bus.$off('post:confirm-report', this.showModal)
		$(this.$el).modal('hide')
		$(this.$el).off('hide.bs.modal', this.hideModal)
	},

	methods: {
		report () {
			this.loaders.submitting = true
			const apiPost = new ApiPost(this.hub)

			apiPost.report(this.postId)
				.then(response => {
					this.loaders.submitting = false

					// emit post:reported event
					let data = response.data.data
					this.$bus.$emit(`post:reported.${data.post.id}`, data)

					// close modal
					$(this.$el).modal('hide')
				})
				.catch(error => {
					this.loaders.submitting = false
					console.errror(error)
				})
		},

		showModal (postId) {
			// assign postId
			this.postId = postId
			
			// show modal
			$(this.$el).modal('show')
		},

		hideModal () {
			// reset data
			this.postId = -1
		}
	}
}
</script>