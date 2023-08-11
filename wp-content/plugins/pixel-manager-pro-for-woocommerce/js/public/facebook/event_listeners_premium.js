/**
 * All event listeners
 * */

// add_to_cart event
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		fbq("track", "AddToWishlist", {
			content_name    : product.name,
			content_category: product.category,
			content_ids     : product.dyn_r_ids[wpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
			// contents          : "",
			currency: product.currency,
			value   : product.price,
		})
	} catch (e) {
		console.error(e)
	}
})

jQuery(document).on("wpmFbCapiEvent", function (event, eventData) {

	try {
		if (!wpmDataLayer.pixels.facebook.capi) return

		// save the state in the database
		let data = {
			action: "wpm_facebook_capi_event",
			data  : eventData,
			// nonce : wpm_facebook_premium_only_ajax_object.nonce,
			nonce: wpm.nonce,
		}

		jQuery.ajax(
			{
				type    : "post",
				dataType: "json",
				// url     : wpm_facebook_premium_only_ajax_object.ajax_url,
				url    : wpm.ajax_url,
				data   : data,
				success: function (msg) {
					// console.log(msg);
				},
				error  : function (msg) {
					// console.log(msg);
				},
			})
	} catch (e) {
		console.error(e)
	}
})


jQuery(document).on("wpmLoadAlways", function () {

	try {

	} catch (e) {
		console.error(e)
	}
})
