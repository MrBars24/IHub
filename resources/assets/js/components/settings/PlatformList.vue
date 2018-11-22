<template>
	<ul>
		<li :class="['item', itemClass(item.pivot)]"
			v-for="(item, index) in items"
			:key="index">
			<input class="hidden" 
				:id="index | generateNameId('platform')"
				:checked="item.pivot.is_selected"
				@change="onChange($event, item)"
				type="checkbox">
			<label :for="index | generateNameId('platform')">
				<!-- <i :class="['fa icon-sm', platformFilter(item.name)]" 
					aria-hidden="true">
				</i> -->
				<div :class="['icon-container-static--wbackground', svgfy(item.name)]">
					<svg-filler class="icon-container-static__icon" :path="getSvgPath(item.name)" width="30px" height="30px" :fill="colorFill" />
				</div>
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
	data () {
		return {
			colorFill: '#ffffff'
		}
	},
	methods: {
		platformFilter (value) {
			value = value.toLowerCase()
			let platform = value
			if (value == 'pinterest')
				platform = 'pinterest-p'
			else if (value == 'youtube')
				platform = 'youtube-play'

			return 'fa-' + platform
		},
		svgfy (value) {
			value = value.toLowerCase()
			let platform = value
			if (value == 'pinterest')
				platform = 'pinterest-p'
			else if (value == 'youtube')
				platform = 'youtube-play'

			return 'svg-' + platform
		},
		itemClass ({is_selected}) {
			return is_selected ? '--active' : ''
		},
		onChange ($event, item) {
			let target = $event.target
			item.pivot.is_selected = target.checked
			return item
		}
	},
	filters: {
		generateNameId (id, name) {
			return name + '-' + id
		},
	}
}
</script>