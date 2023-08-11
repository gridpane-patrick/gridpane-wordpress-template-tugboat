/**
 * Load Hotjar event listeners
 */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.hotjar?.site_id && !wpmDataLayer?.pixels?.hotjar?.loaded) {
		if (wpm.canIFire("analytics", "hotjar") && !wpmDataLayer?.pixels?.hotjar?.loaded) wpm.load_hotjar_pixel()
	}
})
