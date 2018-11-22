<template>
	<li class="dropup" ref="dropup">
		<!-- <a @click.prevent.stop="showDropdown" v-if="!isAllExpired"
			:class="[platformClass, enabled, 'platform-icon']">
			
		</a> -->
		<div @click.prevent.stop="showDropdown" v-if="!isAllExpired"
			:class="[platformClass, enabled, 'platform-icon', 'icon-container-static--wbackground-lighter', svgfy(platformName)]">
			<svg-filler class="icon-container-static__icon" :path="getSvgPath(platformName)" width="30px" height="30px" :fill="colorFill" />
		</div>
		<div class="dropdown-menu accounts-dropdown">
			<img :src="resolveStaticAsset('/images/img-arrow-down.png')" alt="Image" class="arrow-down">
			<ul class="social-accounts-list" ref="social-list">
				<li v-for="(account, index) in platform.list" class="account-list-wrapper" :key="index" v-if="!isExpired(account.expired_at)">
					<a href="#" @click.prevent="toggleAccount($event, account)" 
						:class="['account-list', isExpired(account.expired_at)]">
						<!-- <img class="social-account-avatar hidden" src="/images/img-profile-30.jpg" alt="Image"> -->
						<span class="social-account-name">{{ account.name }}</span>
					</a>
				</li>
			</ul>
		</div>
	</li>
</template>
<script>
import mixinAuthoring from '../../mixins/authoring'
import mixinUser from '../../mixins/user'
export default {
	mixins: [mixinAuthoring, mixinUser],
	props: {
		platform: {
			type: Object, // we are now expecting the accounts from backend
			required: true
		},
		platformName: {
			type: String,
			required: true
		}
	},
	data () {
		return {
			rules: {
				instagram: {
					attachment: {
						type: 'image|video',
						required: true
					}
				},
				pinterest: {
					attachment: {
						type: 'image',
						required: true
					}
				},
				youtube: {
					attachment: {
						type: 'video', // allowed only
						required: true
					}
				}
			},
			isAllExpired: true,
			colorFill: '#999999'
		}
	},

	mounted () {
		$('body').on('click', this.hideDropdown)
		this.isAllListExpired();
	},

	beforeDestroy () {
		$('body').off('click', this.hideDropdown)
	},

	methods: {
		isExpired (hasExpired) {
			return hasExpired ? '--expired' : ''
		},

		svgfy (value) {
			value = value.toLowerCase()
			let platform = value
			if (value == 'pinterest')
				platform = 'pinterest-p'
			else if (value == 'youtube')
				platform = 'youtube-play'

			return 'svg-' + platform
		},

		/**
		 * categorize the 5 attachment types
		 * 
		 * @param {String} type
		 * @return {String}
		 */
		getGeneralAttachmentType(type) {
			return type.match(/youtube|vimeo/) ? 'link' : type
		},
		
		/**
		 * get attachments with passed type.
		 *
		 * @param {String} generalAttachmentType
		 * @return {Array}
		 */
		getAttachmentsOfType(generalAttachmentType) {
			return this.current.attachments.filter(attachment => {
				let originalType = attachment.type.match(/youtube|vimeo/)
					? "link"
					: attachment.type;
				return originalType == generalAttachmentType;
			});
		},

		hideDropdown (e) {
			let target = e.target
			if (target.classList.contains('dropup') || 
					target.classList.contains('platform-icon') ||
					target.classList.contains('dropdown-menu') ||
					target.classList.contains('account-list') ||
					target.classList.contains('account-list-wrapper') ||
					target.classList.contains('social-account-name')) {
					// target.classList.contains('social-account-avatar')
				e.preventDefault()
				return
			}

			$('.dropup').removeClass('open')
		},
		toggleAccount($event, account) {
			let btn = $event.target
			if (btn.nodeName !== 'A') {
				btn = $event.target.parentElement
			}
			
			if (btn.classList.contains('--expired')) {
				return
			}

			btn.classList.toggle('selected')

			let finder = platform => platform.platform === this.platformName
			let found = _.find(this.current.platforms, finder)
			let exists = _.find(this.current.platforms, item => item.native_id === account.native_id)
			let filter = this.current.platforms.filter(finder)
			if (!found) {
				if (!this.$refs.dropup.classList.contains('selected')) {
					this.$refs.dropup.classList.add('selected')
				}
			}
			else {
				if (exists && filter.length <= 1) {
					this.$refs.dropup.classList.remove('selected')
				}
			}
			// append the platform name into account object
			account.platform = this.platformName
			this.$store.dispatch('updateAuthoringPlatform', account)
		},
		showDropdown ($event) {
			let $btn = $event.target.parentNode
			
			if($btn.tagName == 'svg') $btn = $btn.parentNode

			if (!$btn.classList.contains('--enabled'))
				return

			let parentsLi = document.querySelector('.social-icons-list').children
			let filtered = _.filter(parentsLi, el => el != $btn.parentElement)
			for (let i = 0; i < filtered.length; i++) {
				filtered[i].classList.remove('open')
			}
			$btn.parentElement.classList.toggle('open')
		},

		isAllListExpired () {
			for(var i=0; i<this.platform.list.length; i++) {
				if(this.platform.list[i].expired_at == null) {
					this.isAllExpired = false;
				}
			}
		}
	},

	computed: {
		/**
		 * @desc: get the supported attachment types for specific platform
		 *
		 * @return {Array}
		 */
		supportedAttachments() {
			let supported = [];
			if (this.platformName === "pinterest") {
				supported.push("image");
			} else if (this.platformName === "youtube") {
				supported.push("video");
			} else if (this.platformName === "instagram") {
				supported.push("video", "image");
			} else {
				// facebook, twitter, linkedin
				supported.push("video", "image", "link");
			}
			return supported;
		},
		hasSelectedAccount () {
			let finder = platform => platform.platform === this.platformName
			let found = _.find(this.current.platforms, finder)
			return Boolean(found)
		},
		platformClass () {
			return `icon-${this.platformName}2-hover`
		},
		enabled () {
			let status = true

			if (this.isRuleFollowed && !this.supportedTypesUploaded.length) {
				status = false
			}

			if (this.context === 'gig') {
				// check if the gig has allowed the platform
				// this.platform.disabled is not defined when enabled
				let _status = status
				status = this.platform.disabled === undefined && _status
			}
			return status ? '--enabled' : null
		},
		supportedTypesUploaded() {
			let supportedTypesUploaded = this.current.attachments.filter(attachment => {
				let origGeneralAttachmentType = this.getGeneralAttachmentType(attachment.type)

				return this.supportedAttachments.includes(origGeneralAttachmentType)
			})
			return supportedTypesUploaded
		},
		isRuleFollowed () {
			let rule = this.rules[this.platformName]
			return rule && this.supportedAttachments.some(type => type.match(rule.attachment.type))
		}
	},
}
</script>