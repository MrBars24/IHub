<template>
<div>
	<shares-list />
	<alert-post-unpublish />
	<alert-post-report />
	<div id="display-area" class="profile-page-post">
		<div class="bg-white-outer"></div>
		<div class="container">
			<div class="bg-white-inner"></div>
			<div class="row">
				<div class="col-md-12">
					<div class="content-area">

						<div class="text-center" v-if="loaders.profile">
							<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
						</div>

						<p class="no-post text-center" v-if="!posts.length && profileUser.name">
							{{ profileUser.name }} has not made any posts yet.
						</p>
						<!-- populate the posts -->

						<transition-group appear name="fade">
							<post v-for="post in posts" 
								:post="post"
								:key="post.id">
							</post>
						</transition-group>

					</div>
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
export default {
	name: 'ProfilePost',

	mixins: [mixinHub, mixinPost],

	components: {
		Post,
		SharesList,
		AlertPostReport,
		AlertPostUnpublish
	},

	data () {
		return {
			loaders: {
				profile: false
			},
			isOnMediumDevice: false
		}
	},

	mounted () {
		if (this.init) {
			this.fetchUserData()
		}
		this.onResize()
		window.addEventListener('resize', _.debounce(this.onResize, 500))
	},

	beforeDestroy () {
		window.removeEventListener('resize', this.onResize)
	},

	watch: {
		'$route': 'fetchUserData',
		init (isInitialized)  {
			if (isInitialized) {
				this.fetchUserData()
			}
		},
		isOnMediumDevice (value) {
			if (value) {
				this.$router.replace({
					name: 'profile.home'
				})
			}
		}
	},

	computed: {
		profileUser () {
			return this.$store.state.Profile.user
		},
	},

	methods: {
		onResize () {
			this.isOnMediumDevice = window.innerWidth >= 992
		},
		fetchUserData () {
			console.log('fetchUserData')
			this.loaders.profile = true
			this.$store.dispatch('fetchUserData', {
				hub: this.hub,
				user: {
					slug: this.$route.params.user_slug
				}
			})
			.then(response => {
				this.loaders.profile = false
			})
		}
	}
}
</script>