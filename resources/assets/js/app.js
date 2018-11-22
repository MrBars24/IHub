import Vue from "vue";
import Raven from "raven-js";
import RavenVue from "raven-js/plugins/vue";
import App from "./app.vue";
import Bootstrap from './bootstrap'
import router from "./routes";
import store from "./store";
import { sync } from "vuex-router-sync";
import Notifications from 'vue-notification'
import VueCordova from 'vue-cordova'
//import SvgFiller from 'vue-svg-filler'
import SvgFiller from './bootstrap/vue-svg-filler'

// require tw-bootstrap and lodash
require("./includes");
sync(store, router);

Vue.use(Bootstrap.oauth)
Vue.use(Bootstrap.bus)
Vue.use(Notifications)
Vue.use(VueCordova)
Vue.mixin({
	data () {
		return {
			cordova: Vue.cordova
		}
	}
})
Vue.component('svg-filler', SvgFiller)

Raven.config("https://e0c407b39e6a4e9097d7ff0c766429b5@sentry.io/302561")
	.addPlugin(RavenVue, Vue)
	.install();

// initialize the app first
store.dispatch("initApp").then(() => {
	var app = new Vue({
		components: {
			"influencer-hub": App
		},
		router,
		store
	}).$mount("#app");
});