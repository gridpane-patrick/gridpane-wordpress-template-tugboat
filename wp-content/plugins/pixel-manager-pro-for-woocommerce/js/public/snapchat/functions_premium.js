/**
 * Load Snapchat Ads functions
 * */

(function (wpm, $, undefined) {

	wpm.snapchatGetEmail = function () {

		let userInfo = {}

		if (wpmDataLayer?.user?.email_sha256) {
			userInfo.user_hashed_email = wpmDataLayer.user?.email_sha256
		}

		return userInfo
	}

	wpm.loadSnapchatPixel = function () {

		try {
			wpmDataLayer.pixels.snapchat.loaded = true;

			// @formatter:off
			(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
			{a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
				a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
				r.src=n;var u=t.getElementsByTagName(s)[0];
				u.parentNode.insertBefore(r,u);})(window,document,
				'https://sc-static.net/scevent.min.js');

			snaptr("init", wpmDataLayer.pixels.snapchat.pixel_id, wpm.snapchatGetEmail())

			snaptr("track", "PAGE_VIEW")
			// @formatter:on


		} catch (e) {
			console.error(e)
		}
	}

	wpm.getSnapchatOrderItemIds = function () {
		let contentIds = []

		for (const [key, item] of Object.entries(wpmDataLayer.order.items)) {

			if (wpmDataLayer?.general?.variationsOutput && 0 !== item.variation_id) {
				contentIds.push(String(wpmDataLayer.products[item.variation_id].dyn_r_ids[wpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]))
			} else {
				contentIds.push(String(wpmDataLayer.products[item.id].dyn_r_ids[wpmDataLayer.pixels.snapchat.dynamic_remarketing.id_type]))
			}
		}

		return contentIds
	}

}(window.wpm = window.wpm || {}, jQuery));
