<template>
	<div class="member-group-container">
		<div class="form-field">
			<div class="table-layout table-layout--influencer table-layout--member-groups">
				<div class="table-layout__header">
					<div class="row member-group-item">
						<div class="col-xs-4 col-sm-4">
							<label>Name</label>
						</div>

						<div class="col-xs-4 col-sm-4">
							<label>Multiplier</label>
						</div>
					</div><!-- /row -->
				</div><!-- /table-layout__header -->

				<div class="table-layout__body" v-if="form.membership_groups.length">
					<div class="row member-group-item" 
						v-for="(group,index) in form.membership_groups" 
						:key="index">
						<div class="col-xs-4 col-sm-4">
							<label>{{ group.name }}</label>
						</div>

						<div class="col-xs-3 col-sm-4">
							<label>{{ group.multiplier }}</label>
						</div>

						<div class="col-xs-1 col-sm-1 col-xs-offset-3 col-sm-offset-3">
							<button @click="removeGroup(group, index)" 
								type="button" 
								class="close-btn close-btn3">		
							</button>
						</div>
					</div><!-- /row -->
				</div><!-- /table-layout__body -->
			</div><!-- /table-layout -->
		</div><!-- /form-field -->

		<div class="form-field">
			<p>Add a new membership group</p>

			<div class="add-member-group-holder">
				<div class="row">
					<div class="col-sm-12 col-md-4 col-md-offset-2 clearfix">
						<input v-model="form.group.name" 
							placeholder="Group name" type="text" 
							class="add-member-group-name pull-left">
					</div>

					<div class="col-sm-12 col-md-2 clearfix">
						<input v-model="form.group.multiplier" 
							step="0.25" min="0.5" max="4" maxlength="1" 
							placeholder="Points x1" type="number" 
							class="add-member-group-points pull-left">
					</div>

					<div class="col-sm-12 col-md-3 clearfix">
						<button @click.stop.prevent="addGroup" :disabled="addDisabled" 
							class="btn-submit pull-left js-branding-button">
							<i v-show="loaders.submitting" 
								class="fa fa-spinner fa-pulse fa-fw"></i> Add
						</button>
					</div>
				</div>
			</div><!-- /add-member-group-holder -->
		</div><!-- /form-field -->
	</div>
</template>
<script>
import mixinHub from '../../mixins/hub'
import SettingsApi from '../../api/settings'

export default {

	mixins: [mixinHub],

	data () {
		return {
			loaders: {
				fetching: false,
				submitting: false
			},
			form: {
				membership_groups: [],
				group: {
					name: null,
					multiplier: null,
				}
			}
		}
	},

	mounted () {
		if (this.init) {
			this.getGroups()
		}
	},

	watch: {
		'$route': 'getGroups',
		init (value) {
			if (value) {
				this.getGroups()
			}
		}
	},

	computed: {
		addDisabled () {
			return this.loaders.submitting ||
							(!this.form.group.name ||
							(!this.form.group.multiplier || this.form.group.multiplier < 0)
							)
		}
	},

	methods: {
		getGroups () {
			this.loaders.fetching = true
			let apiSettings = new SettingsApi(this.hub)
			apiSettings.getGroups()
				.then(response => {
					this.loaders.fetching = false
					let groups = response.data.data.membership_groups
					// Object.assign(this.form.membership_groups, groups)
					this.form.membership_groups = groups
					this.$bus.$emit('group-added', groups)
				})
				.catch(error => {
					this.loaders.fetching = false
					console.error(error)
				})
		},
		
		removeGroup (group, index) {
			if (group.id !== undefined) {
				let apiSettings = new SettingsApi(this.hub)
				apiSettings.deleteGroup(group.id)
					.then(response => {
						this.form.membership_groups.splice(index, 1)
						this.$bus.$emit('group-removed', group.id)
					})
					.catch(error => console.error(error))
			}
			else {
				this.form.membership_groups.splice(index, 1)
			}
		},

		addGroup (group) {
			this.loaders.submitting = true
			let apiSettings = new SettingsApi(this.hub)
			apiSettings.postGroups(this.form.group)
				.then(response => {
					this.loaders.submitting = false
					let group = response.data.data.membership_group
					this.form.membership_groups.push(group)
					this.$bus.$emit('group-added', group)
					this.clearFormGroup()
				})
				.catch(error => {
					console.error(error)
					this.loaders.submitting = false
					this.clearFormGroup()
				})
		},

		clearFormGroup () {
			this.form.group.name = null
			this.form.group.multiplier = null
		}
	}
}
</script>