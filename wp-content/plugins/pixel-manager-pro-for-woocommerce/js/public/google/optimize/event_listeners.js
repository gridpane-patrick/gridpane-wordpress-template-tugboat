/**
 * Load Google Optimize event listeners
 */

jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.google?.optimize?.container_id && !wpmDataLayer?.pixels?.google?.optimize?.loaded) {
		if (wpm.canIFire("analytics", "google-optimize")) wpm.load_google_optimize_pixel()
	}
})
