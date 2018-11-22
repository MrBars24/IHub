<template>
<div class="leaderboard-area clearfix">
	<div class="lead-rank pull-left">
		<p>{{ rank }}</p>
	</div><!-- /lead-rank -->

	<div class="lead-profile pull-left clearfix">
		<router-link :to="profileRoute">
			<img :src="lead.user.profile_picture_tiny" 
				:alt="lead.user.name" 
				class="pull-left lead-avatar">
		</router-link>
		<h3 class="lead-user">
			<router-link :to="profileRoute">{{ lead.user.name }}</router-link>
		</h3>
		<p>{{ lead.points }} <span>POINTS</span></p>
	</div><!-- /lead-profile -->

	<div class="lead-icon pull-right">
		<!-- <img :src="leadIcon" alt="Image"> -->
		<svg-filler path="/images/icon-points.svg" width="30px" height="30px" :fill="leadColor" />
	</div><!-- /lead-icon -->
</div><!-- /leaderboard-area -->
</template>
<script>
import filters from '../../filters'
export default {
	filters,
	props: {
		rank: Number,
		lead: { // the Lead Data
			type: Object,
			required: true
		}
	},

	computed: {
		profileRoute () {
			let isHubManager = this.lead.user.object_class === 'Hub'
			return { 
				name: 'profile.home', 
				params: { 
					user_slug: isHubManager ? 'about' : this.lead.user.slug
				}
			}
		},

		leadColor () {
			let color = '#f5a194'
			if (this.rank == 1) {
				color = '#d49c14'
			}
			else if (this.rank == 2) {
				color = '#bbbbbb'
			}
			else if (this.rank == 3) {
				color = '#b47c5a'
			}
			return color
		},

		/**
		//  * leadIcon 
		//  * @return string 
		//  */		
		// leadIcon () {
		// 	let icon = "/images/icon-default-points.png"

		// 	if (this.rank == 1) 
		// 		icon = "/images/icon-gold-points.png"
		// 	else if (this.rank == 2) 
		// 		icon = "/images/icon-silver-points.png"
		// 	else if (this.rank == 3)
		// 		icon = "/images/icon-bronze-points.png"
		// 	return resolveStaticAsset(icon)
		// }
	}
}
</script>