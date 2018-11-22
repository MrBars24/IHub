<template>
<div>
	<router-view name="submenu"></router-view>
	<!-- MODALS -->
	<div class="modal fade" id="modalConfirmDelete" ref="modalConfirmDelete" 
		tabindex="-1" role="dialog" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">You are about to delete this gig</h4>
				</div>
				<div class="modal-body">
					<h5>Are you sure you want to delete gig "{{ form.title }}"?.</h5>
				</div>
				<div class="modal-footer">
					<button type="button" :disabled="loaders.deleting" @click="deleteGig" 
						class="btn-submit js-branding-button">
						<i v-if="loaders.deleting" class="fa fa-spinner fa-pulse fa-fw"></i> DELETE
					</button>
					<button type="button" class="btn --default" 
						data-dismiss="modal">
						CANCEL
					</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<attach-link @attach-link="addToAttachment">
		<template slot="preview" scope="preview">
			<gig-attachment :attachment="preview.attachment"
				:is-viewing="false" :is-attaching="true" :ignore-classes="true">
			</gig-attachment>
		</template>
	</attach-link>

	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 col-md-8 col-md-offset-2" v-if="loaders.commonForms">
					<div class="text-center">
						<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
					</div>
				</div>
				<div class="col-sm-12 col-md-8 col-md-offset-2" v-else>
					<div class="detail-box">
						<div class="head">
							<p>Details</p>
						</div><!-- /head -->

						<div class="body">
							<div class="bordered-box form-gig">
								<div class="form-field">
									<label>Name</label>
									<input type="text" 
										id="title"
										v-model="form.title"
										placeholder="What do you want to call this gig?">

									<small class="text-danger" v-if="error.title">
										{{ error.title }}
									</small>
								</div><!-- /form-field -->

								<div class="form-field">
									<label>Places on offer</label>
									<input type="number" autocomplete="false" max="50" min="3"
									 	value="3"
										v-model.number="form.place_count">
									<small class="text-danger" v-if="error.place_count">
										{{ error.place_count }}
									</small>
								</div><!-- /form-field -->

								<div class="form-field clearfix">
									<div class="two-col-form pull-left">
										<label>Start</label>
										<input type="date" v-model="dates.commence_at.date" class="custom-select">
									</div><!-- /two-col-form -->

									<div class="two-col-form pull-right">
										<label>&nbsp;</label>
										<select v-model="dates.commence_at.time" class="custom-select">
											<option :key="index" v-for="(time,index) in defaults.time">{{ time }}</option>
										</select>
									</div><!-- /two-col-form -->  
								</div><!-- /form-field -->

								<div class="form-field clearfix">
									<div class="two-col-form pull-left">
										<label>Deadline</label>
										<input type="date" v-model="dates.deadline_at.date" class="custom-select">
									</div><!-- /two-col-form -->

									<div class="two-col-form pull-right">
										<label>&nbsp;</label>
										<select v-model="dates.deadline_at.time" class="custom-select">
											<option :key="index" v-for="(time,index) in defaults.time">{{ time }}</option>
										</select>
									</div><!-- /two-col-form -->  
								</div><!-- /form-field -->

								<div class="form-field">
									<label>Brief</label>
									<input type="text" 
										id="description"
										v-model="form.description"
										placeholder="What do you want this gig to achieve?">
									<small class="text-danger" v-if="error.description">
										{{ error.description }}
									</small>
								</div><!-- /form-field -->

								<div class="form-field">
									<label>Wording Ideas</label>
									<text-counter
										:pre-text="form.ideas" 
										v-model="form.ideas"
										ref="refIdeas"
										:show-counter="false"
										placeholder="What wording do you suggest to help your influencers to get started?"
										@input="listenMainWording">
									</text-counter>
									<small class="text-danger" v-if="error.ideas">
										{{ error.ideas }}
									</small>
								</div>

								<div class="form-field">
									<label>How many points are awarded for this gig?</label>
									<input type="number" autocomplete="false" step="5" id="points" min="10" max="1000"  placeholder="10" v-model.number="form.points">
									<small class="text-danger" v-if="error.points">
										{{ error.points }}
									</small>
								</div><!-- /form-field -->


								<div class="attachments-area form-field clearfix"> <!-- NOTE: skip for now -->
									<label>Content to be included in this gig</label>
									<div class="row" ref="gigAttachment">
										<gig-attachment :attachment="attachment"
											:index="index"
											:key="index"
											:is-viewing="false"
											:upload-progress="attachmentUploading"
											@remove="removeAttachment"
											v-for="(attachment,index) in form.attachments">
										</gig-attachment>
									</div>

									<input type="file" ref="media" @change="processAttachments" multiple class="hidden" 
										accept="image/*;video/*">

									<div class="row">
										<div class="col-xs-6">
											<a href="#" @click.prevent.stop="$refs.media.click()" 
												class="btn-full-width js-branding-button">ADD MEDIA
											</a>
										</div>
										<div class="col-xs-6">
											<a href="#" data-toggle="modal" data-target="#modalAttachLinks" 
												class="btn-full-width js-branding-button">ADD LINK
											</a>
										</div>
									</div>
								</div><!-- /form-field -->
							</div><!-- /bordered-box -->
						</div><!-- /body -->
					</div><!-- /detail-box -->

					<div class="detail-box">
						<div class="head">
							<p>Specifications</p>
						</div><!-- /head -->

						<div class="body">
							<div class="bordered-box form-gig">
								<div class="form-field">
									<label>Categories</label>
									<div class="row">
										<div class="col-md-6" v-for="(categories,index) in chunkedCategories" :key="index">
											<div class="checkbox-area list-label" v-for="category in categories" :key="category.id">
												<input class="styled-checkbox enabled-alerts" 
													:id="category.id | generateNameId('category')" 
													type="checkbox" 
													:value="category.id"
													v-model="form.categories">
												<label :for="category.id | generateNameId('category')">{{ category.name }}</label>
											</div><!-- /checkbox-area -->
										</div>
									</div>
								</div><!-- /form-field -->

								<div class="platforms-area form-field clearfix">
									<label>Platforms</label>
									<div class="prefered-platform-list">
										<ul class="platforms-list">
											<li v-for="(platform,index) in commonForms.platforms" ref="platforms" :key="index">
												<label :for="platform.platform | generateNameId('platform')"
													:class="platform.platform | socialIcon">
													<div :class="['icon-container-static--wbackground-lighter', svgfy(platform.name)]">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath(platform.name)" width="30px" height="30px" :fill="colorFill" />
													</div>
													<input :id="platform.platform | generateNameId('platform')" 
														type="checkbox" 
														v-model="form.platforms"
														:value="platform"
														@change="updatePlatformUI"
														class="hidden">
												</label>
											</li>
										</ul>
									</div>
								</div><!-- /form-field -->
							</div><!-- /bordered-box -->
						</div><!-- /body -->
					</div><!-- /detail-box -->

					<div class="detail-box" v-show="form.platforms.length">
						<div class="head">
							<p>Wording Ideas</p>

							<p><span>Tailor your wording ideas to your chosen platforms</span></p>
						</div><!-- /head -->

						<div class="body">
							<div class="bordered-box wording-ideas">
								<div class="panel-group sm-list">
									<div class="panel panel-default" v-show="platformFacebook">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-fb" 
													data-toggle="collapse" 
													href="#wi-facebook" 
													aria-expanded="false" 
													aria-controls="wi-facebook">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-facebook']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('facebook')" width="25px" height="25px" :fill="'white'" />
													</div>
													Facebook ideas
												</a>
											</h4>

											<span id="wi-facebook-counter" class="character-count pull-right">1500</span>
										</div><!-- /panel-heading -->
										<div class="collapse multi-collapse" id="wi-facebook" data-platform="Facebook">
											<div class="card card-body">
												<div class="form-field">
													
													<text-counter :max="1500" ref="refPlatformFacebook" 
														target="#wi-facebook-counter" 
														counter-suffix=""
														:pre-text="form.ideas_facebook"
														v-model="form.ideas_facebook"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->

									<div class="panel panel-default" v-show="platformTwitter">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-tw" 
													data-toggle="collapse"
													href="#wi-twitter" 
												 	aria-expanded="false"	
												 	aria-controls="wi-twitter">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-twitter']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('twitter')" width="25px" height="25px" :fill="'white'" />
													</div>
													Twitter ideas
												</a>
											</h4>

											<span id="wi-twitter-counter" class="character-count pull-right">280</span>
										</div><!-- panel-heading -->
										<div class="collapse multi-collapse" id="wi-twitter" data-platform="Twitter">
											<div class="card card-body">
												<div class="form-field">
													<text-counter :max="280" ref="refPlatformTwitter" 
														target="#wi-twitter-counter" 
														counter-suffix=""
														:pre-text="form.ideas_twitter"
														v-model="form.ideas_twitter"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->

									<div class="panel panel-default" v-show="platformLinkedin">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-li" 
													data-toggle="collapse"
													href="#wi-linkedin" 
												 	aria-expanded="false"	
												 	aria-controls="wi-linkedin">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-linkedin']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('linkedin')" width="25px" height="25px" :fill="'white'" />
													</div>
												 	Linkedin ideas
												</a>
											</h4>

											<span id="wi-linkedin-counter" class="character-count pull-right">1500</span>
										</div><!-- panel-heading -->
										<div class="collapse multi-collapse" id="wi-linkedin" data-platform="Linkedin">
											<div class="card card-body">
												<div class="form-field">
													
													<text-counter :max="1500" ref="refPlatformLinkedin" 
														target="#wi-linkedin-counter" 
														counter-suffix=""
														:pre-text="form.ideas_linkedin"
														v-model="form.ideas_linkedin"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->

									<div class="panel panel-default" v-show="platformPinterest">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-pin" 
													data-toggle="collapse" 
													href="#wi-pin"
													aria-expanded="false"	
													aria-controls="wi-pin">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-pinterest-p']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('pinterest')" width="25px" height="25px" :fill="'white'" />
													</div>
													Pinterest ideas
												</a>
											</h4>

											<span id="wi-pin-counter" class="character-count pull-right">1500</span>
										</div><!-- panel-heading -->
										<div class="collapse multi-collapse" id="wi-pin" data-platform="Pinterest">
											<div class="card card-body">
												<div class="form-field">
													
													<text-counter :max="1500" ref="refPlatformPinterest" 
														target="#wi-pin-counter" 
														counter-suffix=""
														:pre-text="form.ideas_pinterest"
														v-model="form.ideas_pinterest"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->

									<div class="panel panel-default" v-show="platformYoutube">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-yt" 
													data-toggle="collapse" 
													href="#wi-yt" 
													aria-expanded="false"	
													aria-controls="yt">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-youtube-play']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('youtube')" width="25px" height="25px" :fill="'white'" />
													</div>
													Youtube ideas
												</a>
											</h4>

											<span id="wi-yt-counter" class="character-count pull-right">1500</span>
										</div><!-- panel-heading -->
										<div class="collapse multi-collapse" id="wi-yt" data-platform="Youtube">
											<div class="card card-body">
												<div class="form-field">
													<text-counter :max="1500" ref="refPlatformYoutube"
														target="#wi-yt-counter" 
														counter-suffix=""
														:pre-text="form.ideas_youtube"
														v-model="form.ideas_youtube"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->

									<div class="panel panel-default" v-show="platformInstagram">
										<div class="panel-heading clearfix">
											<h4 class="panel-title pull-left">
												<a class="icon-in" 
													data-toggle="collapse" 
													href="#wi-in" 
													aria-expanded="false"	
													aria-controls="wi-in">
													<div :class="['icon-container-static--wbackground', '--active', 'svg-instagram']">
														<svg-filler class="icon-container-static__icon" :path="getSvgPath('instagram')" width="25px" height="25px" :fill="'white'" />
													</div>
													Instagram ideas
												</a>
											</h4>
											<span id="wi-in-counter" class="character-count pull-right">1500</span>
										</div><!-- panel-heading -->
										<div class="collapse multi-collapse" id="wi-in" data-platform="Instagram">
											<div class="card card-body">
												<div class="form-field">
													<text-counter :max="1500" ref="refPlatformInstagram"
														target="#wi-in-counter" 
														counter-suffix=""
														:pre-text="form.ideas_instagram"
														v-model="form.ideas_instagram"
														:parentText="mainWording">
													</text-counter>
												</div><!-- /form-field -->
											</div><!-- /card -->
										</div><!-- /collapse -->
									</div><!-- /panel -->
								</div><!-- /panel-group -->

							</div><!-- /bordered-box -->
						</div><!-- /body -->
					</div><!-- /detail-box -->

					<!--
					<div class="detail-box">
						<div class="head">
							<p>Rewards</p>
						</div>

						<div class="body">
							<div class="bordered-box form-gig">
								<div class="reward-box">
									<p><b>Reward(s) for influencers who complete the gigs</b></p>
									<div class="form-field">
										<div class="reward-box__list clearfix reward-holder add-field">
											<draggable v-model="form.rewards" element="ul" :options="{draggable: '.item'}">
												<li class="item reward-item add-field-item"
													v-for="(reward, index) in form.rewards" :key="index">
													<input placeholder="Reward description" v-model="reward.description" type="text">
													<a href="#" tabindex="-1" role="button" class="btn-remove" 
														@click.prevent.stop="removeReward(reward, index)">		
														<i class="fa fa-times"></i>
													</a>
												</li>
											</draggable>
										</div>
									</div>
									<a href="#" @click.prevent="addReward" 
										class="btn-full-width js-branding-button">ADD REWARD
									</a>

								</div>
							</div>
						</div>
					</div>
					-->

					<div class="checkbox-area checkbox-area-gig-approval">
						<div>
							<input v-model="form.require_approval" 
								class="styled-checkbox"
								id="styled-checkbox-1" 
								type="checkbox" 
								:value="commonForms.default_gig_require_approval" 
								checked>
							<label for="styled-checkbox-1">
								Gig requires approval by hub managers
							</label>
						</div>

						<div>
							<input class="styled-checkbox" 
								id="styled-checkbox-2"
								:checked="!form.is_active"
								@change="publishGig"
								type="checkbox">
							<label for="styled-checkbox-2" class="last">
								Unpublish Gig
							</label>
						</div>	

						<p><i>You can publish it again when it is ready</i></p>
					</div><!-- /checkbox-area -->

					<button type="button" :disabled="saveEnable" @click.prevent="save"
						class="btn-full-width btn-gig-save js-branding-button" 
						v-if="!isEditing">
						<i v-if="loaders.submitting" class="fa fa-spinner fa-spin"></i> Save
					</button>

					<button type="button" :disabled="saveEnable" @click.prevent="update"
						class="btn-full-width btn-gig-save js-branding-button" v-else>
						<i v-if="loaders.submitting" class="fa fa-spinner fa-spin"></i> Update
					</button>

					<div class="danger-area">
						<a href="#" class="btn-gig-delete" data-toggle="modal" 
							data-target="#modalConfirmDelete" v-if="isEditing">
							Delete Gig
						</a>
					</div>

				</div>
			</div>
		</div><!-- /container -->
	</div><!-- /display-area -->
