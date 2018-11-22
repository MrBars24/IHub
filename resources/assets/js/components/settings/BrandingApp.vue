<template>
	<main id="branding_app_preview" class="preview">
		<header>
			<div id="js-preview-branding-header-colour" class="top-head preview-top-head" 
				:style="headerStyle">
				<div class="header-nav-area col-xs-2 col-md-1">
					<nav class="visible-lg visible-md">
						<div class="buttonset">
							<div id="branding-nav_list">
								<div class="main-nav edit-box hidden-xs">
									<img :src="resolveStaticAsset('/images/img-main-nav.png')" alt="Image" class="main-nav">
								</div>
							</div>
						</div>
					</nav>
				</div>
				<div class="header-title col-xs-6 col-xs-offset-1 col-md-4 col-md-offset-3">
					<div id="branding-logo">
						<a href="#">
							<img id="js-preview-branding-header-logo" alt="Bode Contagion" 
								:src="brandingData.branding_header_logo_web_path">
						</a>
					</div>
				</div>
				<div class="header-account-dropdown col-xs-3 col-md-4">
					<div id="menu-dropdown" class="img-circle-profile">
						<button type="button" class="btn btn-secondary dropdown-toggle">
							<img height="40" width="40" :src="user.avatar" :alt="user.name">
						</button>
					</div>
				</div>
			</div>
		</header>
		
		<div class="row">
			<div class="col-md-12 preview-body">
				<button type="type" class="btn-submit js-preview-branding-button" :style="buttonStyle">
					Button
				</button>
			</div>
		</div>

	</main>
</template>

<script>
export default {
	name: 'BrandingApp',

	props: {
		brandingData: {
			type: Object,
			default () {
				return {
					"branding_header_colour": '#3a348c',
					"branding_header_colour_gradient": '#d13f26',
					"branding_header_logo": '/images/img-main-nav.png',
					'branding_header_logo_web_path': '/images/img-main-nav.png',
					"branding_primary_button": '#f5a194',
					"branding_primary_button_text": '#ffffff'
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
		headerStyle () {
			return `background: linear-gradient(to right, ${this.brandingData.branding_header_colour}, ${this.brandingData.branding_header_colour_gradient}) !important;`
		},
		buttonStyle () {
			return {
				color: this.brandingData.branding_primary_button_text,
				background: this.brandingData.branding_primary_button
			}
		}
	},

	methods : {
		isTermsPage () {
			console.log("test")
			return true;
		}
	}
}
</script>
