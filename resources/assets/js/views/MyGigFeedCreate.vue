<template>
<div class="posts-container">
  <h1>{{ isEditing ? 'Update' : 'Create' }} Gig Feed</h1>
  <div class="row">
    <div class="text-center col-md-12" v-if="loaders.fetching">
      <i class="fa fa-spinner fa-pulse fa-fw margin-bottom"></i>
    </div>
    <div v-else class="col-md-12">
      <div class="detail-box">
        <div class="body">
          <div class="bordered-box form-gig">
            <div class="form-field">
              <label>Feed type</label>
              <select @change="clearForm" v-model="form.type" class="custom-select">
                <option value="rssfeed">RSS Feed</option>
                <option value="twitteraccount">Twitter Account</option>
                <option value="instagramaccount">Instagram Account</option>
                <!-- <option value="facebookpage">Facebook Page</option> -->
                <!-- <option value="linkedincompany">Linkedin Company</option> -->
                <!-- <option value="pinterestboard">Pinterest Board</option> -->
                <!-- <option value="googlepluspage">Google+ Page</option> -->
              </select>
              <small class="text-danger" v-if="error">
                {{ error.type }}
              </small>
            </div><!-- /form-field -->

            <div class="form-field" v-if="form.type === 'rssfeed'">
              <label for="feed_url-rss">RSS Feed Url</label>
              <input type="url" id="feed_url-rss" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div><!-- /form-field -->
            
            <!-- facebook -->
            <!-- <div class="form-field" v-if="form.type === 'facebookpage'">
              <label for="feed_url-facebook">Facebook Page URL</label>              
              <input type="url" id="feed_url-facebook" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div>
            <div class="form-field" v-if="form.type === 'facebookpage'">
              <label for="feed_url-facebook">Media Type</label>
              <select v-model="form.options" class="custom-select">
                <option value="all">All</option>
                <option value="video">Video</option>
                <option value="novideo">No Video</option>
              </select>              
              <small class="text-danger" v-if="error">
                {{ error.options }}
              </small>
            </div> -->

            <!-- twitter -->
            <div class="form-field" v-if="form.type === 'twitteraccount'">
              <label for="feed_url-twitter">Twitter Account URL or Twitter Username</label>
              <input type="url" id="feed_url-twitter" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div><!-- /form-field -->

            <!-- instagram -->
            <div class="form-field" v-if="form.type === 'instagramaccount'">
              <label for="feed_url-instagram">Instagram Account URL or Instagram Username</label>
              <input type="url" id="feed_url-instagram" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div><!-- /form-field -->            
            <div class="form-field" v-if="form.type === 'instagramaccount'">
              <label for="feed_url-facebook">Media Type</label>
              <select v-model="form.options" class="custom-select">
                <option value="all">Both</option>
                <option value="video">Video</option>
                <option value="picture">Picture</option>
              </select>              
              <small class="text-danger" v-if="error">
                {{ error.options }}
              </small>
            </div><!-- /form-field -->

            <!-- pinterest -->
            <!-- <div class="form-field" v-if="form.type === 'pinterestboard'">
              <label for="feed_url-pinterest">Pinterest Board URL</label>
              <input type="url" id="feed_url-pinterest" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div> -->
            
            <!-- googleplus -->
            <!-- <div class="form-field" v-if="form.type === 'googlepluspage'">
              <label for="feed_url-googleplus">Google Plus Page URL</label>
              <input type="url" id="feed_url-googleplus" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div> -->

            <!-- linkedincompany -->
            <!-- <div class="form-field" v-if="form.type === 'linkedincompany'">
              <label for="feed_url-linkedin">Linkedin Company URL</label>
              <input type="url" id="feed_url-linkedin" v-model="form.source_url">
              <small class="text-danger" v-if="error">
                {{ error.source_url }}
              </small>
            </div> -->

            <div class="form-field">
              <label for="hard_limit_days">Number of days in the past to load</label>
              <input type="number" id="hard_limit_days" v-model="form.hard_limit_days"
                min="3" max="14" maxlength="2" minlength="1">
              <small class="text-danger" v-if="error">
                {{ error.hard_limit_days }}
              </small>
            </div>

            <div class="form-field">
							<input :disabled="saveEnable" v-model="form.is_active" class="styled-checkbox"
							  id="is_active" type="checkbox" :value="form.is_active">
							<label for="is_active">
								Enable this Feed source
							</label>
						</div>
          </div><!-- /bordered-box -->
        </div><!-- /body -->

      </div> <!-- /detail-box -->

      <button v-if="!isEditing" class="btn-full-width js-branding-button" 
        :disabled="saveEnable" @click="save">
        <i v-if="loaders.saving" class="fa fa-spinner fa-pulse fa-fw"></i> Save
      </button>

      <button v-else class="btn-full-width js-branding-button" 
        :disabled="saveEnable" @click="update">
        <i v-if="loaders.saving" class="fa fa-spinner fa-pulse fa-fw"></i> update
      </button>

      <!-- <button v-if="isEditing" class="btn-full-width --default" 
        @click="deleteSource">
        <i v-if="loaders.deleting" class="fa fa-spinner fa-pulse fa-fw"></i> delete
      </button> -->
    </div>
  </div>
