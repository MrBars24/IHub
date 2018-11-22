const message = [
	{
		exact: true,
		path: "inbox",
		name: "messages.inbox",
		meta: {
			requiresAuth: true,
			title: "Inbox",
			bodyClass: "messages-page"
		},
		components: {
			default: require("../../views/MessageInbox.vue"),
			submenu: require("../../views/submenu/Messages.vue")
		}
	},
	{
		exact: true,
		path: "notifications",
		name: "messages.notifications",
		meta: {
			requiresAuth: true,
			title: "Notifications",
			bodyClass: "messages-page"
		},
		components: {
			default: require("../../views/Notifications.vue"),
			submenu: require("../../views/submenu/Messages.vue")
		}
	}
];
export default message