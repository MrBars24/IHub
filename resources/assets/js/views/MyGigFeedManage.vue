<template>
	<div class="posts-container">

		<h1>Manage Gig Feeds
			<router-link :to="{name: 'my.gigs.feed.create'}" class="btn-submit js-branding-button">
				<i class="fa fa-plus"></i> Create Gig Feed
			</router-link>
		</h1>

		<div class="alert alert-success" v-if="successMessage">
			{{ successMessage.message }}
		</div>

		<div class="gig-feed-container">
			<div class="detail-box">
				<div class="body">
					<div class="bordered-box">

					<div class="text-center" v-if="loaders.fetching">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>

						<div class="dynamic-list">
							<ul>
								<li class="item" v-for="feed in feedsList" :key="feed.id">
									<div class="gig-feed-wrapper">
										<span class="platform-name">{{ feed.source_url }}</span>
										<router-link class="gig-edit-button"
											:to="{name: 'my.gigs.feed.edit', params: {feed_id: feed.id}}">
											<i aria-hidden="true" class="fa fa-pencil"></i>
										</router-link>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
	import ApiMyGig from '../api/mygigs'
	import mixinHub from '../mixins/hub'
	export default {
		mixins: [mixinHub],

		data() {
			return {
				loaders: {
					fetching: false
				}
			}
		},

		mounted() {
			if (this.init)
				this.fetchGigFeedList()
		},

		methods: {
			fetchGigFeedList() {
				this.loaders.fetching = true
				const apiMyGig = new ApiMyGig(this.hub)
				apiMyGig
					.getFeedConfigList()
					.then(response => {
						let feeds = response.data.data.feeds
						if (feeds.length) {
							this.$store.dispatch("updateFeedConfigList", {
								feeds: response.data.data.feeds
							})
						}

						this.loaders.fetching = false
					})
					.catch(error => {
						console.error(error);
						this.loaders.fetching = false
					});
			}
		},

		computed: {
			feedsList() {
				return this.$store.state.MyGig.feeds_list;
			},
			successMessage: {
				get() {
					let successMessage = this.$route.params.success;
					if (successMessage === undefined) return;
					return {
						message: successMessage.message
					};
				},

				set(value) {
					delete this.$route.params.success;
				}
			},

			errorMessage: {
				get() {
					let errorMessage = this.$route.params.error;
					if (errorMessage === undefined) return;
					return {
						message: errorMessage.message
					};
				},
				set(value) {
					delete this.$route.params.error;
				}
			}
		},

		watch: {
			'$route': 'fetchConfigs',
			init(value) {
				if (value)
					this.fetchGigFeedList()
			}
		}
	}
</script>