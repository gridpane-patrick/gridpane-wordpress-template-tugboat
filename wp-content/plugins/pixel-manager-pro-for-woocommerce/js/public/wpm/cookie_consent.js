/**
 * Consent Mode functions
 */

(function (wpm, $, undefined) {


	/**
	 * Handle Cookie Management Platforms
	 */

	let getComplianzCookies = () => {

		let cmplz_statistics     = wpm.getCookie("cmplz_statistics")
		let cmplz_marketing      = wpm.getCookie("cmplz_marketing")
		let cmplz_consent_status = wpm.getCookie("cmplz_consent_status") || wpm.getCookie("cmplz_banner-status")

		if (cmplz_consent_status) {
			return {
				analytics       : cmplz_statistics === "allow",
				ads             : cmplz_marketing === "allow",
				visitorHasChosen: true,
			}
		} else {
			return false
		}
	}

	let getCookieLawInfoCookies = () => {

		let analyticsCookie  = wpm.getCookie("cookielawinfo-checkbox-analytics") || wpm.getCookie("cookielawinfo-checkbox-analytiques")
		let adsCookie        = wpm.getCookie("cookielawinfo-checkbox-advertisement") || wpm.getCookie("cookielawinfo-checkbox-performance") || wpm.getCookie("cookielawinfo-checkbox-publicite")
		let visitorHasChosen = wpm.getCookie("CookieLawInfoConsent")

		if (analyticsCookie || adsCookie) {

			return {
				analytics       : analyticsCookie === "yes",
				ads             : adsCookie === "yes",
				visitorHasChosen: !!visitorHasChosen,
			}
		} else {
			return false
		}
	}


	let
		wpmConsentValues              = {}
	wpmConsentValues.categories       = {}
	wpmConsentValues.pixels           = []
	wpmConsentValues.mode             = "category"
	wpmConsentValues.visitorHasChosen = false

	wpm.getConsentValues = () => wpmConsentValues

	wpm.setConsentValueCategories = (analytics = false, ads = false) => {
		wpmConsentValues.categories.analytics = analytics
		wpmConsentValues.categories.ads       = ads
	}

	wpm.updateConsentCookieValues = (analytics = null, ads = null, explicitConsent = false) => {

		// ad_storage
		// analytics_storage
		// functionality_storage
		// personalization_storage
		// security_storage

		let cookie

		if (analytics || ads) {

			if (analytics) {
				wpmConsentValues.categories.analytics = !!analytics
			}
			if (ads) {
				wpmConsentValues.categories.ads = !!ads
			}

		} else if (cookie = wpm.getCookie("CookieConsent")) {

			// Cookiebot
			// https://wordpress.org/plugins/cookiebot/
			cookie = decodeURI(cookie)

			wpmConsentValues.categories.analytics = cookie.indexOf("statistics:true") >= 0
			wpmConsentValues.categories.ads       = cookie.indexOf("marketing:true") >= 0
			wpmConsentValues.visitorHasChosen     = true

		} else if (cookie = wpm.getCookie("CookieScriptConsent")) {

			// Cookie Script
			// https://wordpress.org/plugins/cookie-script-com/

			cookie = JSON.parse(cookie)

			if (cookie.action === "reject") {
				wpmConsentValues.categories.analytics = false
				wpmConsentValues.categories.ads       = false
			} else if (cookie.categories.length === 2) {
				wpmConsentValues.categories.analytics = true
				wpmConsentValues.categories.ads       = true
			} else {
				wpmConsentValues.categories.analytics = cookie.categories.indexOf("performance") >= 0
				wpmConsentValues.categories.ads       = cookie.categories.indexOf("targeting") >= 0
			}

			wpmConsentValues.visitorHasChosen = true

		} else if (cookie = wpm.getCookie("borlabs-cookie")) {

			// Borlabs Cookie
			// https://borlabs.io/borlabs-cookie/

			cookie = decodeURI(cookie)
			cookie = JSON.parse(cookie)

			wpmConsentValues.categories.analytics = !!cookie?.consents?.statistics
			wpmConsentValues.categories.ads       = !!cookie?.consents?.marketing
			wpmConsentValues.visitorHasChosen     = true
			wpmConsentValues.pixels               = [...cookie?.consents?.statistics || [], ...cookie?.consents?.marketing || []]
			wpmConsentValues.mode                 = "pixel"

		} else if (cookie = getComplianzCookies()) {

			// Complianz Cookie
			// https://wordpress.org/plugins/complianz-gdpr/

			wpmConsentValues.categories.analytics = cookie.analytics === true
			wpmConsentValues.categories.ads       = cookie.ads === true
			wpmConsentValues.visitorHasChosen     = cookie.visitorHasChosen

		} else if (cookie = wpm.getCookie("cookie_notice_accepted")) {

			// Cookie Compliance (free version)
			// https://wordpress.org/plugins/cookie-notice/

			wpmConsentValues.categories.analytics = true
			wpmConsentValues.categories.ads       = true
			wpmConsentValues.visitorHasChosen     = true

		} else if (cookie = wpm.getCookie("hu-consent")) {

			// Cookie Compliance (pro version)
			// https://wordpress.org/plugins/cookie-notice/

			cookie = JSON.parse(cookie)

			wpmConsentValues.categories.analytics = !!cookie.categories["3"]
			wpmConsentValues.categories.ads       = !!cookie.categories["4"]
			wpmConsentValues.visitorHasChosen     = true

		} else if (cookie = getCookieLawInfoCookies()) {

			// CookieYes, GDPR Cookie Consent (Cookie Law Info)
			// https://wordpress.org/plugins/cookie-law-info/

			wpmConsentValues.categories.analytics = cookie.analytics === true
			wpmConsentValues.categories.ads       = cookie.ads === true
			wpmConsentValues.visitorHasChosen     = cookie.visitorHasChosen === true

		} else if (cookie = wpm.getCookie("moove_gdpr_popup")) {

			// GDPR Cookie Compliance Plugin by Moove Agency
			// https://wordpress.org/plugins/gdpr-cookie-compliance/
			// TODO write documentation on how to set up the plugin in order for this to work properly

			cookie = JSON.parse(cookie)

			wpmConsentValues.categories.analytics = cookie.thirdparty === "1"
			wpmConsentValues.categories.ads       = cookie.advanced === "1"
			wpmConsentValues.visitorHasChosen     = true

		} else {
			// consentValues.categories.analytics = true
			// consentValues.categories.ads       = true

			wpmConsentValues.categories.analytics = !explicitConsent
			wpmConsentValues.categories.ads       = !explicitConsent
		}
	}

	wpm.updateConsentCookieValues()

	wpm.setConsentDefaultValuesToExplicit = () => {
		wpmConsentValues.categories = {
			analytics: false,
			ads      : false,
		}
	}

	wpm.canIFire = (category, pixelName) => {

		let canIFireMode

		if ("category" === wpmConsentValues.mode) {
			canIFireMode = !!wpmConsentValues.categories[category]
		} else if ("pixel" === wpmConsentValues.mode) {
			canIFireMode = wpmConsentValues.pixels.includes(pixelName)

			// If a user sets "bing-ads" in Borlabs Cookie instead of
			// "microsoft-ads" in the Borlabs settings, we need to check
			// for that too.
			if (false === canIFireMode && "microsoft-ads" === pixelName) {
				canIFireMode = wpmConsentValues.pixels.includes("bing-ads")
			}
		} else {
			console.error("Couldn't find a valid consent mode in wpmConsentValues")
			canIFireMode = false
		}

		if (canIFireMode) {
			return true
		} else {
			if (true || wpm.urlHasParameter("debugConsentMode")) {
				wpm.logPreventedPixelLoading(pixelName, category)
			}

			return false
		}
	}

	wpm.logPreventedPixelLoading = (pixelName, category) => {

		if (wpmDataLayer?.shop?.cookie_consent_mgmt?.explicit_consent) {
			console.log("Pixel Manager Pro for WooCommerce: The \"" + pixelName + " (category: " + category + ")\" pixel has not fired because you have not given consent for it yet. (WPM is in explicit consent mode.)")
		} else {
			console.log("Pixel Manager Pro for WooCommerce: The \"" + pixelName + " (category: " + category + ")\" pixel has not fired because you have removed consent for this pixel. (WPM is in implicit consent mode.)")
		}
	}

	/**
	 * Runs through each script in <head> and blocks / unblocks it according to the plugin settings
	 * and user consent.
	 */

	// https://stackoverflow.com/q/65453565/4688612
	wpm.scriptTagObserver = new MutationObserver((mutations) => {
		mutations.forEach(({addedNodes}) => {
			[...addedNodes]
				.forEach(node => {

					if ($(node).data("wpm-cookie-category")) {

						// If the pixel category has been approved > unblock
						// If the pixel belongs to more than one category, then unblock if one of the categories has been approved
						// If no category has been approved, but the Google Consent Mode is active, then only unblock the Google scripts

						if (wpm.shouldScriptBeActive(node)) {
							wpm.unblockScript(node)
						} else {
							wpm.blockScript(node)
						}
					}
				})
		})
	})

	wpm.scriptTagObserver.observe(document.head, {childList: true, subtree: true})
	jQuery(document).on("DOMContentLoaded", () => wpm.scriptTagObserver.disconnect())

	wpm.shouldScriptBeActive = node => {

		if (
			wpmDataLayer.shop.cookie_consent_mgmt.explicit_consent ||
			wpmConsentValues.visitorHasChosen
		) {

			if (wpmConsentValues.mode === "category" && $(node).data("wpm-cookie-category").split(",").some(element => wpmConsentValues.categories[element])) {
				return true
			} else if (wpmConsentValues.mode === "pixel" && wpmConsentValues.pixels.includes($(node).data("wpm-pixel-name"))) {
				return true
			} else if (wpmConsentValues.mode === "pixel" && $(node).data("wpm-pixel-name") === "google" && ["google-analytics", "google-ads"].some(element => wpmConsentValues.pixels.includes(element))) {
				return true
			} else if (wpmDataLayer?.pixels?.google?.consent_mode?.active && $(node).data("wpm-pixel-name") === "google") {
				return true
			} else {
				return false
			}
		} else {
			return true
		}
	}


	wpm.unblockScript = (scriptNode, removeAttach = false) => {

		if (removeAttach) $(scriptNode).remove()

		let wpmSrc = $(scriptNode).data("wpm-src")
		if (wpmSrc) $(scriptNode).attr("src", wpmSrc)

		scriptNode.type = "text/javascript"

		if (removeAttach) $(scriptNode).appendTo("head")

		jQuery(document).trigger("wpmPreLoadPixels", {})
	}

	wpm.blockScript = (scriptNode, removeAttach = false) => {

		if (removeAttach) $(scriptNode).remove()

		if ($(scriptNode).attr("src")) $(scriptNode).removeAttr("src")
		scriptNode.type = "blocked/javascript"

		if (removeAttach) $(scriptNode).appendTo("head")
	}

	wpm.unblockAllScripts = (analytics = true, ads = true) => {
		jQuery(document).trigger("wpmPreLoadPixels", {})
	}

	wpm.unblockSelectedPixels = () => {
		jQuery(document).trigger("wpmPreLoadPixels", {})
	}


	/**
	 * Block or unblock scripts for each CMP immediately after cookie consent has been updated
	 * by the visitor.
	 */

	// Borlabs Cookie
	// If visitor accepts cookies in Borlabs Cookie unblock the scripts
	jQuery(document).on("borlabs-cookie-consent-saved", () => {

		wpm.updateConsentCookieValues()

		if (wpmConsentValues.mode === "pixel") {

			wpm.unblockSelectedPixels()
			wpm.updateGoogleConsentMode(wpmConsentValues.pixels.includes("google-analytics"), wpmConsentValues.pixels.includes("google-ads"))
		} else {

			wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
			wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
		}
	})

	// Cookiebot
	// If visitor accepts cookies in Cookiebot unblock the scripts
	// https://www.cookiebot.com/en/developer/
	jQuery(document).on("CookiebotOnAccept", () => {

		if (Cookiebot.consent.statistics) wpmConsentValues.categories.analytics = true
		if (Cookiebot.consent.marketing) wpmConsentValues.categories.ads = true

		wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
		wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)

	}, false)

	/**
	 * Cookie Script
	 * If visitor accepts cookies in Cookie Script unblock the scripts
	 * https://support.cookie-script.com/article/20-custom-events
	 */
	jQuery(document).on("CookieScriptAccept", e => {

		if (e.detail.categories.includes("performance")) wpmConsentValues.categories.analytics = true
		if (e.detail.categories.includes("targeting")) wpmConsentValues.categories.ads = true

		wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
		wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
	})

	/**
	 * Cookie Script
	 * If visitor accepts cookies in Cookie Script unblock the scripts
	 * https://support.cookie-script.com/
	 */
	jQuery(document).on("CookieScriptAcceptAll", () => {

		wpm.unblockAllScripts(true, true)
		wpm.updateGoogleConsentMode(true, true)
	})

	/**
	 * Complianz Cookie
	 *
	 * If visitor accepts cookies in Complianz unblock the scripts
	 */

	wpm.cmplzStatusChange = (cmplzConsentData) => {

		if (cmplzConsentData.detail.categories.includes("statistics")) wpm.updateConsentCookieValues(true, null)
		if (cmplzConsentData.detail.categories.includes("marketing")) wpm.updateConsentCookieValues(null, true)

		wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
		wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
	}

	jQuery(document).on("cmplzStatusChange", wpm.cmplzStatusChange)
	jQuery(document).on("cmplz_status_change", wpm.cmplzStatusChange)


	// Cookie Compliance by hu-manity.co (free and pro)
	// If visitor accepts cookies in Cookie Notice by hu-manity.co unblock the scripts (free version)
	// https://wordpress.org/support/topic/events-on-consent-change/#post-15202792
	jQuery(document).on("setCookieNotice", () => {

		wpm.updateConsentCookieValues()

		wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
		wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
	})

	/**
	 * Cookie Compliance by hu-manity.co (free and pro)
	 * If visitor accepts cookies in Cookie Notice by hu-manity.co unblock the scripts (pro version)
	 * https://wordpress.org/support/topic/events-on-consent-change/#post-15202792
	 * Because Cookie Notice has no documented API or event that is being triggered on consent save or update
	 * we have to solve this by using a mutation observer.
	 *
	 * @type {MutationObserver}
	 */

	wpm.huObserver = new MutationObserver(mutations => {
		mutations.forEach(({addedNodes}) => {
			[...addedNodes]
				.forEach(node => {

					if (node.id === "hu") {

						jQuery(".hu-cookies-save").on("click", function () {
							wpm.updateConsentCookieValues()
							wpm.unblockAllScripts(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
							wpm.updateGoogleConsentMode(wpmConsentValues.categories.analytics, wpmConsentValues.categories.ads)
						})
					}
				})
		})
	})

	if (window.hu) {
		wpm.huObserver.observe(document.documentElement || document.body, {childList: true, subtree: true})
	}

	wpm.explicitConsentStateAlreadySet = () => {

		if (wpmConsentValues.explicitConsentStateAlreadySet) {
			return true
		} else {
			wpmConsentValues.explicitConsentStateAlreadySet = true
		}
	}


}(window.wpm = window.wpm || {}, jQuery))
