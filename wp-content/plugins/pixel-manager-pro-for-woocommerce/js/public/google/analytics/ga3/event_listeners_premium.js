/**
 * Load Google Universal Analytics (GA3) premium event listeners
 * */


// view_item_list event
jQuery(document).on("wpmViewItemList", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item_list", {
				send_to: wpmDataLayer.pixels.google.analytics.universal.property_id,
				items  : [wpm.ga3GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// select_content event
jQuery(document).on("wpmSelectContentGaUa", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "select_content", {
				send_to     : wpmDataLayer.pixels.google.analytics.universal.property_id,
				content_type: "product",
				items       : [wpm.ga3GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// add_to_cart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "add_to_cart", {
				send_to : wpmDataLayer.pixels.google.analytics.universal.property_id,
				currency: wpmDataLayer.shop.currency,
				items   : [wpm.ga3GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// view_item event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		let data = {
			send_to: wpmDataLayer.pixels.google.analytics.universal.property_id,
		}

		if (product) {
			data.items = [wpm.ga3GetFullProductItemData(product)]
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item", data)
		})
	} catch (e) {
		console.error(e)
	}
})

// add_to_wishlist event
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "add_to_wishlist", {
				send_to: wpmDataLayer.pixels.google.analytics.universal.property_id,
				items  : [wpm.ga3GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// remove_from_cart event
jQuery(document).on("wpmRemoveFromCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "remove_from_cart", {
				send_to : wpmDataLayer.pixels.google.analytics.universal.property_id,
				currency: wpmDataLayer.shop.currency,
				items   : [wpm.ga3GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// begin_checkout event
jQuery(document).on("wpmBeginCheckout", function (event) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "begin_checkout", {
				send_to : wpmDataLayer.pixels.google.analytics.universal.property_id,
				currency: wpmDataLayer.shop.currency,
				items   : wpm.getCartItemsGaUa(),
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// set_checkout_option event
jQuery(document).on("wpmFireCheckoutOption", function (event, data) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "set_checkout_option", {
				send_to        : wpmDataLayer.pixels.google.analytics.universal.property_id,
				checkout_step  : data.step,
				checkout_option: data.checkout_option,
				value          : data.value,
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// checkout_progress event
jQuery(document).on("wpmFireCheckoutProgress", function (event, data) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "checkout_progress", {
				send_to      : wpmDataLayer.pixels.google.analytics.universal.property_id,
				checkout_step: data.step,
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// view search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		let products = []

		for (const [key, product] of Object.entries(wpmDataLayer.products)) {
			// console.log(`${key}: ${value}`);

			products.push(wpm.ga3GetFullProductItemData(product))
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_search_results", {
				send_to    : wpmDataLayer.pixels.google.analytics.universal.property_id,
				search_term: wpm.getSearchTermFromUrl(),
				items      : products,
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// User login event
jQuery(document).on("wpmLogin", function () {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "login", {
				send_to: wpmDataLayer.pixels.google.analytics.universal.property_id,
			})
		})
	} catch (e) {
		console.error(e)
	}
})

