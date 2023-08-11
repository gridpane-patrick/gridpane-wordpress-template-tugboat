/**
 * Load Microsoft Ads premium functions
 * */

(function (wpm, $, undefined) {

	wpm.load_bing_pixel = function () {

		try {
			wpmDataLayer.pixels.bing.loaded = true

			// @formatter:off
			window.uetq = window.uetq || [];

			(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:wpmDataLayer.pixels.bing.uet_tag_id};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");
			// @formatter:on

		} catch (e) {
			console.error(e)
		}
	}

	wpm.bing_purchase_ecomm_prodids = function () {

		let prodIds = []

		for (const [key, orderItem] of Object.entries(wpmDataLayer.order.items)) {

			if (wpmDataLayer?.general?.variationsOutput && 0 !== orderItem.variation_id) {
				prodIds.push(String(wpmDataLayer.products[orderItem.variation_id].dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type]))
			} else {
				prodIds.push(String(wpmDataLayer.products[orderItem.id].dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type]))
			}
		}

		return prodIds
	}

	wpm.bing_purchase_items = function () {

		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				quantity: item.quantity,
				price   : item.price,
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.id = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			} else {

				orderItem.id = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.bing.dynamic_remarketing.id_type])
				orderItems.push(orderItem)
			}
		}

		return orderItems
	}

}(window.wpm = window.wpm || {}, jQuery));
