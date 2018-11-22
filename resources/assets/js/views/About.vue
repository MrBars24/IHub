<template>
<div id="display-area">
	<div id="banner">
		<div class="visible-lg visible-md">
			<img :src="resolveStaticAsset('/images/img-banner.jpg')" alt="Banner" class="img-responsive">
		</div>
		<div class="visible-sm visible-xs">
			<img :src="resolveStaticAsset('/images/img-banner-mobile.jpg')" alt="Banner" class="img-responsive">
		</div>
	</div><!-- /banner -->
	<div class="bg-white-outer"></div>
	<div class="container" v-cloak>
		<div class="bg-white-inner"></div>
		<div class="row" v-if="loaders.profile">
			<div class="text-center">
				<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
			</div>
		</div>
		<div class="row" v-else>
			<div class="profile-info col-md-4">
				<div class="profile-area">
					<div class="profile-summary-area">
						<div class="profile-area-img">
							<a href="#"><img :src="resolveStaticAsset('/images/img-profile-165.jpg')" alt="Profile Image"></a>
						</div><!-- /profile-area-img -->
						<p>{{ user.name }}</p>
						<p><span class="trophy-point">{{ user.points }}</span> <span class="point-txt">POINTS</span></p>
						<p><i>{{ user.summary }}</i></p>
					</div><!-- /profile-area -->

					<div class="social-media-area">
						<ul class="sm-top row">
							<li class="col-xs-4 col-md-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-fb.png')" alt="Facebook">
									0 <span>FRIENDS</span>
								</a>
							</li>
							<li class="col-xs-4 col-md-4 col-md-offset-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-twitter.png')" alt="Twitter">
									0 <span>FOLLOWERS</span>
								</a>
							</li>
							<li class="col-xs-4 col-md-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-linkedin.png')" alt="Linkedin">
									0 <span>FOLLOWERS</span>
								</a>
							</li>
							<li class="col-xs-4 col-md-4 col-md-offset-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-pinterest.png')" alt="Pinterest">
									0 <span>FOLLOWERS</span>
								</a>
							</li>
							<li class="col-xs-4 col-md-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-youtube.png')" alt="Youtube">
									0 <span>CONNECTION</span>
								</a>
							</li>
							<li class="col-xs-4 col-md-4 col-md-offset-4">
								<a href="#">
									<img :src="resolveStaticAsset('/images/icon-instagram.png')" alt="Instagram">
									0 <span>FOLLOWERS</span>
								</a>
							</li>
						</ul>
					</div><!-- /social-media-area -->

					<div class="profile-bottom-area visible-md visible-lg">
						<p>No gigs has been rated</p>
						<p>80% of gigs completed</p>
					</div>

					<router-link v-if="isViewingOther"
						class="btn-full-width visible-md visible-lg"
						:to="{ name: 'message', params: { conversation_id: undefined, user_id: user.id, user_slug: user.slug } }">
						Get in Touch
					</router-link>
				</div>
			</div>

			<div class="profile-newsfeed col-md-6 col-md-offset-1">
				<div class="content-area">

					<!-- populate user posts -->
					<post v-for="post in posts" 
						:post="post"
						:key="post.id"
						:profile="user">
					</post>

				</div>
			</div>
		</div>
	</div><!-- /container -->
</div><!-- /display-area -->
</template>
<script>
import ProfileArea from '../components/profile/ProfileArea.vue'
import SocialMediaArea from '../components/profile/SocialMediaArea.vue'
import Post from '../components/newsfeed/Post.vue'
import mixinHub from '../mixins/hub'
import mixinProfile from '../mixins/profile'

export default {
	mixins: [mixinHub, mixinProfile],
	components: {
		ProfileArea,
		SocialMediaArea,
		Post
	},

	data () {
		return {
			loaders: {
				profile: false
			}
		}
	},

	mounted () {
		// this.fetchUserData()
	},

	watch: {
		'$route': 'fetchUserData'
	},

	computed: {
		isViewingOther () {
			return this.user.id !== this.$store.state.user.id
		}
	},

	methods: {
		fetchUserData () {

			console.log('fetchUserData')
			this.loaders.profile = true
			let user = {
				slug: this.$route.params.user_slug
			}
			setTimeout(() => {
				this.$store.dispatch('fetchUserData', {
					hub: this.hub,
					user: user
				})
					.then(response => {
						this.loaders.profile = false
					})
			}, this.loadInterval)
		}
	}
}
</script>