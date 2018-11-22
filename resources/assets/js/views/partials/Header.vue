<template>
	<header>
		<nav id="pushmenu" class="pushmenu pushmenu-left" ref="pushMenu" v-if="isAuthenticated">
			<ul class="links">
				<li>
					<router-link :to="{ name: 'gigs' }" class="nav-menu-links" exact>
						Gig Carousel
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'newsfeed' }"  class="nav-menu-links" exact>
						Newsfeed
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'leaderboard' }"  class="nav-menu-links" exact>
						Leaderboard
					</router-link>
				</li>
				<li>
					<router-link :to="routeProfile" class="nav-menu-links">
						My Profile
					</router-link>
				</li>
				<li> 
					<router-link :to="{ name: 'messages' }" class="icon-speech nav-menu-links">
						Inbox
						<!-- <span class="js-branding-counter circle-counter">4</span> -->
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'messages.notifications' }" class="nav-menu-links">
						Notifications
						<!-- <span class="js-branding-counter circle-counter">5</span> -->
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'settings' }" exact class="nav-menu-links">
						Settings
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'my.gigs' }" exact class="nav-menu-links">
						My Gigs
					</router-link>
				</li>
				<li>
					<router-link :to="{ name: 'reporting' }" exact class="nav-menu-links" v-if="isHubManager">
						Reporting
					</router-link>
				</li>
			</ul>
		</nav>
		<div class="top-head" id="js-branding-header-colour">
			<div class="container">
				<div class="row">
					
					<div class="header-nav-area col-xs-3 col-md-1" v-if="isAuthenticated">
						<nav class="visible-lg visible-md">
							<div class="buttonset">
								<div id="nav_list" ref="navList">
									<div id="main-nav" class="main-nav edit-box hidden-xs">
										<img class="main-nav" :src="resolveStaticAsset('/images/img-main-nav.png')" alt="Image">
									</div>
								</div>
							</div>
						</nav>
						<div class="edit-box small-arrow visible-xs visible-sm" v-if="headerTitleText">
							<a href="#"
								@click.prevent.stop="$router.go(-1)">
								<img :src="resolveStaticAsset('/images/icon-left-arrow.png')" alt="Image" v-if="!isOnWrite">
								<span v-else>Cancel</span>
							</a>
						</div><!-- /edit-box -->
					</div><!-- /header-nav-area -->
					<div class="header-nav-area col-xs-3 col-md-1" v-else></div>

					<div class="header-title col-xs-6 col-md-4 col-md-offset-3">
						<div id="logo" v-show="!headerTitleText" v-cloak>
							<router-link :to="routeProfileHub"> 
								<img id="js-branding-header-logo" :alt="hub.name" :src="brandingLogo">
							</router-link>
						</div><!-- /logo -->

						<div class="head-txt text-center" v-if="headerTitleText">
							<p>{{ headerTitleText }}</p>
						</div><!-- /head-text -->

					</div>

					<div class="header-account-dropdown col-xs-3 col-md-4">
						<div class="img-circle-profile" id="menu-dropdown" v-if="isAuthenticated" ref="dropdown">
							<button class="btn btn-secondary dropdown-toggle" 
								type="button" 
								id="dropdownMenuButton"
								data-toggle="dropdown" 
								aria-haspopup="true" 
								aria-expanded="false">
								<img :src="user.profile_picture_tiny" 
									:alt="user.name" height="40" width="40">
							</button>

							<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
								<img :src="resolveStaticAsset('/images/img-arrow-up.png')" alt="Image" class="arrow-up">
								<div class="head clearfix">

									<div class="dropdown-area-name pull-left">
										<router-link 
											:to="routeProfile">
											{{ user.name }}
										</router-link>
									</div><!-- /dropdown-area-name -->

									<div class="dropdown-area-name pull-right" v-if="user.original && user.original.from_master">
										<span class="btn button-danger">Hub viewed from master</span>
									</div>

									<div class="dropdown-area-rank pull-right" v-if="membership && !isHubManager">
										<p>
											<span class="trophy-point">
												{{ membership.points }}
											</span> 
											<span class="point-txt">POINTS</span>

											<span class="rank-txt">&nbsp; | &nbsp; <b>#1</b></span>
										</p>
									</div><!-- /dropdown-area-rank -->
								</div><!-- /head -->

								<div class="body">
									<div class="dropdown-area clearfix hub-list" 
										:key="hub.id"
										v-for="hub in hubs"
										v-if="hubs.length > 1">
										<div class="dropdown-img-area pull-left">
											<img class="--profile-picture" :src="hub.profile_picture_tiny" 
												:alt="hub.name">
										</div>
										<router-link class="--name"
											:to="{ name: 'hub', params: { hub_slug: hub.slug }}">
											{{ hub.name }} 
										</router-link>
									</div>
								</div><!-- /body -->
								<div class="dropdown-area-bottom">
									<router-link :to="{ name: 'settings' }" exact>
										<svg-filler :path="getSvgPath('gear')" width="20px" height="20px" :fill="fill('999999')" /> <span>SETTINGS</span>
									</router-link>
								</div><!-- /dropdown-area-bottom -->
							</div>
						</div><!-- /img-circle -->
					</div>
				</div>
			</div><!-- /container -->
		</div><!-- /top-head -->
	</header><!-- /header -->
