/**
 * Register event listeners
 */

// remove_from_cart event
// jQuery('.remove_from_cart_button, .remove').on('click', function (e) {
jQuery(document).on("click", ".remove_from_cart_button, .remove", function () {

	try {

		let url       = new URL(jQuery(this).attr("href"))
		let productId = wpm.getProductIdByCartItemKeyUrl(url)

		wpm.removeProductFromCart(productId)

	} catch (e) {
		console.error(e)
	}
})


// add_to_cart event
jQuery(document).on("click", ".add_to_cart_button:not(.product_type_variable), .ajax_add_to_cart, .single_add_to_cart_button", function () {

	try {

		let quantity = 1,
			productId


		getProductDetails:

			// Only process on product pages
			if (wpmDataLayer.shop.page_type === "product") {

				// First process related and upsell products
				if (typeof jQuery(this).attr("href") !== "undefined" && jQuery(this).attr("href").includes("add-to-cart")) {

					productId = jQuery(this).data("product_id")
					break getProductDetails
				}

				// If is simple product
				if (wpmDataLayer.shop.product_type === "simple") {

					quantity = Number(jQuery(".input-text.qty").val())
					if (!quantity && quantity !== 0) quantity = 1

					productId = jQuery(this).val()
					break getProductDetails
				}

				// If is variable product or variable-subscription
				if (["variable", "variable-subscription"].indexOf(wpmDataLayer.shop.product_type) >= 0) {

					quantity = Number(jQuery(".input-text.qty").val())
					if (!quantity && quantity !== 0) quantity = 1

					productId = jQuery("[name='variation_id']").val()
					break getProductDetails
				}

				// If is grouped product
				if (wpmDataLayer.shop.product_type === "grouped") {

					jQuery(".woocommerce-grouped-product-list-item").each(function () {

						quantity = Number(jQuery(this).find(".input-text.qty").val())

						if (!quantity && quantity !== 0) quantity = 1

						let classes = jQuery(this).attr("class")
						productId   = wpm.getPostIdFromString(classes)
					})

					break getProductDetails
				}

				// If is bundle product
				if (wpmDataLayer.shop.product_type === "bundle") {

					quantity = Number(jQuery(".input-text.qty").val())
					if (!quantity && quantity !== 0) quantity = 1

					productId = jQuery("input[name=add-to-cart]").val()
					break getProductDetails
				}

			} else {

				productId = jQuery(this).data("product_id")
			}

		wpm.addProductToCart(productId, quantity)

	} catch (e) {
		console.error(e)
	}
})


/**
 * If someone clicks anywhere on a custom /?add-to-cart=123 link
 * trigger the add to cart event
 */
// jQuery('a:not(.add_to_cart_button, .ajax_add_to_cart, .single_add_to_cart_button)').one('click', function (event) {
jQuery(document).one("click", "a:not(.add_to_cart_button, .ajax_add_to_cart, .single_add_to_cart_button)", function (event) {

	try {
		if (jQuery(event.target).closest("a").attr("href")) {

			let href = jQuery(event.target).closest("a").attr("href")

			if (href.includes("add-to-cart=")) {

				let matches = href.match(/(add-to-cart=)(\d+)/)
				if (matches) wpm.addProductToCart(matches[2], 1)
			}
		}
	} catch (e) {
		console.error(e)
	}
})

// select_content GA UA event
// select_item GA 4 event
// jQuery(document).on('click', '.woocommerce-LoopProduct-link, .wc-block-grid__product, .product-small.box', function (e) {
// jQuery('.woocommerce-LoopProduct-link, .wc-block-grid__product, .product, .product-small, .type-product').on('click', function (e) {
jQuery(document).on("click", ".woocommerce-LoopProduct-link, .wc-block-grid__product, .product, .product-small, .type-product", function () {

	try {

		/**
		 * On some pages the event fires multiple times, and on product pages
		 * even on page load. Using e.stopPropagation helps to prevent this,
		 * but I don't know why. We don't even have to use this, since only a real
		 * product click yields a valid productId. So we filter the invalid click
		 * events out later down in the code. I'll keep it that way because this is
		 * the most compatible way across shops.
		 *
		 * e.stopPropagation();
		 * */

		let productId = jQuery(this).nextAll(".wpmProductId:first").data("id")

		/**
		 * On product pages, for some reason, the click event is triggered on the main product on page load.
		 * In that case no ID is found. But we can discard it, since we only want to trigger the event on
		 * related products, which are found below.
		 */

		if (productId) {

			productId = wpm.getIdBasedOndVariationsOutputSetting(productId)

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			if (wpmDataLayer.products && wpmDataLayer.products[productId]) {

				let product = wpm.getProductDetailsFormattedForEvent(productId)

				jQuery(document).trigger("wpmSelectContentGaUa", product)
				jQuery(document).trigger("wpmSelectItem", product)
			}
		}
	} catch (e) {
		console.error(e)
	}
})

