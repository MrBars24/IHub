<template>
<div class="container mobile-post-menu clearfix">
	<div class="footer-post-menu pull-left clearfix">
		<ul class="nav social-icons-list">
			<social-button v-for="(platform,platformName) in linkedAccounts" 
				:key="platformName"
				:platform-name="platformName"
				:platform="platform">
			</social-button>
		</ul>
	</div><!-- /footer-post-menu -->

	<button ref="postButton" 
		class="btn-post pull-right js-branding-button"
		:disabled="btnDisabled"
	 	@click="submit">
		<i v-if="loaders.post" class="fa fa-spinner fa-spin"></i> SHARE
	</button>
</div><!-- /mobie-footer-menu -->
</template>
<script>
import mixinHub from '../../mixins/hub'
import mixinAuthoring from '../../mixins/authoring'
import mixinUser from '../../mixins/user'
import SocialButton from './SocialButton.vue'
export default {
	components: {
		SocialButton
	},
	mixins: [mixinHub,mixinUser,mixinAuthoring],
	data () {
		return {
			socials: [],
			loaders: {
				post: false,
				submitting: false
			}
		}
	},
	mounted () {
		this.$bus.$on('post-authoring-submitted', this.submitted)	
	},
	beforeDestroy () {
		this.$bus.$off('post-authoring-submitted', this.submitted)
	},
	
	methods: {
		submitted () {
			this.loaders.post = false
			this.loaders.submitting = false
		},
		submit () {
			this.loaders.post = true
			this.loaders.submitting = true
			this.$bus.$emit('post-authoring-submit')
		}
	},
	computed: {
		btnDisabled () {
			let message = this.current.message
			
			return this.loaders.submitting || !message || 
				(this.context.match(/gig|share/) && !this.current.platforms.length)
				// this.subPostInvalid
		},

		subPostInvalid () {
			return this.current.platforms.some(platform => platform.has_error)
		}
	}
}
</script>