</div>
</template>
<script>
import ApiMyGig from '../api/mygigs'
import mixinHub from '../mixins/hub'
export default {
  name: 'MyGigFeedCreate',

  mixins: [mixinHub],

  data () {
    return {
      form: {
        type: null,
        source_url: null,
        options: null,
        is_active: true,
        hard_limit_days: 7
      },
      loaders: {
        fetching: false,
        saving: false,
        validating: false
      },
      error: null
    }
  },

  methods: {
    initialize () {
      if (this.isEditing) {
        this.getFeedConfig()
      }
    },

    validateRssUrl (event) {
      if (!this.isFormatCorrect) {
        return
      }
      this.loaders.validating = true

      const apiMyGig = new ApiMyGig(this.hub)

      apiMyGig.validate(this.form.source_url)
        .then(response => {
          this.loaders.validating = false
          if (!response.data.success)  {
            this.error = {
              source_url: response.data.message
            }
          }
        })
        .catch(error => {
          console.error(error)
        })
    },

    getFeedConfig() {
      this.loaders.fetching = true
      const apiMyGig = new ApiMyGig(this.hub)
      apiMyGig.getFeedConfig(this.$route.params.feed_id)
        .then(response => {
          this.loaders.fetching = false
          let feed = response.data.data.feed
          let options = feed.options
          if (options !== null) {
            feed.options = options.media_type
          }
          this.form = Object.assign({}, this.form, feed)
        })
        .catch(error => {
          console.error(error)
          this.loaders.fetching = false
          this.$router.replace({
            name: 'my.gigs.feed.create'
          })
        })
    },

    clearError () {
      if (this.error != null) {
        this.error = null
      }
    },

    save () {
      this.loaders.saving = true
      this.sanitizeForm()      
      const apiMyGig = new ApiMyGig(this.hub)
      apiMyGig.createFeedConfig(this.form)
        .then(response => {
          this.loaders.saving = false
          // commit the new saved item to store
          // this.$store.commit('setGigFeedList', {
          //   feeds: response.data.data.feed,
          //   isNew: true
          // })

          // redirect
          this.$router.replace({
            name: 'my.gigs.feed.manage',
						params: {
							success: {
								type: 'created',
								message: 'Your new Gig Feed has been created.'
							}
						}
          })
        })
        .catch(error => {
          console.error(error)
          this.loaders.saving = false
        })
    },

    update () {
      this.loaders.saving = true
      this.sanitizeForm()
      const apiMyGig = new ApiMyGig(this.hub)
      apiMyGig.updateFeedConfig(this.form)
        .then(response => {
          this.loaders.saving = false
          this.$router.replace({
            name: 'my.gigs.feed.manage',
						params: {
							success: {
								type: 'updated',
								message: 'Gig Feed source has been updated.'
							}
						}
          })
        })
        .catch(error => {
          this.loaders.saving = false
          console.log(error)
        })
    },

    sanitizeForm () {
      if (this.type.match(/facebook|instagram/)) {
        let options = this.form.options
        this.form.options = {
          'media_type': options
        }
      }
      else {
        this.form.options = null
      }

      // fixed the url handle
      if (!this.url && this.type != 'rss') {
        this.form.source_url = this.fixedUrl.href
      }
    },

    clearForm() {
      this.form.source_url = ''
      this.form.options = null
      this.form.hard_limit_days = 7
    }
  },

  
  computed: {
		isEditing () {
			return this.$route.meta.edit !== undefined
    },
    saveEnable () {      
      return this.loaders.saving || 
              !this.form.type || 
              !this.form.source_url || 
              // !this.isFormatCorrect ||
              this.error != null ||
              this.loaders.validating
    },
    url () {
      let url = null
      try {
        url = new URL(this.form.source_url)
      } catch (error) {
        url = null
      }
      return url
    },
    fixedUrl () {
      if (!this.url) {
        if (this.type != 'rss') {
          let sanitizedSource = this.form.source_url.replace(/[^\w\s]/gi, '').trim()
          let fixedUrl = `https://${this.type}.com/${sanitizedSource}`
          return new URL(fixedUrl)
        }
      }
      return this.url
    },
    isFormatCorrect () {
      if (!this.url || !this.url.host) {
        return 
      }

      if (this.type === 'rss') {
        return this.form.source_url !== ''
      }
      return Boolean(this.url.host.match(new RegExp(this.type)))
    },

    type () {
      switch(this.form.type) {
        // case 'facebookpage':
        //   return 'facebook' 
        //   break
        case 'twitteraccount':
          return 'twitter' 
          break
        case 'instagramaccount':
          return 'instagram' 
          break
        // case 'pinterestboard':
        //   return 'pinterest' 
        //   break
        // case 'googlepluspage':
        //   return 'plus.google' 
        //   break
        // case 'linkedincompany':
        //   return 'linkedin'
        //   break
        default:
          return 'rss'
          break
      }
    }
  },

  mounted () {
    if (this.init) {
      this.initialize()
    }
  },

  watch: {
    '$route': 'initialize',
    init (value) {
      if (value) {
        this.initialize()
      }
    },
    'form.type': 'clearError',
    'form.source_url': 'clearError'
  }
}
</script>
