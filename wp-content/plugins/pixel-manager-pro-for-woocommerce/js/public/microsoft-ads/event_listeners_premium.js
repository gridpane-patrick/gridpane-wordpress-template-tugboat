/**
 * Load Microsoft Ads event listeners
 *
 * https://help.ads.microsoft.com/#apex/ads/en/56684/2
 *
 * add_payment_info, add_to_cart, add_to_wishlist, begin_checkout, checkout_progress, exception, generate_lead, login, page_view, purchase, refund, remove_from_cart, screen_view, search, select_content, set_checkout_option, share, sign_up, timing_complete, view_item, view_item_list, view_promotion, view_search_results
 *
 * */

// Pixel load event listener
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.bing?.uet_tag_id && !wpmDataLayer?.pixels?.bing?.loaded) {
		if (wpm.canIFire("ads", "microsoft-ads")) wpm.load_bing_pixel()
	}
})

// https://help.ads.microsoft.com/#apex/ads/en/60118/-1
// add-to-cart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.bing?.loaded) return

		window.uetq.push("event", "add_to_cart", {
			ecomm_pagetype: "cart",
			ecomm_prodid  : product.dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type],
		})
	} catch (e) {
		console.error(e)
	}
})

// https://help.ads.microsoft.com/#apex/ads/en/60118/-1
// view product event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.bing?.loaded) return

		let data = {}

		if (product) {
			data.ecomm_pagetype = "product"
			data.ecomm_prodid   = product.dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type]
		}

		window.uetq.push("event", "view_item", data)
	} catch (e) {
		console.error(e)
	}
})

// https://help.ads.microsoft.com/#apex/ads/en/60118/-1
// view category event
jQuery(document).on("wpmCategory", function () {

	try {
		if (!wpmDataLayer?.pixels?.bing?.loaded) return

		window.uetq.push("event", "", {
			ecomm_pagetype: "category",
		})
	} catch (e) {
		console.error(e)
	}
})

// https://help.ads.microsoft.com/#apex/ads/en/60118/-1
// view search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.bing?.loaded) return

		window.uetq.push("event", "search", {
			ecomm_pagetype: "searchresults",
		})
	} catch (e) {
		console.error(e)
	}
})

// https://help.ads.microsoft.com/#apex/ads/en/60118/-1
// view order received page event
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.bing?.loaded) return

		window.uetq.push("event", "purchase", {
			ecomm_pagetype: "purchase",
			ecomm_prodid  : wpm.bing_purchase_ecomm_prodids(),
			revenue_value : wpmDataLayer.order.value_filtered,
			currency      : wpmDataLayer.order.currency,
			items         : wpm.bing_purchase_items(),
		})

	} catch (e) {
		console.error(e)
	}
})

