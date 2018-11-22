<template>
<transition appear name="fade" :duration="300" @after-leave="afterAnimationLeave">
	<div class="gig-item" :class="{'--expired': gig.has_expired}" :data-gig-id="gig.id">
		<div class="gig-item-inner">
			<div class="head clearfix">
				<div class="more-box" v-if="canEdit">
					<router-link :to="routeGig">
						<i class="fa fa-pencil" aria-hidden="true"></i>
					</router-link>
				</div><!-- /more-box -->

				<h1 class="gig-title js-gig-title">{{ gig.title }}</h1>

				<div class="head-bottom clearfix">
					<div class="head-point pull-left">
						<span>
							<svg-filler class="icon-container-static__icon head-point-icon" :path="getSvgPath('points')" width="24px" height="24px" :fill="'#DB3F20'" />
							{{ gig.points }}
							<label>Points</label>
						</span>
					</div><!-- /head-point -->

					<div class="head-social-media pull-right">
						<ul>
							<li v-for="platform in gig.platforms" :key="platform.id">
								<a>
									<!-- <img :src="platform.platform | socialIcon" :alt="platform.name"> -->
									<div :class="['icon-container-static--wbackground', '--active', svgfy(platform.name)]">
										<svg-filler class="icon-container-static__icon" :path="getSvgPath(platform.name)" width="30px" height="30px" :fill="colorFill" />
									</div>
								</a>
							</li>
						</ul>
					</div><!-- /head-social-media -->
				</div><!-- /head-bottom -->
			</div><!-- /head -->

			<div class="body js-gig-body">
				<p class="gig-description --clamped" v-html="gig.description_cached"></p>
				<p class="gig-ideas-label" v-if="gig.ideas">Wording ideas</p>
				<p :class="['gig-ideas --clamped', clampedLineClass]" v-if="gig.ideas" 
					v-html="gig.ideas_cached" @mouseup="toggleWordings">
				</p>
				<div class="body-media row clearfix" ref="bodyMedia" 
					v-if="firstTwoAttachments && firstTwoAttachments.length">
					<gig-attachment v-for="(attachment,index) in firstTwoAttachments" 
						:key="index"
						:attachments-count="firstTwoAttachments.length"
						:attachment="attachment">
					</gig-attachment>
				</div><!-- /body-media -->
			</div><!-- /body -->

			<div class="deadline-info clearfix">
				<p v-if="gig.deadline_at">
					Deadline: <b>{{ deadlineAt }}</b>
				</p>
			</div><!-- /deadline-info -->

			<div class="btn-accept" v-show="showAcceptButton">
				<router-link :to="routeWrite"
					class="btn-full-width js-branding-button">
					Accept
				</router-link>
			</div><!-- /btn-accept -->
			<div class="btn-ignore text-center" v-show="role == 'influencer'">
				<a href="#" @click.prevent.stop="ignore">Ignore this gig</a>
			</div>
		</div><!-- /gig-item-inner -->
	</div><!-- /gig-item -->
</transition>
</template>

<script>
import moment from "moment";
import GigAttachment from "../Attachment.vue";
import mixinUser from "../../../mixins/user";
import mixinHub from "../../../mixins/hub";
import ApiGig from "../../../api/gigs";

export default {
	mixins: [mixinUser, mixinHub],
	components: {
		GigAttachment
	},

	props: {
		gig: {
			required: true,
			type: Object
		},
		canEdit: Boolean,
		index: {
			type: Number,
			required: false
		},
		isCarouselMode: Boolean
	},

	data() {
		return {
			slickCurrentHeight : 0,
			colorFill: '#ffffff'
		};
	},

	computed: {
		showAcceptButton () {
			return this.role == 'influencer' && !this.isCarouselMode
		},

		routeWrite() {
			return {
				name: "write",
				params: {
					gig_id: this.gig.id,
					from: "gig"
				},
				query: {
					gig: this.gig.id
				}
			};
		},

		clampedLineClass () {
			return this.firstTwoAttachments && this.firstTwoAttachments.length ? 
				'--clamped-lines-4' : '--clamped-lines-12'
		},

		firstTwoAttachments() {
			if (this.gig.attachments && this.gig.attachments.length) {
				return this.gig.attachments.slice(0, 2);
			}
		},

		deadlineAt() {
			if (!this.gig.deadline_at) return;

			let deadline = moment(this.gig.deadline_at);
			return deadline.format("D MMMM YYYY");
		},

		routeGig() {
			return {
				name: "gigs.edit",
				params: {
					gig_slug: this.gig.slug,
					id: this.gig.id
				}
			};
		}
	},

	methods: {
		afterAnimationLeave(el) {
			// remove via jquery
			$(el)
				.parents(".slick-slide")
				.remove();
		},

		ignore() {
			const apiGig = new ApiGig(this.hub);

			apiGig
				.gigIgnore(this.gig.id)
				.then(response => {
					this.$emit("gig-ignored", this.index);
				})
				.catch(error => console.error(error));
		},

		toggleWordings(e) {
			e.target.classList.toggle('--clamped')
			this.$emit('gig-clamp-toggled')
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

		// toggleWordings(e) {
		// 	let slick = document.querySelector(".slick-slider").clientHeight;
		// 	if(e.target.classList.contains("--clamped-lines-4")){
		// 		if(slick <= 0){
		// 			if(e.target.classList.contains("toggle")){
		// 				e.target.classList.remove("toggle");
		// 			}else{
		// 				e.target.classList.add("toggle");
		// 				e.target.parentElement.style.height = "";
		// 				e.target.parentElement.parentElement.style.height = "";
		// 			}
		// 		}else{
		// 			if(e.target.classList.contains("toggle")){
		// 				e.target.classList.remove("toggle");
		// 				document.querySelector(".slick-current .gig-item-inner").style.height = this.slickCurrentHeight;
		// 			}else{
		// 				this.slickCurrentHeight = document.querySelector(".slick-current .gig-item-inner").style.height;
		// 				e.target.classList.add("toggle");
		// 				e.target.parentElement.style.height = "auto";
		// 				e.target.parentElement.parentElement.style.height = "";
		// 				document.querySelector(".slick-list").style.height = "auto";
		// 				document.querySelector(".slick-list .slick-track").style.height = "auto";
		// 			}
		// 		}
		// 	}
		// }
	},

	filters: {
		socialIcon(value) {
			if (!value) return;
			if (value == "facebook") value = "fb";
			let resolvedFile = resolveStaticAsset(`/images/icon-${value}.png`)
			return resolvedFile
		}
	},
};
</script>