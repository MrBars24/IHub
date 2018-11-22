<template>
	<div class="gig-post-scheduled">
		<div class="detail-box">
			<div class="body">

				<transition name="fade" v-if="!isScheduling" mode="out-in">
				<div class="row schedule-buttons">
					<div class="col-sm-6 col-xs-12">
						<button class="btn-submit js-branding-button" 
							@click="isScheduling = !isScheduling">Re-schedule Post
						</button>
					</div>
					<div class="col-sm-6 col-xs-12">
						<button class="btn-submit btn-cancel" @click="cancelPost"
							:disabled="loaders.cancelling">
							<i v-show="loaders.cancelling" 
								class="fa fa-spinner fa-pulse fa-fw"></i> Cancel Post
						</button>
					</div>
				</div>
				</transition>

				<transition name="fade" v-else mode="out-in">
					<div>
						<div class="form-field">
							<label>
								Reschedule the publishing of this post at
							</label>

							<div class="row">
								<div class="col-sm-6">
									<input type="date" v-model="scheduled_at.date">
								</div>
								<div class="col-sm-6">
									<select v-model="scheduled_at.time" class="custom-select">
										<option :key="index" v-for="(time,index) in defaults.time">{{ time }}</option>
									</select>
								</div>
							</div>
						</div>
						<div class="form-field">
							<div class="row schedule-buttons">
								<div class="col-sm-6 col-xs-12">
									<button class="btn-submit btn-cancel" 
										:disabled="loaders.scheduling"
										@click="isScheduling = !isScheduling"> Back
									</button>
								</div>
								<div class="col-sm-6 col-xs-12">
									<button class="btn-submit js-branding-button" 
										:disabled="loaders.scheduling" @click="reSchedulePost">
										<i v-show="loaders.scheduling" 
											class="fa fa-spinner fa-pulse fa-fw"></i> Reschedule Post
									</button>
								</div>
							</div>
						</div>
					</div>
				</transition>

			</div>
		</div>
	</div>
</template>

<script>
import ApiMyGig from '../../api/mygigs'
import mixinHub from '../../mixins/hub'
import moment from 'moment'

export default {
	mixins: [mixinHub],

	props: {
		post: {
			type: Object,
			required: true
		}
	},

	data () {
		return {
			loaders: {
				scheduling: false,
				cancelling: false
			},
			form: {
				schedule_at: null
			},
			isScheduling: false,
			
			scheduled_at: {
				date: null,
				time: '1:00AM'
			},
			defaults: {
				time: [
					'1:00AM','1:15AM','1:30AM','1:45AM',
					'2:00AM','2:15AM','2:30AM','2:45AM',
					'3:00AM','3:15AM','3:30AM','3:45AM',
					'4:00AM','4:15AM','4:30AM','4:45AM',
					'5:00AM','5:15AM','5:30AM','5:45AM',
					'6:00AM','6:15AM','6:30AM','6:45AM',
					'7:00AM','7:15AM','7:30AM','7:45AM',
					'8:00AM','8:15AM','8:30AM','8:45AM',
					'9:00AM','9:15AM','9:30AM','9:45AM',
					'10:00AM','10:15AM','10:30AM','10:45AM',
					'11:00AM','11:15AM','11:30AM','11:45AM',
					'12:00PM','12:15PM','12:30PM','12:45PM',
					'1:00PM','1:15PM','1:30PM','1:45PM',
					'2:00PM','2:15PM','2:30PM','2:45PM',
					'3:00PM','3:15PM','3:30PM','3:45PM',
					'4:00PM','4:15PM','4:30PM','4:45PM',
					'5:00PM','5:15PM','5:30PM','5:45PM',
					'6:00PM','6:15PM','6:30PM','6:45PM',
					'7:00PM','7:15PM','7:30PM','7:45PM',
					'8:00PM','8:15PM','8:30PM','8:45PM',
					'9:00PM','9:15PM','9:30PM','9:45PM',
					'10:00PM','10:15PM','10:30PM','10:45PM',
					'11:00PM','11:15PM','11:30PM','11:45PM',
					'12:00AM','12:15AM','12:30AM','12:45AM'
				]
			}
		}
	},

	mounted () {
		this.initializeSchedule()
	},

	methods: {

		/**
		 * [initializeSchedule description]
		 * @return {[type]} [description]
		 */
		initializeSchedule () {
			let now = moment(this.post.schedule_at, 'YYYY-MM-DD HH:mm:ss')
			let date = now.format('YYYY-MM-DD')
			let minutes = now.format('mm')
			let hours = now.format('h')
			let ampm = now.format('A')
			let finalTime = null
			this.scheduled_at.date = date
			if (minutes > 0 && minutes <= 15) { // 01:01
				minutes = 15
			}
			else if (minutes > 15 && minutes <= 30) { // 01:01
				minutes = 30
			}
			else if (minutes > 30 && minutes <= 45) {
				minutes = 45
			}
			else if (minutes > 45 && minutes <= 60) {
				minutes = '00'
				hours = now.add(1, 'hours').format('h')
			}

			finalTime = `${hours}:${minutes}${ampm}`
			this.scheduled_at.time = finalTime
		},

		cancelPost () {
			this.loaders.cancelling = true

			const apiMyGig = new ApiMyGig(this.hub)

			apiMyGig.cancelPost(this.payload)
				.then(response => {
					this.loaders.cancelling = false
					this.$emit('post-cancelled', response.data.data.gig_post)
				})
				.catch(error => {
					console.error(error)
					this.loaders.cancelling = false
				})
		},

		reSchedulePost () {
			this.loaders.scheduling = true

			const apiMyGig = new ApiMyGig(this.hub)

			let renderedDate = `${this.scheduled_at.date} ${this.scheduled_at.time}`
			let schedule_at = moment(renderedDate, 'YYYY-MM-DD hh:mmA').format('YYYY-MM-DD HH:mm:ss')
			let payload = Object.assign(this.payload, {
				schedule_at
			})

			apiMyGig.reSchedulePost(payload)
				.then(response => {
					this.loaders.scheduling = false
					this.isScheduling = false
					this.$emit('post-rescheduled', response.data.data.gig_post)
				})
				.catch(error => {
					console.error(error)
					this.loaders.scheduling = false
				})
		}
	},

	computed: {
		payload () {
			let payload = {
				gig_id: this.post.gig_id,
				post_id: this.post.id
			}
			return payload
		}
	}
}
</script>