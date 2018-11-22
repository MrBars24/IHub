<template>
	<div class="submenu">
		<div class="container">
			<div class="text-center" v-if="!this.init">
				<i class="fa fa-spinner fa-pulse fa-fw"></i>
			</div>
			<ul class="row nav nav-tabs" v-else>
				<router-link :to="{ name: 'settings.account' }" 
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('lock')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>
				
				<router-link :to="{ name: 'settings.alerts' }" 
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('msg')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>

				<router-link :to="{ name: 'settings.messages' }" 
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('bell')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>

				<router-link :to="{ name: 'settings.profile' }" 
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('profile')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>
				
				<!-- TEMP: Remove this tab for now as per discussions earlier 18-02-06 10:36 -->
				<!-- <router-link :to="{ name: 'settings.hub' }" 
					v-if="!isHubManager"
					exact-active-class="active"
					class="col-xs-2 nav-item"
					tag="li" 
					exact>
					<a class="nav-link icon-home"></a>
				</router-link> -->

				<router-link :to="{ name: 'settings.community' }" 
					v-if="isHubManager"
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('group')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>

				<router-link :to="{ name: 'settings.influencer' }" 
					v-if="isHubManager"
					exact-active-class="active"
					:class="navClass"
					tag="li" 
					exact>
					<a>
						<svg-filler class="icon-container__icon center-block" :path="getSvgPath('star')" width="30px" height="30px" :fill="colorFill" :hover-color="hoverFill" />
					</a>
				</router-link>
			</ul>
		</div>
	</div>
</template>
<script>
import mixinUser from '../../mixins/user'
export default {
	name: 'SettingsMenu',
	
	mixins: [mixinUser],

	data () {
		return {
			colorFill: '#dddddd',
			hoverFill: '#df3f12'
		}
	},

	methods: {
		getFill () {
			
		}
	},

	computed: {
		navClass () {
			let col = !this.isHubManager ? 'col-xs-3' : 'col-xs-2'
			return `nav-item ${col}`
		}
	}
}
</script>