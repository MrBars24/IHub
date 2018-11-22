<template>
<div class="influencer-list-container">

	<!-- MODALS -->
	<div class="modal fade" ref="modalConfirmAction" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<h5>
						{{ modalTitle }}
					</h5>
				</div>
				<div class="modal-footer">
					<button type="button" @click="removeInfluencers" 
						:disabled="loaders.removing" class="btn-submit js-branding-button">
						<i v-if="loaders.removing" class="fa fa-spinner fa-pulse fa-fw"></i> REMOVE
					</button>
					<button type="button" class="btn --default" 
						data-dismiss="modal">
						CANCEL
					</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="table-layout table-layout--influencer table-layout--influencer-user">
		<div class="table-layout__header">
			<div class="row">
				<div class="col-xs-8 col-sm-10 col-md-6">
					<label>Influencer</label>
				</div>
				<div class="col-xs-4 hidden-xs hidden-sm">
					<label>Group</label>
				</div>
			</div><!-- /row -->
		</div><!-- /table-layout__header -->

		<div class="table-layout__body">
			<div :class="['row member-item', deletedAtClass(influencer.deleted_at), inActiveMembershipClass(influencer.is_active)]" 
				:key="influencer.id"
				v-for="(influencer, index) in form.influencers">
				<div class="col-xs-10 col-sm-11 col-md-6">
					<img :src="influencer.user.profile_picture_tiny" 
						:alt="influencer.user.name" 
						class="influencer-avatar">
					<label class="influencer-name">{{ influencer.user.name }}</label>
					<label class="influencer-email">{{ influencer.user.email }}</label>
				</div>

				<div class="col-xs-4 col-md-5 hidden-xs hidden-sm">
					<div :class="groupClass">
						<select class="custom-select" @change="setGroup($event, influencer.id)">
							<option value="" selected>Set Group</option>
							<option :key="group.id" v-for="group in form.membership_groups"
								:selected="influencer.group && group.id == influencer.group.id"
								:value="group.id">{{ group.name }}
							</option>
						</select>
					</div>
				</div>

				<div class="col-xs-1 col-md-1">
					<div class="checkbox-area">
						<input class="styled-checkbox enabled-alerts" 
						:id="influencer.user_id | generateNameId('influencer-list')" 
						type="checkbox" 
						v-model="form.influencers[index].is_selected"
						:value="influencer.user_id">
						<label :for="influencer.user_id | generateNameId('influencer-list')">&nbsp;</label>
					</div>
				</div>
			</div><!-- /row -->
			<div class="row load-more-button">

				<div v-if="loaders.fetching" class="text-center">
					<i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
				</div>

				<button @click.stop.prevent="getInfluencers" 
					v-show="hasNextPage && form.influencers.length"
					class="btn-submit">
					Load more
				</button>
			</div>
		</div><!-- /table-layout__body -->
	</div><!-- /table-layout -->

	<div class="form-field clearfix">
		<select class="custom-select custom-select-group-influencer pull-right" 
			@change="influencerListAction"
			:disabled="disabledListAction"
			v-model="form.selectedAction">
			<option value="select" disabled default>(select action)</option>
			<option value="remove">Remove from Hub</option>
			<option value="reset">Reset Points</option>
		</select>
	</div><!-- /form-field -->

	<input @click.prevent.stop="exportCSV" type="submit" value="Export Influencers" 
		class="btn-submit btn-submit-export js-branding-button">
</div>
</template>
<script>
import SettingsApi from '../../api/settings'
import mixinHub from '../../mixins/hub'
import InfiniteScroll from 'vue-infinite-loading'
import moment from 'moment'
import filters from '../../filters'
import Papa from 'papaparse'

