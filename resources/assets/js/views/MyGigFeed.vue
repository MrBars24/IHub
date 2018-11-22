<template>
<div class="gig-feeds-container">
	<div class="modal fade" ref="modalGigContext" 
		id="modalGigContext" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">You are about to create a Gig from this feed.</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">

							<div class="detail-box">

								<div class="body">
									<div class="bordered-box form-gig">

										<div class="form-field">
											<label>Name</label>
											<input type="text" 
												id="title"
												v-model="form.title"
												placeholder="What do you want to call this gig?">

											<small class="text-danger" v-if="error.title">
												{{ error.title }}
											</small>
										</div><!-- /form-field -->

										<div class="form-field">
											<label>How many points are awarded for this gig?</label>
											<input type="number" autocomplete="false" step="5" id="points" 
												min="10" max="1000" placeholder="10" v-model.number="form.points">

											<small class="text-danger" v-if="error.points">
												{{ error.points }}
											</small>
										</div><!-- /form-field -->

										<div class="platforms-area form-field clearfix">
											<label>Platforms</label>
											<div class="prefered-platform-list">
												<ul class="platforms-list">
													<li v-for="(platform,index) in commonForms.platforms" :key="index" ref="platforms">
														<label :for="platform.platform | generateNameId('platform')"
															:class="platform.platform | socialIcon">
															<input :id="platform.platform | generateNameId('platform')" 
																type="checkbox" v-model="form.platforms"
																:value="platform" @change="updatePlatformUI"
																class="hidden">
														</label>
													</li>
												</ul>
											</div>
										</div><!-- /form-field -->

									</div>
								</div>
								
							</div><!-- /detail-box -->

						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn-post js-branding-button" @click="createContext"
						:disabled="disabledCreateGig">
						<i v-if="loaders.creating_gig" 
							class="fa fa-spinner fa-pulse fa-fw"></i> Create Gig
					</button>
					<button type="button" class="btn-post --default" data-dismiss="modal"
						:disabled="loaders.creating_gig">
						Cancel
					</button>
				</div>
			</div>
		</div>
	</div>

	<h1>Gig Feeds
		<router-link :to="{name: 'my.gigs.feed.manage'}" 
			class="btn-submit pull-right js-branding-button text-center">
			<i class="fa fa-cog"></i> Manage
		</router-link>
	</h1>
	
	<div class="text-center" v-if="loaders.fetching">
		<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
	</div>

	<div class="row">
		<div class="col-md-10">
			<feed v-for="feed in feeds" :feed="feed" :key="feed.id"
				@feed-create-gig="showModalCreateGig"
				@feed-created-gig="onGigCreated">
			</feed>
		</div>
	</div>
	

	<infinite-scroll v-if="state.infinite && !reachedLast" 
		@infinite="loadOlderFeeds">
		<div slot="spinner" class="text-center">
			<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
		</div>
		<div slot="no-more" class="text-center">
			Looks like you've reached the end
		</div>
	</infinite-scroll>
</div>
</template>
<script>
import ApiMyGig from '../api/mygigs'
import mixinHub from '../mixins/hub'
import mixinMyGig from '../mixins/mygig'
import InfiniteScroll from 'vue-infinite-loading'
import GigFeedComponent from '../components/mygigs/GigFeed.vue'

