// TODO implement AddPaymentInfo event
// TODO check if more values can be passed to product and cart pages

// https://business.twitter.com/en/help/campaign-measurement-and-analytics/conversion-tracking-for-websites.html

/**
 * Load Twitter Ads event listeners
 * */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.twitter?.pixel_id && !wpmDataLayer?.pixels?.twitter?.loaded) {
		if (wpm.canIFire("ads", "twitter-ads")) wpm.loadTwitterPixel()
	}
})

// add-to-cart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "AddToCart")
	} catch (e) {
		console.error(e)
	}
})

// view product event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "ViewContent")
	} catch (e) {
		console.error(e)
	}
})

// // view category event
// jQuery(document).on('wpmCategory', function () {
//
// 	if (!wpmDataLayer?.pixels?.twitter?.loaded) return;
//
// 	// twq('track', 'AddToWishlist');
// });

// add-to-wishlist event
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "AddToWishlist")
	} catch (e) {
		console.error(e)
	}
})

// start checkout event
jQuery(document).on("wpmBeginCheckout", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "InitiateCheckout")
	} catch (e) {
		console.error(e)
	}
})

// view search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "Search")
	} catch (e) {
		console.error(e)
	}
})


// view order received page event
// TODO find out under which circumstances to use different values in content_type
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.twitter?.loaded) return

		twq("track", "Purchase", {
			order_id: wpmDataLayer.order.id,
			// content_type: 'product',
			value      : wpmDataLayer.order.value_filtered,
			currency   : wpmDataLayer.order.currency,
			num_items  : wpmDataLayer.order.quantity,
			content_ids: wpm.twitterGetOrderContentIds(),
		})

	} catch (e) {
		console.error(e)
	}
})

