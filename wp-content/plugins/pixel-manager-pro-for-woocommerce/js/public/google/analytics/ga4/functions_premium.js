/**
 * Load GA4 premium functions
 * */

(function (wpm, $, undefined) {

	wpm.ga4AddFormattedCategories = function (item_data, categories) {

		let maxCategories = 5

		// remove categories with equal names from array
		categories = Array.from(new Set(categories))

		if (Array.isArray(categories) && categories.length) {

			item_data["item_category"] = categories[0]

			let max = categories.length > maxCategories ? maxCategories : categories.length

			for (let i = 1; i < max; i++) {
				item_data["item_category" + (i + 1)] = categories[i]
			}
		}

		return item_data
	}

	wpm.getCartItemsGa4 = function () {

		let data = []

		for (const [productId, product] of Object.entries(wpmDataLayer.cart)) {

			data.push(wpm.ga4GetFullProductItemData(product))
		}

		return data
	}

	wpm.ga4GetBasicProductItemData = function (product) {

		return {
			item_id  : product.dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type],
			item_name: product.name,
			// coupon   : "",
			// discount: 0,
			// affiliation: "",
			item_brand  : product.brand,
			item_variant: product.variant,
			price       : product.price,
			currency    : wpmDataLayer.shop.currency,
			quantity    : product.quantity,
		}
	}

	wpm.ga4AddListNameToProduct = function (item_data, productPosition = null) {

		item_data.item_list_name = wpmDataLayer.shop.list_name
		item_data.item_list_id = wpmDataLayer.shop.list_id

		if (productPosition) {
			item_data.index = productPosition
		}

		return item_data
	}

	wpm.ga4GetFullProductItemData = function (product) {

		let item_data

		item_data = wpm.ga4GetBasicProductItemData(product)
		item_data = wpm.ga4AddListNameToProduct(item_data, product.position)
		item_data = wpm.ga4AddFormattedCategories(item_data, product.category)

		return item_data
	}

}(window.wpm = window.wpm || {}, jQuery))
