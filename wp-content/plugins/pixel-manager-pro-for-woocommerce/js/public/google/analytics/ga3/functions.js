/**
 * Add functions for Google Analytics Universal
 * */

(function (wpm, $, undefined) {

	wpm.getGAUAOrderItems = function () {

		// "id"           : "34",
		// "name"         : "Hoodie",
		// "brand"        : "",
		// "category"     : "Hoodies",
		// "list_position": 1,
		// "price"        : 45,
		// "quantity"     : 1,
		// "variant"      : "Color: blue | Logo: yes"


		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				quantity: item.quantity,
				price   : item.price,
				name    : item.name,
				currency: wpmDataLayer.order.currency,
				category: wpmDataLayer.products[item.id].category.join("/"),
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.id      = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type])
				orderItem.variant = wpmDataLayer.products[item.variation_id].variant_name
				orderItem.brand   = wpmDataLayer.products[item.variation_id].brand
			} else {

				orderItem.id    = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type])
				orderItem.brand = wpmDataLayer.products[item.id].brand
			}

			orderItem = wpm.ga3AddListNameToProduct(orderItem)

			orderItems.push(orderItem)
		}

		return orderItems
	}

	wpm.ga3AddListNameToProduct = function (item_data, productPosition = null) {

		// if (wpm.ga3CanProductListBeSet(item_data.id)) {
		// 	item_data.listname = wpmDataLayer.shop.list_name
		//
		// 	if (productPosition) {
		// 		item_data.list_position = productPosition
		// 	}
		// }

		item_data.list_name = wpmDataLayer.shop.list_name

		if (productPosition) {
			item_data.list_position = productPosition
		}

		return item_data
	}

}(window.wpm = window.wpm || {}, jQuery))
