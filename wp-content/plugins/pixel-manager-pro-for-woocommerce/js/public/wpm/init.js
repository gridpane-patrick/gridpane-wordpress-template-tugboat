/**
 * After WPM is loaded
 * we first check if wpmDataLayer is loaded,
 * and as soon as it is, we load the pixels,
 * and as soon as the page load is complete,
 * we fire the wpmLoad event.
 *
 * @param {{pro:bool}} wpmDataLayer.version
 *
 * https://stackoverflow.com/a/25868457/4688612
 * https://stackoverflow.com/a/44093516/4688612
 */

wpm.wpmDataLayerExists()
	.then(function () {
		console.log("Pixel Manager Pro for WooCommerce: " + (wpmDataLayer.version.pro ? "Pro" : "Free") +" Version " + wpmDataLayer.version.number + " loaded")
		jQuery(document).trigger("wpmPreLoadPixels", {})
	})
	.then(function () {
		wpm.pageLoaded().then(function () {
			// const myEvent = new Event("wpmLoad", {cancelable: false})
			// document.dispatchEvent(myEvent)
			jQuery(document).trigger("wpmLoad")
		})
	})



/**
 * Run when page is ready
 *
 */

wpm.pageReady().then(function () {

	/**
	 * Run as soon as wpm namespace is loaded
	 */

	wpm.wpmDataLayerExists()
		.then(function () {
			// watch for products visible in viewport
			wpm.startIntersectionObserverToWatch()

			// watch for lazy loaded products
			wpm.startProductsMutationObserverToWatch()
		})
})

