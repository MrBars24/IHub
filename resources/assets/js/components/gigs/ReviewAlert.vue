 <template>
	<div class="col-md-6 col-sm-8 col-lg-push-1 col-md-push-1 col-md-offset-2 message-box-holder" 
		v-show="reviewPagination.total">
		<div class="message-box">
			<div class="message__heading">
				<h4>Hi {{ user.name }}</h4>
			</div><!-- /message__heading -->

			<div class="message__text">
				<p>You have {{ reviewPagination.total }} gig post/s waiting for your approval.</p>
			</div><!-- /message__text -->
			<router-link :to="{name: 'my.gigs.pending'}" class="btn-submit">
				VIEW
			</router-link>
		</div><!-- /message-box -->
	</div>
</template>
<script>
import mixinUser from '../../mixins/user'
import mixinHub from '../../mixins/hub'
import mixinGig from '../../mixins/gig'

export default {
	mixins: [mixinUser, mixinHub, mixinGig],
	
	data () {
		return {
			loaders: {
				reviews: false
			}
		}
	},

	mounted () {
		if (this.init) {
			this.fetchReviews()
		}
	},

	watch: {
		'$route' : 'fetchReviews',
		init (value) {
			if (value) {
				this.fetchReviews()
			}
		}
	},
	methods: {
		fetchReviews () {
			this.loaders.reviews = true
			let payload = {
				hub: this.hub
			}
			this.$store.dispatch('getReviews', payload)
				.then(response => {
					this.loaders.reviews = false
				})
				.catch(error => {
					this.errors.push(error)
					this.loaders.reviews = false
				})
		}
	},
	computed: {
		postText () {
			return this.reviewPosts.length > 1 ? 'posts' : 'post'
		},
		reviewPagination () {
			return this.$store.state.Gig.postsPagination
		}
	}
}
</script>