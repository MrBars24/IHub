<template>
	<div class="reward-item clearfix">
		<div class="reward-box__list-txt pull-left">
			<div class="form-field">
				<input placeholder="Reward description" 
					@input="$emit('input', $event.target.value)" :value="reward.description" type="text">
			</div><!-- /form-field -->
		</div><!-- /reward-box__list-txt -->

		<div class="reward-box__list-btn pull-right">
			<button type="button" @click="removeReward" 
			class="close-btn close-btn1">
		</button>
	</div><!-- /reward-box__list-btn -->
</div>
</template>
<script>
export default {
	props: {
		index: {
			type: Number,
			required: true
		},
		reward: {
			type: Object,
			required: true
		}
	},
	computed: {
		isNew () {
			return !this.reward.id
		}
	},
	methods: {
		removeReward () {
			this.$emit('remove', {
				id: this.isNew ? undefined : this.reward.id,
				index: this.index
			})
		}
	}
}
</script>