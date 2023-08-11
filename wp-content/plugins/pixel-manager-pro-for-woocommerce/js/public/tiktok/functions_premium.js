/**
 * Load TikTok Ads functions
 * */

(function (wpm, $, undefined) {

	wpm.loadTikTokPixel = function () {

		try {
			wpmDataLayer.pixels.tiktok.loaded = true

			// @formatter:off
			!function (w, d, t) {
				w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
				ttq.load(wpmDataLayer.pixels.tiktok.pixel_id);
				ttq.page();
			}(window, document, 'ttq');
			// @formatter:on

			// ttq.track("Browse")

		} catch (e) {
			console.error(e)
		}
	}

	wpm.getTikTokOrderItemIds = function () {

		let orderItems = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			let orderItem

			orderItem = {
				content_type: "product",
				quantity    : item.quantity,
				price       : item.price,
			}

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {

				orderItem.content_id   = String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type])
				orderItem.content_name = wpmDataLayer.products[item.variation_id].name
				orderItems.push(orderItem)
			} else {

				orderItem.content_id   = String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.tiktok.dynamic_remarketing.id_type])
				orderItem.content_name = wpmDataLayer.products[item.id].name
				orderItems.push(orderItem)
			}
		}

		return orderItems
	}

}(window.wpm = window.wpm || {}, jQuery));
