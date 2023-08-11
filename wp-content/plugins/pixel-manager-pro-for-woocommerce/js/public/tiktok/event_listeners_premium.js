// https://ads.tiktok.com/help/article?aid=10028
// TODO check all events and add more if there are any


/**
 * Load TikTok Ads event listeners
 * */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.tiktok?.pixel_id && !wpmDataLayer?.pixels?.tiktok?.loaded) {
		if (wpm.canIFire("ads", "tiktok-ads")) wpm.loadTikTokPixel()
	}
})

// AddToCart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		ttq.track("AddToCart", {
			content_id  : product.dyn_r_ids[wpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type],
			content_type: "product",
			content_name: product.name,
			quantity    : product.quantity,
			value       : product.price,
			currency    : product.currency,
		})
	} catch (e) {
		console.error(e)
	}
})

// InitiateCheckout event
jQuery(document).on("wpmBeginCheckout", (event) => {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		ttq.track("InitiateCheckout")
	} catch (e) {
		console.error(e)
	}
})

// ViewContent event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		let data = {}

		if (product) {
			data.content_id   = product.dyn_r_ids[wpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type]
			data.content_type = "product"
			data.content_name = product.name
			data.quantity     = product.quantity
			data.value        = product.price
			data.currency     = product.currency
		}

		ttq.track("ViewContent", data)
	} catch (e) {
		console.error(e)
	}
})

// AddToWishlist event
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		ttq.track("AddToWishlist", {
			content_id  : product.dyn_r_ids[wpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type],
			content_type: "product",
			content_name: product.name,
			quantity    : product.quantity,
			value       : product.price,
			currency    : product.currency,
		})
	} catch (e) {
		console.error(e)
	}
})

// search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		ttq.track("Search")
	} catch (e) {
		console.error(e)
	}

})


// view order received page event
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.tiktok?.loaded) return

		ttq.track(wpmDataLayer.pixels.tiktok.purchase_event_name, {
			value   : wpmDataLayer.order.value_filtered,
			currency: wpmDataLayer.order.currency,
			contents: wpm.getTikTokOrderItemIds(),
		})

	} catch (e) {
		console.error(e)
	}
})