</div>
</template>
<script>
import TextCounter from '../components/TextCounter.vue'
import GigAttachment from '../components/gigs/Attachment.vue'
import AttachLink from '../components/AttachLink.vue'
import GigReward from '../components/gigs/Reward.vue'
import FileUploader from '../components/FileUploader.vue'
import mixinHub from '../mixins/hub'
import mixinGigs from '../mixins/gig'
import FileApi from '../api/file'
import Gig from '../api/gigs'
import moment from 'moment'
import matchHeight from 'jquery-match-height'
// import Draggable from "vuedraggable";

export default {
	name: "CreateGig",

	mixins: [mixinGigs, mixinHub],

	components: {
		// Draggable,
		TextCounter,
		GigAttachment,
		GigReward,
		AttachLink,
		FileUploader
	},

	data() {
		return {
			name: 'CreateGig',
			colorFill: '#999999',
			form: {
				title: null,
				place_count: 0,
				description: null,
				ideas: null,
				ideas_facebook: null,
				ideas_twitter: null,
				ideas_linkedin: null,
				ideas_pinterest: null,
				ideas_youtube: null,
				ideas_instagram: null,
				attachments: [],
				points: 10,
				commence_at: null,
				deadline_at: null,
				rewards: [],
				categories: [],
				platforms: [],
				require_approval: false,
				is_active: true
			},
			error: {},
			loaders: {
				commonForms: false,
				submitting: false,
				deleting: false
			},
			dates: {
				commence_at: {
					date: moment().format("YYYY-MM-DD"),
					time: moment()
						.local()
						.format("HH:mm")
				},
				deadline_at: {
					date: moment()
						.add(14, "days")
						.format("YYYY-MM-DD"),
					time: moment()
						.add(14, "days")
						.format("HH:mm")
				}
			},
			commonForms: {
				platforms: [],
				categories: [],
				default_gig_require_approval: false
			},
			defaults: {
				time: [
					'1:00AM','1:15AM','1:30AM','1:45AM',
					'2:00AM','2:15AM','2:30AM','2:45AM',
					'3:00AM','3:15AM','3:30AM','3:45AM',
					'4:00AM','4:15AM','4:30AM','4:45AM',
					'5:00AM','5:15AM','5:30AM','5:45AM',
					'6:00AM','6:15AM','6:30AM','6:45AM',
					'7:00AM','7:15AM','7:30AM','7:45AM',
					'8:00AM','8:15AM','8:30AM','8:45AM',
					'9:00AM','9:15AM','9:30AM','9:45AM',
					'10:00AM','10:15AM','10:30AM','10:45AM',
					'11:00AM','11:15AM','11:30AM','11:45AM',
					'12:00PM','12:15PM','12:30PM','12:45PM',
					'1:00PM','1:15PM','1:30PM','1:45PM',
					'2:00PM','2:15PM','2:30PM','2:45PM',
					'3:00PM','3:15PM','3:30PM','3:45PM',
					'4:00PM','4:15PM','4:30PM','4:45PM',
					'5:00PM','5:15PM','5:30PM','5:45PM',
					'6:00PM','6:15PM','6:30PM','6:45PM',
					'7:00PM','7:15PM','7:30PM','7:45PM',
					'8:00PM','8:15PM','8:30PM','8:45PM',
					'9:00PM','9:15PM','9:30PM','9:45PM',
					'10:00PM','10:15PM','10:30PM','10:45PM',
					'11:00PM','11:15PM','11:30PM','11:45PM',
					'12:00AM','12:15AM','12:30AM','12:45AM'
				]
			},
			mainWording : "",
		};
	},

	mounted() {
		if (this.init) {
			this.initForms();
		}

		$.fn.matchHeight._throttle = 200;

		this.initializeSchedule();

		this.$nextTick(() => {
			if (!this.isEditing) {
				$(this.$el)
					.find(".collapse")
					.on("shown.bs.collapse", this.onCollapsed);
			}
		})
	},

	beforeRouteLeave(to, from, next) {
		if (this.isEditing) {
			$(this.$el)
				.find(".collapse")
				.off("shown.bs.collapse", this.onCollapsed);
			$(this.$refs.modalConfirmDelete).modal("hide");
		}
		next();
	},

	destroy() {
		if (this.isEditing) {
			$(this.$el)
				.find(".collapse")
				.off("shown.bs.collapse", this.onCollapsed);
			$(this.$refs.modalConfirmDelete).modal("hide");
		}
	},

	watch: {
		$route: "initForms",
		init(value) {
			if (value) {
				this.initForms();
			}
		},
		commence_at(value) {
			this.form.commence_at = value === "Invalid date" ? null : value;
		},
		deadline_at(value) {
			this.form.deadline_at = value === "Invalid date" ? null : value;
		},
		"form.attachments"(attachments) {
			if (attachments.length > 1) {
				// this.initializeAttachmentMediaHeight()
				setTimeout(() => {
					$(this.$refs.gigAttachment)
						.find(".up-media")
						.matchHeight();
				}, 2000);
			}
		},
		platformFacebook(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_facebook
					? this.form.ideas_facebook
					: this.form.ideas;
			}
			this.form.ideas_facebook = text;
		},
		platformTwitter(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_twitter
					? this.form.ideas_twitter
					: this.form.ideas;
			}
			this.form.ideas_twitter = text;
		},
		platformInstagram(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_instagram
					? this.form.ideas_instagram
					: this.form.ideas;
			}
			this.form.ideas_instagram = text;
		},
		platformYoutube(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_youtube
					? this.form.ideas_youtube
					: this.form.ideas;
			}
			this.form.ideas_youtube = text;
		},
		platformLinkedin(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_linkedin
					? this.form.ideas_linkedin
					: this.form.ideas;
			}
			this.form.ideas_linkedin = text;
		},
		platformPinterest(value) {
			if (value === undefined) {
				return;
			}

			let text = this.form.ideas;
			if (this.isEditing) {
				text = this.form.ideas_pinterest
					? this.form.ideas_pinterest
					: this.form.ideas;
			}
			this.form.ideas_pinterest = text;
		}
		// platforms: 'updatePlatformUI'
	},

	methods: {
		sanitizeText(text) {
			if (!text || !text.length) {
				return
			}
			return text.replace(/(<([^>]+)>)/gi, "");
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
		onCollapsed($evt) {
			let target = $evt.target;
			console.log(target, target.dataset)
			this.$refs[`refPlatform${target.dataset.platform}`].update();
		},
		// general
		initForms() {
			if (!this.isEditing) {
				this.form.rewards = []; // clear the rewards
				this.addReward(); // populate 1 reward field
				// fix dates
				this.form.commence_at = this.commence_at;
				this.form.deadline_at = this.deadline_at;
				this.fetchCommonForms();
			} else {
				this.getGig();
			}
		},

		deleteGig() {
			this.loaders.deleting = true;

			const apiGig = new Gig(this.hub);

			apiGig
				.deleteGig(this.gigId)
				.then(response => {
					console.log(response.data);
					this.loaders.deleting = false;
					let gig = response.data.data;
					this.$router.replace({
						name: "gigs.carousel",
						params: {
							success: {
								type: "deleted",
								message: `Gig "${this.form.title}" has been deleted.`
							}
						}
					});
				})
				.catch(error => {
					console.error(error);
					this.loaders.deleting = false;
				});
		},

		/**
		 * [initializeSchedule description]
		 * @return {[type]} [description]
		 */
		initializeSchedule() {
			this.dates.commence_at.time = this.createTime();
			this.dates.deadline_at.time = this.createTime(null, 14);
		},

		createTime(basedDate = null, daysToAdd = 0) {
			let now = null;
			if (basedDate) {
				now = moment(basedDate);
			} else {
				now = moment().local();
			}

			if (daysToAdd || daysToAdd > 0) now = now.add(daysToAdd, "days");

			let date = now.format("YYYY-MM-DD");
			let minutes = now.format("mm");
			let hours = now.format("h");
			let ampm = now.format("A");
			let finalTime = null;

			if (minutes > 0 && minutes <= 15) {
				// 01-15
				minutes = 15;
			} else if (minutes > 15 && minutes <= 30) {
				// 16 - 30
				minutes = 30;
			} else if (minutes > 30 && minutes <= 45) {
				// 31 - 45
				minutes = 45;
			} else if (minutes > 45 && minutes <= 60) {
				// 46 - 60
				minutes = "00";
				hours = now.add(1, "hours").format("h");
			}

			finalTime = `${hours}:${minutes}${ampm}`;
			return finalTime;
		},

		processAttachments($event) {
			let target = $event.target;
			if (!target.files.length && !target.files[0]) {
				return
			}

			const fileApi = new FileApi();
			for (let i = 0; i < target.files.length; i++) {
				let file = target.files[i]
				// create attachment placeholder
				let placeholder = {
					is_uploading: true,
					type: this.getTypeOfMime(file.type),
					upload_progress: 0,
					temp_id: _.uniqueId('placeholder-'),
				}
				this.addToAttachment(placeholder)
				let placeholderIndex = _.findIndex(this.form.attachments, placeholder)

				// upload files
				fileApi
					.upload(file, {
						onUploadProgress: (progressEvent) => {
							// attach placeholder attachment in progressEvent object
							progressEvent.placeholder = placeholder
							progressEvent.placeholderIndex = placeholderIndex
							this.attachmentUploading(progressEvent)
						}
					})
					.then(response => {
						let attachment = response.data.data.file;
						attachment.resource = attachment.path
						attachment.path = attachment.full_path
						// clean mime type
						let type = this.getTypeOfMime(attachment.type)
						attachment.type = type
						this.addToAttachment(attachment, placeholderIndex)
					})
					.catch(error => {
						// TODO: notify the client that their uploads failed.
						console.error(error);
					});
			}
			// process the file and render it to browser
		},

		/**
		 * TODO: optimize, make an ABSTRACT component for this one.
		 */
		attachmentUploading (progressEvent) {
			let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total)
			this.form.attachments[progressEvent.placeholderIndex].upload_progress = percent
		},

		/**
		 * get the type from mime
		 */
		getTypeOfMime(mime) {
			return mime.split("/")[0]
		},

		/**
		 * add files to attachments
		 * replace existing attachment if placeholderIndex is present.
		 * 
		 * @param {Number} placeholderIndex
		 */
		addToAttachment(attachment, placeholderIndex = -1) {
			if (placeholderIndex > -1) {
				this.$set(this.form.attachments, placeholderIndex, attachment)
			}
			else {
				this.form.attachments.push(attachment);
			}
		},

		getGig() {
			this.loaders.commonForms = true;
			const _gig = new Gig(this.hub);

			_gig
				.edit(this.gigId)
				.then(response => {
					const data = response.data.data;
					// map categories, platform
					data.gig.categories = data.gig.categories.map(item => item.id);
					data.gig.platforms = data.gig.platforms.map(this.mapPlatforms);

					// sanitize texts
					data.gig.description = this.sanitizeText(data.gig.description);
					data.gig.ideas = this.sanitizeText(data.gig.ideas);
					data.gig.ideas_facebook = this.sanitizeText(data.gig.ideas_facebook);
					data.gig.ideas_linkedin = this.sanitizeText(data.gig.ideas_linkedin);
					data.gig.ideas_twitter = this.sanitizeText(data.gig.ideas_twitter);
					data.gig.ideas_pinterest = this.sanitizeText(data.gig.ideas_pinterest)
					data.gig.ideas_instagram = this.sanitizeText(data.gig.ideas_instagram);
					data.gig.ideas_youtube = this.sanitizeText(data.gig.ideas_youtube);

					Object.assign(this.form, data.gig);

					this.fixCommonForms(data.commonForms);
					// fix the dates
					let commence_at = moment(this.form.commence_at);
					let deadline_at = moment(this.form.deadline_at);
					this.dates.commence_at.date = commence_at.format("YYYY-MM-DD");
					this.dates.deadline_at.date = deadline_at.format("YYYY-MM-DD");

					this.dates.commence_at.time = this.createTime(this.form.commence_at);
					this.dates.deadline_at.time = this.createTime(this.form.deadline_at);

					setTimeout(() => {
						$(this.$el)
							.find(".collapse")
							.on("shown.bs.collapse", this.onCollapsed);
					}, 500)
				})
				.catch(error => {
					console.error(error.message);
				})
				.then(() => {
					this.loaders.commonForms = false;
				});
		},

		fetchCommonForms() {
			const _gig = new Gig(this.hub);
			_gig
				.getCreate()
				.then(response => {
					let data = response.data.data;
					this.fixCommonForms(data);
				})
				.catch(error => console.error(error));
		},

		fixCommonForms(commonForms) {
			commonForms.platforms = commonForms.platforms.map(this.mapPlatforms);
			Object.assign(this.commonForms, commonForms);
			this.form.require_approval = commonForms.default_gig_require_approval;
			this.updatePlatformUI();
		},

		save($event) {
			this.loaders.submitting = true;

			this.cleanForm();

			const _gig = new Gig(this.hub);
			_gig
				.store(this.form)
				.then(response => {
					this.loaders.submitting = false;
					let gig = response.data.data;
					this.$router.replace({
						name: "gigs.carousel",
						params: {
							success: {
								type: "created",
								message: `Gig "${gig.title}" has been created.`
							}
						}
					});
				})
				.catch(error => {
					console.error(error);
					this.loaders.submitting = false;
				});
		},

		update($event) {
			this.loaders.submitting = true;

			this.cleanForm();

			const _gig = new Gig(this.hub);
			_gig
				.update(this.form)
				.then(response => {
					this.loaders.submitting = false;
					let gig = response.data.data;

					this.$router.replace({
						name: "gigs.carousel",
						params: {
							success: {
								type: "updated",
								message: `Gig "${gig.title}" has been updated.`
							}
						}
					});
				})
				.catch(error => {
					console.error(error);
					this.loaders.submitting = false;
				});
		},

		updatePlatformUI($event) {
			if ($event === undefined)
				setTimeout(
					() => this.$refs.platforms.forEach(this.updatePlatform),
					100
				);
			else {
				let $li = $($event.target).parents("li");
				this.updatePlatform($li);
			}
		},

		updatePlatform(target) {
			let $cb = $(target).find('input[type="checkbox"]'),
				$label = $cb.parents("label");
			if ($cb.is(":checked")) $label.addClass("selected");
			else $label.removeClass("selected");
		},

		// Rewards
		addReward() {
			let lastId = _.tail(this.form.rewards).id;

			let reward = {
				description: null
			};
			this.form.rewards.push(reward);
		},

		removeReward(reward, index) {
			if (reward.id !== undefined) {
				// perform ajax request to delete the reward from rewards
				const _gig = new Gig(this.hub);
				_gig
					.deleteReward(this.form.id, reward.id)
					.then(response => this.form.rewards.splice(index, 1))
					.catch(error => console.error(error));
			} else {
				this.form.rewards.splice(index, 1);
			}
		},

		removeAttachment({ index, id }) {
			if (id !== undefined) {
				// perform ajax request to delete the attachment from gig_attachment
				const _gig = new Gig(this.hub);
				_gig
					.deleteAttachment(this.form.id, id)
					.then(response => this.form.attachments.splice(index, 1))
					.catch(error => console.error(error));
			} else {
				this.form.attachments.splice(index, 1);
			}
		},

		/// Helpers
		findPlatform(platform) {
			return this.form.platforms.find(item => item.platform === platform);
		},

		publishGig($e) {
			this.form.is_active = !$e.target.checked;
		},

		cleanForm() {
			// fix rewards
			this.form.rewards = this.form.rewards.filter(item =>
				Boolean(item.description)
			);

			// sanitize inputs
			this.form.description = this.sanitizeText(this.form.description);
			this.form.ideas = this.sanitizeText(this.form.ideas);
			this.form.ideas_facebook = this.sanitizeText(this.form.ideas_facebook);
			this.form.ideas_linkedin = this.sanitizeText(this.form.ideas_linkedin);
			this.form.ideas_twitter = this.sanitizeText(this.form.ideas_twitter);
			this.form.ideas_pinterest = this.sanitizeText(this.form.ideas_pinterest);
			this.form.ideas_instagram = this.sanitizeText(this.form.ideas_instagram);
			this.form.ideas_youtube = this.sanitizeText(this.form.ideas_youtube);

			// fix dates
			let dates = this.dates;
			let commence_at = this.fixDate(dates.commence_at);
			let deadline_at = this.fixDate(dates.deadline_at);
			this.form.commence_at = commence_at;
			this.form.deadline_at = deadline_at;
		},

		fixDate(date) {
			if (!date) return;

			let finalDate = `${date.date} ${date.time.toLowerCase()}`;
			let parsedDate = moment(finalDate, "YYYY-MM-DD hh:mmA").format(
				"YYYY-MM-DD HH:mm:ss"
			);
			return parsedDate;
		},

		mapPlatforms(item) {
			return {
				id: item.id,
				name: item.name,
				platform: item.platform
			};
		},
		listenMainWording(val) {
			this.mainWording = val;
		}
	},

	computed: {
		platformFacebook() {
			return this.findPlatform("facebook");
		},
		platformTwitter() {
			return this.findPlatform("twitter");
		},
		platformInstagram() {
			return this.findPlatform("instagram");
		},
		platformYoutube() {
			return this.findPlatform("youtube");
		},
		platformLinkedin() {
			return this.findPlatform("linkedin");
		},
		platformPinterest() {
			return this.findPlatform("pinterest");
		},
		isEditing() {
			return this.$route.meta.edit !== undefined;
		},
		chunkedCategories() {
			let categories = this.commonForms.categories;
			let chunkCount = Math.ceil(categories.length / 2);
			return _.chunk(categories, chunkCount);
		},
		saveEnable() {
			let form = this.form;
			return (
				!form.title ||
				!form.description ||
				!form.ideas ||
				!form.points ||
				this.loaders.submitting ||
				this.loaders.deleting
			);
		},
		commence_at() {
			let datetime = this.dates.commence_at;
			return moment(`${datetime.date} ${datetime.time}`).format(
				"YYYY-MM-DD HH:mm:ss"
			);
		},
		deadline_at() {
			let datetime = this.dates.deadline_at;
			return moment(`${datetime.date} ${datetime.time}`).format(
				"YYYY-MM-DD HH:mm:ss"
			);
		},
		platforms() {
			return this.form.platforms;
		}
	},

	filters: {
		generateNameId(id, name) {
			return name + "-" + id;
		},
		socialIcon(value) {
			const social = {
				facebook: "fb3",
				twitter: "tw3",
				linkedin: "li3",
				pinterest: "pin3",
				youtube: "yt3",
				instagram: "in3"
			};
			return "icon-" + social[value];
		},
		wording(value, platform) {
			return value ? value : platform + " ideas";
		}
	}
};
</script>