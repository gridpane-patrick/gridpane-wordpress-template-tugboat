/**
 * Load Google Optimize functions
 */


(function (wpm, $, undefined) {

	wpm.load_google_optimize_pixel = function () {

		try {
			wpmDataLayer.pixels.google.optimize.loaded = true

			wpm.loadScriptAndCacheIt("https://www.googleoptimize.com/optimize.js?id=" + wpmDataLayer.pixels.google.optimize.container_id)
			// .done(function (script, textStatus) {
			// 		console.log('Google Optimize loaded')
			// });

		} catch (e) {
			console.error(e)
		}
	}

}(window.wpm = window.wpm || {}, jQuery));
