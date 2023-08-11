/**
 * All event listeners
 *
 * https://developers.facebook.com/docs/meta-pixel/reference
 * */

// Load pixel event
jQuery(document).on("wpmLoadPixels", function () {

	if (wpmDataLayer?.pixels?.facebook?.pixel_id && !wpmDataLayer?.pixels?.facebook?.loaded) {
		if (wpm.canIFire("ads", "facebook-ads")) wpm.loadFacebookPixel()
	}
})

// AddToCart event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmAddToCart", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		let eventId = wpm.getRandomEventId()

		fbq("track", "AddToCart", {
			content_type: "product",
			content_name: product.name,
			content_ids : product.dyn_r_ids[wpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
			value       : parseFloat(product.quantity * product.price),
			currency    : product.currency,
		}, {
			eventID: eventId,
		})

		product["currency"] = wpmDataLayer.shop.currency

		jQuery(document).trigger("wpmFbCapiEvent", {
			event_name      : "AddToCart",
			event_id        : eventId,
			user_data       : wpm.getFbUserData(),
			event_source_url: window.location.href,
			custom_data     : wpm.fbGetProductDataForCapiEvent(product),
		})
	} catch (e) {
		console.error(e)
	}
})

// InitiateCheckout event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmBeginCheckout", (event) => {

	try {

		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		let eventId = wpm.getRandomEventId()

		let data = {}

		if (wpmDataLayer?.cart && !jQuery.isEmptyObject(wpmDataLayer.cart)) {
			data.content_type = "product"
			data.content_ids  = wpm.fbGetContentIdsFromCart()
			data.value        = wpm.getCartValue()
			data.currency     = wpmDataLayer.shop.currency
		}

		fbq("track", "InitiateCheckout", data, {
			eventID: eventId,
		})

		jQuery(document).trigger("wpmFbCapiEvent", {
			event_name      : "InitiateCheckout",
			event_id        : eventId,
			user_data       : wpm.getFbUserData(),
			event_source_url: window.location.href,
			custom_data     : data,
		})
	} catch (e) {
		console.error(e)
	}
})

// AddToWishlist event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmAddToWishlist", function (event, product) {

	try {

		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		let eventId = wpm.getRandomEventId()

		fbq("track", "AddToWishlist", {
			content_type: "product",
			content_name: product.name,
			content_ids : product.dyn_r_ids[wpmDataLayer.pixels.facebook.dynamic_remarketing.id_type],
			value       : parseFloat(product.quantity * product.price),
			currency    : product.currency,
		}, {
			eventID: eventId,
		})

		product["currency"] = wpmDataLayer.shop.currency

		jQuery(document).trigger("wpmFbCapiEvent", {
			event_name      : "AddToWishlist",
			event_id        : eventId,
			user_data       : wpm.getFbUserData(),
			event_source_url: window.location.href,
			custom_data     : wpm.fbGetProductDataForCapiEvent(product),
		})
	} catch (e) {
		console.error(e)
	}
})

// ViewContent event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmViewItem", (event, product = null) => {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		wpm.fbViewContent(product)
	} catch (e) {
		console.error(e)
	}
})

// ViewContent event with no product
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmViewItemNoProduct", function (event, product) {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		let eventId = wpm.getRandomEventId()

		fbq("track", "ViewContent", {}, {
			eventID: eventId,
		})

		jQuery(document).trigger("wpmFbCapiEvent", {
			event_name      : "ViewContent",
			event_id        : eventId,
			user_data       : wpm.getFbUserData(),
			event_source_url: window.location.href,
		})
	} catch (e) {
		console.error(e)
	}
})


// view search event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmSearch", function () {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		let eventId = wpm.getRandomEventId()

		fbq("track", "Search", {}, {
			eventID: eventId,
		})

		jQuery(document).trigger("wpmFbCapiEvent", {
			event_name      : "Search",
			event_id        : eventId,
			user_data       : wpm.getFbUserData(),
			event_source_url: window.location.href,
			custom_data     : {
				search_string: wpm.getSearchTermFromUrl(),
			},
		})
	} catch (e) {
		console.error(e)
	}
})

// load always event
jQuery(document).on("wpmLoadAlways", function () {

	try {
		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		wpm.setFbUserData()
	} catch (e) {
		console.error(e)
	}
})

// view order received page event
// https://developers.facebook.com/docs/meta-pixel/reference
jQuery(document).on("wpmOrderReceivedPage", function () {

	try {

		if (!wpmDataLayer?.pixels?.facebook?.loaded) return

		fbq("track", "Purchase",
			{
				content_type: "product",
				value       : wpmDataLayer.order.value_filtered,
				currency    : wpmDataLayer.order.currency,
				content_ids : wpm.facebookContentIds(),
			},
			{eventID: wpmDataLayer.order.id},
		)

	} catch (e) {
		console.error(e)
	}
})
