<template>
<div>
	<shares-list />
	<alert-post-unpublish />
	<alert-post-report />
	<div id="display-area">
		<div id="banner">
			<div class="banner-image" :style="bannerClass"></div>
		</div><!-- /banner -->
		<!-- <div class="bg-white-outer" v-if="!loaders.profile" v-cloak></div> -->
		<div class="container" v-if="loaders.profile">
			<div class="row">
				<div class="text-center">
					<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
				</div>
			</div>
		</div>
		<div class="container" id="main-container" v-else>
			<!-- <div class="bg-white-inner" v-if="!loaders.profile"></div> -->
			<div class="row">
				<div class="profile-info col-md-4">
					<div class="profile-area">
						<div class="profile-summary-area">
							<div class="profile-area-img">
								<router-link :to="profileRoute">
									<img :src="profileUser.profile_picture_medium" 
										alt="Profile Image">
								</router-link>			
							</div><!-- /profile-area-img -->
							<p class="profile-name">{{ profileUser.name }}</p>
							<p class="points" v-if="!isHubManager">
								<svg-filler path="/images/svg/icon-points.svg" width="30px" height="30px" :fill="'#f5a194'" />
								<span class="trophy-point" 
									v-if="profileUser.membership">{{ profileUser.membership.points }}</span> 
								<span class="point-txt">
									POINTS
								</span>
							</p>
							<p class="profile-summary">{{ profileUser.summary }}</p>
						</div><!-- /profile-area -->

						<div class="social-media-area"  v-if="profileUser.accounts.length">
							<ul class="sm-top row">
								<li class="col-xs-4 col-md-4 social-media-item" v-for="account in profileUser.accounts"
									:key="account.platform">
									<!-- <img :src="account.platform | socialIcon" :alt="account.platform"> -->
									<div :class="['icon-container-static--wbackground', '--active', svgfy(account.platform)]">
										<svg-filler class="icon-container-static__icon" :path="getSvgPath(account.platform)" width="25px" height="25px" :fill="'#ffffff'" />
									</div>
									<span class="account-number" v-if="account.followers">{{ account.followers }}</span>
									<span class="account-label" v-if="account.followers">{{ account.followers_label }}</span>
								</li>
							</ul>
						</div><!-- /social-media-area -->

						<div class="profile-bottom-area visible-md visible-lg" v-if="!isHubManager">
							<p class="gig-metrics">{{ form.gig_metrics }}% of gigs completed</p>
						</div>

						<router-link v-if="isViewingOther"
							class="btn-full-width visible-md visible-lg get-in-touch js-branding-button"
							:to="routeMessage">
							Get in Touch
						</router-link>

						<router-link v-else-if="!isViewingOther"
							class="btn-full-width visible-md visible-lg get-in-touch js-branding-button"
							:to="{name: 'my.gigs'}">
							View My Gigs
						</router-link>
					</div>
				</div>

				<div class="profile-newsfeed visible-md visible-lg col-md-8">
					<div class="row">
						<div class="content-area col-md-10 col-md-offset-1 col-lg-8 col-md-offset-2"  
							v-if="showPosts">
							<p class="no-post text-center" v-if="!posts.length">
								{{ profileUser.name }} has not made any posts yet.
							</p>
							<!-- populate user posts -->
							<transition-group appear name="fade">
								<post v-for="post in posts" 
									:post="post"
									:key="post.id">
								</post>
							</transition-group>
							
						</div>
					</div>
				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import Post from '../components/newsfeed/Post.vue'
import mixinHub from '../mixins/hub'
import mixinPost from '../mixins/post'
import mixinUser from '../mixins/user'
import filters from '../filters'
import matchHeight from 'jquery-match-height'
import SharesList from '../components/posts/SharesList.vue'
import AlertPostReport from '../components/posts/AlertPostReport.vue'
import AlertPostUnpublish from '../components/posts/AlertPostUnpublish.vue'

export default {
	name: 'ProfileHome',

	mixins: [mixinHub, mixinPost, mixinUser],

	filters: {
		imgPlaceholder: filters.imgPlaceholder,
		fixTempPath: filters.fixTempPath,
		bannerPlaceholder (value) {
			return value ? value : '/images/img-banner.jpg'
		},
		socialIcon (value) {
			if (value === 'facebook') 
				value = 'fb'
			
			let platform = value
			return resolveStaticAsset(`/images/icon-${platform}.png`)
		},
	},
	
	components: {
		Post,
		SharesList,
		AlertPostReport,
		AlertPostUnpublish
	},

	data () {
		return {
			loaders: {
				profile: true
			},
			showPosts: false,  // bootstrap md size,
			form: {
				gig_metrics: 0,
			}
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
		init (value) {
			if (value)
				this.fetchUserData()
		}
	},

	computed: {
		bannerClass () {
			let coverPicture = this.profileUser.cover_picture_web_path

			let backgroundImage = coverPicture ? coverPicture : null
			return {
				backgroundImage: `url(${backgroundImage})`
			}
		},
		isHubManager () {
			return this.profileUser.object_class === 'Hub'
		},
		profileRoute () {
			return { 
				name: 'profile.home', 
				params: { 
					user_slug: this.isHubManager ? 'about' : this.profileUser.slug
				}
			}
		},
		profileUser () {
			return this.$store.state.Profile.user
		},
		// isHubManager () {
		// 	// return this.
		// },
		isViewingOther () {
			return this.profileUser.id !== this.$store.state.user.id
		},
		routeMessage () {
			return { 
				name: 'message', 
				params: { 
					conversation_id: undefined,
					user_id: this.profileUser.id,
					user_slug: this.profileUser.slug 
				}
			}
		},
		routeMyGig () {
			return {
				name: 'my.gigs'
			}
		}
	},

	methods: {
		onResize () {
			this.showPosts = window.innerWidth >= 992
			$(this.$el).find('.social-media-area .social-media-item').matchHeight()
		},
		fetchUserData () {
			this.loaders.profile = true
			this.$store.dispatch('fetchUserData', {
				hub: this.hub,
				user: {
					slug: this.$route.params.user_slug
				}
			})
			.then(response => {
				this.loaders.profile = false
				this.form.gig_metrics = response.data.data.gig_metrics
				this.$nextTick(() => {
					$(this.$el).find('.social-media-area .social-media-item').matchHeight()
				})
			})
		},
		svgfy (value) {
			value = value.toLowerCase()
			let platform = value
			if (value == 'pinterest')
				platform = 'pinterest-p'
			else if (value == 'youtube')
				platform = 'youtube-play'

			return 'svg-' + platform
		},
	},
}
</script>