</template>
<script>
import mixinUser from "../../mixins/user";
import mixinHub from "../../mixins/hub";
import mixinMessages from "../../mixins/messages";
import mixinNotifications from "../../mixins/notifications";
import Hub from "../../api/hub";
import filters from "../../filters";
const hub = new Hub();
export default {
	name: "Header",

	mixins: [mixinUser, mixinHub, mixinMessages, mixinNotifications],

	data() {
		return {
			loaders: {
				hub: false
			}
		};
	},

	filters,

	mounted() {
		$(document).on("click", "#main-nav", this.toggleMenu);
		$("body").on("click", this.hideMenu);
		$(".dropdown-menu").on("click", this.preventClosing);
	},

	methods: {
		preventClosing(e) {
			e.stopPropagation();
			e.preventDefault();
			return;
		},
		hideMenu(event) {
			let target = event.target;
			if (
				target.id == "pushmenu" ||
				target.classList.contains("nav-menu-links") ||
				target.classList.contains("links") ||
				target.id == "#nav_list" ||
				target.classList.contains("main-nav")
			) {
				event.preventDefault();
				return;
			}
			// hide menu
			if (document.body.classList.contains("pushmenu-push-toright")) {
				this.toggleMenu();
			}
		},
		toggleMenu(event) {
			this.$refs.navList.classList.toggle("active");
			document.body.classList.toggle("pushmenu-push-toright");
			this.$refs.pushMenu.classList.toggle("pushmenu-open");
		},

		fill(unhex) {
			return '#' + unhex;
		}
	},

	computed: {
		brandingLogo() {
			if (!this.init || !this.isAuthenticated) {
				return resolveStaticAsset("/images/logo.png");
			}
			return this.hub.branding_header_logo_web_path;
		},

		routeProfile() {
			// route: hub profile
			if (this.isHubManager) {
				return this.routeProfileHub
			}

			return {
				name: "profile",
				params: {
					hub_slug: this.$route.params.hub_slug,
					user_slug: this.user.slug
				}
			}
		},

		routeProfileHub() {
			let hub_slug = this.$route.params.hub_slug
			return {
				name: 'profile',
				params: {
					hub_slug,
					user_slug: 'about'
				}
			}
		},

		headerTitleText() {
			const routeName = this.$route.name;
			if (!routeName) return null;

			if (routeName === "gigs.new") return "Create New Gig";
			else if (routeName === "gigs.edit") return "Edit Gig";
			else if (routeName === "message.old")
				return this.talkingTo ? this.talkingTo.name : "";
			else if (routeName === "message.new") return "Start Conversation";
			else if (routeName === "leaderboard") return "Leaderboard";
			else if (routeName === "messages.inbox") return "Messages";
			else if (routeName === "messages.notifications") return "Notifications";
			else if (routeName === "write") return "Write a Post";
			else if (routeName.match(/settings/)) return "Settings";
			else return null;
		},
		isOnWrite() {
			return this.$route.name === "write";
		},
		isOnNewsfeed() {
			return this.$route.name === "newsfeed";
		}
	}
};
</script>