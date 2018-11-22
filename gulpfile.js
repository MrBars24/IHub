const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir((mix) => {
	// app
	mix.sass("app.scss")   // public/css/app.css
		.webpack("app.js"); // public/js/app.js

	// master
	mix.sass('./resources/assets/master/master.scss') // public/css/master.css
		.scriptsIn('resources/assets/master', 'public/js/master.js'); // public/js/master.js

	// implement cache busting.
	// - build files should be saved into database
	// - and load by the mobile app.
	// mix.version([
	// 	'css/app.css', 
	// 	'css/master.css', 
	// 	'js/app.js', 
	// 	'js/master.js'
	// ]);
});
