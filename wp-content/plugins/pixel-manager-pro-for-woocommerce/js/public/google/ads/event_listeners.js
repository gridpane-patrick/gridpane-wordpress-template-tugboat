/**
 * Load Google Ads event listeners
 * */

// view_item_list event
jQuery(document).on("wpmViewItemList", function (event, product) {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return


		if (
			wpmDataLayer?.general?.variationsOutput &&
			product.isVariable &&
			wpmDataLayer.pixels.google.ads.dynamic_remarketing.send_events_with_parent_ids === false
		) return

		// try to prevent that WC sends cached hits to Google
		if (!product) return

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
			items  : [{
				id                      : product.dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
				google_business_vertical: wpmDataLayer.pixels.google.ads.google_business_vertical,
			}],
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item_list", data)
		})
	} catch (e) {
		console.error(e)
	}
})

// add_to_cart event
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
			value  : product.quantity * product.price,
			items  : [{
				id                      : product.dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
				quantity                : product.quantity,
				price                   : product.price,
				google_business_vertical: wpmDataLayer.pixels.google.ads.google_business_vertical,
			}],
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "add_to_cart", data)
		})
	} catch (e) {
		console.error(e)
	}
})

// view_item event
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
		}

		if (product) {
			data.value = (product.quantity ? product.quantity : 1) * product.price
			data.items = [{
				id                      : product.dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
				quantity                : (product.quantity ? product.quantity : 1),
				price                   : product.price,
				google_business_vertical: wpmDataLayer.pixels.google.ads.google_business_vertical,
			}]
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_item", data)
		})
	} catch (e) {
		console.error(e)
	}
})


// view search event
jQuery(document).on("wpmSearch", function () {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return


		let products = []

		for (const [key, product] of Object.entries(wpmDataLayer.products)) {

			if (
				wpmDataLayer?.general?.variationsOutput &&
				product.isVariable &&
				wpmDataLayer.pixels.google.ads.dynamic_remarketing.send_events_with_parent_ids === false
			) return

			products.push({
				id                      : product.dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type],
				google_business_vertical: wpmDataLayer.pixels.google.ads.google_business_vertical,
			})
		}

		// console.log(products);

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
			// value  : 1 * product.price,
			items: products,
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "view_search_results", data)
		})
	} catch (e) {
		console.error(e)
	}
})


// view order received page event
// TODO distinguish with or without cart data active
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
			value  : wpmDataLayer.order.value_filtered,
			items  : wpm.getGoogleAdsDynamicRemarketingOrderItems(),
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "purchase", data)
		})

		// console.log(wpm.getGoogleAdsDynamicRemarketingOrderItems())
	} catch (e) {
		console.error(e)
	}
})

// user log in event
jQuery(document).on("wpmLogin", function () {

	try {
		if (jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) return
		if (!wpmDataLayer?.pixels?.google?.ads?.dynamic_remarketing?.status) return
		if (!wpm.googleConfigConditionsMet("ads")) return

		let data = {
			send_to: wpm.getGoogleAdsConversionIdentifiers(),
		}

		if (wpmDataLayer?.user?.id) {
			data.user_id = wpmDataLayer.user.id
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "login", data)
		})
	} catch (e) {
		console.error(e)
	}
})

// conversion event
// new_customer parameter: https://support.google.com/google-ads/answer/9917012
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (jQuery.isEmptyObject(wpm.getGoogleAdsConversionIdentifiersWithLabel())) return
		if (!wpm.googleConfigConditionsMet("ads")) return

		let data_basic     = {}
		let data_with_cart = {}

		data_basic = {
			send_to       : wpm.getGoogleAdsConversionIdentifiersWithLabel(),
			transaction_id: wpmDataLayer.order.number,
			value         : wpmDataLayer.order.value_filtered,
			currency      : wpmDataLayer.order.currency,
			new_customer  : wpmDataLayer.order.new_customer,
		}

		if (wpmDataLayer?.order?.clv_order_value_filtered) {
			data_basic.customer_lifetime_value = wpmDataLayer.order.clv_order_value_filtered
		}

		if (wpmDataLayer?.user?.id) {
			data_basic.user_id = wpmDataLayer.user.id
		}

		if (wpmDataLayer?.order?.aw_merchant_id) {
			data_with_cart = {
				discount        : wpmDataLayer.order.discount,
				aw_merchant_id  : wpmDataLayer.order.aw_merchant_id,
				aw_feed_country : wpmDataLayer.order.aw_feed_country,
				aw_feed_language: wpmDataLayer.order.aw_feed_language,
				items           : wpm.getGoogleAdsRegularOrderItems(),
			}
		}

		wpm.gtagLoaded().then(function () {
			gtag("event", "conversion", {...data_basic, ...data_with_cart})
		})


	} catch (e) {
		console.error(e)
	}
})
