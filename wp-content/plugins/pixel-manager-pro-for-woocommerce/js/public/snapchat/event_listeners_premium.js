// TODO check all events and add more if there are any

/**
 * All event listeners
 * */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.snapchat?.pixel_id && !wpmDataLayer?.pixels?.snapchat?.loaded) {
		if (wpm.canIFire("ads", "snapchat-ads")) wpm.loadSnapchatPixel()
	}
})

// AddToCart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.snapchat?.loaded) return

		snaptr("track", "ADD_CART", {
			item_ids: [product.dyn_r_ids[wpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]],
		})
	} catch (e) {
		console.error(e)
	}
})

// VIEW_CONTENT event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.snapchat?.loaded) return

		let data = {}

		if (product) {
			data.item_ids = [product.dyn_r_ids[wpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]]
		}

		snaptr("track", "VIEW_CONTENT", data)
	} catch (e) {
		console.error(e)
	}
})


// view order received page event
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.snapchat?.loaded) return

		snaptr("track", "PURCHASE", {
			currency      : wpmDataLayer.order.currency,
			price         : wpmDataLayer.order.value_filtered,
			transaction_id: wpmDataLayer.order.id,
			item_ids      : wpm.getSnapchatOrderItemIds(),
		})

	} catch (e) {
		console.error(e)
	}
})
