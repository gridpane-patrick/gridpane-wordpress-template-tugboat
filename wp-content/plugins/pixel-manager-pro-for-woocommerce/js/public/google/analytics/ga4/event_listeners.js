/**
 * Load GA4 event listeners
 * */


// view order received page event
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {
		if (!wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) return
		if (wpmDataLayer?.pixels?.google?.analytics?.ga4?.mp_active) return
		if (!wpm.googleConfigConditionsMet("analytics")) return

		wpm.gtagLoaded().then(function () {
			gtag("event", "purchase", {
				send_to       : [wpmDataLayer.pixels.google.analytics.ga4.measurement_id],
				transaction_id: wpmDataLayer.order.number,
				affiliation   : wpmDataLayer.order.affiliation,
				currency      : wpmDataLayer.order.currency,
				value         : wpmDataLayer.order.value_regular,
				discount      : wpmDataLayer.order.discount,
				tax           : wpmDataLayer.order.tax,
				shipping      : wpmDataLayer.order.shipping,
				coupon        : wpmDataLayer.order.coupon,
				items         : wpm.getGA4OrderItems(),
			})
		})
	} catch (e) {
		console.error(e)
	}
})