export default {
	mixins: [mixinHub],

	components: {
		InfiniteScroll
	},

	data () {
		return {
			loaders: {
				fetching: false,
				setting_group: false
			},
			form: {
				influencers: [],
				membership_groups: [],	
				selectedUsers: [],
				selectedAction: 'select',
			},
			pagination: {
				next_page_url: null,
			},
			// infinite loading
			hasNextPage: true,
			// influencer list
		}
	},

	mounted () {
		if (this.init) {
			this.getInfluencers()
		}
		$(this.$refs.modalConfirmAction).on('hidden.bs.modal', () => this.form.selectedAction = 'select')
		this.$bus.$on('invitation-sent', this.addNewMembersToList)
		this.$bus.$on('group-added', this.onAddedGroup)
		this.$bus.$on('group-removed', this.onRemovedGroup)
	},

	beforeDestroy () {
		$(this.$refs.modalConfirmAction).off('hidden.bs.modal')
		this.$bus.$off('invitation-sent')
		this.$bus.$off('group-added')
		this.$bus.$off('group-removed')
	},

	filters: {
		fixTempPath: filters.fixTempPath,
		imgPlaceholder: filters.imgPlaceholder,
		generateNameId (id, name) {
			return name + '-' + id
		}
	},

	watch: {
		'$route': 'getInfluencers',
		init (value) {
			if (value)
				this.getInfluencers()
		}
	},

	computed: {
		disabledListAction () {
			return !this.selectedUsers.length
		},
		modalTitle () {
			let action = this.form.selectedAction
			let title = ''
			switch (action) {
				case 'remove':
					title = `Want to remove ${this.selectedUsers.length} influencer/s from ${this.hub.name}? Please confirm`
					break
				case 'reset':
					title = `Reset Points for Influencers in ${this.hub.name}? Please confirm.`
					break
				case 'message': 
					title = `Message Influencers in ${this.hub.name}`
					break
				default:
					title = ''
					break;
			}
			return title
		},
		groupClass () {
			let cls = 'group-influencer'
			if (!this.form.membership_groups.length) {
				cls += ' --hidden'
			}
			return cls
		},
		selectedUsers () {
			return this.form.influencers.filter(influencer => influencer.is_selected)
		}
	},

	methods: {
		setGroup ($event, membership_id) {
			let apiSettings = new SettingsApi(this.hub)
			let $select = $event.target
			let payload = {
				membership_id,
				group_id: $select.value
			}
			$select.classList.toggle('--loading')
			$select.disabled = true
			apiSettings.setGroup(payload)
				.then(response => {
					$select.classList.toggle('--loading')
					$select.disabled = false
				})
				.catch(error => {
					$select.classList.toggle('--loading')
					$select.disabled = false
				})
		},
		
		onAddedGroup (groups) {
			if (_.isArray(groups)) {
				groups.forEach(group => this.form.membership_groups.push(group))
			}
			else {
				this.form.membership_groups.push(groups)
			}
		},

		onRemovedGroup (group_id) {
			let index = _.findIndex(this.form.membership_groups, item => item.id == group_id)
			this.form.membership_groups.splice(index, 1)
		},

		addNewMembersToList (payload) {
			// create object
			let createObj = (data) => {
				// shift
				let membership = data.membership
				membership.user = _.omit(data, 'membership')
				return membership
			}

			if (_.isArray(payload)) {
				// before adding it to influencers list. map the object first
				payload.forEach(user => this.form.influencers.push(createObj(user)))
			}
			else {
				this.form.influencers.push(createObj(payload))
			}
		},

		exportCSV () {
			let apiSettings = new SettingsApi(this.hub)
			apiSettings.exportCSV()
			.then(link => {
				console.log(link)
				link.click()
			})
			.catch(error => {
				console.error(error)
			})
		},

		getInfluencers () {
			this.loaders.fetching = true
			let apiSettings = new SettingsApi(this.hub)
			let payload = {
				pagination: this.pagination,
				hasNextPage: this.hasNextPage
			}
			apiSettings.getInfluencers(payload)
				.then(response => {
					let influencers = response.data.data.influencers
					
					influencers.data.forEach(influencer => {
						// append is_selected attribute
						this.$set(influencer, 'is_selected', false)
						// map group
						let group = null
						if (influencer.groups.length) {
							group = _.pick(influencer.groups[0], ['id', 'name'])
						}
						this.$set(influencer, 'group', group)
						
						this.form.influencers.push(influencer)
					})

					// set pagination info
					let pagination = _.omit(influencers, 'data')
					this.pagination = pagination
					this.hasNextPage = pagination.next_page_url !== null
					this.loaders.fetching = false
				})
				.catch(error => {
					this.loaders.fetching = false
					console.error(error)
				})
		},

		deletedAtClass (deleted_at) {
			if (deleted_at != null) 
				return '--deleted'
		},

		inActiveMembershipClass (is_active) {
			return is_active ? '' : '--inactive'
		},

		influencerListAction ($event) {
			let value = $event.target.value
			if (value == '' || !this.selectedUsers.length)
				return

			if (value === 'remove') {
				$(this.$refs.modalConfirmAction).modal('show')
			}
			else if (value === 'reset')
				this.resetPoints()
		},

		clearAction () {
			this.form.influencers = this.form.influencers.map(influencer => {
				influencer.is_selected = false
				return influencer
			})
			this.form.selectedAction = 'select'
		},

		removeInfluencers () {
			const settingsApi = new SettingsApi(this.hub)
			let payload = {
				influencer_ids: this.selectedUsers.map(selected => selected.user.id)
			}
			this.loaders.removing = true
			settingsApi.removeFromHub(payload)
				.then(response => {
					// append --deleted class
					// update influencers list
					this.form.influencers = this.form.influencers.filter(influencer => !influencer.is_selected)
					
					this.clearAction()
					this.loaders.removing = false
					$(this.$refs.modalConfirmAction).modal('hide')
				})
				.catch(error => {
					this.loaders.removing = false
					this.clearAction()
				})
		},

		resetPoints () {
			const settingsApi = new SettingsApi(this.hub)
			let payload = {
				influencer_ids: this.selectedUsers.map(selected => selected.user.id)
			}
			settingsApi.resetPoints(payload)
				.then(response => {
					this.clearAction()
				})
				.catch(error => {
					console.log(error)
					this.clearAction()
				})
		},
	}
}
</script>