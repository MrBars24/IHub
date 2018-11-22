<template>
	<ul>
		<li class="category-item" v-for="(item, index) in items"	:key="index">
			<input class="styled-checkbox enabled-alerts" 
				:id="index | generateNameId('category')"
				:checked="item.pivot.is_selected"
				@change="$event => item.pivot.is_selected = $event.target.checked"
				type="checkbox">
			<label :for="index | generateNameId('category')">
				{{ item.name }}
			</label>
		</li>
	</ul>
</template>
<script>
export default {
	model: {
		prop: 'items',
		event: 'change'
	},

	props: {
		items: {
			type: Array,
			required: true
		}
	},
	
	filters: {
		generateNameId (id, name) {
			return name + '-' + id
		},
	}
}
</script>