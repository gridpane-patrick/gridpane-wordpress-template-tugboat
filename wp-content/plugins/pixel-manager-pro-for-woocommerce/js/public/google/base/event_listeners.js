/**
 * Load Google base event listeners
 */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (typeof wpmDataLayer?.pixels?.google?.state === "undefined") {
		if (wpm.canGoogleLoad()) {
			wpm.loadGoogle()
		} else {
			wpm.logPreventedPixelLoading("google", "analytics / ads")
		}
	}
})
