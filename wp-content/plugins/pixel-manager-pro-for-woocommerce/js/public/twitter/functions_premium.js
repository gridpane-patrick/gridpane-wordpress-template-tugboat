/**
 * Load Twitter Ads functions
 * */

(function (wpm, $, undefined) {

	wpm.loadTwitterPixel = function () {

		try {
			wpmDataLayer.pixels.twitter.loaded = true

			// @formatter:off
			!function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
			},s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
				a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');

			twq('init', wpmDataLayer.pixels.twitter.pixel_id);
			// @formatter:on

			twq("track", "PageView")

		} catch (e) {
			console.error(e)
		}
	}

	wpm.twitterGetOrderContentIds = function () {
		let contentIds = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {
				contentIds.push(String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.twitter.dynamic_remarketing.id_type]))
			} else {
				contentIds.push(String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.twitter.dynamic_remarketing.id_type]))
			}
		}

		return contentIds
	}

}(window.wpm = window.wpm || {}, jQuery));
