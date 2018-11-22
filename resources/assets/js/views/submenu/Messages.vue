<template>
	<div class="submenu">
		<div class="container">
			<div class="text-center" v-if="!this.init">
				<i class="fa fa-spinner fa-pulse fa-fw"></i>
			</div>
			<ul class="row nav nav-tabs" v-else>
				<li class="col-xs-6 nav-item">
					<router-link class="icon-msg icon-hover"
						:to="{ name: 'messages.inbox' }">
					</router-link>
				</li>
				<li class="col-xs-6 nav-item">
					<router-link class="icon-bell icon-hover"
						:to="{ name: 'messages.notifications' }">
					</router-link>
				</li>
			</ul>
		</div>
	</div>
</template>
<script>
	export default {}
</script>