export default {
	name: 'MyGigFeed',
	mixins: [mixinHub, mixinMyGig],

	components: {
		InfiniteScroll,
		'feed': GigFeedComponent
	},
	
	data () {
		return {
			form: {
				title: null,
				points: 10,
				platforms: [],
				feed_id: null
			},
			error: {},
			commonForms: {
				platforms: []
			},
			loaders: {
				fetching: false,
				creating_gig: false
			},
			feeds: [],
			state: {
				infinite: false
			},
			reachedLast: false,
			pagination: {
				next_page_url: null
			}
		}
	},
	
	mounted () {
		if (this.init) {
			this.initGigFeed()
		}

		$(this.$refs.modalGigContext).on('hidden.bs.modal', this.modalClearData)
	},

	destroy () {
		$(this.$refs.modalGigContext).modal('hide')

		$(this.$refs.modalGigContext).off('hidden.bs.modal', this.modalClearData)
	},

	beforeRouteLeave (to, from, next) {
		$(this.$refs.modalGigContext).modal('hide')

		$(this.$refs.modalGigContext).off('hidden.bs.modal', this.modalClearData)
		next()
	},

	watch: {
		'$route': 'initGigFeed',
		init (value) {
			if (value) {
				this.initGigFeed()
			}
		}
	},

	methods: {
		onGigCreated () {
			this.loaders.creating_gig = false
			$(this.$refs.modalGigContext).modal('hide')
		},
		createContext () {
			this.loaders.creating_gig = true
			this.$bus.$emit('feed-creating-gig', this.form)
		},
		modalClearData () {
			this.form.title = null
			this.form.points = 10
			this.form.platforms = []
			this.updatePlatformUI()
		},
		showModalCreateGig (feed_id) {
			this.form.feed_id = feed_id
			$(this.$refs.modalGigContext).modal('show')
		},
		updatePlatformUI ($event) {
			if ($event === undefined) 
				setTimeout(() => this.$refs.platforms.forEach(this.updatePlatform), 100)
			else {
				let $li = $($event.target).parents('li')
				this.updatePlatform($li)
			}
		},
		updatePlatform (target) {
			let $cb = $(target).find('input[type="checkbox"]'),
				$label = $cb.parents('label')
			if ($cb.is(':checked'))
				$label.addClass('selected')
			else
				$label.removeClass('selected')
		},
		initGigFeed () {
			this.fetchPlatforms()
			this.fetchFeeds()
		},
    fetchFeeds () {
			this.loaders.fetching = true
			this.fetchData()
				.then(feeds => {
					this.feeds = feeds.data
					this.pagination = _.omit(feeds, 'data')
					this.loaders.fetching = false
					this.state.infinite = true
					
					if (!this.pagination.next_page_url) {
						this.reachedLast = true
					}
				})
				.catch(error => {
					console.error(error)
					this.loaders.fetching = false
				})
		},
		loadOlderFeeds ($state) {
			if (this.reachedLast) 
				return

			this.fetchData()
				.then(feeds => {
					this.feeds = this.feeds.concat(feeds.data)
					let pagination = _.omit(feeds, 'data')
					this.pagination = pagination
					$state.loaded()
					if (!pagination.next_page_url) {
						this.reachedLast = true
					}
				})
				.catch(error => {
					console.error(error)
					this.reachedLast = true
					$state.loaded()
				})
		},
		fetchPlatforms () {
			const apiMyGig = new ApiMyGig(this.hub)

			apiMyGig.getPlatforms()
				.then(response => {
					let platforms = response.data.data.platforms.map(item => {
						return {
							id: item.id,
							name: item.name,
							platform: item.platform
						}
					})
					this.commonForms.platforms = platforms
				})
				.catch(error => {
					console.error(error)
				})
		},
		fetchData () {
			const apiMyGig = new ApiMyGig(this.hub)
			return new Promise((resolve, reject) => {
				apiMyGig.getGigFeeds(this.pagination.next_page_url)
					.then(response => {
						let feeds = response.data.data.feeds
						this.$store.commit('updateTotal', {
							feeds_list: feeds.total
						})
						resolve(feeds)
					})
					.catch(error => reject(error))
			})
		}
	},

	computed: {
		disabledCreateGig () {
			return this.loaders.creating_gig ||
				!this.form.title ||
				this.form.points < 10
		}
	},

	filters: {
		generateNameId (id, name) {
			return name + '-' + id
		},
		socialIcon (value) {
			const social = {
				facebook: 'fb3',
				twitter: 'tw3',
				linkedin: 'li3',
				pinterest: 'pin3',
				youtube: 'yt3',
				instagram: 'in3',	
			}
			return 'icon-'+social[value]
		}
	}
}
</script>