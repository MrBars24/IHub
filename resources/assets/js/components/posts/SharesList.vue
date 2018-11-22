<template>
  <div class="modal fade" id="modalSharesList" ref="modalSharesList" 
		tabindex="-1" role="dialog" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">Shares out to social media</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 shares-container">

							<div class="text-center">
								<i v-if="loaders.fetching" class="fa fa-spinner fa-pulse fa-fw"></i>
							</div>

							<transition-group appear name="fade">
								<platform-post v-for="post in list"
									:post="post"
									:key="post.id">
								</platform-post>
							</transition-group>
							
							<infinite-scroll v-if="state.infinite && !reachedLast" 
								@infinite="loadOlderSharesList">
								<div slot="spinner" class="text-center">
									<i class="fa fa-spinner fa-pulse fa-fw"></i>
								</div>
								<div slot="no-more" class="text-center">
									Limit reached!.
								</div>
							</infinite-scroll>

						</div>
					</div>

				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</template>
<script>
import InfiniteScroll from 'vue-infinite-loading'
import ApiPost from '../../api/post'
import mixinHub from '../../mixins/hub' 
import PlatformPost from '../newsfeed/PlatformPost.vue'

export default {
	name: 'SharesList',

	mixins: [mixinHub],

	components: {
		InfiniteScroll,
		PlatformPost
	},

	data () {
		return {
			loaders: {
				fetching: false
			},
			list: [],
			postId: -1,
			state: {
				infinite: false
			},
			reachedLast: false,
			pagination: {
				next_page_url: null
			}
		}
	},

	mounted () {
		this.$bus.$on('show-shares-list', this.showModal)
		$(this.$el).on('show.bs.modal', this.fetchShareList)
		$(this.$el).on('hidden.bs.modal', this.onModalHidden)
	},

	beforeDestroy () {
		this.$bus.$off('show-shares-list', this.showModal)
		$(this.$el).modal('hide')
		$(this.$el).off('show.bs.modal', this.fetchShareList)
		$(this.$el).off('hidden.bs.modal', this.onModalHidden)
	},

	beforeRouteLeave () {
		this.$bus.$off('show-shares-list', this.showModal)
		$(this.$el).modal('hide')
		$(this.$el).off('show.bs.modal', this.fetchShareList)
		$(this.$el).off('hidden.bs.modal', this.onModalHidden)		
	},

	methods: {
		// Events

		/**
		 * revert the initial state to default
		 */
		onModalHidden () {
			this.loaders.fetching = false
			this.postId = -1
			this.list = [],
			this.state.infinite = false
			this.reachedLast = false
			this.pagination = {
				next_page_url: null
			}
		},

		/**
		 * trigger modal
		 */
		showModal (postId) {
			this.postId = postId
			$(this.$el).modal('show')
		},

		/**
		 * get the initial platform posts of the post.
		 */
		fetchShareList() {
			console.log('fetchShareList')
			this.loaders.fetching = true
			this.fetch()
				.then(response => {
					this.list = response.data
					this.pagination = _.omit(response, 'data')
					
					// start the infinite scrolling
					this.state.infinite = true
					this.loaders.fetching = false
					console.log(this.state.infinite)
					
					if (!this.pagination.next_page_url) {
						this.reachedLast = true
					}
				})
				.catch(error => {
					this.loaders.fetching = false
				})
		},

		/** 
		 * load old shares list
		 */
		loadOlderSharesList ($state) {
			if (this.reachedLast)
				return

			this.fetch()
				.then(response => {
					this.list = this.list.concat(response.data)
					let pagination = _.omit(response, 'data')
					this.pagination = pagination
					$state.loaded()
					if (!pagination.next_page_url) {
						this.reachedLast = true
					}
				})
				.catch(error => {
					this.reachedLast = true
					$state.loaded()
				})
		},

		/** 
		 * fetch shares list api
		 */
		fetch () {
			const apiPost = new ApiPost(this.hub)

			return new Promise((resolve, reject) => {
				apiPost.getSharesList(this.postId, this.pagination.next_page_url)
					.then(response => {
						let shares = response.data.data.shares
						resolve(shares)
					})
					.catch(error => reject(error))
			})
			
		}
	},
}
</script>

