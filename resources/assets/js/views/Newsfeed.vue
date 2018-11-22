<template>
<div>
	<!-- shares-list modal -->
	<shares-list />
	<alert-post-unpublish />
	<alert-post-report />
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="text-center write-post" v-if="state.infinite && !loaders.newsfeed">
						<router-link :to="{name:'write'}">Write post</router-link>
					</div>

					<!-- loaders				 -->
					<div class="text-center" v-if="loaders.newsfeed">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
					
					<div class="alert alert-success" v-if="successMessage">
						{{ successMessage.message }}
					</div>

					<div class="alert alert-success" v-if="!posts.length && state.infinite">
						No posts have been made.
					</div>

					<!-- populate newsfeed -->
					<transition-group appear name="fade">
						<post v-for="post in posts"
							:post="post"
							:key="post.id">
						</post>
					</transition-group>

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
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import Post from '../components/newsfeed/Post.vue'
import SharesList from '../components/posts/SharesList.vue'
import AlertPostReport from '../components/posts/AlertPostReport.vue'
import AlertPostUnpublish from '../components/posts/AlertPostUnpublish.vue'
import mixinHub from '../mixins/hub'
import mixinPost from '../mixins/post'
import InfiniteScroll from 'vue-infinite-loading'

export default {
	name: 'Newsfeed',

	mixins: [mixinPost, mixinHub],

	components: {
		Post,
		InfiniteScroll,
		SharesList,
		AlertPostReport,
		AlertPostUnpublish
	},
	
	data () {
		return {
			loaders: {
				newsfeed: false
			},
			state: {
				infinite: false
			},
			reachedLast: false,
		}
	},

	mounted () {
		if (this.init)
			this.fetchPosts()
	},

	watch: {
		'$route': 'fetchPosts',
		init (value) {
			if (value) 
				this.fetchPosts()
		},
		successMessage (value) {
			if (value) {
				console.log(value)
				setTimeout (() => {
					this.successMessage = false
				}, 2000)
			}
		}
	},
	
	computed: {
		successMessage: {
			get ( value ) {
				if (!this.$route.params.success) return false
				return {
					message: this.$route.params.success.message
				}
			},
			set (value) {
				return false;
			}
		}
	},	

	methods: {
		fetchPosts () {
			console.log('fetchPosts')
			this.loaders.newsfeed = true
			this.$store.dispatch('getPosts', this.hub)
				.then(response => {
					this.loaders.newsfeed = false
					this.state.infinite = true
				})
		},
		loadOlderPosts ($state) {
			if (!this.reachedLast) {
			this.$store.dispatch('getOldPosts', this.hub)
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
	},
}
</script>