/**
 * Load Pinterest event listeners
 * */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.pinterest?.pixel_id && !wpmDataLayer?.pixels?.pinterest?.loaded) {
		if (wpm.canIFire("ads", "pinterest-ads")) wpm.loadPinterestPixel()
	}
})


// https://help.pinterest.com/en/business/article/add-event-codes
// AddToCart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.pinterest?.loaded) return

		pintrk("track", "addtocart", {
			value     : parseFloat(product.quantity * product.price),
			currency  : product.currency,
			line_items: [wpm.getPinterestProductData(product)],
		})
	} catch (e) {
		console.error(e)
	}
})

// https://help.pinterest.com/en/business/article/add-event-codes
// pageview event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.pinterest?.loaded) return

		let data = {}

		if (product) {
			data.currency   = product.currency
			data.line_items = [wpm.getPinterestProductData(product)]
		}

		pintrk("track", "pagevisit", data)
	} catch (e) {
		console.error(e)
	}
})

// view search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.pinterest?.loaded) return

		let urlParams = new URLSearchParams(window.location.search)

		pintrk("track", "search", {
			search_query: urlParams.get("s"),
		})
	} catch (e) {
		console.error(e)
	}
})

// view category event
jQuery(document).on("wpmCategory", function () {

	try {
		if (!wpmDataLayer?.pixels?.pinterest?.loaded) return

		pintrk("track", "viewcategory")
	} catch (e) {
		console.error(e)
	}
})


// view order received page event
// https://developers.pinterest.com/docs/tag/conversion/
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.pinterest?.loaded) return

		pintrk("track", "checkout", {
			value         : wpmDataLayer.order.value_filtered,
			order_quantity: wpmDataLayer.order.quantity,
			currency      : wpmDataLayer.order.currency,
			order_id      : wpmDataLayer.order.id,
			line_items    : wpm.pinterestFormattedOrderItems(),
		})

	} catch (e) {
		console.error(e)
	}
})
