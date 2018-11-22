<template>
<div id="display-area">	
	<div class="modal fade" ref="modalGigContext" 
		id="modalGigContext" v-if="context === 'gig'" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">You are about to publish this post</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p>Publishing options</p>
							<div class="form-field">
								<label for="publish-immediate">
									<input id="publish-immediate" type="radio" 
										v-model="publishing.type" value="immediate">
									Publish this post instantly
								</label>
								<label for="publish-scheduled">
									<input id="publish-scheduled" type="radio" 
										v-model="publishing.type" value="scheduled">
									Schedule this post
								</label>
							</div>
							<div class="form-field">
								<label :class="[{ '--disabled': publishing.type == 'immediate'}]">
									Schedule the publishing of this post at
								</label>
								<div class="row">
									<div class="col-sm-6">
										<input :disabled="publishing.type == 'immediate'" 
											type="date" v-model="scheduled_at.date">
									</div>
									<div class="col-sm-6">
										<select :disabled="publishing.type == 'immediate'" 
											v-model="scheduled_at.time" class="custom-select">
											<option :key="index" v-for="(time,index) in defaults.time">{{ time }}</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn-post js-branding-button" @click="submitPostCheckpoint"
						:disabled="loaders.submitting">
						<i v-if="loaders.submitting" class="fa fa-spinner fa-pulse fa-fw"></i> Publish
					</button>
					<button type="button" class="btn-post --default" data-dismiss="modal"
						:disabled="loaders.submitting">
						Cancel
					</button>
				</div>
			</div>
		</div>
	</div>
	<!-- attach link modal -->
	<attach-link @attach-link="attachLink" :context="form.context">
		<template slot="preview" scope="preview" v-if="preview.attachment.type">
			<post-attachment :attachment="preview.attachment"></post-attachment>
		</template>
	</attach-link>

	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-md-8 col-md-offset-2">
				<div class="row">
					<div class="col-md-12">
						<div class="bordered-shadow-box">
							<div class="write-post-area">
								<text-counter :show-counter="false"
									placeholder="Write your post..."
									:pre-text="form.message"
									:readonly="context === 'share'"
									@update-message="updateMessage($event)"
									v-model="form.message">
								</text-counter>
							</div><!-- /write-post-area -->
							<div class="post-uploader text-right">
									<div class="upload-area">
										<image-pre-upload @uploading="fileUploading" 
											v-show="context !== 'share'"
											@uploaded="fileUploaded"
											accept="image/*;video/*">
											<i title="Upload Attachment" slot="label" 
												class="btn-upload"><svg-filler title="Upload Attachment" slot="label" class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('link')" width="13px" height="13px" /></i>
										</image-pre-upload>
									</div>

									<div class="upload-area" v-show="context !== 'share'">
										<i class="btn-upload" data-toggle="modal" title="Attach a link"
											data-target="#modalAttachLinks"><svg-filler title="Upload Attachment" slot="label" class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('link')" width="13px" height="13px" /></i>
									</div>

									<div class="upload-area" v-if="context === 'gig'">
										<assets-gallery :collection="form.attachments"
											@selected="assetsGallerySelected">
										</assets-gallery>
									</div>
								</div>
						</div><!-- /bordered-shadow-box -->
					</div>
				</div>

				<div class="row">
					<div class="col-md-12 body-area">

						<!-- progress UI -->
						<div class="progress" v-show="showUploadProgress">
							<div class="progress-bar progress-bar-striped active" 
								ref="progressBar"
								role="progressbar" 
								:aria-valuenow="progress" 
								aria-valuemin="0" 
								aria-valuemax="100">
								<span class="sr-only">{{ progress }}% Complete</span>
							</div>
						</div>
						<div class="text-center" v-if="scrapeProgress.scraping">
							<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
						</div>

						<!-- Post component -->
						<!-- TODO: abstract post component -->
						<div class="post-container" v-if="form.attachment">
							<!-- <post :post="postData" :is-creating="true" class="content-area">	
								<button @click="removeAttachment" slot="remove-attachment"
									v-show="context !== 'share'"
									class="btn-remove pull-right">
								</button>
							</post> -->
							<post-attachment :attachment="form.attachment" v-if="form.attachment">
								<button @click="removeAttachment" slot="remove-attachment"
									v-show="context !== 'share'"
									class="btn-remove pull-right">
								</button>
							</post-attachment>              
							<div class="attachment-selectors --main-post">
								<a href="#" :class="['selector selector-link', selectAttachmentClass('link')]"
									@click.prevent.stop="showAttachment('link')">
									<!-- <i class="fa fa-link"></i> -->
									<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('link')" width="15px" height="15px" />
								</a>
								<a href="#" :class="['selector selector-image', selectAttachmentClass('image')]"
									@click.prevent.stop="showAttachment('image')">
									<!-- <i class="fa fa-image"></i> -->
									<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('image')" width="17px" height="16px" />
								</a>
								<a href="#" :class="['selector selector-video', selectAttachmentClass('video')]" 
									@click.prevent.stop="showAttachment('video')">
									<!-- <i class="fa fa-video-camera"></i> -->
									<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('video')" width="16px" height="16px" />

								</a>
							</div>
						</div>
						
						<!-- social platforms -->
						<div class="post-panel-container content-area" v-if="current.platforms.length">
							<social-post v-for="(platform,index) in current.platforms" 
								:platform="platform"
								:index="index"
								:attachments="form.attachments"
								:parent-message="form.message"
								:key="index">
							</social-post>
						</div>

					</div>
				</div>
			</div>
		</div>

	</div><!-- /container -->
