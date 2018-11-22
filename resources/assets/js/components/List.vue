<template>
	<draggable v-model="originalList" :options="configOptions">
		<slot></slot>
		<slot name="footer"></slot>
	</draggable>
</template>
<script>
import Draggable from 'vuedraggable'

export default {
	components: {
		Draggable
	},
	props: {
		list: {
			type: Array,
			required: true
		},
		options: {
			type: Object,
			default: () => {
				return {
					draggable: '.item',
				}
			}
		}
	},
	data () {
		return {
			configOptions: {
				draggable: '.item',
			},
			originalList: this.list,
		}
	},

	mounted () {
		Object.assign(this.configOptions, this.options)
	}
	
}
</script>