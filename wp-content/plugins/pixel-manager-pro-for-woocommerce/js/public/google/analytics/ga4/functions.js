/**
 * Load GA4 functions
 * */

(function (wpm, $, undefined) {

	wpm.getGA4OrderItems = function () {

		// "item_id"      : "34",
		// "item_name"    : "Hoodie",
		// "quantity"     : 1,
		// "item_brand"   : "",
		// "item_variant" : "Color: blue | Logo: yes",
		// "price"        : 45,
		// "currency"     : "CHF",
		// "item_category": "Hoodies"


		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				quantity     : item.quantity,
				price        : item.price,
				item_name    : item.name,
				currency     : wpmDataLayer.order.currency,
				item_category: wpmDataLayer.products[item.id].category.join("/"),
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.item_id      = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type])
				orderItem.item_variant = wpmDataLayer.products[item.variation_id].variant_name
				orderItem.item_brand   = wpmDataLayer.products[item.variation_id].brand
			} else {

				orderItem.item_id    = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type])
				orderItem.item_brand = wpmDataLayer.products[item.id].brand
			}

			orderItems.push(orderItem)
		}

		return orderItems
	}

}(window.wpm = window.wpm || {}, jQuery))
