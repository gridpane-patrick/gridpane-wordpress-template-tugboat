/**
 * Load Google Ads functions
 * */

(function (wpm, $, undefined) {


	wpm.getGoogleAdsConversionIdentifiersWithLabel = function () {

		let conversionIdentifiers = []

		if (wpmDataLayer?.pixels?.google?.ads?.conversionIds) {
			for (const [key, item] of Object.entries(wpmDataLayer.pixels.google.ads.conversionIds)) {
				if (item) {
					conversionIdentifiers.push(key + "/" + item)
				}
			}
		}

		return conversionIdentifiers
	}

	wpm.getGoogleAdsConversionIdentifiers = function () {

		let conversionIdentifiers = []

		for (const [key, item] of Object.entries(wpmDataLayer.pixels.google.ads.conversionIds)) {
			conversionIdentifiers.push(key)
		}

		return conversionIdentifiers
	}

	wpm.getGoogleAdsRegularOrderItems = function () {

		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				quantity: item.quantity,
				price   : item.price,
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.id = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			} else {

				orderItem.id = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			}
		}

		return orderItems
	}

	wpm.getGoogleAdsDynamicRemarketingOrderItems = function () {

		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				quantity                : item.quantity,
				price                   : item.price,
				google_business_vertical: wpmDataLayer.pixels.google.ads.google_business_vertical,
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.id = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			} else {

				orderItem.id = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.google.ads.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			}
		}

		return orderItems
	}

}(window.wpm = window.wpm || {}, jQuery))
