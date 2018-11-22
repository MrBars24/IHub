<template>
	<div class="gig-post-review">
		<div class="detail-box">
			<div class="head" v-if="post.sub_posts && post.sub_posts.length">
				<p>Social Posts</p>
			</div><!-- /head -->

			<div class="body">
				<p class="gig-post-review-text" v-if="post.sub_posts && post.sub_posts.length">
					The posts below will appear on various social media
				</p>

				<div class="form-area">
					<form action="#">
						<div class="bordered-box form-box" v-if="post.sub_posts && post.sub_posts.length">
							<div class="gig-post-review-body">
								<ul>
									<li class="item" v-for="(sub_post,index) in post.sub_posts" :key="index">
										<div class="gig-post-account-item">
											<a class="gig-post-account-link">
												<i :class="['fa icon-sm', fafy(sub_post.platform)]" 
													aria-hidden="true"></i>{{ sub_post.params.name }}
											</a>
											<p class="gig-post-text">{{ sub_post.params.message }}</p>
										</div><!-- /gig-post-account -->
									</li>
								</ul>
							</div><!-- /gig-post-review__body -->
						</div><!-- /bordered-box -->

						<div class="gig-post-review-submission" 
							v-if="!reviewStatus.rejected && !reviewStatus.accepted">
							<p class="gig-post-publish-text">Publish this post for gig "This is my gig"?</p>

							<textarea v-if="rejecting"
								v-model="form.rejection_reason"
								:placeholder="rejectMessagePlaceholder">
							</textarea>

							<button type="button"
								v-if="!rejecting" 
								@click.prevent="acceptPost"
								value="Accept"
								:disabled="loaders.accepting"
								class="btn-submit js-branding-button">
								<i v-if="loaders.accepting" class="fa fa-spinner fa-spin"></i> ACCEPT
							</button>

							<input v-else 
								:disabled="reviewStatus.rejected || loaders.rejecting"
								@click.prevent="rejecting = false" 
								type="submit" 
								value="Back" 
								class="btn-submit js-branding-button">

							<button type="button"
								:disabled="reviewStatus.rejected || loaders.rejecting"
								@click.prevent="rejectPost"
								class="btn-submit js-branding-button">
								<i v-if="loaders.rejecting" class="fa fa-spinner fa-spin"></i> REJECT
							</button>

						</div><!-- /gig-post-review-submission -->

						<div class="gig-post-review-result">
							<div class="message-box --message-error"
							 	v-if="reviewStatus.rejected">
								<div class="message__text">
									<p>You have rejected this post, and the influencer has been notified</p>
								</div><!-- /message__text -->
							</div><!-- /message-box -->

							<div class="message-box --message-success" 
								v-if="reviewStatus.accepted">
								<div class="message__text">
									<p>
										Thanks for accepting this post. Want to <router-link :to="{name: 'newsfeed'}">view in the Newsfeed?</router-link>
									</p>
								</div><!-- /message__text -->
							</div><!-- /message-box -->
						</div><!-- /gig-post-review-result -->
					</form>
				</div><!-- /form-area -->

			</div><!-- /body -->
		</div>
	</div><!-- gig-post-review -->
</template>
<script>
import GigApi from '../../api/gigs'
import mixinHub from '../../mixins/hub'
export default {
	name: 'for-review',
	mixins: [mixinHub],
	props: {
		post: {
			type: Object,
			required: true
		}
	},
	data () {
		return {
			rejecting: false,
			form: {
				rejection_reason: null
			},
			loaders: {
				rejecting: false,
				accepting: false
			},
			reviewStatus: {
				accepted: false,
				rejected: false
			}
		}
	},
	methods: {
		fafy (value) {
			return 'fa-'+value
		},

		rejectPost () {
			// first, checking.
			if (!this.rejecting) {
				this.rejecting = true
				return
			}
			// finally, send the payload to server
			else {
				const gigApi = new GigApi(this.hub)
				this.loaders.rejecting = true
				gigApi.postReject(this.getPayload())
					.then(response => {
						this.reviewStatus.rejected = true
						this.loaders.rejecting = false
						this.reviewStatus.accepted = false // just to make sure it's not accepted. lol
						console.log('post rejected')	
					})
					.catch(error => {
						this.loaders.rejecting = false
						console.log(error)
					})
			}			
		},
		acceptPost () {
			const gigApi = new GigApi(this.hub)
			this.loaders.accepting = true
			gigApi.postAccept(this.getPayload())
				.then(response => {
					this.reviewStatus.rejected = false // just to make sure it's not rejected. lol
					this.reviewStatus.accepted = true
					this.loaders.accepting = false
					console.log('post accepted')
				})
				.catch(error => {
					console.log(error)
					this.loaders.accepting = false
				})
		},

		getPayload () {
			let payload = {
				rejection_reason: this.form.rejection_reason,
				post_id: this.post.id,
				gig_id: this.post.gig_id
			}
			return payload
				
		}
	},
	computed: {
		rejectMessagePlaceholder () {
			return `Let ${this.post.author.name} know why you are rejecting this post`
		},
	}
}
</script>