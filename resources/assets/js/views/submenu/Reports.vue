<template>
	<div class="submenu">
		<div class="container">
			<div class="text-center" v-if="!this.init">
				<i class="fa fa-spinner fa-pulse fa-fw"></i>
			</div>
			<ul class="row nav nav-tabs" v-else>
				<router-link :to="{ name: 'report.gigs' }"
					tag="li"
					:class="navClass"
					exact>
					<a class="nav-link icon-calendar" :class="{ 'selected' : $route.name === 'report.gigs' }"></a>
				</router-link>
				<router-link :to="{ name: 'report.influencers' }"
					tag="li"
					:class="navClass"
					exact>
					<a class="nav-link icon-profile" :class="{ 'selected' : $route.name === 'report.influencers' }"></a>
				</router-link>
				<router-link :to="{ name: 'report.alerts' }"
					tag="li"
					:class="navClass"
					exact>
					<a class="nav-link icon-msg" :class="{ 'selected' : $route.name === 'report.alerts' }"></a>
				</router-link>
				<router-link :to="{ name: 'report.social' }"
					tag="li"
					:class="navClass"
					exact>
					<a class="nav-link icon-share" :class="{ 'selected' : $route.name === 'report.social' }"></a>
				</router-link>
				<router-link :to="{ name: 'report.history' }"
					tag="li"
					:class="navClass"
					exact>
					<a class="nav-link icon-history" :class="{ 'selected' : $route.name === 'report.history' }"></a>
				</router-link>
			</ul>
		</div>
	</div>
</template>
<script>
export default {
	name: 'ReportMenu',

	computed: {
		navClass () {
			return `nav-item col-xs-20`
		}
	}
}
</script>