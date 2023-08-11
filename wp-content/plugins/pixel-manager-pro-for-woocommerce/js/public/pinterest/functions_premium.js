// TODO add enhanced match email hash to uncached pages like cart and purchase confirmation page
// TODO check if more values can be passed to product and category pages
// TODO look into how Pinterest handles variants separately https://developers.pinterest.com/docs/tag/conversion/

/**
 * Load Pinterest premium functions
 * */

(function (wpm, $, undefined) {

	wpm.getPinterestProductData = function (product) {

		if (product.isVariation) {
			return {
				product_name      : product.name,
				product_variant_id: product.dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
				// product_id        : wpmDataLayer.products[product.parentId].dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
				product_id      : product.parentId_dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
				product_category: product.category,
				product_variant : product.variant,
				product_price   : product.price,
				product_quantity: product.quantity,
				product_brand   : product.brand,
			}
		} else {
			return {
				product_name    : product.name,
				product_id      : product.dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type],
				product_category: product.category,
				product_price   : product.price,
				product_quantity: product.quantity,
				product_brand   : product.brand,
			}
		}
	}

	wpm.pinterestFormattedOrderItems = function () {

		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				product_category: wpmDataLayer.products[key].category.join(","),
				// product_brand   : wpmDataLayer.products[key].brand,
				product_quantity: item.quantity,
				product_price   : item.price,
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.product_id   = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type])
				orderItem.product_name = wpmDataLayer.products[item.variation_id].name
				orderItems.push(orderItem)
			} else {

				orderItem.product_id   = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.pinterest.dynamic_remarketing.id_type])
				orderItem.product_name = wpmDataLayer.products[item.id].name
				orderItems.push(orderItem)
			}
		}

		return orderItems
	}

	// https://developers.pinterest.com/docs/tag/conversion/
	wpm.loadPinterestPixel = function () {

		try {
			wpmDataLayer.pixels.pinterest.loaded = true

			// @formatter:off
			!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(
				Array.prototype.slice.call(arguments))};var
				n=window.pintrk;n.queue=[],n.version="3.0";var
				t=document.createElement("script");t.async=!0,t.src=e;var
				r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");

			wpm.pinterestLoadEvent();
			pintrk('page');
			// @formatter:on

		} catch (e) {
			console.error(e)
		}
	}

	wpm.pinterestLoadEvent = function () {
		try {
			if (
				(
					wpmDataLayer.general.userLoggedIn ||
					"order_received_page" === wpmDataLayer.shop.page_type
				)
				&& wpmDataLayer?.pixels?.pinterest?.enhanced_match
			) {
				pintrk("load", wpmDataLayer.pixels.pinterest.pixel_id, {em: wpmDataLayer.pixels.pinterest.enhanced_match_email})
			} else {
				pintrk("load", wpmDataLayer.pixels.pinterest.pixel_id)
			}
		} catch (e) {
			console.error(e)
		}
	}

}(window.wpm = window.wpm || {}, jQuery));
