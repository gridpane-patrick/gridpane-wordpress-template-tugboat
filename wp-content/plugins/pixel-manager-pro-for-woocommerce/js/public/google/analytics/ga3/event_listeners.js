/**
 * Load Google Universal Analytics (GA3) event listeners
 * */


// view order received page event
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) return
		if (wpmDataLayer?.pixels?.google?.analytics?.universal?.mp_active) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "purchase", {
				send_to       : [wpmDataLayer.pixels.google.analytics.universal.property_id],
				transaction_id: wpmDataLayer.order.number,
				affiliation   : wpmDataLayer.order.affiliation,
				currency      : wpmDataLayer.order.currency,
				value         : wpmDataLayer.order.value_regular,
				discount      : wpmDataLayer.order.discount,
				tax           : wpmDataLayer.order.tax,
				shipping      : wpmDataLayer.order.shipping,
				coupon        : wpmDataLayer.order.coupon,
				items         : wpm.getGAUAOrderItems(),
			})
		})

	} catch (e) {
		console.error(e)
	}
})
