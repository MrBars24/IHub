const Post = [
	{
		exact: true,
		path: '',
		name: 'post.view',
		meta: {
			requiresAuth: true,
			title: 'Post', // NOTE: dynamic 
		},
		component: require('../../views/Post.vue')
	},
	{
		exact: true,
		path: 'instagram/:item_id',
		name: 'post.instagram',
		meta: {
			requiresAuth: true,
			title: 'Post Instagram', // NOTE: dynamic 
			bodyClass: 'post-instagram-page'
		},
		component: require('../../views/PostInstagram.vue')
	}
]

export default Post