<template>
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">

					<div class="text-center" v-if="loaders.fetching">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>

					<gig :gig="gig" v-if="gig"></gig>
					
				</div><!-- /gig-carousel-container -->
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</template>
<script>
import GigItem from '../components/gigs/carousel/Gig.vue'
import mixinHub from '../mixins/hub'
import mixinGig from '../mixins/gig'
import ApiGig from '../api/gigs'
import SlickSlider from '../components/SlickSlider.vue'

export default {
	name: 'GigView',

	components: { 
		Gig: GigItem,
		SlickSlider
	},

	mixins: [mixinHub, mixinGig],

	data () {
		return {
			loaders: {
				fetching: false,
			},
			gig: null
		}
	},

	mounted () {
		if(this.init) {
			this.fetchGigs()
		}
	},

	watch: {
		'$route' () {
			this.fetchGigs()
		},

		init (value) {
			if (value) {
				this.fetchGigs()
			}
		},
	},
	
	methods: {
		fetchGigs () {
			this.loaders.fetching = true
			const apiGig = new ApiGig(this.hub)
			apiGig.view(this.gigId)
				.then(response => {
					this.loaders.fetching = false
					this.gig = response.data.data.gig
				})
				.catch(error => {
					console.error(error)
					this.loaders.fetching = false
				})
		}
	}
}
</script>