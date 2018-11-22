<template>
	<div>
		<h3 class="comment__list-name">
			<router-link :to="fixProfileRoute(comment.author)">
				{{ comment.author.name }}
			</router-link>
		</h3>
		<div class="comment__list-avatar">
			<router-link :to="fixProfileRoute(comment.author)">
				<img :src="comment.author.profile_picture_tiny" :alt="comment.author.name">
			</router-link>
		</div><!-- /comment__list-avatar -->
		<router-link :to="{ name: 'post', params: { post_id: comment.post_id } }"
			class="comment__list-time" 
			tag="span">
			<a>{{ comment.created_at | fromNow }}</a>
		</router-link>
		<p class="comment__list-message" v-html="comment.message_cached"></p>
	</div>
</template>

<script>
import moment from 'moment'

export default {
	name: 'Comment',

	props: {
		comment: {
			type: Object,
			required: true
		}
	},

	mounted () {
		// attach click event to tagged elements via jquery
		$(this.$el).find('.comment__entity-tag').on('click', this.handleTagLinks)
	},

	beforeRouteLeave (to, from, next) {
		// remove event bindings
		$(this.$el).find('.comment__entity-tag').off('click', this.handleTagLinks)
	},

	methods: {
		handleTagLinks ($el) {
			$el.preventDefault()

			let target = $el.target;
	
			let routeSlug = target.dataset.routeSlug

			// fallback
			if (!routeSlug) {
				let url = target.pathname.split('/')
				if (url.length && url.length === 3) {
					routeSlug = url[2]
				}
			}
			
			// trigger programmatic navigation using vue-router
			this.$router.push({
				name: 'profile.home',
				params: {
					user_slug: routeSlug
				}
			})
		},

		fixProfileRoute (user) {
			let isHubManager = user.object_class === 'Hub'
			return { 
				name: 'profile.home', 
				params: { 
					user_slug: isHubManager ? 'about' : user.slug
				} 
			}
		}
	}
}
</script>
