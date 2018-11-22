<template>	
<div class="form-area social-accounts">
	<div class="form-row">
	<ul>
		<li :class="['item', itemClass(account)]" v-for="(account, index) in platforms" :key="index">
			<div class="linked-account-item">
				<div :class="['icon-container--wbackground', svgfy(account.platform)]">
					<svg-filler class="icon-container__icon" :path="getSvgPath(account.platform)" width="25px" height="25px" :fill="colorFill" />
				</div>
				<div class="account-item">
					<span class="platform-name">{{ account.platform }}</span>
					<a class="expired-label" :href="hrefy(account.platform)" v-if="account.expired_at"> <!-- refresh -->
						Account expired, tap to refresh
					</a>
					 <!-- account.id is undefined for non linked accounts -->
					<span class="account-name" v-if="account.id">{{ account.name }}</span>

					<a class="account-action" data-external="true" href="#" v-if="isInAppBrowser && !account.id"
						@click.stop.prevent="openInAppBrowser(hrefy(account.platform))">
						<i class="fa fa-angle-right icon-state"></i>
					</a>

					<a class="account-action" :href="hrefy(account.platform)" v-else-if="!isInAppBrowser && !account.id">
						<i class="fa fa-angle-right icon-state"></i>
					</a>

					<a class="account-action" @click.prevent.stop="markForDeletion($event, account)" href="#" v-else> <!-- remove -->
						<i class="fa fa-times icon-state" 
							v-if="!account.deleted && !account.removing">
						</i>
						<i class="fa fa-undo icon-state" 
							v-else-if="account.deleted && !account.removing">
						</i>
						<i class="fa fa-spin fa-spinner icon-state" 
							v-else-if="account.deleted && account.removing">
						</i>
					</a>
				</div>
			</div>
		</li>
	</ul>
	</div>
	<button type="submit"
		@click.prevent="removeLinkedAccounts" 
		:disabled="disabledButton"
		class="btn-submit js-branding-button">
		<i v-if="loaders.removing" class="fa fa-spinner fa-pulse fa-fw"></i> 
		SAVE
	</button>
</div>
</template>
<script>
import mixinUser from '../../mixins/user'
import mixinHub from '../../mixins/hub'
import mixinCordova from "../../mixins/cordova"
import { BASE_TOKEN } from '../../config/auth'
import { Platform } from 'quasar-framework'

export default {
	name: 'SocialAccount',

	mixins: [mixinHub, mixinCordova],

	props: {
		accountUserId: {
			type: [Number,Function],
			default: () => {
				return 0
			}
		}
	},

	data () {
		return {
			loaders: {
				removing: false
			},
			colorFill: '#ffffff',
			platforms: [
				{
					platform: 'facebook'
				},
				{
					platform: 'twitter'
				},
				{
					platform: 'linkedin'
				},
				{
					platform: 'pinterest'
				},
				{
					platform: 'youtube'
				},
				{
					platform: 'instagram'
				},
			],
			isInAppBrowser: false,
		}
	},

	mounted () {
		this.initializeAccounts()
		if (this.$route.params.$message) {
			let account = this.$route.params.$message
			alert(JSON.stringify(account))
			this.addToPlatforms(account)
		}
	},

	methods: {
		initializeAccounts () {
			let accounts = this.$store.state.user.accounts
			
			let mappedAccounts = this.platforms.map(account => {
				let origAccount = _.find(accounts, item => item.platform == account.platform)
				if (!origAccount) { // if no original account found. assign to local account
					if (account.id) { // has been deleted but not refreshed yet.
						// revert back to default form: {platform: platformName}
						let platform = account.platform
						account = {}
						this.$set(account, 'platform', platform)
					}
					origAccount = account
				}

				this.$set(origAccount, 'deleted', Boolean(origAccount.deleted_at)) // make it reactive
				this.$set(origAccount, 'removing', false) // make it reactive
				account = origAccount // revert back to modified object
				return account
			})
			console.log(mappedAccounts)
			// initialize broadcasting
			this.platforms = mappedAccounts
		},

		/**
		 * add newly linked account to existing platforms object
		 */
		addToPlatforms (account) {
			account = Object.assign({}, account, account)
			this.$set(account, 'deleted', Boolean(account.deleted_at)) // make it reactive. should be false
			this.$set(account, 'removing', false) // make it reactive
			let newAccountIndex = _.findIndex(this.platforms, accountPlatform => accountPlatform.platform == account.platform)
			this.$set(this.platforms, newAccountIndex, account)
			console.log(this.platforms)
		},

		markForDeletion ($event, account) {
			if (account.removing)
				return

			account.deleted = !account.deleted
		},

		itemClass (platform) {
			if (!platform.id) // non existed
				return

			return platform.expired_at ? '--active --expired' : '--active'
		},

		fafy (platform) {
			let name = platform
			if (name === 'pinterest') {
				name = 'pinterest-p'
			}
			else if (name === 'youtube') {
				name = 'youtube-play'
			}

			let fa = 'fa-' + name
			return fa
		},

		svgfy (platform) {
			let name = platform
			if (name === 'pinterest') {
				name = 'pinterest-p'
			}
			else if (name === 'youtube') {
				name = 'youtube-play'
			}

			let svg = 'svg-' + name
			return svg
		},
		
		hrefy (platform) {
			let hubSlug = this.hub.slug
			return `${BASE_TOKEN}social/${platform}?hub=${hubSlug}`
		},

		removeLinkedAccounts () {
			this.toggleRemoving(true) // spin the world. :)

			let payload = {
				hub: this.hub,
				account_ids: this.deletedAccountIds
			}

			this.$store.dispatch('updateLinkedAccounts', payload)
				.then(response => {
					// this.initializeAccounts()
					this.deletedAccounts.forEach(item => {
						let deletedAccountIndex = _.findIndex(this.platforms, account => account.id == item.id)
						let deletedAccount = this.platforms[deletedAccountIndex]
						deletedAccount = Object.assign({}, {
							platform: deletedAccount.platform,
							deleted: false,
							removing: false
						})
						// this.platforms[deletedAccountIndex] = deletedAccount
						this.$set(this.platforms, deletedAccountIndex, deletedAccount)
					})
					this.toggleRemoving(false)
				})
				.catch(error => {
					this.toggleRemoving(false)
				})
		},

		toggleRemoving (removing = true) {
			this.loaders.removing = removing
			this.deletedAccounts.forEach(item => {
				item.removing = removing
			})
		},

		handleAppRedirect (data) {
			alert(JSON.stringify(data))
			this.addToPlatforms(data)
		}
	},

	computed: {
		disabledButton () {
			return this.deletedAccounts.length == 0 || this.loaders.removing
		},

		deletedAccounts () {
			return this.platforms.filter(item => item.deleted)
		},

		deletedAccountIds () {
			return this.deletedAccounts.map(item => item.id)
		}
	},

	watch: {
		init(value) {
			console.log("watched")
			if (value) {
				this.initializeAccounts();
			}
		}
	}
}
</script>