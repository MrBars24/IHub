<template>
	<div class="post-panel-item post-attachment bordered-shadow-box">
		<div class="row" v-bind:class="{ 'row--collapse': isCollapse }">
			<div class="write-post-area post-area col-sm-11">
				<div class="write-post-profile clearfix">
					<a :href="collapseTarget | hashify"
						data-toggle="collapse"
						role="button"
						tabindex="-1"
						aria-expanded="true" :aria-controls="collapseTarget">
						<!-- @click="collapseButtons()" -->
						<span class="social-post-title">{{ platform.name }}</span>
						<div :class="[platformClass, enabled, 'pull-right', 'w-30', '--active', 'icon-container-static', svgfy(platformName)]">
							<svg-filler class="icon-container-static__icon" :path="getSvgPath(platformName)" width="30px" height="30px" :fill="socialFill" />
						</div>

					</a>
				</div><!-- /write-post-profile -->
				<div class="write-post-social collapse in" :id="collapseTarget" ref="buttonCollapse">
					<text-counter :max="rules[platformName].message.max"
						:show-counter="platformName === 'twitter'"
						text-align="text-right"
						@input="onContentInput"
						ref="text"
						:content-editable="true"
						:pre-text="form.message"
						:parent-text="parentMessage"
						:variable-size="false"
						counter-suffix="">
					</text-counter>
					<div v-if="platformName === 'youtube'" class="extra-fields row">
						<div class="col-xs-6">
							<div class="form-field">
								<label for="video_title">Video Title</label>
								<input id="video_title" type="text" v-model="form.title" maxlength="100">
							</div><!-- /form-field -->
						</div>
						<div class="col-xs-6">
							<div class="form-field">
								<label for="video_category">Video Category</label>
								<select id="video_category" v-model="form.category" 
									name="video_category" class="custom-select">
									<option v-for="category in rules.youtube.video_categories" 
										:value="category.native_id" :key="category.native_id">
										{{ category.title }}
									</option>
								</select>
							</div><!-- /form-field -->
						</div>
					</div>
				</div>
			</div><!-- /write-post-area -->
			<div class="attachment-selector-area col-sm-1">
				<div class="attachment-selectors attachment-selectors--sub-post --sub-post" v-bind:class="{ '--collapse': isCollapse }" ref="mediaButtons"> 
					<a href="#" :class="['selector selector-link', selectAttachmentClass('link')]"
						@click.prevent.stop="selectAttachment('link', $event)" tabindex="-1">
						<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('link')" width="15px" height="15px" />
					</a>
					<a href="#" :class="['selector selector-image', selectAttachmentClass('image')]"
						@click.prevent.stop="selectAttachment('image', $event)" tabindex="-1">
						<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('image')" width="15px" height="15px" />
					</a>
					<a href="#" :class="['selector selector-video', selectAttachmentClass('video')]" 
						@click.prevent.stop="selectAttachment('video', $event)" tabindex="-1">
						<svg-filler class="icon-container__icon--no-pad" :fill="colorFill" :path="getSvgPath('video')" width="15px" height="15px" />
					</a>
				</div>
			</div>
		</div>
		<div class="row" v-if="showValidations && hasErrors">
			<div class="col-md-12">
				<transition name="fade" appear :duration="300">
					<div class="alert alert-danger">
						<p v-for="(message,index) in validation.messages" :key="index">
							{{ message }} 
						</p>
					</div>
				</transition>
			</div>
		</div>
	</div><!-- /post-attachment -->
</template>
<script>
import mixinAuthoring from "../../mixins/authoring";
import mixinHub from "../../mixins/hub";
import TextCounter from "../TextCounter.vue";
import Tribute from "tributejs";
import ApiPost from "../../api/post";

