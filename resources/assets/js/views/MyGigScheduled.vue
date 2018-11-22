<template>
<div class="posts-container">
	<h1>Scheduled Posts</h1>
	<div class="text-center" v-if="loaders.fetching">
		<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
	</div>

	<div class="row">
		<div class="col-md-10">
			<transition-group name="fade" appear :duration="300">
				<post v-for="post in postScheduled" :post="post" :is-scheduled="true"
					:key="post.id" :on-post-cancelled="onPostCancelled"
					:on-post-rescheduled="onPostRescheduled">
				</post>
			</transition-group>
		</div>
	</div>

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
</template>
<script>
import Post from '../components/newsfeed/Post.vue'
import mixinHub from '../mixins/hub'
import mixinMyGig from '../mixins/mygig'
import InfiniteScroll from 'vue-infinite-loading'

export default {
	name: 'MyGigsScheduled',
	
	mixins: [mixinHub, mixinMyGig],

	components: {
		Post,
		InfiniteScroll
	},

	data () {
		return {
			loaders: {
				fetching: false
			},
			state: {
				infinite: false
			},
			reachedLast: false
		}
	},

	mounted () {
		if (this.init) {
			this.fetchPosts()
		}
	},

	watch: {
		'$route': 'fetchPosts',
		init (value) {
			if (value)
				this.fetchPosts()
		}
	},

	methods: {
		onPostCancelled (data) {
			let index = this.findIndex(data.post_id)
			this.postScheduled.splice(index, 1)
			this.$store.commit('updateTotal', { 
				scheduled: this.postScheduled.length
			})
		},
		onPostRescheduled (data) {
			let index = this.findIndex(data.post_id)
			this.postScheduled[index].schedule_at = data.schedule_at
		},
		findIndex (dataId) {
			return _.findIndex(this.postScheduled, item => item.id == dataId)
		},
		fetchPosts () {
			this.loaders.fetching = true
			let payload = {
				hub: this.hub,
				fetchNew: true
			}
			this.$store.dispatch('getScheduled', payload)
				.then(response => {
					let pagination = _.omit(response.data.data.posts, 'data')
					if (!pagination.next_page_url) {
						this.reachedLast = true
					}
					this.loaders.fetching = false
					this.state.infinite = true
				})
				.catch(error => {
					console.error(error)
					this.loaders.fetching = false
				})
		},

		loadOlderPosts ($state) {
			if (!this.reachedLast) {
				let payload = {
					hub: this.hub,
					fetchNew: false
				}
				this.$store.dispatch('getScheduled', payload)
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