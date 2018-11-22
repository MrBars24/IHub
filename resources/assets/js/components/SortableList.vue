<template>
		<draggable v-model="list" :element="element" :options="{draggable: '.item'}" @change="change">
			<slot></slot>
		</draggable>
</template>
<script>
import Draggable from 'vuedraggable'
export default {
	components: {
		Draggable
	},
	props: {
		value: {
			type: Array,
			required: true	
		},
		element: {
			type: String,
			default: 'div'
		}
	},
	data () {
		return {
			list: this.value
		}
	},
	methods: {
		change ({moved}) {
			
		}
	}
}
</script>