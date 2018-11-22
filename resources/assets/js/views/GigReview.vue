<template>
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="text-center" v-if="loaders.reviews">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
					<post v-for="post in reviewPosts" 
						:post="post"
						:is-reviewing="true"
						:key="post.id">
					</post>

					<infinite-scroll v-if="state.infinite && !reachedLast" 
						@infinite="loadOlderPosts">
					  <div slot="spinner" class="text-center">
							<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
						</div>
						<div slot="no-more" class="text-center">
							Limit reached!.
						</div>
					</infinite-scroll>

				</div>
			</div>
		</div>
	</div>
</template>
<script>
import mixinHub from '../mixins/hub'
import mixinGig from '../mixins/gig'
import PostComponent from '../components/newsfeed/Post.vue'
import InfiniteScroll from 'vue-infinite-loading'

export default {
	mixins: [mixinHub, mixinGig],
	components: {
		'post': PostComponent,
		InfiniteScroll
	},
	data () {
		return {
			loaders: {
				reviews: true
			},
			state: {
				infinite: false
			},
			reachedLast: false,
		}
	},
	mounted () {
		if (this.init) {
			this.fetchReviews()
		}
	},
	watch: {
		'$route': 'fetchReviews',
		init (value)  {
			if (value) {
				this.fetchReviews()
			}
		}
	},
	methods: {
		fetchReviews () {
			console.log('fetchReviews')
			this.loaders.reviews = true
			this.$store.dispatch('getReviews', this.hub)
				.then(response => {
					this.loaders.reviews = false
					this.state.infinite = true
				})
				.catch(error => {
					this.errors.push(error)
					this.loaders.reviews = false
				})
		},

		loadOlderPosts ($state) {
			console.log('fetching older posts?')
			if (!this.reachedLast) {
				this.$store.dispatch('getOldReviews', this.hub)
					.then(response => {
						$state.loaded()
					})
					.catch(error => {
						console.error(error)
						this.reachedLast = true
						$state.loaded()
					})
			}
		}
	}
}
</script>