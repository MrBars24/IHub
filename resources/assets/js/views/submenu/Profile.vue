<template>
	<div class="submenu visible-xs visible-sm">
		<div class="container">
			<ul class="row">
				<li class="col-xs-6 nav-item">
					<router-link class="nav-link icon-profile"
						:to="{ name: 'profile.home' }"
						exact>
					</router-link>
				</li>
				<li class="col-xs-6 nav-item">
					<router-link class="nav-link icon-post"
						:to="{ name: 'profile.posts' }"
						exact>
					</router-link>
				</li>
			</ul>
		</div>
	</div>
</template>
<script>
export default {
	name: 'ProfileMenu'
}
</script>