// begin_checkout event
let checkoutButtonClasses = [
	".checkout-button",
	".cart-checkout-button",
	".button.checkout",
	".xoo-wsc-ft-btn-checkout", // https://xootix.com/side-cart-for-woocommerce/
	".elementor-button--checkout",
]

jQuery(document).one("click", checkoutButtonClasses.join(","), function () {
	jQuery(document).trigger("wpmBeginCheckout")
})


// checkout_progress event
// track checkout option event: entered valid billing email
jQuery(document).on("input", "#billing_email", function () {

	if (wpm.isEmail(jQuery(this).val())) {
		// wpm.fireCheckoutOption(2);
		wpm.fireCheckoutProgress(2)
		wpm.emailSelected = true
	}
})

// track checkout option event: purchase click
jQuery(document).on("click", ".wc_payment_methods", function () {

	if (false === wpm.paymentMethodSelected) {
		wpm.fireCheckoutProgress(3)
	}

	wpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val())
	wpm.paymentMethodSelected = true
})

// track checkout option event: purchase click
// jQuery('#place_order').one('click',  function () {
jQuery(document).one("click", "#place_order", function () {

	if (false === wpm.emailSelected) {
		wpm.fireCheckoutProgress(2)
	}

	if (false === wpm.paymentMethodSelected) {
		wpm.fireCheckoutProgress(3)
		wpm.fireCheckoutOption(3, jQuery("input[name='payment_method']:checked").val())
	}

	wpm.fireCheckoutProgress(4)
})

// update cart event
//     jQuery("[name='update_cart']").on('click',  function (e) {
jQuery(document).on("click", "[name='update_cart']", function () {

	try {
		jQuery(".cart_item").each(function () {

			let url       = new URL(jQuery(this).find(".product-remove").find("a").attr("href"))
			let productId = wpm.getProductIdByCartItemKeyUrl(url)


			let quantity = jQuery(this).find(".qty").val()

			if (quantity === 0) {
				wpm.removeProductFromCart(productId)
			} else if (quantity < wpmDataLayer.cart[productId].quantity) {
				wpm.removeProductFromCart(productId, wpmDataLayer.cart[productId].quantity - quantity)
			} else if (quantity > wpmDataLayer.cart[productId].quantity) {
				wpm.addProductToCart(productId, quantity - wpmDataLayer.cart[productId].quantity)
			}
		})
	} catch (e) {
		console.error(e)
		wpm.getCartItemsFromBackend()
	}
})


// add_to_wishlist
jQuery(document).on("click", ".add_to_wishlist, .wl-add-to", function () {

	try {

		let productId

		if (jQuery(this).data("productid")) { // for the WooCommerce wishlist plugin

			productId = jQuery(this).data("productid")
		} else if (jQuery(this).data("product-id")) {  // for the YITH wishlist plugin

			productId = jQuery(this).data("product-id")
		}

		if (!productId) throw Error("Wasn't able to retrieve a productId")

		let product = wpm.getProductDetailsFormattedForEvent(productId)

		jQuery(document).trigger("wpmAddToWishlist", product)
	} catch (e) {
		console.error(e)
	}
})

jQuery(document).on("updated_cart_totals", function () {
	jQuery(document).trigger("wpmViewCart")
})


/**
 * Called when the user selects all the required dropdowns / attributes
 *
 * Has to be hooked after document ready !
 *
 * https://stackoverflow.com/a/27849208/4688612
 * https://stackoverflow.com/a/65065335/4688612
 */

jQuery(function () {

	jQuery(".single_variation_wrap").on("show_variation", function (event, variation) {

		try {
			let productId = wpm.getIdBasedOndVariationsOutputSetting(variation.variation_id)

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			wpm.triggerViewItemEventPrep(productId)

		} catch (e) {
			console.error(e)
		}
	})
})


/**
 * Called on variable products when no selection has been done yet
 * or when the visitor deselects his choice.
 *
 * Has to be hooked after document ready !
 */

// jQuery(function () {
//
// 	jQuery(".single_variation_wrap").on("hide_variation", function () {
//
// 		try {
// 			let classes   = jQuery("body").attr("class")
// 			let productId = classes.match(/(postid-)(\d+)/)[2]
//
// 			if (!productId) throw Error("Wasn't able to retrieve a productId")
//
// 			/**
// 			 * If we have a variable product with no preset,
// 			 * and variations output is enabled,
// 			 * then we send a viewItem event with the first
// 			 * variation we find for the parent.
// 			 * If variations output is disabled,
// 			 * we just send the parent ID.
// 			 *
// 			 * And if Facebook microdata is active, use the
// 			 * microdata product ID.
// 			 */
//
// 			if (
// 				"variable" === wpmDataLayer.shop.product_type &&
// 				wpmDataLayer?.general?.variationsOutput
// 			) {
// 				for (const [key, product] of Object.entries(wpmDataLayer.products)) {
// 					if ("parentId" in product) {
//
// 						productId = product.id
// 						break
// 					}
// 				}
//
// 				if (wpmDataLayer?.pixels?.facebook?.microdata_product_id) {
// 					productId = wpmDataLayer.pixels.facebook.microdata_product_id
// 				}
// 			}
//
// 			// console.log("hmm")
// 			wpm.triggerViewItemEventPrep(productId)
//
// 		} catch (e) {
// 			console.error(e)
// 		}
// 	})
// })

