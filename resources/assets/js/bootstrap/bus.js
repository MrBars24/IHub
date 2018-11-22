export default {
	install(Vue) {
		const EventBus = new Vue()
		Vue.prototype.$bus = EventBus
	}
}