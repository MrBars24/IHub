import { Platform } from 'quasar-framework'
import { BASE_TOKEN } from './config/auth'

// Load lodash
window._ = require("lodash");

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

window.$ = window.jQuery = require("jquery");
require("bootstrap-sass");

// load momentjs
import moment from "moment-timezone";
moment.tz.setDefault("Australia/Melbourne");
window.moment = moment;

// load polyfills
require("./polyfills");

/**
 * resolve the static assets in both mobile and web app.
 * 
 * @param {string} file 
 * @return {string}
 */
window.resolveStaticAsset = file => {
  // remove the last charatacter in BASE_TOKEN ('/')
  let baseUrl = BASE_TOKEN.slice(0, -1)
  return Platform.is.mobile ? `${baseUrl}${file}` : file
}