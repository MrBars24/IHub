<template>
<div>
	<shares-list />
	<alert-post-unpublish />
	<alert-post-report />
	<div id="display-area" class="newsfeed-page">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-6 col-md-offset-3">
					<div class="text-center" v-if="loaders.post">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>

					<item v-if="post.id && !loaders.post" :post="post" :initialComments="true"></item>

				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import mixinHub from '../mixins/hub'
import mixinPost from '../mixins/post' 
import Post from '../components/newsfeed/Post.vue'
import SharesList from '../components/posts/SharesList.vue'
import AlertPostReport from '../components/posts/AlertPostReport.vue'
import AlertPostUnpublish from '../components/posts/AlertPostUnpublish.vue'

export default {
	name: 'Post',

	mixins: [mixinPost, mixinHub],

	components: {
		'item': Post,
		SharesList,
		AlertPostReport,
		AlertPostUnpublish
	},

	data () {
		return {
			loaders: {
				post: false
			}
		}
	},

	mounted () {
		if (this.init) {
			this.fetchPost()
		}
	},

	watch: {
		'$route': 'fetchPost',
		init (value) {
			if (value) {
				this.fetchPost()
			}
		}
	},

	methods: {
		fetchPost () {
			console.log('fetchPost')
			this.loaders.post = true
			this.$store.dispatch('getPost', {
				post_id: this.$route.params.post_id,
				hub: this.hub
			}).then(response => {
				this.loaders.post = false
			})
		}
	}
}
</script>