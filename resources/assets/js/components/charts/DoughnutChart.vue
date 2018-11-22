<template>
	<div class="container">
		<canvas ref="canvas" width="900" height="400"></canvas>
	</div>
</template>

<script>
import VueCharts from 'vue-chartjs'
import { Bar, Line } from 'vue-chartjs'

export default {
	name: 'DoughnutChart',

	props: ['data', 'options'],

	extends: VueCharts.Doughnut,

	watch:{
		data (val) {
			this.initialize();
		}
	},

	mounted () {
		
	},

	methods: {
		onSuccess() {
			console.log("success");
		},
		initialize(){
			if(typeof this.data != "undefined"){
				this.renderChart(this.data);
			}
		}
	}
}
</script>
