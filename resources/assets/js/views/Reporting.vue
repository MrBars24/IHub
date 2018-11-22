<template> 
<div>
	<router-view name="submenu"></router-view>
	<div id="display-area">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="tab-content card">
						<router-view></router-view>
					</div>
				</div>
			</div>
		</div>
	</div><!-- /display-area -->
</div>
</template> 
<script> 
 
export default {
	name: 'ReportingGig'
}
</script>