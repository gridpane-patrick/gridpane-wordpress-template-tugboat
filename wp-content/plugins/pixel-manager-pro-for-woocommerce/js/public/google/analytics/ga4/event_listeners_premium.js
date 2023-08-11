/**
 * Load GA4 premium event listeners
 * */

// view_item_list event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#view_item_list
jQuery(document).on("wpmViewItemList", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item_list", {
				send_to       : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				items         : [wpm.ga4GetFullProductItemData(product)],
				item_list_name: wpmDataLayer.shop.list_name, // doesn't make sense on mini_cart
				item_list_id  : wpmDataLayer.shop.list_id, // doesn't make sense on mini_cart
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// select_item event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#select_item
jQuery(document).on("wpmSelectItem", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "select_item", {
				send_to: wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				items  : [wpm.ga4GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// add_to_cart event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#add_to_cart
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "add_to_cart", {
				send_to : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				currency: wpmDataLayer.shop.currency,
				// value: 0,
				items: [wpm.ga4GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// view_item event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#view_item
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		let data = {
			send_to: wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
		}

		if (product) {
			data.currency = wpmDataLayer.shop.currency
			// data.value = 0
			data.items    = [wpm.ga4GetFullProductItemData(product)]
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item", data)
		})
	} catch (e) {
		console.error(e)
	}
})

// add_to_wishlist event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#add_to_wishlist
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "add_to_wishlist", {
				send_to : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				currency: wpmDataLayer.shop.currency,
				// value: 0,
				items: [wpm.ga4GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// remove_from_cart event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#remove_from_cart
jQuery(document).on("wpmRemoveFromCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "remove_from_cart", {
				send_to : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				currency: wpmDataLayer.shop.currency,
				// value: 0,
				items: [wpm.ga4GetFullProductItemData(product)],
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// begin_checkout event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#begin_checkout
jQuery(document).on("wpmBeginCheckout", function (event) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "begin_checkout", {
				send_to: wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				// coupon: "",
				currency: wpmDataLayer.shop.currency,
				// value: 0,
				items: wpm.getCartItemsGa4(),
			})
		})
	} catch (e) {
		console.error(e)
	}
})

// view_cart event
// https://developers.google.com/analytics/devguides/collection/ga4/ecommerce?client_type=gtag#view_cart
jQuery(document).on("wpmViewCart", function (event) {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		if (jQuery.isEmptyObject(wpmDataLayer.cart)) return

		let products  = []
		let cartValue = null

		for (const [key, product] of Object.entries(wpmDataLayer.cart)) {

			products.push(wpm.ga4GetFullProductItemData(product))

			cartValue = cartValue + product.quantity * product.price
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_cart", {
				send_to : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				currency: wpmDataLayer.shop.currency,
				value   : cartValue.toFixed(2),
				items   : products,
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
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		let products = []

		for (const [key, product] of Object.entries(wpmDataLayer.products)) {
			// console.log(`${key}: ${value}`);

			products.push(wpm.ga4GetFullProductItemData(product))
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_search_results", {
				send_to    : wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
				search_term: wpm.getSearchTermFromUrl(),
				items      : products,
			})

		})


	} catch (e) {
		console.error(e)
	}
})

// user log in event
jQuery(document).on("wpmLogin", function () {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.eec) return
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (!wpm.googleConfigConditionsMet("analytics")) return


		wpm.gtagLoaded().then(function () {
			gtag("event", "login", {
				send_to: wpmDataLayer.pixels.google.analytics.ga4.measurement_id,
			})
		})
	} catch (e) {
		console.error(e)
	}
})
