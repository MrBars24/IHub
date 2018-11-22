// webpack.config.js
const webpack = require("webpack");
const path = require("path");
const Dotenv = require('dotenv-webpack');
console.log(process.env.NODE_ENV)
module.exports = {
	resolveLoader: {
		root: path.join(__dirname, "node_modules")
	},
	module: {
		// rules is for webpack versions 2.* .... phew.
		loaders: [ //
			{
				include: /\.json$/,
				loaders: ["json-loader"]
			},
			{
				include: /\.css$/,
				loaders: ["style-loader", "css-loader"]
			},
			{
				include: /\.modernizrrc\.js$/,
				loaders: ["webpack-modernizr-loader"]
			}
		]
	},
	resolve: {
		alias: {
			vue: process.env.NODE_ENV == 'production' ? "vue/dist/vue.min.js" : "vue/dist/vue.common.js", // 'vue/dist/vue.common.js' 
			modernizr$: path.resolve(__dirname, ".modernizrrc.js")
		}
	},
	vue: {
		loaders: {
			js: "buble-loader",
			scss: "vue-style-loader!css-loader!sass-loader",
		}
	},
	// configuring webpack is so hard because i can't find any documentation pointing to version 1 on the internet.
	plugins: [
		new Dotenv({
			path: './.env'
		}),
		// new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /en/)
		new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
		// define system variables to use in app
		new webpack.DefinePlugin({ // this is not working :/
			'PUSHER_KEY': JSON.stringify(process.env.PUSHER_KEY || "cf16e40419031d332777"), // default is the prod pusher_key
			'PUSHER_CLUSTER': JSON.stringify(process.env.PUSHER_CLUSTER || "ap1"),
			'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || "production"), // default value if not specified
		}),

	]
};