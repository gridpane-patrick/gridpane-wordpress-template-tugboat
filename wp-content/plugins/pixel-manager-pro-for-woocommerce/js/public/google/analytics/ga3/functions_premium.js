/**
 * Load Google Universal Analytics (GA3) premium functions
 * */

(function (wpm, $, undefined) {

	wpm.getCartItemsGaUa = function () {

		let data = []

		for (const [productId, product] of Object.entries(wpmDataLayer.cart)) {

			data.push(wpm.ga3GetFullProductItemData(product))
		}

		return data
	}

	wpm.ga3GetBasicProductItemData = function (product) {

		return {
			id      : product.dyn_r_ids[wpmDataLayer.pixels.google.analytics.id_type],
			name    : product.name,
			brand   : product.brand,
			category: product.category.join("/"),
			// coupon   : "",
			// list_name    : wpmDataLayer.shop.list_name,
			// list_position: product.list_position, // doesn't make sense on mini_cart
			price   : product.price,
			quantity: product.quantity,
			variant : product.variant,
		}
	}



	wpm.ga3CanProductListBeSet = function (productId) {

		if (window.sessionStorage) {

			// Check if the wpm_product_list_store_ga3 already exists,
			// and if not, create it
			if (window.sessionStorage.getItem("wpm_product_list_store_ga3") === null) {
				window.sessionStorage.setItem("wpm_product_list_store_ga3", JSON.stringify([]))
			}

			let wpmProductListStore = JSON.parse(window.sessionStorage.getItem("wpm_product_list_store_ga3"))

			if (wpmProductListStore.includes(productId)) {

				return false
			} else {

				wpmProductListStore.push(productId)
				window.sessionStorage.setItem("wpm_product_list_store_ga3", JSON.stringify(wpmProductListStore))

				return true
			}

		} else {
			return false
		}
	}

	wpm.ga3GetFullProductItemData = function (product) {

		let item_data

		item_data = wpm.ga3GetBasicProductItemData(product)
		item_data = wpm.ga3AddListNameToProduct(item_data, product.position)

		return item_data
	}

}(window.wpm = window.wpm || {}, jQuery))