// jQuery(function () {
//
// 	jQuery(".single_variation_wrap").on("hide_variation", function () {
// 		jQuery(document).trigger("wpmviewitem")
// 	})
// })


/**
 * Set up wpm events
 */

// populate the wpmDataLayer with the cart items
jQuery(document).on("wpmLoad", function () {

	try {
		// When a new session is initiated there are no items in the cart,
		// so we can save the call to get the cart items
		if (wpm.doesWooCommerceCartExist()) wpm.getCartItems()

	} catch (e) {
		console.error(e)
	}
})

// get all add-to-cart= products from backend
jQuery(document).on("wpmLoad", function () {

	wpmDataLayer.products = wpmDataLayer.products || {}

	// scan page for add-to-cart= links
	let productIds = wpm.getAddToCartLinkProductIds()

	wpm.getProductsFromBackend(productIds)
})

/**
 * Save the referrer into a cookie
 */

jQuery(document).on("wpmLoad", function () {

	// can't use session storage as we can't read it from the server
	if (!wpm.getCookie("wpmReferrer")) {

		if (document.referrer) {
			let referrerUrl      = new URL(document.referrer)
			let referrerHostname = referrerUrl.hostname

			if (referrerHostname !== window.location.host) {
				wpm.setCookie("wpmReferrer", referrerHostname)
			}
		}
	}
})


/**
 * Create our own load event in order to better handle script flow execution when JS "optimizers" shuffle the code.
 */

jQuery(document).on("wpmLoad", function () {
	// document.addEventListener("wpmLoad", function () {
	try {
		if (typeof wpmDataLayer != "undefined" && !wpmDataLayer?.wpmLoadFired) {

			jQuery(document).trigger("wpmLoadAlways")

			if (wpmDataLayer?.shop) {
				if (
					"product" === wpmDataLayer.shop.page_type &&
					"variable" !== wpmDataLayer.shop.product_type &&
					wpm.getMainProductIdFromProductPage()
				) {
					let product = wpm.getProductDataForViewItemEvent(wpm.getMainProductIdFromProductPage())
					jQuery(document).trigger("wpmViewItem", product)
				} else if ("product_category" === wpmDataLayer.shop.page_type) {
					jQuery(document).trigger("wpmCategory")
				} else if ("search" === wpmDataLayer.shop.page_type) {
					jQuery(document).trigger("wpmSearch")
				} else if ("cart" === wpmDataLayer.shop.page_type) {
					jQuery(document).trigger("wpmViewCart")
				} else if ("order_received_page" === wpmDataLayer.shop.page_type && wpmDataLayer.order) {
					if (!wpm.isOrderIdStored(wpmDataLayer.order.id)) {
						jQuery(document).trigger("wpmOrderReceivedPage")
						wpm.writeOrderIdToStorage(wpmDataLayer.order.id)
					}
				} else {
					jQuery(document).trigger("wpmEverywhereElse")
				}
			} else {
				jQuery(document).trigger("wpmEverywhereElse")
			}

			if (wpmDataLayer?.user?.id && !wpm.hasLoginEventFired()) {
				jQuery(document).trigger("wpmLogin")
				wpm.setLoginEventFired()
			}

			// /**
			//  * Load mini cart fragments into a wpm session storage key,
			//  * after the document load event.
			//  */
			// jQuery(document).ajaxSend(function (event, jqxhr, settings) {
			// 	// console.log('settings.url: ' + settings.url);
			//
			// 	if (settings.url.includes("get_refreshed_fragments") && sessionStorage) {
			// 		if (!sessionStorage.getItem("wpmMiniCartActive")) {
			// 			sessionStorage.setItem("wpmMiniCartActive", JSON.stringify(true))
			// 		}
			// 	}
			// })

			wpmDataLayer.wpmLoadFired = true
		}

	} catch (e) {
		console.error(e)
	}
})

/**
 * Load all pixels
 */
jQuery(document).on("wpmPreLoadPixels", function () {

	if (wpmDataLayer?.shop?.cookie_consent_mgmt?.explicit_consent && !wpm.explicitConsentStateAlreadySet()) {
		wpm.updateConsentCookieValues(null, null, true)
	}

	jQuery(document).trigger("wpmLoadPixels", {})
})
