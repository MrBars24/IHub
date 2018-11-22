<template>
<div class="mobile-submenu">
	<ul class="row nav nav-tabs">
		<router-link :to="{ name: 'settings.account' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-lock"></a>
		</router-link>

		<router-link :to="{ name: 'settings.alerts' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-msg"></a>
		</router-link>

		<router-link :to="{ name: 'settings.messages' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-bell"></a>
		</router-link>

		<router-link :to="{ name: 'settings.profile' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-profile"></a>
		</router-link>

		<router-link :to="{ name: 'settings.alerts' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-group"></a>
		</router-link>

		<router-link :to="{ name: 'settings.alerts' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-stars"></a>
		</router-link>

		<router-link :to="{ name: 'settings.alerts' }" 
			exact-active-class="active"
			class="col-md-2 nav-item"
			tag="li" 
			exact>
			<a class="nav-link icon-home"></a>
		</router-link>
	</ul>
</div><!-- /mob-submenu -->
</template>
<script>
export default {

}
</script>