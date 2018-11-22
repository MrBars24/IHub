<template>
	<div class="gig-feed-item gig-feed clearfix">
		<div class="gig-feed-meta">
			<h5 class="gig-feed-author">
				<a :href="feed.url_profile" target="_blank">{{ feed.title }}</a>
			</h5>
			<span class="gig-feed-timestamp">
				<a target="_blank" :href="feed.link">
					<small>{{ feed.originally_published_at | fromNow }}</small>
				</a>
			</span>
		</div>
		<div class="gig-feed-body">
			 <div class="gig-feed-description" v-html="feed.description"></div>
			 <div class="gig-feed-thumbnail" v-if="feed.thumbnail_web_path">
				 <img class="gig-feed-image img-responsive" :src="feed.thumbnail_web_path">
			 </div>
		</div>
		<div class="gig-feed-footer text-center">
			<div class="text-center" v-if="loaders.gigs">
				<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
			</div>

			<transition appear :duration="300" name="fade">
				<div class="alert alert-success" v-if="successMessage.message">
					{{ successMessage.message }}
				</div>
			</transition>

			<button type="button" class="btn-submit js-branding-button" 
				@click="showGigModal" :disabled="loaders.creating_gig">
				<i v-if="loaders.creating_gig" class="fa fa-spinner fa-pulse fa-fw"></i> Create Gig
			</button>

			<button type="button" class="btn-submit js-branding-button" 
				@click="createContext('post')" :disabled="loaders.creating_post">
				<i v-if="loaders.creating_post" class="fa fa-spinner fa-pulse fa-fw"></i> Create Post
			</button>

		</div>
	</div>
</template>
<script>
import moment from 'moment'
import ApiMyGig from "../../api/mygigs"
import mixinHub from '../../mixins/hub'
export default {
	name: 'Feed',

	mixins: [mixinHub],

	props: {
		feed: {
			type: Object,
			required: true
		}
	},

	data () {
		return {
			loaders: {
				creating_gig: false,
				creating_post: false
			},
			successMessage: {
				message: null
			}
		}
	},

	mounted () {
		this.$bus.$on('feed-creating-gig', this.createGigContext)
	},

	destroy () {
		this.$bus.$off('feed-creating-gig', this.createGigContext)
	},

	beforeRouteLeave (to, from, next) {
		this.$bus.$off('feed-creating-gig', this.createGigContext)

		next()
	},

	methods: {
		showGigModal () {
			this.$emit('feed-create-gig', this.feed.id)
		},
		
		createGigContext (payload) {
			if (payload.feed_id != this.feed.id)
				return

			this.createContext('gig', payload)
		},

		createContext (context, gigPayload = null) {
			this.loaders[`creating_${context}`] = true
			this.successMessage.message = null
			
			const apiMyGig = new ApiMyGig(this.hub)
			let payload = {
				context,
				feed_id: this.feed.id
			}

			if (context === 'gig', gigPayload != null) {
				Object.assign(payload, gigPayload)
			}

			apiMyGig.createFeedPostContext(payload)
				.then(response => {
					console.log(response.data)
					this.loaders[`creating_${context}`] = false
					this.successMessage.message = response.data.data.message

					if (context == 'gig') {
						this.$emit('feed-created-gig')
					}

				})
				.catch(error => {
					console.error(error)
					this.loaders[`creating_${context}`] = false
				})
		},
	}
}
</script>