export default {
	mixins: [mixinAuthoring, mixinHub],

	components: {
		TextCounter
	},

	props: {
		platform: {
			type: Object,
			required: true
		},
		index: {
			type: Number,
			required: true
		},
		allowedTaggablePlatform: {
			type: RegExp,
			default() {
				return /facebook|twitter|instagram/;
			}
		},
		parentMessage: {
			type: String
		}
	},

	data() {
		return {
			form: {
				message: null,
				message_store: [],
				category: null,
				title: null,
				category_title: null,
				attachment: null,
				attachment_index: -1
			},
			isInputted: false,
			isCollapse: false,
			socialButtonsHeight : 0,
			subPostType:'',
			rules: {
				twitter: {
					message: {
						max: 280,
						validateMax: true
					}
				},
				youtube: {
					message: {
						max: 1500
					},
					video_categories: [],
					attachment: {
						type: 'video',
						required: true
					}
				},
				instagram: {
					message: {
						max: 1500
					},
					attachment: {
						type: 'image|video',
						required: true
					}
				},
				linkedin: {
					message: {
						max: 1500
					}
				},
				pinterest: {
					message: {
						max: 1500
					},
					attachment: {
						type: 'image',
						required: true
					}
				},
				facebook: {
					message: {
						max: 1500
					},
				}
			},
			screenSize:0,
			tribute: null,
			tags: [],
			showValidations: false,
			parentText: null,
			colorFill: '#d7d7d7',
			socialFill: '#ffffff'
		};
	},

	methods: {

		/**
		 * @desc: select attachment for this sub post
		 *
		 * @param {String} generalAttachmentType
		 * @return {void}
		 */
		selectAttachment(generalAttachmentType, $event = null) {
			let $target = null
			if ($event) {
				$target = $event.target.nodeName === 'I' ? $event.target.parentElement : $event.target;
			}

			let attachments = this.getAttachmentsOfType(generalAttachmentType);

			let isTypeSupported = this.supportedAttachments.includes(generalAttachmentType);
			// no attachment of type is found
			if (!attachments.length) {
				return;
			}
			// attachment of type is found, but is'nt supported in this platform
			if (!isTypeSupported && 
				attachments.length && 
				this.generalAttachmentType === generalAttachmentType) {
				return;
			}
			
			// toggle off attachment.
			if ($target && $target.classList.contains('--active')) {
				this.form.attachment = null
			}
			else {
				this.form.attachment = attachments[0];
			}
		},

		/**
		 * @desc: toggle classes for attachment selectors
		 *
		 * @param {String} generalAttachmentType
		 * @return {String}
		 */
		selectAttachmentClass(generalAttachmentType) {
			let attachments = this.getAttachmentsOfType(generalAttachmentType);
			let selectedAttachment = _.find(attachments, this.form.attachment);
			let isTypeSupported = this.supportedAttachments.includes(generalAttachmentType);
			// attachment of type not uploaded yet.
			if (!attachments.length || !isTypeSupported) {
				this.subPostType = "";
				return "--disabled";
			} 
			else if (
				attachments.length &&
				isTypeSupported &&
				selectedAttachment &&
				this.generalAttachmentType === generalAttachmentType
			) {
				this.subPostType = generalAttachmentType;
				// attached, and selected as attachment
				return "--active";
			} 
			else
				// attached but not selected as attachment
				return ""; // this is the default one
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

		/** 
		 * automatically add eligible attachment for this subpost 
		 * @param: 
		 * @return:  
		 */    
		addEligibleAttachment() {
			if (!this.current.attachments.length) {
				return
			}

			// get the intersected types that is based from the uploaded attachments type
			let intersectedTypes = _.intersection(this.generalAttachmentsType, this.supportedAttachments)
			this.selectAttachment(intersectedTypes[0])
		},

		/**
		 * @desc handle the input event of post message
		 *
		 * @param {String} text
		 * @return {void}
		 */
		onContentInput(text) {
			this.form.message = text;
			this.$emit("input", this.form);
		},

		/**
		 * TODO: move the templates into a different file for easy maintainability
		 */
		initializeTribute() {
			this.tribute = new Tribute({
				values: _.debounce(this.getTaggableUsers, 300),
				menuItemTemplate: taggable => {
					return `
						<div class="tags-suggestion-item">
							<img class="tags-suggestion-item-avatar" src="${taggable.original.avatar}"/>
							<span class="tags-suggestion-item-name">${taggable.original.display_name}</span>
							<i class="tags-suggestion-item-platform-icon fa ${this.fafy}"></i>
						</div>`;
				},
				selectTemplate: taggable => {
					let param = _.pick(taggable.original, [
						"native_id",
						"display_name",
						"screen_name",
						"profile_id",
						"type"
					]);

					let name = this.getDisplayTypeName(param)

					return `
						<span class="selected-tag --${this.platformName}" contenteditable="false"
							data-profile_id="${param.profile_id}"
							data-screen_name="${param.screen_name}"
							data-native_id="${param.native_id}"
							data-display_name="${param.display_name}"
							data-type="${param.type}"
							data-name="${name}">
							${name}
						</span>`;
				},
				noMatchTemplate: function(tribute) {
					return '<li style="pointer-events: none;" onclick="return false;" class="no-match">No matches found</li>';
				},
				allowSpaces: true,
				lookup: taggable => {
					return taggable.display_name + " " + taggable.screen_name;
				},
				fillAttr: "display_name"
			});
			this.tribute.attach(this.$refs.text.$refs.text);

			// add tribute-replaced event listener for the element
			this.$refs.text.$refs.text.addEventListener(
				"tribute-replaced",
				this.tributeReplaced
			);
		},
		
		/**
		 * get the name that will be displayed in the platform post UI
		 * 
		 * NOTE: might come handy for other platform tagging.
		 * 
		 * just in case:
		 * facebook, linkedin, pinterest uses display_name,
		 * twitter, instagram and perhaps youtube? uses screen_name with '@' 
		 * as the tagged name in social platform post
		 * @param {object} param
		 * @return {object}
		 */
		getDisplayTypeName (param) {
			// will default to screen_name because some platform_connection don't have a screen_name
			if (this.platformName.match(/facebook|linkedin|pinterest/)) {
				// force the display to display_name
				name = param.display_name
			}
			else {
				name = '@'+param.screen_name
			}
			return name
		},

		tributeReplaced(e) {
			// manually trigger the input event to the text
			this.$refs.text.$refs.text.dispatchEvent(new Event("input", { bubbles: true }))

			// push to tags array
			let item = e.detail.item.original
			let taggable = _.pick(item, [
				"native_id",
				"display_name",
				"screen_name",
				"profile_id",
				"type"
			])
			taggable['name'] = this.getDisplayTypeName(taggable)
			this.tags.push(taggable)
		},

		getTaggableUsers(query, callback) {
			if (query.length < 3) return;
			if (this.platformName != 'twitter') return;

			let apiPost = new ApiPost(this.hub);
			let payload = {
				account_id: this.accountId,
				query,
				platform: this.platformName
			};
			apiPost
				.getTaggableUsers(payload)
				.then(response => callback(response.data.data))
				.catch(error => callback([]));
		},

		/**
		 * TODO: 
		 * clean the tags array after the input update so we can use it for later when submitting the post.
		 * @note: we can't rely on the tags data. we should loop the message and get the tagged users from there.
		 * @param  {string} text
		 */
		cleanTaggedUsers () {
			let cleanedTaggedUsers = []
			$(this.$refs.text.$refs.text).find('span.selected-tag')
				.each((index, item) => {
					let data = $(item).data()
					cleanedTaggedUsers.push(data)
				})
			return _.uniqBy(cleanedTaggedUsers, 'profile_id')
		},

		/**
		 * get the tagged accounts and replace the tagged users in message with its profile_id
		 *
		 * @return {void}
		 */
		getTaggedAccounts() {
			this.form.message_store = this.cleanTaggedUsers()
		},

		toggleValidations(show) {
			this.showValidations = show
		},

		updateFormObject() {
			// update the sub post form object to trigger the update of the values
			// a hack to update the form object
			this.form.attachment_index = this.attachmentIndex
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

		isVisible(){ 
			return this.isCollapse || false; 
		},
		toggleMediaButtons(elem,status){
			if(status == 'close'){
				$(elem).animate({"height":"36px","overflow":"hidden"},{
					duration:300,
					complete:function(){
						$(this).find("a:not(.--active)").each(function(index,item){
							$(item).hide();
						});
					}
				});
			}else{
				$(elem).animate({"height":(this.socialButtonsHeight > 0) ? this.socialButtonsHeight + "px" : this.socialButtonsHeight},{
					duration:300
				});
				$(elem).find("a:not(.--active)").each(function(index,item){
					$(item).css({"display":""}).animate();
				});
			}
		},
		toggleMediaButtonsMobile(elem,status){
			if(status == 'close'){
				
			}else{
				$(elem).find("a:not(.--active)").each(function(index,item){
					$(item).css({"display":""}).animate();
				});
			}
		},

		svgfy (value) {
			value = value.toLowerCase()
			let platform = value
			if (value == 'pinterest')
				platform = 'pinterest-p'
			else if (value == 'youtube')
				platform = 'youtube-play'

			return 'svg-' + platform
		}
	},

	mounted() {
		let self = this;
		let message = this.current.message;
		// if it's a gig context, pass the ideas_platformName
		if (this.context === "gig") {
			message = this.$store.state.Authoring.gig["ideas_" + this.platformName];
		}
		// if it's a youtube platform. then add title and category to form
		let fields = this.$store.state.Authoring.platform_fields;
		if (this.platformName === "youtube") {
			this.rules.youtube.video_categories = fields.youtube.categories;
			this.form.title = null;
			this.form.category = null;
		}

		this.form.message = message == null ? '' : message;

		// only initialize the tagging function if it's facebook and twitter
		if (this.platformName.match(this.allowedTaggablePlatform))
			this.initializeTribute();

		if($(window).width() >= 768){
			this.socialButtonsHeight = this.$refs.mediaButtons.clientHeight;
		}else{
			this.socialButtonsHeight = 'auto';
		}
		
		$(this.$refs.buttonCollapse).on('hide.bs.collapse',function(){
			self.isCollapse = true;
			if($(window).width() >= 768){
				self.toggleMediaButtons(self.$refs.mediaButtons,'close');	
			}else{
				self.toggleMediaButtonsMobile(self.$refs.mediaButtons,'close');
			}
		});
		
		$(this.$refs.buttonCollapse).on('show.bs.collapse',function(){ 
			self.isCollapse = false; 
			if($(window).width() >= 768){
				self.toggleMediaButtons(self.$refs.mediaButtons,'open');
			}else{
				self.toggleMediaButtonsMobile(self.$refs.mediaButtons,'open');
			}
		});

		$(window).on('resize',function(){
			self.screenSize = $(window).width();
		});

		this.$bus.$on("getTaggedAccounts", this.getTaggedAccounts);
		this.$bus.$on("post-authoring-show-validations", this.toggleValidations);
		this.$bus.$on("post-authoring-update-payload", this.updateFormObject);

		this.addEligibleAttachment()
	},

	beforeDestroy() {
		$(window).off('resize');
		$(".write-post-social.collapse").off('hide.bs.collapse');
    	$(".write-post-social.collapse").off('show.bs.collapse');
		this.$bus.$off("getTaggedAccounts", this.getTaggedAccounts);
		this.$bus.$off("post-authoring-show-validations", this.toggleValidations);
		this.$bus.$off("post-authoring-update-payload", this.updateFormObject);
	},

	computed: {
		/**
		 * we will use this accountId to identify the parent id of taggable connections.
		 */
		accountId () {
			let accountId = this.platform.native_id
			if (this.platformName == 'facebook') { 
				// i think only facebook that has parent_id (not sure)
				accountId = this.platform.parent_id
			}
			return accountId
		},

		validation () {
			let messages = []
			if (this.supportedAttachments.length < 3 && !this.hasAttachmentSelected) {
				messages.push(`Please select ${this.supportedAttachments.join(' or ')} attachment to continue.`)
			}

			if (this.hasExceededTextLimit) {
				messages.push(`Your message exceeds the ${this.platformName} limit.`)
			}

			return {
				messages
			}
		},

		hasAttachmentSelected () {
			let rule = this.rules[this.platformName]
			if (!this.form.attachment && rule.attachment && rule.attachment.required) {
				return false
			}
			return true;
		},

		hasExceededTextLimit () {
			let rule = this.rules[this.platformName]
			if (rule.message.validateMax && this.form.message) {
				return this.form.message.length > rule.message.max
			}
			return false
		},

		/** 
		 * define the sub post errors
		 *
		 * @return {Boolean}
		 */    
		hasErrors () {
			return !this.hasAttachmentSelected ||this.hasExceededTextLimit
		},

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

		/**
		 * @desc: get the general attachment type of selected attachment for this post
		 *
		 * @return {String}
		 */
		generalAttachmentType() {
			if (!this.form.attachment) return "";

			return this.form.attachment.type.match(/youtube|vimeo/)
				? "link"
				: this.form.attachment.type;
		},

		/** 
		 * @desc: get the general type of all uploaded attachments 
		 *
		 * @return {Array}
		 */    
		generalAttachmentsType() {
			return this.current.attachments.map(attachment => {
				return this.getGeneralAttachmentType(attachment.type)
			})
		},

		// small helper
		fafy() {
			let platform = this.platformName;
			let fa = "fa-";
			if (platform === "facebook") {
				fa = fa + "facebook-official";
			} else {
				fa = fa + platform;
			}
			return fa;
		},

		category() {
			if (this.platformName !== "youtube") return;

			let categories = this.rules.youtube.video_categories;
			let category = _.find(
				categories,
				category => category.native_id == this.form.category
			);
			return category;
		},

		mappedPlatform() {
			let platform = _.pick(this.platform, ["id", "native_id", "user_id"]);
			return platform;
		},

		platformName() {
			return this.platform.platform;
		},

		collapseTarget() {
			return `collapse-${this.platformName}-${this.platform.id}`;
		},

		postIcon() {
			let platform =
				this.platformName === "facebook" ? "fb" : this.platformName;
			return resolveStaticAsset(`/images/icon-${platform}.png`);
		},

		attachmentIndex() {
			if (!this.form.attachment) return -1;
			return _.findIndex(this.current.attachments, this.form.attachment);
		}
	},

	watch: {
		form: {
			handler: function(value) {
				if (!this.isInputted) {
					this.isInputted = true;
					Object.assign(this.form, this.mappedPlatform);
				}

				// assign category if it's youtube
				if (this.platformName === "youtube" && this.category) {
					value.category_title = this.category.title;
				}
				
				// assign attachment_index
				value.attachment_index = this.attachmentIndex
				value.has_error = this.hasErrors
				this.$store.commit("updateAuthoringPlatformMessage", value);
			},
			deep: true
		},

		'current.attachments' (attachments) {
			// only select eligible attachment if you haven't selected yet.
			if (!this.form.attachment) {
				this.addEligibleAttachment()
			}

			// clear the attachment
			let selectedAttachment = _.find(attachments, this.form.attachment);
			if (!selectedAttachment) {
				this.form.attachment = null;
			}
		},
		screenSize : function(value){
			if(value > 768){
				if(this.isCollapse){
					$(this.$refs.mediaButtons).css({"height":"auto"});
					this.socialButtonsHeight = 110;
				}
			}else{
				this.socialButtonsHeight = "auto";
				$(this.$refs.mediaButtons).css({"height":"auto"});
			}
		}
	},

	filters: {
		hashify(value) {
			if (!value) return;
			return `#${value}`;
		}
	}
};
</script>