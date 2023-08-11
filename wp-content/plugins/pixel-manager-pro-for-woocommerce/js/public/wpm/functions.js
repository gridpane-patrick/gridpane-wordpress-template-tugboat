/**
 * Create a wpm namespace under which all functions are declared
 */

// https://stackoverflow.com/a/5947280/4688612

(function (wpm, $, undefined) {

	const wpmDeduper = {
		keyName          : "_wpm_order_ids",
		cookieExpiresDays: 365,
	}

	const wpmRestSettings = {
		// cookiesAvailable                  : '_wpm_cookies_are_available',
		cookieWpmRestEndpointAvailable: "_wpm_endpoint_available",
		restEndpoint                  : "/wp-json/",
		restFails                     : 0,
		restFailsThreshold            : 10,
	}

	wpm.emailSelected         = false
	wpm.paymentMethodSelected = false

	// wpm.checkIfCookiesAvailable = function () {
	//
	//     // read the cookie if previously set, if it is return true, otherwise continue
	//     if (wpm.getCookie(wpmRestSettings.cookiesAvailable)) {
	//         return true;
	//     }
	//
	//     // set the cookie for the session
	//     Cookies.set(wpmRestSettings.cookiesAvailable, true);
	//
	//     // read cookie, true if ok, false if not ok
	//     return !!wpm.getCookie(wpmRestSettings.cookiesAvailable);
	// }

	wpm.useRestEndpoint = () => {

		// only if sessionStorage is available

		// only if REST API endpoint is generally accessible
		// check in sessionStorage if we checked before and return answer
		// otherwise check if the endpoint is available, save answer in sessionStorage and return answer

		// only if not too many REST API errors happened

		return wpm.isSessionStorageAvailable() &&
			wpm.isRestEndpointAvailable() &&
			wpm.isBelowRestErrorThreshold()
	}

	wpm.isBelowRestErrorThreshold = () => window.sessionStorage.getItem(wpmRestSettings.restFails) <= wpmRestSettings.restFailsThreshold

	wpm.isRestEndpointAvailable = () => {

		if (window.sessionStorage.getItem(wpmRestSettings.cookieWpmRestEndpointAvailable)) {
			return JSON.parse(window.sessionStorage.getItem(wpmRestSettings.cookieWpmRestEndpointAvailable))
		} else {
			// return wpm.testEndpoint();
			// just set the value whenever possible in order not to wait or block the main thread
			wpm.testEndpoint()
		}
	}

	wpm.isSessionStorageAvailable = () => !!window.sessionStorage

	wpm.testEndpoint = (
		url        = location.protocol + "//" + location.host + wpmRestSettings.restEndpoint,
		cookieName = wpmRestSettings.cookieWpmRestEndpointAvailable,
	) => {
		// console.log('testing endpoint');

		jQuery.ajax(url, {
			type   : "HEAD",
			timeout: 1000,
			// async: false,
			statusCode: {
				200: function (response) {
					// Cookies.set(cookieName, true);
					// console.log('endpoint works');
					window.sessionStorage.setItem(cookieName, JSON.stringify(true))
				},
				404: function (response) {
					// Cookies.set(cookieName, false);
					// console.log('endpoint doesn\'t work');
					window.sessionStorage.setItem(cookieName, JSON.stringify(false))
				},
				0  : function (response) {
					// Cookies.set(cookieName, false);
					// console.log('endpoint doesn\'t work');
					window.sessionStorage.setItem(cookieName, JSON.stringify(false))
				},
			},
		}).then(response => {
			// console.log('test done')
			// console.log('result: ' + JSON.parse(window.sessionStorage.getItem(cookieName)));
			// return JSON.parse(window.sessionStorage.getItem(cookieName));
		})
	}

	wpm.isWpmRestEndpointAvailable = (cookieName = wpmRestSettings.cookieWpmRestEndpointAvailable) => !!wpm.getCookie(cookieName)

	wpm.writeOrderIdToStorage = (orderId, expireDays = 365) => {

		// save the order ID in the browser storage

		if (!window.Storage) {
			let expiresDate = new Date()
			expiresDate.setDate(expiresDate.getDate() + wpmDeduper.cookieExpiresDays)

			let ids = []
			if (checkCookie()) {
				ids = JSON.parse(wpm.getCookie(wpmDeduper.keyName))
			}

			if (!ids.includes(orderId)) {
				ids.push(orderId)
				document.cookie = wpmDeduper.keyName + "=" + JSON.stringify(ids) + ";expires=" + expiresDate.toUTCString()
			}

		} else {
			if (localStorage.getItem(wpmDeduper.keyName) === null) {
				let ids = []
				ids.push(orderId)
				window.localStorage.setItem(wpmDeduper.keyName, JSON.stringify(ids))

			} else {
				let ids = JSON.parse(localStorage.getItem(wpmDeduper.keyName))
				if (!ids.includes(orderId)) {
					ids.push(orderId)
					window.localStorage.setItem(wpmDeduper.keyName, JSON.stringify(ids))
				}
			}
		}

		if (typeof wpm.storeOrderIdOnServer === "function" && wpmDataLayer.orderDeduplication) {
			wpm.storeOrderIdOnServer(orderId)
		}
	}

	function checkCookie() {
		let key = wpm.getCookie(wpmDeduper.keyName)
		return key !== ""
	}

	wpm.isOrderIdStored = orderId => {

		if (wpmDataLayer.orderDeduplication) {

			if (!window.Storage) {

				if (checkCookie()) {
					let ids = JSON.parse(wpm.getCookie(wpmDeduper.keyName))
					return ids.includes(orderId)
				} else {
					return false
				}
			} else {
				if (localStorage.getItem(wpmDeduper.keyName) !== null) {
					let ids = JSON.parse(localStorage.getItem(wpmDeduper.keyName))
					return ids.includes(orderId)
				} else {
					return false
				}
			}
		} else {
			console.log("order duplication prevention: off")
			return false
		}
	}

	wpm.isEmail = email => {

		// https://emailregex.com/

		let regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

		return regex.test(email)
	}

	wpm.removeProductFromCart = (productId, quantityToRemove = null) => {

		try {

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			productId = wpm.getIdBasedOndVariationsOutputSetting(productId)

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			let quantity

			if (quantityToRemove == null) {
				quantity = wpmDataLayer.cart[productId].quantity
			} else {
				quantity = quantityToRemove
			}

			if (wpmDataLayer.cart[productId]) {

				let product = wpm.getProductDetailsFormattedForEvent(productId, quantity)

				jQuery(document).trigger("wpmRemoveFromCart", product)

				if (quantityToRemove == null || wpmDataLayer.cart[productId].quantity === quantityToRemove) {

					delete wpmDataLayer.cart[productId]

					if (sessionStorage) sessionStorage.setItem("wpmDataLayerCart", JSON.stringify(wpmDataLayer.cart))
				} else {

					wpmDataLayer.cart[productId].quantity = wpmDataLayer.cart[productId].quantity - quantity

					if (sessionStorage) sessionStorage.setItem("wpmDataLayerCart", JSON.stringify(wpmDataLayer.cart))
				}
			}
		} catch (e) {
			console.error(e)
			// console.log('getting cart from back end');
			// wpm.getCartItemsFromBackend();
			// console.log('getting cart from back end done');
		}
	}

	wpm.getIdBasedOndVariationsOutputSetting = productId => {

		try {
			if (wpmDataLayer?.general?.variationsOutput) {

				return productId
			} else {
				if (wpmDataLayer.products[productId].isVariation) {

					return wpmDataLayer.products[productId].parentId
				} else {

					return productId
				}
			}
		} catch (e) {
			console.error(e)
		}
	}

	// add_to_cart
	wpm.addProductToCart = (productId, quantity) => {

		try {

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			productId = wpm.getIdBasedOndVariationsOutputSetting(productId)

			if (!productId) throw Error("Wasn't able to retrieve a productId")

			if (wpmDataLayer?.products[productId]) {

				let product = wpm.getProductDetailsFormattedForEvent(productId, quantity)

				jQuery(document).trigger("wpmAddToCart", product)

				// add product to cart wpmDataLayer['cart']

				// if the product already exists in the object, only add the additional quantity
				// otherwise create that product object in the wpmDataLayer['cart']
				if (wpmDataLayer?.cart[productId]) {

					wpmDataLayer.cart[productId].quantity = wpmDataLayer.cart[productId].quantity + quantity
				} else {

					if (!("cart" in wpmDataLayer)) wpmDataLayer.cart = {}

					wpmDataLayer.cart[productId] = wpm.getProductDetailsFormattedForEvent(productId, quantity)
				}

				if (sessionStorage) sessionStorage.setItem("wpmDataLayerCart", JSON.stringify(wpmDataLayer.cart))
			}
		} catch (e) {
			console.error(e)

			// fallback if wpmDataLayer.cart and wpmDataLayer.products got out of sync in case cart caching has an issue
			wpm.getCartItemsFromBackend()
		}
	}

	wpm.getCartItems = () => {

		if (sessionStorage) {
			if (!sessionStorage.getItem("wpmDataLayerCart") || wpmDataLayer.shop.page_type === "order_received_page") {
				sessionStorage.setItem("wpmDataLayerCart", JSON.stringify({}))
			} else {
				wpm.saveCartObjectToDataLayer(JSON.parse(sessionStorage.getItem("wpmDataLayerCart")))
			}
		} else {
			wpm.getCartItemsFromBackend()
		}
	}

	// get all cart items from the backend
	wpm.getCartItemsFromBackend = () => {
		try {
			let data = {
				action: "wpm_get_cart_items",
			}

			jQuery.ajax(
				{
					type    : "get",
					dataType: "json",
					// url     : ajax_object.ajax_url,
					url    : wpm.ajax_url,
					data   : data,
					success: function (cartItems) {

						// save all cart items into wpmDataLayer

						if (!cartItems["cart"]) cartItems["cart"] = {}

						wpm.saveCartObjectToDataLayer(cartItems["cart"])

						if (sessionStorage) sessionStorage.setItem("wpmDataLayerCart", JSON.stringify(cartItems["cart"]))
					},
				})
		} catch (e) {
			console.error(e)
		}
	}

	// get productIds from the backend
	wpm.getProductsFromBackend = productIds => {

		if (wpmDataLayer?.products) {
			// reduce productIds by products already in the dataLayer
			productIds = productIds.filter(item => !wpmDataLayer.products.hasOwnProperty(item))
		}

		// if no products IDs are in the object, don't try to get anything from the server
		if (!productIds || productIds.length === 0) return

		try {
			let data = {
				action    : "wpm_get_product_ids",
				productIds: productIds,
			}

			return jQuery.ajax(
				{
					type    : "get",
					dataType: "json",
					// url     : ajax_object.ajax_url,
					url    : wpm.ajax_url,
					data   : data,
					success: function (products) {

						// merge products into wpmDataLayer.products
						wpmDataLayer.products = Object.assign({}, wpmDataLayer.products, products)
					},
					error  : function (response) {
						console.log(response)
					},
				})
		} catch (e) {
			console.error(e)
		}
	}

	wpm.saveCartObjectToDataLayer = cartObject => {

		wpmDataLayer.cart     = cartObject
		wpmDataLayer.products = Object.assign({}, wpmDataLayer.products, cartObject)
	}

	wpm.triggerViewItemEventPrep = productId => {

		if (wpmDataLayer.products && wpmDataLayer.products[productId]) {

			wpm.triggerViewItemEvent(productId)
		} else {
			wpm.getProductsFromBackend([productId]).then(() => {

				wpm.triggerViewItemEvent(productId)
			})
		}
	}

	wpm.triggerViewItemEvent = productId => {

		let product = wpm.getProductDetailsFormattedForEvent(productId)

		jQuery(document).trigger("wpmViewItem", product)
	}

	wpm.triggerViewItemEventNoProduct = () => {
		jQuery(document).trigger("wpmViewItemNoProduct")
	}

	wpm.fireCheckoutOption = (step, checkout_option = null, value = null) => {

		let data = {
			step           : step,
			checkout_option: checkout_option,
			value          : value,
		}

		jQuery(document).trigger("wpmFireCheckoutOption", data)
	}

	wpm.fireCheckoutProgress = step => {

		let data = {
			step: step,
		}

		jQuery(document).trigger("wpmFireCheckoutProgress", data)
	}

	wpm.getPostIdFromString = string => {

		try {
			return string.match(/(post-)(\d+)/)[2]
		} catch (e) {
			console.error(e)
		}
	}

	wpm.triggerViewItemList = productId => {

		if (!productId) throw Error("Wasn't able to retrieve a productId")

		productId = wpm.getIdBasedOndVariationsOutputSetting(productId)

		if (!productId) throw Error("Wasn't able to retrieve a productId")

		jQuery(document).trigger("wpmViewItemList", wpm.getProductDataForViewItemEvent(productId))
	}

	wpm.getProductDataForViewItemEvent = productId => {

		if (!productId) throw Error("Wasn't able to retrieve a productId")

		try {
			if (wpmDataLayer.products[productId]) {

				return wpm.getProductDetailsFormattedForEvent(productId)
			}
		} catch (e) {
			console.error(e)
		}
	}

	wpm.getMainProductIdFromProductPage = () => {

		try {
			if (["simple", "variable", "grouped", "composite", "bundle"].indexOf(wpmDataLayer.shop.product_type) >= 0) {
				return jQuery(".wpmProductId:first").data("id")
			} else {
				return false
			}
		} catch (e) {
			console.error(e)
		}
	}

	wpm.viewItemListTriggerTestMode = target => {

		jQuery(target).css({"position": "relative"})
		jQuery(target).append("<div id=\"viewItemListTriggerOverlay\"></div>")
		jQuery(target).find("#viewItemListTriggerOverlay").css({
			"z-index"         : "10",
			"display"         : "block",
			"position"        : "absolute",
			"height"          : "100%",
			"top"             : "0",
			"left"            : "0",
			"right"           : "0",
			"opacity"         : wpmDataLayer.viewItemListTrigger.opacity,
			"background-color": wpmDataLayer.viewItemListTrigger.backgroundColor,
		})
	}

	wpm.getSearchTermFromUrl = () => {

		try {
			let urlParameters = new URLSearchParams(window.location.search)
			return urlParameters.get("s")
		} catch (e) {
			console.error(e)
		}
	}

	// we need this to track timeouts for intersection observers
	let ioTimeouts = {}

	wpm.observerCallback = (entries, observer) => {

		entries.forEach((entry) => {

			try {
				let productId

				let elementId = jQuery(entry.target).data("ioid")

				// Get the productId from next element, if wpmProductId is a sibling, like in Gutenberg blocks
				// otherwise go search in children, like in regular WC loop items
				if (jQuery(entry.target).next(".wpmProductId").length) {
					// console.log('test 1');
					productId = jQuery(entry.target).next(".wpmProductId").data("id")
				} else {
					productId = jQuery(entry.target).find(".wpmProductId").data("id")
				}


				if (!productId) throw Error("wpmProductId element not found")

				if (entry.isIntersecting) {

					ioTimeouts[elementId] = setTimeout(() => {

						wpm.triggerViewItemList(productId)
						if (wpmDataLayer.viewItemListTrigger.testMode) wpm.viewItemListTriggerTestMode(entry.target)
						if (wpmDataLayer.viewItemListTrigger.repeat === false) observer.unobserve(entry.target)
					}, wpmDataLayer.viewItemListTrigger.timeout)

				} else {

					clearTimeout(ioTimeouts[elementId])
					if (wpmDataLayer.viewItemListTrigger.testMode) jQuery(entry.target).find("#viewItemListTriggerOverlay").remove()
				}
			} catch (e) {
				console.error(e)
			}
		})
	}

	// fire view_item_list only on products that have become visible
	let io
	let ioid = 0
	let allIoElementsToWatch

	let getAllElementsToWatch = () => {

		allIoElementsToWatch = jQuery(".wpmProductId")
			.map(function (i, elem) {

				if (
					jQuery(elem).parent().hasClass("type-product") ||
					jQuery(elem).parent().hasClass("product") ||
					jQuery(elem).parent().hasClass("product-item-inner")
				) {
					return jQuery(elem).parent()
				} else if (
					jQuery(elem).prev().hasClass("wc-block-grid__product") ||
					jQuery(elem).prev().hasClass("product") ||
					jQuery(elem).prev().hasClass("product-small") ||
					jQuery(elem).prev().hasClass("woocommerce-LoopProduct-link")
				) {
					return jQuery(this).prev()
				} else if (jQuery(elem).closest(".product").length) {
					return jQuery(elem).closest(".product")
				}
			})
	}

	wpm.startIntersectionObserverToWatch = () => {

		try {
			// enable view_item_list test mode from browser
			if (wpm.urlHasParameter("vildemomode")) wpmDataLayer.viewItemListTrigger.testMode = true

			// set up intersection observer
			io = new IntersectionObserver(wpm.observerCallback, {
				threshold: wpmDataLayer.viewItemListTrigger.threshold,
			})

			getAllElementsToWatch()

			allIoElementsToWatch.each((i, elem) => {

				jQuery(elem[0]).data("ioid", ioid++)

				io.observe(elem[0])
			})
		} catch (e) {
			console.error(e)
		}
	}

	// watch DOM for new lazy loaded products and add them to the intersection observer
	wpm.startProductsMutationObserverToWatch = () => {

		try {
			// Pass in the target node, as well as the observer options

			// selects the most common parent node
			// https://stackoverflow.com/a/7648323/4688612
			let productsNode = jQuery(".wpmProductId:eq(0)").parents().has(jQuery(".wpmProductId:eq(1)").parents()).first()

			if (productsNode.length) {
				productsMutationObserver.observe(productsNode[0], {
					attributes   : true,
					childList    : true,
					characterData: true,
				})
			}
		} catch (e) {
			console.error(e)
		}
	}

	// Create an observer instance
	let productsMutationObserver = new MutationObserver(mutations => {

		mutations.forEach(mutation => {
			let newNodes = mutation.addedNodes // DOM NodeList
			if (newNodes !== null) { // If there are new nodes added
				let nodes = jQuery(newNodes) // jQuery set
				nodes.each(function () {
					if (
						jQuery(this).hasClass("type-product") ||
						jQuery(this).hasClass("product-small") ||
						jQuery(this).hasClass("wc-block-grid__product")
					) {
						// check if the node has a child or sibling wpmProductId
						// if yes add it to the intersectionObserver
						if (hasWpmProductIdElement(this)) {
							jQuery(this).data("ioid", ioid++)
							io.observe(this)
						}
					}
				})
			}
		})
	})

	let hasWpmProductIdElement = elem =>
		!!(jQuery(elem).find(".wpmProductId").length ||
			jQuery(elem).siblings(".wpmProductId").length)

	wpm.setCookie = (cookieName, cookieValue = "", expiryDays = null) => {

		if (expiryDays) {

			let d = new Date()
			d.setTime(d.getTime() + (expiryDays * 24 * 60 * 60 * 1000))
			let expires     = "expires=" + d.toUTCString()
			document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/"
		} else {
			document.cookie = cookieName + "=" + cookieValue + ";path=/"
		}
	}

	wpm.getCookie = cookieName => {

		let name          = cookieName + "="
		let decodedCookie = decodeURIComponent(document.cookie)
		let ca            = decodedCookie.split(";")

		for (let i = 0; i < ca.length; i++) {

			let c = ca[i]

			while (c.charAt(0) == " ") {
				c = c.substring(1)
			}

			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length)
			}
		}

		return ""
	}

	wpm.getWpmSessionData = () => {

		if (window.sessionStorage) {

			let data = window.sessionStorage.getItem("_wpm")

			if (data !== null) {
				return JSON.parse(data)
			} else {
				return {}
			}
		} else {
			return {}
		}
	}

	wpm.setWpmSessionData = data => {
		if (window.sessionStorage) {
			window.sessionStorage.setItem("_wpm", JSON.stringify(data))
		}
	}

	wpm.storeOrderIdOnServer = orderId => {

		try {
			// save the state in the database
			let data = {
				action  : "wpm_purchase_pixels_fired",
				order_id: orderId,
				// nonce   : ajax_object.nonce,
				nonce: wpm.nonce,
			}

			jQuery.ajax(
				{
					type    : "post",
					dataType: "json",
					// url     : ajax_object.ajax_url,
					url    : wpm.ajax_url,
					data   : data,
					success: function (response) {
						if (response.success === false) {
							console.log(response)
						}
					},
					error  : function (response) {
						console.log(response)
					},
				})
		} catch (e) {
			console.error(e)
		}
	}

	wpm.getProductIdByCartItemKeyUrl = url => {

		let searchParams = new URLSearchParams(url.search)
		let cartItemKey  = searchParams.get("remove_item")

		let productId

		if (wpmDataLayer.cartItemKeys[cartItemKey]["variation_id"] === 0) {
			productId = wpmDataLayer.cartItemKeys[cartItemKey]["product_id"]
		} else {
			productId = wpmDataLayer.cartItemKeys[cartItemKey]["variation_id"]
		}

		return productId
	}

	wpm.getAddToCartLinkProductIds = () =>
		jQuery("a").map(function () {
			let href = jQuery(this).attr("href")

			if (href && href.includes("?add-to-cart=")) {
				let matches = href.match(/(add-to-cart=)(\d+)/)
				if (matches) return matches[2]
			}
		}).get()

	wpm.getProductDetailsFormattedForEvent = (productId, quantity = 1) => {

		let product = {
			id           : productId.toString(),
			dyn_r_ids    : wpmDataLayer.products[productId].dyn_r_ids,
			name         : wpmDataLayer.products[productId].name,
			list_name    : wpmDataLayer.shop.list_name,
			brand        : wpmDataLayer.products[productId].brand,
			category     : wpmDataLayer.products[productId].category,
			variant      : wpmDataLayer.products[productId].variant,
			list_position: wpmDataLayer.products[productId].position,
			quantity     : quantity,
			price        : wpmDataLayer.products[productId].price,
			currency     : wpmDataLayer.shop.currency,
			isVariable   : wpmDataLayer.products[productId].isVariable,
			isVariation  : wpmDataLayer.products[productId].isVariation,
			parentId     : wpmDataLayer.products[productId].parentId,
		}

		if (product.isVariation) product["parentId_dyn_r_ids"] = wpmDataLayer.products[productId].parentId_dyn_r_ids

		return product
	}

	wpm.setReferrerToCookie = () => {

		// can't use session storage as we can't read it from the server
		if (!wpm.getCookie("wpmReferrer")) {
			wpm.setCookie("wpmReferrer", document.referrer)
		}
	}

	wpm.getReferrerFromCookie = () => {

		if (wpm.getCookie("wpmReferrer")) {
			return wpm.getCookie("wpmReferrer")
		} else {
			return null
		}
	}

	wpm.getClidFromBrowser = (clidId = "gclid") => {

		let clidCookieId

		clidCookieId = {
			gclid: "_gcl_aw",
			dclid: "_gcl_dc",
		}

		if (wpm.getCookie(clidCookieId[clidId])) {

			let clidCookie = wpm.getCookie(clidCookieId[clidId])
			let matches    = clidCookie.match(/(GCL.[\d]*.)(.*)/)
			return matches[2]
		} else {
			return ""
		}
	}

	wpm.getUserAgent = () => navigator.userAgent

	wpm.getViewPort = () => ({
		width : Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
		height: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0),
	})


	wpm.version = () => {
		console.log(wpmDataLayer.version)
	}

	// https://api.jquery.com/jquery.getscript/
	wpm.loadScriptAndCacheIt = (url, options) => {

		// Allow user to set any option except for dataType, cache, and url
		options = jQuery.extend(options || {}, {
			dataType: "script",
			cache   : true,
			url     : url,
		})

		// Use $.ajax() since it is more flexible than $.getScript
		// Return the jqXHR object so we can chain callbacks
		return jQuery.ajax(options)
	}

	wpm.getOrderItemPrice = orderItem => (orderItem.total + orderItem.total_tax) / orderItem.quantity

	wpm.hasLoginEventFired = () => {
		let data = wpm.getWpmSessionData()
		return data?.loginEventFired
	}

	wpm.setLoginEventFired = () => {
		let data                = wpm.getWpmSessionData()
		data["loginEventFired"] = true
		wpm.setWpmSessionData(data)
	}

	wpm.wpmDataLayerExists = () => new Promise(resolve => {
		(function waitForVar() {
			if (typeof wpmDataLayer !== "undefined") return resolve()
			setTimeout(waitForVar, 50)
		})()
	})

	wpm.jQueryExists = () => new Promise(resolve => {
		(function waitForjQuery() {
			if (typeof jQuery !== "undefined") return resolve()
			setTimeout(waitForjQuery, 100)
		})()
	})

	wpm.pageLoaded = () => new Promise(resolve => {
		(function waitForVar() {
			if ("complete" === document.readyState) return resolve()
			setTimeout(waitForVar, 50)
		})()
	})

	wpm.pageReady = () => {
		return new Promise(resolve => {
			(function waitForVar() {
				if ("interactive" === document.readyState || "complete" === document.readyState) return resolve()
				setTimeout(waitForVar, 50)
			})()
		})
	}

	wpm.isMiniCartActive = () => {
		if (window.sessionStorage) {
			for (const [key, value] of Object.entries(window.sessionStorage)) {
				if (key.includes("wc_fragments")) {
					return true
				}
			}
			return false
		} else {
			return false
		}
	}

	wpm.doesWooCommerceCartExist = () => document.cookie.includes("woocommerce_items_in_cart")

	wpm.urlHasParameter = parameter => {
		let urlParams = new URLSearchParams(window.location.search)
		return urlParams.has(parameter)
	}

	// https://stackoverflow.com/a/60606893/4688612
	wpm.hashAsync = (algo, str) => {
		return crypto.subtle.digest(algo, new TextEncoder("utf-8").encode(str)).then(buf => {
			return Array.prototype.map.call(new Uint8Array(buf), x => (("00" + x.toString(16)).slice(-2))).join("")
		})
	}

	wpm.getCartValue = () => {

		let value = 0

		if(wpmDataLayer?.cart){

			for (const key in wpmDataLayer.cart) {
				// content_ids.push(wpmDataLayer.products[key].dyn_r_ids[wpmDataLayer.pixels.facebook.dynamic_remarketing.id_type])

				let product = wpmDataLayer.cart[key]

				value += product.quantity * product.price
			}
		}

		return value
	}

}(window.wpm = window.wpm || {}, jQuery))
