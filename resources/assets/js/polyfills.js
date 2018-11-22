import modernizr from "modernizr";
window.Modernizr = modernizr; // NOTE: for debugging purposes, remove this later.

if (!modernizr.inputtypes.date) {
	require("date-input-polyfill")
}

// if (!modernizr.inputtypes.number) {
// 	$('input[type="number"]').spinner(); // not working
// }
