<template>
<div class="post-item clearfix">
	<div class="post-meta" v-if="isCreating == false">
		<div class="post-area">
			<div class="post-info clearfix">
				<router-link :to="fixProfileRoute(post.author)">
					<img :src="post.author.profile_picture_tiny"
						class="pull-left">
				</router-link>
				<h5 class="post-author">
					<router-link :to="fixProfileRoute(post.author)">{{ post.author.name }}</router-link>
					<p class="pull-right small post-scheduled" v-if="post.schedule_at">
						Scheduled on {{ post.schedule_at | scheduled }}
					</p>
				</h5>
				<router-link :to="{ name: 'post', params: { post_id: post.id } }"
					class="post-timestamp"
					tag="span">
				 	<a>{{ post.created_at | fromNow }}</a>
				</router-link>
				<div class="pull-right post-actions-wrapper">
					<span class="dropdown post-actions" v-if="showActions">
						<a id="post-actions-label" href="#" data-toggle="dropdown" role="button" 
							aria-haspopup="true" aria-expanded="false" class="post-actions-label">
							<i class="fa fa-ellipsis-v"></i>
						</a>

						<ul class="dropdown-menu" aria-labelledby="post-actions-label">
							<img :src="resolveStaticAsset('/images/img-arrow-up.png')" alt="Image" class="arrow-up">
							<li class="item" :class="{'--busy': loaders.hiddenToggling}" 
								v-if="!isHubManager && isPostVisible">
								<a @click.prevent.stop="toggleHidden" href="#">Hide this post</a>
							</li>
							<li class="item" :class="{'--busy': loaders.hiddenToggling}" 
								v-if="!isHubManager && !isPostVisible">
								<a @click.prevent.stop="toggleHidden" href="#">Unhide this post</a>
							</li>
							<li class="item item-danger" :class="{'--busy': loaders.reporting, '--reported': isPostReported}" 
								v-if="!isHubManager && !isPostAuthor">
								<a @click.prevent.stop="postReport" href="#">Report this post</a>
							</li>
							<li class="item item-danger" :class="{'--busy': loaders.unpublishing}" v-if="isHubManager">
								<a @click.prevent.stop="postUnpublish" href="#">Unpublish this post </a>
							</li>
						</ul>
					</span>
				</div>
				<span class="reports-count" v-if="isHubManager && (post.reports && post.reports.length)">
						Reported {{ post.reports.length }} {{ post.reports.length > 1 ? 'times' : 'time' }}.
				</span>
			</div><!-- /post-info -->
		</div>
	</div><!-- /post-meta -->

	<div class="post-content" v-if="isCreating == false">
		<p v-if="isPostVisible">
			<span v-html="post.message_cached"></span>
		</p>
		<p v-else class="text-italic post-hidden">
			This post has been hidden by you.
		</p>
	</div><!-- /post-content -->

	<div v-if="isPostVisible">
		<attachment :attachment="post.attachment" v-if="post.attachment">
			<slot name="remove-attachment" slot="remove-attachment"></slot>
		</attachment>

		<div class="post-footer" v-if="showPostFooter">
			<div class="bottom-right-box text-center">
				<a @click.prevent.stop="postLike" href="#" :class="{'selected': postLiked}">
					<!-- <i class="fa fa-thumbs-up" aria-hidden="true"></i> -->
					<svg-filler class="icon-container__icon--no-padding icon-container__icon--baseline" :path="getSvgPath('like')" width="10px" height="10px" :fill="colorFill" :hover-color="hoverFill" />
					{{ likesCount }}
				</a>
				<a href="#" @click.prevent="showComments = !showComments">
					<!-- <i class="fa fa-comment" aria-hidden="true"></i> -->
					<svg-filler class="icon-container__icon--no-padding icon-container__icon--baseline" :path="getSvgPath('comment')" width="10px" height="10px" :fill="colorFill" :hover-color="hoverFill" />
					{{ post.comments.length }}
				</a>
				<span class="dropdown">
					<a href="#" :id="dropdownNameId" role="button" data-toggle="dropdown" aria-haspopup="true" 
						aria-expanded="false">
						<!-- <i class="fa fa-share" aria-hidden="true"></i>  -->
						<svg-filler class="icon-container__icon--no-padding icon-container__icon--baseline" :path="getSvgPath('share')" width="10px" height="10px" :fill="colorFill" :hover-color="hoverFill" />
						share
					</a>
					<div class="dropdown-menu share-post-dropdown" :aria-labelledby="dropdownNameId">
						<img :src="resolveStaticAsset('/images/img-arrow-up.png')" alt="Image" class="arrow-up">
						<ul>
							<li>
								<router-link :to="{name: 'write', params: { post_id: post.id, from: 'post' } }">
									Share this post
								</router-link>
							</li>
							<li class="all-shares gig-shares" v-if="groupedGigPlatformShares">
								<p>GIG SHARES</p>
								<ul>
									<li v-for="(shares, platform) in groupedGigPlatformShares" :key="platform">
										<div class="social-counter">
											<i :class="['social-counter__icon fa', sharePlatformClass(platform)]"></i>
											<span v-if="shares.length > 1" 
												class="social-counter__counter">
												{{ shares.length }}
											</span>
										</div>
									</li>
								</ul>
							</li>
							<li class="all-shares post-shares" v-if="groupedAllPlatformShares">
								<p>ALL SHARES  &mdash; <a href="#" @click.prevent.stop="viewShareList">VIEW ALL</a></p>
								<ul>
									<li v-for="(shares, platform) in groupedAllPlatformShares" :key="platform">
										<div class="social-counter">
											<i :class="['social-counter__icon fa', sharePlatformClass(platform)]"></i>
											<span v-if="shares.length > 1" 
												class="social-counter__counter">
												{{ shares.length }}
											</span>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</span>
			</div><!-- /bottom-right-box -->
		</div><!-- /post-footer -->

		<transition name="fade" v-show="showPostFooter" appear>
			<div class="post-comment" v-show="showComments">
				<div class="post-comment__list">
					<transition name="fade">
						<ul v-if="post.comments && post.comments.length">
							<li class="post-comment__list-item" v-for="comment in post.comments" :key="comment.id">
								<comment :comment="comment"></comment>
							</li>
						</ul>
					</transition>
					<!-- loaders				 -->
					<div class="text-center" v-if="loaders.comments">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
				</div><!-- /post-comment__list -->

				<div class="post-comment__write">
					<div class="post-comment__write-avatar">
						<div class="post-comment__write-msg">
							<div class="post-comment__write-author">
								<router-link :to="fixProfileRoute(user)">
									<img :src="user.profile_picture_tiny" alt="">
								</router-link>
							</div><!-- /post-comment__write-author -->
							<div class="post-comment__write-txt">
								<text-counter :show-counter="false"
									placeholder="Write your comment..."
									ref="commentForm"
									v-model="form.message">		
								</text-counter>
							</div><!-- /post-comment__write-txt -->
							<div class="post-comment__write-btn">
								<button @click="postComment" :disabled="disablePostComment" 
									type="submit" 
									class="js-branding-button btn-submit">
									<i v-if="loaders.commenting" class="fa fa-spinner fa-spin"></i> Post
								</button>
							</div><!-- /post-comment__write-btn -->
						</div><!-- /post-comment__write-msg -->
					</div><!-- /post-comment__write-avatar -->
				</div><!-- /post-comment__write -->
			</div><!-- /post-comment -->
		</transition>
	</div>

	<for-review v-if="isReviewing && isHubManager" :post="post"></for-review>
	<for-scheduled @post-cancelled="onPostCancelled" :post="post"
		v-if="isScheduled && !isHubManager" @post-rescheduled="onPostRescheduled">
	</for-scheduled>
	<for-approval  @post-cancelled="onPostCancelled" v-if="isReviewing && !isHubManager" :post="post"></for-approval>