</div><!-- /display-area -->
</template>
<script>
import TextCounter from "../components/TextCounter.vue";
import ImagePreUpload from "../components/ImagePreUpload.vue";
import AttachLink from "../components/AttachLink.vue";
import AssetsGallery from "../components/write/AssetsGallery.vue";
import SocialPost from "../components/write/SocialPost.vue";
import Post from "../components/newsfeed/Post.vue";
import PostAttachment from "../components/newsfeed/Attachment.vue";
import mixinHub from "../mixins/hub";
import mixinAuthoring from "../mixins/authoring";
import FileApi from "../api/file";
import PostApi from "../api/post";
import { urlPattern } from "../config/pattern";
import moment from "moment";

export default {
	name: "PostAuthoring",

	mixins: [mixinHub, mixinAuthoring],

	components: {
		TextCounter,
		ImagePreUpload,
		AttachLink,
		AssetsGallery,
		SocialPost,
		Post,
		PostAttachment
	},

	data() {
		return {
			form: {
				message: null,
				attachment: null,
				attachments: [],
				context: "newsfeed",
				platform_post: [],
				platform_post_id: []
			},
			loaders: {
				submitting: false
			},
			linked_accounts: null,
			uploadProgress: {
				progress: 0,
				uploaded: false
			},
			scrapeProgress: {
				scraping: false
			},
			canScrape: true,
			isLocalUpload: false,
			isScrapeUpload: false,
			pattern: urlPattern,
			// publishing for gigs
			publishing: {
				type: "immediate" // or scheduled
			},
			scheduled_at: {
				date: null,
				time: "1:00AM"
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
			originalText: null,
			colorFill: '#636b6f'
		};
	},

	mounted() {
		if (this.init) this.initiate();
		this.$bus.$on("post-authoring-submit", this.submitCheckpoint);
	},

	beforeDestroy() {
		this.$bus.$off("post-authoring-submit", this.submitCheckpoint);
		$(this.$refs.modalGigContext).off("hidden.bs.modal", () => {
			this.$bus.$emit("post-authoring-submitted");
		});
		this.$store.commit("resetCurrent");
		this.reset();
		$(".modal").modal("hide");
	},

	watch: {
		$route: "initiate",
		init(value) {
			if (value) {
				this.initiate();
			}
		},
		"form.message"(value) {
			if (value) this.checkIfScrapable();
			this.$store.commit("updateAuthoringMessage", value);
		},
		"form.attachment"(value) {
			this.$store.commit("updateAuthoringAttachment", value);
		},
		"form.attachments"(attachments) {
			this.$store.commit("updateAuthoringAttachments", attachments)
			this.$bus.$emit('post-authoring-update-payload') // update the subposts form object
		},
		"uploadProgress.progress"(value) {
			this.$refs.progressBar.style.width = value + "%";
		}
	},

	methods: {
		/**
		 * initiate the post authoring
		 * fetch the data from the endpoint
		 * @return {[type]} [description]
		 */
		initiate() {
			let $route = this.$route;
			const _postApi = new PostApi(this.hub);
			// construct the query string to pass into hub::post.write endpoint
			let queryString = "";
			if ($route.query.gig) queryString = "?gig=" + $route.query.gig;
			else if ($route.params.post_id)
				queryString = "?post=" + $route.params.post_id;

			_postApi
				.getWrite(queryString)
				.then(response => {
					let data = response.data.data;

					this.linked_accounts = data.platforms;
					// commit changes to store
					this.$store.commit("updateLinkedAccounts", this.linked_accounts);
					this.$store.commit("updateAuthoringContext", data.context);
					this.$store.commit("updateAuthoringPost", data.post);
					this.$store.commit("updateAuthoringGig", data.gig);
					this.$store.commit("updateAuthoringPlatformFields", data.platform_fields);

					// set main message
					// set attachment
					if (data.context !== "newsfeed") {
						// change the form.message into data.message_raw
						data.message =
							data.context === "share" ? data.post.message_raw : data.gig.ideas;

						if (data.context == "share") {
							data.attachment = data.post.attachment;
							data['attachments'] = data.post.attachments;
							if (data.attachment) {
								if (!data.attachment.source) {
									this.isLocalUpload = true;
								} else {
									this.isScrapeUpload = true;
								}
							} else {
								this.canScrape = false;
							}
						} else if (data.context == "gig") {
							data.attachment = data.gig.attachments[0];
							data.attachments = data.gig.attachments;
							// always local upload
							this.isLocalUpload = true;
							// this.canScrape = false;
						}
					}

					// omit the platforms because we will use the
					// form.platforms attribute in platforms selection
					data = _.omit(data, "platforms");
					Object.assign(this.form, data);
					this.originalText = this.form.message;

					// make sure that con
					if (data.context === "gig") {
						this.initializeSchedule();
						this.initializeModalEvents();
					}

					// check if there's an expired account
					let expiredAccounts = []
					_.forEach(this.linked_accounts, platform => {
						let list = platform.list.filter(account => Boolean(account.expired_at))
						expiredAccounts = _.concat(list, expiredAccounts)
					})

					// compose notification message 
					if (expiredAccounts.length) {
						let count = _.capitalize(this.numToWord(expiredAccounts.length))
						let message = `${count} of your social accounts have expired. 
							<a href="/${this.hub.slug}/settings" target="_blank">
								Go to your settings page
							</a>`
						this.$notify(message)
					}
				})
				.catch(error => {
					console.error(error);
				});
		},

		/**
		 * converts numeric digits to word
		 * NOTE: move this to global helper file
		 * 
		 * @param {Number}
		 */
		numToWord (number) {
			let word = 'a few'

			if (number == 1) {
				word = 'one'
			}
			else if (number == 2) {
				word = 'two'
			}
			
			return word
		},

		/**
		 * [initializeModalEvents description]
		 * @return {[type]} [description]
		 */
		initializeModalEvents() {
			setTimeout(() => {
				$(this.$refs.modalGigContext).on("hidden.bs.modal", () => {
					this.$bus.$emit("post-authoring-submitted");
				});
			}, 100);
		},

		/**
		 * [initializeSchedule description]
		 * @return {[type]} [description]
		 */
		initializeSchedule() {
			let now = moment().add(1, "hours");
			let date = now.format("YYYY-MM-DD");
			let minutes = now.format("mm");
			let hours = now.format("h");
			let ampm = now.format("A");
			let finalTime = null;
			if (minutes > 0 && minutes <= 15) {
				// 01:01
				minutes = 15;
			} else if (minutes > 15 && minutes <= 30) {
				// 01:01
				minutes = 30;
			} else if (minutes > 30 && minutes <= 45) {
				minutes = 45;
			} else if (minutes > 45 && minutes <= 60) {
				minutes = "00";
				hours = now.add(1, "hours").format("h");
			}

			finalTime = `${hours}:${minutes}${ampm}`;
			this.scheduled_at.time = finalTime;
			this.scheduled_at.date = date;
		},

		/**
		 * submit checkpoint
		 * @return {[type]} [description]
		 */
		submitCheckpoint() {
			// clean the form before passing to server
			this.$bus.$emit("getTaggedAccounts")

			this.$nextTick(() => {
				let payload = this.cleanForm(this.form)
				console.log(payload)

				if (!this.checkPayloadValidations(payload)) {
					this.$bus.$emit("post-authoring-submitted")
					return
				}

				if (this.context === "gig") {
					$(this.$refs.modalGigContext).modal("show")
				}
				else {
					this.submitPost(payload) // add a little delay to wait for the getTaggedAccounts execution to be completed
				}
			})
		},

		/**
		 * [submitPostImmediate description]
		 * @return {[type]} [description]
		 */
		submitPostCheckpoint() {
			// set scheduled_at field
			this.form.scheduled_at = null;
			if (this.publishing.type === "scheduled") {
				let scheduled_at = this.scheduled_at;
				this.form.scheduled_at = `${scheduled_at.date} ${scheduled_at.time}`;
			}

			// clean the payload
			let payload = this.cleanForm(this.form);

			this.submitPost(payload);
		},

		/**
		 * submit the post
		 * @param  {[type]} $event [description]
		 * @return {[type]}        [description]
		 */
		submitPost(payload) {
			this.loaders.submitting = true;
			console.log("submitting post");
			const _postApi = new PostApi(this.hub);

			// clean final payload
			payload = this.removeExtraFields(payload)

			_postApi
				.create(payload) // change to this.current? -> the form
				.then(response => {
					let routeName = 'newsfeed'
					if (this.form.context == "gig") {
						$(this.$refs.modalGigContext).modal("hide");
						routeName = 'gigs.carousel'
					}
					this.loaders.submitting = false;
					this.$bus.$emit("post-authoring-submitted");
					
					this.$router.push({
						name: routeName,
						params: {
							success: {
								type: "created",
								message: response.data.data.message
							}
						}
					});
				})
				.catch(error => {
					this.loaders.submitting = false;
					console.error(error.response.data.message);
					this.$bus.$emit("post-authoring-submitted");
				});
		},

		/**
		 * do some validations here before submitting to server
		 */  
		checkPayloadValidations (payload) {
			let subPostsWithErrors = payload.platform_post.some(post => post.has_error)

			let canPass = !subPostsWithErrors
			this.$bus.$emit('post-authoring-show-validations', !canPass)
			return canPass
		},

		removeExtraFields (payload) {
			payload.platform_post = payload.platform_post.map(post => {
				delete post.has_error
				return post
			})
			return payload
		},

		/**
		 * clean form before passing it to server
		 * @param  {[type]} form [description]
		 * @return {[type]}      [description]
		 */
		cleanForm(form) {
			let platforms = this.current.platforms;

			// map platform_post
			let platformsPosts = _.map(platforms, platform => {
				let obj = {
					name: platform.name,
					platform: platform.platform,
					message: platform.message,
					message_store: platform.message_store,
					linked_id: platform.id,
					native_id: platform.native_id,
					attachment_index: platform.attachment_index,
					has_error: platform.has_error
				};

				if (platform.platform === "youtube") {
					obj = Object.assign(obj, {
						youtube_title: platform.title,
						youtube_category: platform.category,
						youtube_category_title: platform.category_title
					});
				}

				return obj;
			});
			let platformIds = _.map(platforms, "native_id");

			// don't use Object.assign
			form.platform_post = platformsPosts; // platform_post
			form.platform_post_id = platformIds;

			// get the tagged users in each platform post

			// truncate all post and gig data except the id
			if (this.context === "gig") {
				form.gig = _.isObject(form.gig) ? form.gig.id : form.gig;

				// revert to database timestamp format
				if (form.scheduled_at) {
					this.form.scheduled_at = moment(
						form.scheduled_at,
						"YYYY-MM-DD hh:mmA"
					).format("YYYY-MM-DD HH:mm:ss");
				}
			} else if (this.context === "share") {
				form.post = _.isObject(form.post) ? form.post.id : form.post;
			}

			return form;
		},

		/**
		 * reset to defaults
		 * @return {[type]} [description]
		 */
		reset() {
			this.isScrapeUpload = false;
			this.isLocalUpload = false;
		},

		/**
		 * remove attachment
		 * 
		 * @param {String|Object} generalAttachmentType
		 * @return {[type]} [description]
		 */
		removeAttachment(generalAttachmentType = null) {
			// check: if generalAttachmentType parameter is a string, 
			// we can assume that we are passing is attachment type 
			// thus we can now remove the attachment of such type in the attachments
			if (typeof generalAttachmentType == "string") {
				this.form.attachments.forEach((attachment, index) => {
					let origGeneralType = this.getGeneralAttachmentType(attachment.type)
					if (origGeneralType == generalAttachmentType) {
						// if the selected/main attachment is equals to attachment, remove it as well
						if (_.isEqual(attachment, this.form.attachment))
							this.form.attachment = null
						this.form.attachments.splice(index, 1)
					}
				})
			}
			else {
				let currentAttachmentIndex = _.findIndex(this.form.attachments, this.form.attachment)
				this.form.attachments.splice(currentAttachmentIndex, 1)
				this.form.attachment = this.form.attachments[0]
			}
			this.isLocalUpload = false
			this.isScrapeUpload = false
			this.uploadProgress.progress = 0
			this.uploadProgress.uploaded = false
			// trigger image-preview-removed event in children
			this.$bus.$emit("image-preview-removed")
			this.$bus.$emit('post-authoring-update-payload')
		},

		/**
		 * check if the url pasted is scrapable
		 * @return {[type]} [description]
		 */
		checkIfScrapable() {
			if (!this.form.attachment && this.form.message) {
				let message = this.form.message.match(this.pattern);
				let url = null;
				if (message) {
					url = message[0];
					// append http if the url is not http | https
					url = url.indexOf("://") === -1 ? "http://" + url : url;
					if (this.canScrape) this.scrape(url);
				}
			}
		},
		/**
		 * scrape url
		 * @param  {[type]} url [description]
		 * @return {[type]}     [description]
		 */
		scrape(url) {
			const fileApi = new FileApi();
			this.scrapeProgress.scraping = true;
			fileApi
				.scrape({ url })
				.then(response => {
					if(response.data.success) {
						this.attachLink(response.data.data.attachment)
						this.isScrapeUpload = true;
						this.scrapeProgress.scraping = false;
					}else{
						this.isScrapeUpload = false;
						this.scrapeProgress.scraping = false;
					}
				})
				.catch(error => {
					this.scrapeProgress.scraping = true;
				});
		},

		attachLink(attachment) {
			let generalAttachmentType = this.getGeneralAttachmentType(attachment.type)
			if (this.checkIfTypeExisting(generalAttachmentType)) {
				this.removeAttachment(generalAttachmentType)
			}
			this.form.attachment = attachment
			this.form.attachments.unshift(attachment)
		},

		assetsGallerySelected(selected) {
			this.form.attachment = selected;
			// move to the first of collection
			this.form.attachments = _.without(this.form.attachments, this.form.attachment)
			this.form.attachments.unshift(this.form.attachment) // prepend
			this.isLocalUpload = true;
		},

		fileUploading(progress) {
			if (this.uploadProgress.uploaded) this.uploadProgress.uploaded = false;
			this.uploadProgress.progress = progress;
		},

		fileUploaded(file) {
			let generalAttachmentType = this.getGeneralAttachmentType(file.type)
			
			// check: if the generalAttachmentType is existing, replace
			if (this.checkIfTypeExisting(generalAttachmentType)) { 
				this.removeAttachment(generalAttachmentType);
			}

			// always put the new attachment to the first of collection
			this.form.attachment = file;
			this.form.attachments.unshift(file);

			this.uploadProgress.uploaded = true;
			this.uploadProgress.progress = 0;
			this.isLocalUpload = true;
		},

		/**
		 * checks if there's already a same attachment type in the attachments
		 * 
		 * @param {String} attachmentGeneralType
		 * @return {Boolean}
		 */
		checkIfTypeExisting (attachmentGeneralType) {
			let attachments = this.form.attachments.filter(attachment => {
				let origGeneralType = this.getGeneralAttachmentType(attachment.type)
				return origGeneralType === attachmentGeneralType
			})
			return attachments.length > 0
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

		fixDate(date) {
			if (!date) return;

			let finalDate = `${date.date} ${date.time.toLowerCase()}`;
			return moment(finalDate, "YYYY-MM-DD hh:mmA").format("YYYY-MM-DD HH:mm");
		},

		/**
		 * show attachment by passed general attachment type
		 *
		 * TODO: make the attachment as a computed property of attachment first element
		 * 
		 * @param {String} generalAttachmentType
		 * @return {void}
		 */
		showAttachment(generalAttachmentType) {
			if (!this.form.attachment.type) return;

			let attachment = this.getAttachmentsOfType(generalAttachmentType);

			// attachment of type not found
			if (!attachment.length) {
				return;
			}

			// if current attachment type is the same with passed attachment type, do nothing
			if (this.generalAttachmentType == generalAttachmentType) {
				return;
			}

			let currentAttachment = this.form.attachment
			if (this.context === 'share') {
				// not sure but i can't omit the current attachment using
				// _.without(this.form.attachments, this.form.attachment)
				this.form.attachments.splice(0, 1) 
			}
			else {
				this.form.attachments = _.without(this.form.attachments, this.form.attachment) // remove the current
			}

			this.form.attachment = attachment[0]; // assign the next object in array as the main attachment
			this.form.attachments = _.without(this.form.attachments, this.form.attachment) // remove the current

			this.form.attachments.unshift(currentAttachment) // prepend
			this.form.attachments.unshift(this.form.attachment) // prepend
		},

		/**
		 * get attachments with passed type.
		 *
		 * @param {String} generalAttachmentType
		 * @return {Array}
		 */
		getAttachmentsOfType(generalAttachmentType) {
			return this.form.attachments.filter(attachment => {
				let originalType = attachment.type.match(/youtube|vimeo/)
					? "link"
					: attachment.type;
				return originalType == generalAttachmentType;
			});
		},

		/**
		 * check if attachments has this passed type
		 *
		 * @param {String} generalAttachmentType
		 * @return {String}
		 */
		selectAttachmentClass(generalAttachmentType) {
			let attachments = this.getAttachmentsOfType(generalAttachmentType);
			let selectedAttachment = _.find(attachments, this.form.attachment);
			// attachment of type not uploaded yet.
			if (!attachments.length) {
				return "--disabled";
			} else if (attachments.length && selectedAttachment) {
				// attached, and selected as attachment
				return "--active";
			} else
				// attached, but not selected as attachment
				return "";
		}
	},
	computed: {
		/**
		 * truncate string
		 * @return {[type]} [description]
		 */
		truncatedDescription() {
			if (!this.form.attachment) return;

			return _.truncate(this.form.attachment.description);
		},

		/**
		 * upload progress UI
		 * @return {[type]} [description]
		 */
		showUploadProgress() {
			let progress = this.uploadProgress;
			return (
				progress.progress > 0 && progress.progress <= 100 && !progress.uploaded
			);
		},

		/**
		 * get the attachment type
		 * @return {[type]} [description]
		 */
		attachmentType() {
			if (!this.current.attachment) return "";
			return this.current.attachment.type;
		},

		/**
		 * get the general attachment type
		 *
		 * @return string link|video|image
		 */
		generalAttachmentType() {
			return this.attachmentType.match(/youtube|vimeo/)
				? "link"
				: this.attachmentType;
		},

		/**
		 * post component data
		 *
		 * @return {[type]} [description]
		 */
		postData() {
			return {
				attachment: this.form.attachment
			};
		}
	}
};
</script>