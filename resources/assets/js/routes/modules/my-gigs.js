const MyGig = [
	{
		exact: true,
		path: "scheduled",
		name: "my.gigs.scheduled",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Scheduled Posts"
		},
		component: require("../../views/MyGigScheduled.vue")
	},
	{
		exact: true,
		path: "rejected",
		name: "my.gigs.rejected",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Rejected Posts"
		},
		component: require("../../views/MyGigRejected.vue")
	},
	{
		exact: true,
		path: "pending",
		name: "my.gigs.pending",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Gigs Pending Approval"
		},
		component: require("../../views/MyGigApproval.vue")
	},
	{
		path: "feed",
		exact: true,
		name: "my.gigs.feed",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Gig Feeds"
		},
		component: require("../../views/MyGigFeed.vue")
	},
	{
		path: "feed/manage",
		exact: true,
		name: "my.gigs.feed.manage",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Manage Gig Feeds"
		},
		component: require("../../views/MyGigFeedManage.vue")
	},
	{
		path: "feed/create",
		exact: true,
		name: "my.gigs.feed.create",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Create Gig Feed"
		},
		component: require("../../views/MyGigFeedCreate.vue")
	},
	{
		path: "feed/edit/:feed_id",
		exact: true,
		name: "my.gigs.feed.edit",
		meta: {
			bodyClass: "my-gig-page",
			requiresAuth: true,
			title: "Edit Gig Feed",
			edit: true
		},
		component: require("../../views/MyGigFeedCreate.vue")
	}
];

export default MyGig