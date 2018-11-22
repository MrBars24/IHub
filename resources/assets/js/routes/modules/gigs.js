const gig = [
	{
		exact: true,
		path: "",
		name: "gigs.carousel",
		meta: {
			requiresAuth: true,
			title: "Gigs",
			bodyClass: "gig-carousel-page" // ??
		},
		component: require("../../views/GigCarousel.vue")
	},
	{
		exact: true,
		path: "new",
		name: "gigs.new",
		meta: {
			requiresAuth: true,
			title: "Create New Gig",
			bodyClass: "create-gig-page" // ??
		},
		component: require("../../views/CreateGig.vue")
	},
	{
		exact: true,
		path: ":gig_slug",
		name: "gigs.view",
		meta: {
			requiresAuth: true,
			title: "View Gig",
			bodyClass: "gig-carousel-page" // ??
		},
		component: require("../../views/GigView.vue")
	},
	{
		exact: true,
		path: "edit/:gig_slug",
		name: "gigs.edit",
		meta: {
			requiresAuth: true,
			edit: true,
			bodyClass: "create-gig-page",
			title: "Edit Gig" // NOTE: dynamic
		},
		component: require("../../views/CreateGig.vue")
	}
];

export default gig