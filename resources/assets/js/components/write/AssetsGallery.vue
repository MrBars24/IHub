<template>
<div class="assets-gallery">
	<div ref="modal" class="text-left modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" 
						class="close" 
						data-dismiss="modal" 
						aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Assets Gallery</h4>
				</div>
				<div class="modal-body">
					<div class="row collection-container" ref="refCollection">
						<div class="col-md-12 text-center">
							<p v-if="!collection.length">No Assets to display</p>
							<p v-else>
								You may select up to 1 attachment of each attachment type (link, image, video).
							</p>
						</div>
					</div>

					<div class="row" v-for="(row, index) in chunkedItems" :key="index">
						<div class="col-xs-12 col-md-4 item" 
							v-for="(asset, index) in row" 
							:key="index"
						>
							<div class="item-wrapper" 
								@click.prevent.stop="select($event,asset)">
								<gig-attachment :attachment="asset" :show-general-type="true"
									:ignore-classes="true"></gig-attachment>
							</div>
						</div>
					</div>

						
					
				</div>
				<div class="modal-footer">
					<button type="button" 
						class="btn-post  --default" 
						data-dismiss="modal">Close</button>
					<button type="button"
						@click="assetSelected" 
						class="btn-post js-branding-button">SELECT
					</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<div>
		<i title="select attachment for this post" 
			@click="modalShow = true"
			class="btn-upload fa fa-picture-o">
		</i>
	</div>
</div>
</template>
<script>
import GigAttachment from "../gigs/Attachment.vue";

export default {
	name: "AssetsGallery",

	components: {
		GigAttachment
	},

	props: {
		collection: {
			type: Array,
			required: true
		}
	},

	data() {
		return {
			modalShow: false,
			selected: null,
			initiated: false
		};
	},

	mounted() {
		$(this.$refs.modal).on("hidden.bs.modal", this.modalHidden);
		$(this.$refs.modal).on("shown.bs.modal", this.modalShown);
	},

	beforeDestroy() {
		// make sure that modal is turned off when switching to another page
		$(this.$refs.modal).modal("hide");
		$(this.$refs.modal).off("shown.bs.modal", this.modalShown);
		$(this.$refs.modal).off("hidden.bs.modal", this.modalHidden);
	},

	beforeRouteLeave() {
		$(this.$refs.modal).modal("hide");
		$(this.$refs.modal).off("shown.bs.modal", this.modalShown);
		$(this.$refs.modal).off("hidden.bs.modal", this.modalHidden);
	},

	methods: {
		modalHidden() {
			this.modalShow = false;
		},
		modalShown() {
			this.selected = this.collection[0];
			let items = document.querySelectorAll(".item-wrapper");
			if (items.length && !this.initiated) {
				this.initiated = true;
				items[0].classList.add("--selected"); // add --selected on first modal show
			}
		},
		select($event, asset) {
			let selector = $($event.target);
			
			$(this.$refs.refCollection)
				.find(".item-wrapper")
				.removeClass("--selected");

			if (selector.hasClass(".item-wrapper")) {
				selector.addClass("--selected");
			} else {
				selector.parents(".item-wrapper").addClass("--selected");
			}
			this.selected = asset;
		},
		assetSelected() {
			this.$emit("selected", this.selected);
			this.modalShow = false;
		}
	},

	computed: {
		chunkedItems: function() {
			return _.chunk(this.collection, 3);
		}
	},

	watch: {
		modalShow(value) {
			let visibility = value ? "show" : "hide";
			$(this.$refs.modal).modal(visibility);
		}
	}
};
</script>