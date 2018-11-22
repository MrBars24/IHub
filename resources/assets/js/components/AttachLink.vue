<template>
	<div class="modal fade" id="modalAttachLinks" ref="modalAttachLinks" 
		tabindex="-1" role="dialog" data-backdrop="static">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" 
						data-dismiss="modal" 
						aria-hidden="true">&times;</button>
					<h4 class="modal-title">Add a new link attachment</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-field">
								<input type="url" id="url" v-model="form.url" 
									placeholder="Paste or type link here and press enter"
									:readonly="loaders.scraping || scraper.hasScraped" autocomplete="off"
									@paste="startScraping" @keyup.enter="startScraping">
								<small class="text-danger" v-if="error.title">
									{{ error.title }}
								</small>
							</div><!-- /form-field -->
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="text-center" v-if="loaders.scraping">
								<i class="fa fa-spinner fa-pulse fa-fw"></i>
							</div>
							<div class="body-media" v-else>
								<slot name="preview" :attachment="form.attachment"></slot>
							</div>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" :disabled="disableButton" @click="attachLink" 
						class="btn-submit js-branding-button">
						<i v-if="loaders.attaching" class="fa fa-spinner fa-pulse fa-fw"></i> ATTACH
					</button>
					<button type="button" class="btn --default" 
						data-dismiss="modal">
						CANCEL
					</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</template>
<script>
import ApiFile from "../api/file";
import { urlPattern } from "../config/pattern";
export default {
	name: "AttachLink",

	props: {
		context: {
			type: String,
			default: 'gig'
		}
	},

	data() {
		return {
			form: {
				url: null,
				attachment: {
					type: "",
					resource: ""
				}
			},
			error: {},
			loaders: {
				attaching: false,
				scraping: false
			},

			// scraper
			scraper: {
				scrapable: false,
				canScrape: true,
				hasScraped: false
			},
			pattern: urlPattern
		};
	},

	mounted() {
		$(this.$el).on("hidden.bs.modal", this.onModalHidden);
	},

	beforeRouteLeave() {
		$(this.$el).modal('hide')
		$(this.$el).off("hidden.bs.modal", this.onModalHidden);
	},

	beforeDestroy() {
		$(this.$el).modal('hide')
		$(this.$el).off("hidden.bs.modal", this.onModalHidden);
	},

	methods: {
		onModalHidden() {
			this.form.url = null;
			this.form.attachment = {
				type: "",
				resource: ""
			};
			this.scraper.canScrape = true;
			this.loaders.scraping = false;
			this.scraper.hasScraped = false
		},

		attachLink() {
			this.$emit("attach-link", this.form.attachment);
			$(this.$el).modal('hide')
		},

		scrapeLink(url) {
			const apiFile = new ApiFile();
			this.loaders.scraping = true;
			let payload = {
				url,
				context: this.context
			};
			apiFile
				.scrape(payload)
				.then(response => {
					this.loaders.scraping = false;
					this.scraper.canScrape = false;
					let data = response.data.data;
					this.form.attachment = Object.assign(this.form.attachment,{},data.attachment);
					this.scraper.hasScraped = true
				})
				.catch(error => {
					this.loaders.scraping = false;
				});
		},

		startScraping($event) {
			// wrap in setTimeout because paste events fires before the value event set.
			setTimeout(() => {
				console.log("start scraping");
				if (this.scraper.canScrape) {
					let foundUrl = this.form.url.match(this.pattern);
					if (foundUrl && foundUrl.length) {
						foundUrl = foundUrl[0];
						let url =
							foundUrl.indexOf("://") === -1 ? "http://" + foundUrl : foundUrl;
						this.scrapeLink(url);
					}
				}
			}, 100);
		}
	},

	computed: {
		disableButton() {
			return this.loaders.scraping || !this.form.url || !this.form.attachment.type
		}
	}
};
</script>
