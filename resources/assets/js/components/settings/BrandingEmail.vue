<template>
	<main id="branding_email_preview" class="preview">
		<header>
			<div id="js-preview-branding-header-colour" class="top-head preview-top-head" 
				:style="headerStyle">
				<div id="branding-logo">
					<a href="#">
						<img id="js-preview-branding-header-logo" alt="Bode Contagion" 
							:src="brandingData.email_logo_web_path">
					</a>
				</div>
			</div>
		</header>

		<div class="preview-body email-body">
			<div class="row">
				<div class="col-xs-3 image-area">
					<img :src="user.avatar" :alt="user.name" class="email-sender-avatar">
				</div>
				<div class="col-xs-9 content-area">
					<h1 class="email-notification-title">
						Notification Summary
					</h1>
					<p class="email-notification-description">
						Notification description
					</p>
					<p style="email-notification-action">
						<a target="_blank" class="btn-submit email-notification-button" :style="buttonStyle">
							View
						</a>
					</p>
				</div>
			</div>
		</div>

		<footer>
			<div class="bottom preview-bottom email-footer" :style="footerStyle">
				<div class="email-footer-logo-container">  
					<div id="email-footer-logo">
						<a href="#">
							<img id="js-preview-branding-footer-logo" alt="Bode Contagion" 
								:src="brandingData.email_logo_web_path">
						</a>
					</div>
				</div>
				<div class="email-footer-menu">
					<ul :style="menuStyle" class="asd">
						<li><a href="#">Home</a></li>
						<li><a href="#">Privacy</a></li>
						<li><a href="#">Account Settings</a></li>
					</ul>
				</div>
				<span class="email-footer-copyright" :style="copyrightStyle">
					Copyright &copy; Influencer HUB {{ year }}
				</span>
			</div>
		</footer>

	</main>
</template>

<script>
export default {
	name: 'BrandingEmail',

	props: {
		brandingData: {
			type: Object,
			default () {
				return {
					'email_logo': null,
					'email_logo_web_path': null,
					'email_header_colour': '#20272d',
					'email_footer_colour': '#20272d',
					'email_footer_text_1': '#ffffff',
					'email_footer_text_2': '#5e5e5e',
					'branding_primary_button': '#f5a194',
					'branding_primary_button_text': '#ffffff'
				}
			}
		},
		user: {
			type: Object,
			default () {
				return {
					name: null,
					avatar: null
				}
			}
		}
	},

	computed: {
		year () {
			return new Date().getFullYear()
		},

		headerStyle () {
			return `background: ${this.brandingData.email_header_colour};`
		},

		footerStyle () {
			return `background: ${this.brandingData.email_footer_colour};`
		},

		copyrightStyle () {
			return {
				color: this.brandingData.email_footer_text_2
			}
		},

		menuStyle () {
			return {
				color: this.brandingData.email_footer_text_1
			}
		},

		buttonStyle () {
			return {
				color: this.brandingData.branding_primary_button_text,
				background: this.brandingData.branding_primary_button
			}
		}
	}
}
</script>