</div><!-- /post-item -->
</template>
<script>
import moment from "moment-timezone";
import mixinUser from '../../mixins/user'
import ApiPost from '../../api/post'
import ApiUser from '../../api/user'
import mixinHub from '../../mixins/hub'
import TextCounter from '../TextCounter.vue'
import ForReview from '../posts/ForReview.vue'
import ForScheduled from '../posts/ForScheduled.vue'
import ForApproval from '../posts/ForApproval.vue'
import Tribute from "tributejs"
import Comment from './Comment.vue'
import Attachment from './Attachment.vue'

export default {
	name: 'Post',

	mixins: [mixinUser, mixinHub],

	components: {
		Attachment,
		Comment,
		TextCounter,
		ForReview,
		ForScheduled,
		ForApproval
	},

	props: {
		isReviewing: {
			type: Boolean,
			default: false
		},

		showActions: {
			type: Boolean,
			default: true
		},

		isCreating: {
			type: Boolean,
			default: false
		},

		isScheduled: {
			type: Boolean,
			default: false
		},

		onPostCancelled: {
			type: Function,
			required: false,
			default: () => {
				console.log('prop: onPostCancelled')
			}
		},

		onPostRescheduled: {
			type: Function,
			required: false,
			default: () => {
				console.log('prop: onPostRescheduled')
			}
		},

		initialComments: {
			type: Boolean,
			default: false
		}, // single post viewing

		post: {
			type: Object,
			required: true,
			default: () => {
				return {
					author: {
						profile_picture: null,
						name: null
					},
					attachment: {
						source: null,
					},
					likes: [],
					comments: [],
				}
			}
		}
	},

	data () {
		return {
			loaders: {
				comments: false, // for fetching commments
				commenting: false, // for commenting
				liking: false,
				hiddenToggling: false,
				reporting: false,
				unpublishing: false
			},
			showComments: this.initialComments,
			form: {
				message: null
			},

			tribute: null,
			tags: [],
			colorFill: '#999999',
			hoverFill: '#de3d10'
		}
	},

	watch: {
		showComments (shown) {
			if (shown) {
				this.initializeTribute()
			}
			else {
				this.tribute = null
			}
		}
	},

	mounted () {
		this.$nextTick(() => {
			if (this.showComments) {
				this.initializeTribute()
			}

			// handle events
			this.$bus.$on(`post:unpublished.${this.post.id}`, this.postUnpublished)
			this.$bus.$on(`post:reported.${this.post.id}`, this.postReported)
		})
	},

	beforeDestroy () {
		// unbind events
		this.$bus.$off(`post:unpublished.${this.post.id}`, this.postUnpublished)
		this.$bus.$off(`post:reported.${this.post.id}`, this.postReported)
	},

	beforeRouteLeave () {
		// unbind events
		this.$bus.$off(`post:unpublished.${this.post.id}`, this.postUnpublished)
		this.$bus.$off(`post:reported.${this.post.id}`, this.postReported)
	},

	methods: {
		/**
		 * post unpublished event handler
		 */
		postUnpublished (data) {
			this.$store.dispatch('removePostFromList', data)

			// redirect to newsfeed if current view is single post view page.
			if (this.$route.name === 'post.view') {
				this.$router.replace({
					name: 'newsfeed'
				})
			}
		},

		/**
		 * post reported event handler
		 */
		postReported (data) {
			this.$store.dispatch('updatePostReportInList', data)
			this.$store.dispatch('updatePostHideInList', data)
		},

		/**
		 * toggle the visibility of the post. 
		 * Influencer only
		 */
		toggleHidden () {
			this.loaders.hiddenToggling = true

			const apiPost = new ApiPost(this.hub)
			apiPost.toggleHidden(this.post.id)
				.then(response => {
					this.loaders.hiddenToggling = false
					this.$store.dispatch('updatePostHideInList', response.data.data)
				})
				.catch(error => {
					this.loaders.hiddenToggling = false
					console.error(error)
				})
		},

		/**
		 * report the post
		 * Influencer only
		 */
		postReport () {
			this.$bus.$emit('post:confirm-report', this.post.id)
			console.log('emitting event: post:confirm-report', this.post.id)
		},

		/**
		 * unpublish post
		 * Hubmanager only
		 */
		postUnpublish () {
			this.$bus.$emit('post:confirm-unpublish', this.post.id)
			console.log('emitting event: post:confirm-unpublish', this.post.id)
		},

		/**
		 * View share list of a post.
		 */
		viewShareList () {
			this.$bus.$emit('show-shares-list', this.post.id)
		},
		
		/**
		 * determine share if what type of platform
		 * @param  {string} platform
		 */
		sharePlatformClass (platform) {
			let fa = ''
			switch(platform) {
				case 'facebook':
					fa = 'fa-facebook-square'
					break;
				case 'twitter': 
					fa = 'fa-twitter'
					break;
				case 'linkedin':
					fa = 'fa-linkedin-square'
					break;
				case 'pinterest':
					fa = 'fa-pinterest-square'
					break;
				case 'youtube':
					fa = 'fa-youtube-play'
					break;
				case 'instagram':
					fa = 'fa-instagram'
					break;
			}
			return fa
		},

		postComment () {
			this.loaders.commenting = true

			let _post = new ApiPost(this.hub)
			_post.comment(this.post.id, this.form)
				.then(response => {
					this.loaders.commenting = false
					this.form.message = ''
					this.$store.dispatch('updatePostComments', response.data.data.comment)
					this.$refs.commentForm.clear()
				})
				.catch(error => {
					this.loaders.commenting = false
					this.form.message = ''
					console.error(error)
				})
		},

		/**
		 * TODO: move the templates into a different file for easy maintainability
		 */
		initializeTribute () {
			console.log('initializing tribute')
			this.tribute = new Tribute({
				values: _.debounce(this.getTaggableUsers, 300),
				menuItemTemplate: taggable => {
					return `
						<div class="tags-suggestion-item">
							<img class="tags-suggestion-item-avatar" src="${taggable.original.profile_picture_tiny}"/>
							<span class="tags-suggestion-item-name">${taggable.original.slug}</span>
						</div>`
				},
				positionMenu: true,
				selectTemplate: taggable => {
					return `@${taggable.original.slug}`
				},
				noMatchTemplate: function (tribute) {
					return '<li class="no-match">No matches found</li>';
				},
				lookup: (taggable) => {
					return taggable.name + ' ' + taggable.slug
				},
				fillAttr: 'slug'
			})

			let textField = this.$refs.commentForm
			if (textField) {
				this.tribute.attach(textField.$refs.text)
				textField.$refs.text.addEventListener('tribute-replaced', this.tributeReplaced)
			}
		},

		// NOTE: debounce this
		getTaggableUsers (query, callback) {
			if (query.length < 3)
				return
			
			let apiPost = new ApiUser(this.hub)
			apiPost.searchEntity(query)
				.then(response => {
					// map the data to return proper structure
					let data = response.data.data.map(item => {
						return {
							slug: item.slug,
							name: item.name,
							profile_picture_tiny: item.profile_picture_tiny,
						}
					})
					callback(data)
				})
				.catch(error => {
					callback([])
				})
		},

		tributeReplaced (e) {
			// manually trigger the input event to the text
			this.$refs.commentForm.$refs.text.dispatchEvent(new Event('input', {bubbles: true}))
						
			// push to tags array
			let item = e.detail.item.original
			let taggable = _.pick(item, ['id', 'slug', 'name'])
			this.tags.push(taggable)
		},
		
		postLike ($event) {
			// avoid double clicking
			if (this.loaders.liking)
				return

			this.loaders.liking = true
			let _post = new ApiPost(this.hub)
			_post.like(this.post.id)
				.then(response => {
					this.loaders.liking = false
					let like = response.data.data.like
					// @TODO update the store as well?
					this.$store.dispatch('updatePostLikes', {
						post_id: this.post.id,
						like
					})
				})
				.catch(error => {
					this.loaders.liking = false
					console.error(error)
				})
		},

		fixProfileRoute (user) {
			let isHubManager = user.object_class === 'Hub'
			return { 
				name: 'profile.home', 
				params: { 
					user_slug: isHubManager ? 'about' : user.slug
				} 
			}
		}
	},

	computed: {
		/**
		 * determine if this post was reported by this user.
		 */
		isPostReported () {
			let report = Boolean(this.post.reports && this.post.reports.length) ? this.post.reports[0] : null
			let isReported = report ? report.is_reported : false
			return Boolean(isReported)
		},

		/**
		 * determine if the post author is you.
		 */
		isPostAuthor () {
			return this.post.author.id == this.user.id
		},

		/**
		 * determnine if this post was hidden by you.
		 */
		isPostHidden () {
			let hidden_post = Boolean(this.post.hidden_posts && this.post.hidden_posts.length) ? this.post.hidden_posts[0] : null
			let isHidden = hidden_post ? hidden_post.is_hidden : false
			return Boolean(isHidden)
		},
		
		isPostVisible () {
			return !this.isPostHidden
		},

		showPostFooter () {
			return !this.isReviewing && !this.isCreating && !this.isScheduled 
		},

		likesCount () {
			if (this.post.likes)
			return this.post.likes.length
		},

		postLiked () {
			if (this.post.likes.length) {
				return Boolean(this.post.likes.filter(like => {
					return like.entity_id === this.user.id && 
					like.entity_type.match(new RegExp(this.user.object_class))
				}).length)
			}
		},

		allPlatformShares () {
			if (!this.post.shares)
				return []
			return this.post.shares
		},

		gigPlatformShares () {
			if (!this.post.shares) 
				return []
			return this.post.shares.filter(platform => platform.context === 'gig')
		},

		groupedAllPlatformShares () {
			if (!this.allPlatformShares.length) 
				return

			let grouped = _.groupBy(this.allPlatformShares, 'platform')
			return grouped
		},

		groupedGigPlatformShares () {
			if (!this.gigPlatformShares.length) 
				return

			let grouped = _.groupBy(this.gigPlatformShares, 'platform')
			return grouped
		},

		disablePostComment () {
			return !this.form.message || this.loaders.commenting
		},

		dropdownNameId () {
			return 'share-post-'+this.post.id
		}
	},

	filters: {
		scheduled (date) {
			if (!date) {
				return
			}
			return moment.utc(date).format("ddd, MMM D, h:mm a")
		}
	}
}
</script>