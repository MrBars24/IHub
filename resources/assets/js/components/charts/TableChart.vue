<template>
	<div class="container">
		<table class="table">
			<thead>
				<tr>
					<th v-for="col in data.columns" :key="col[0]" v-bind:data-key="col[0]" :class="col[1]">
						{{ col[2] }}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(item, index) in data.data" :key="index">
					<td v-for="(col, idx) in columns" :key="idx">{{ item[col] }}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>

export default {
	name: 'TableChart',

	props: ['data', 'columns'],

	watch:{
		
	},

	mounted () {
		
	},

	methods: {
		
	}
}
</script>
