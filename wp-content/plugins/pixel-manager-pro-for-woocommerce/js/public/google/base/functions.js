/**
 * Load Google base functions
 */

(function (wpm, $, undefined) {

	wpm.googleConfigConditionsMet = function (type) {

		// always returns true if Google Consent Mode is active
		if (wpmDataLayer?.pixels?.google?.consent_mode?.active) {
			return true
		} else if (wpm.getConsentValues().mode === "category") {
			return wpm.getConsentValues().categories[type] === true
		} else if (wpm.getConsentValues().mode === "pixel") {
			return wpm.getConsentValues().pixels.includes("google-" + type)
		} else {
			return false
		}
	}

	wpm.getVisitorConsentStatusAndUpdateGoogleConsentSettings = function (google_consent_settings) {

		if (wpm.getConsentValues().mode === "category") {

			if (wpm.getConsentValues().categories.analytics) google_consent_settings.analytics_storage = "granted"
			if (wpm.getConsentValues().categories.ads) google_consent_settings.ad_storage = "granted"
		} else if ((wpm.getConsentValues().mode === "pixel")) {

			google_consent_settings.analytics_storage = wpm.getConsentValues().pixels.includes("google-analytics") ? "granted" : "denied"
			google_consent_settings.ad_storage        = wpm.getConsentValues().pixels.includes("google-ads") ? "granted" : "denied"
		}

		return google_consent_settings
	}

	wpm.updateGoogleConsentMode = function (analytics = true, ads = true) {

		try {
			if (
				!window.gtag ||
				!wpmDataLayer.shop.cookie_consent_mgmt.explicit_consent
			) return

			gtag("consent", "update", {
				analytics_storage: analytics ? "granted" : "denied",
				ad_storage       : ads ? "granted" : "denied",
			})
		} catch (e) {
			console.error(e)
		}
	}

	wpm.fireGtagGoogleAds = function () {
		try {
			wpmDataLayer.pixels.google.ads.state = "loading"

			if (wpmDataLayer?.pixels?.google?.ads?.enhanced_conversions?.active) {
				for (const [key, item] of Object.entries(wpmDataLayer.pixels.google.ads.conversionIds)) {
					gtag("config", key, {"allow_enhanced_conversions": true})
				}
			} else {
				for (const [key, item] of Object.entries(wpmDataLayer.pixels.google.ads.conversionIds)) {
					gtag("config", key)
				}
			}

			if (wpmDataLayer?.pixels?.google?.ads?.conversionIds && wpmDataLayer?.pixels?.google?.ads?.phone_conversion_label && wpmDataLayer?.pixels?.google?.ads?.phone_conversion_number) {
				gtag("config", Object.keys(wpmDataLayer.pixels.google.ads.conversionIds)[0] + "/" + wpmDataLayer.pixels.google.ads.phone_conversion_label, {
					phone_conversion_number: wpmDataLayer.pixels.google.ads.phone_conversion_number,
				})
			}

			// ! enhanced_conversion_data needs to set on the window object
			// https://support.google.com/google-ads/answer/9888145#zippy=%2Cvalidate-your-implementation-using-chrome-developer-tools
			if (wpmDataLayer?.shop?.page_type && "order_received_page" === wpmDataLayer.shop.page_type && wpmDataLayer?.order?.google?.ads?.enhanced_conversion_data) {
				// window.enhanced_conversion_data = wpmDataLayer.order.google.ads.enhanced_conversion_data

				gtag("set", "user_data", wpmDataLayer.order.google.ads.enhanced_conversion_data)
			}

			wpmDataLayer.pixels.google.ads.state = "ready"
		} catch (e) {
			console.error(e)
		}
	}

	wpm.fireGtagGoogleAnalyticsUA = function () {

		try {
			wpmDataLayer.pixels.google.analytics.universal.state = "loading"

			gtag("config", wpmDataLayer.pixels.google.analytics.universal.property_id, wpmDataLayer.pixels.google.analytics.universal.parameters)
			wpmDataLayer.pixels.google.analytics.universal.state = "ready"
		} catch (e) {
			console.error(e)
		}
	}

	wpm.fireGtagGoogleAnalyticsGA4 = function () {

		try {
			wpmDataLayer.pixels.google.analytics.ga4.state = "loading"

			let parameters = wpmDataLayer.pixels.google.analytics.ga4.parameters

			if (wpmDataLayer?.pixels?.google?.analytics?.ga4?.debug_mode) {
				parameters.debug_mode = true
			}

			gtag("config", wpmDataLayer.pixels.google.analytics.ga4.measurement_id, parameters)

			wpmDataLayer.pixels.google.analytics.ga4.state = "ready"
		} catch (e) {
			console.error(e)
		}
	}

	wpm.isGoogleActive = function () {

		if (
			wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id ||
			wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id ||
			!jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)
		) {
			return true
		} else {
			return false
		}
	}

	wpm.getGoogleGtagId = function () {

		if (wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) {
			return wpmDataLayer.pixels.google.analytics.universal.property_id
		} else if (wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) {
			return wpmDataLayer.pixels.google.analytics.ga4.measurement_id
		} else {
			return Object.keys(wpmDataLayer.pixels.google.ads.conversionIds)[0]
		}
	}

	wpm.loadGoogle = function () {

		if (wpm.isGoogleActive()) {

			wpmDataLayer.pixels.google.state = "loading"

			wpm.loadScriptAndCacheIt("https://www.googletagmanager.com/gtag/js?id=" + wpm.getGoogleGtagId())
				.done(function (script, textStatus) {

					try {

						// Initiate Google dataLayer and gtag
						window.dataLayer = window.dataLayer || []
						window.gtag      = function gtag() {
							dataLayer.push(arguments)
						}

						// Google Consent Mode
						if (wpmDataLayer?.pixels?.google?.consent_mode?.active) {

							let google_consent_settings = {
								"ad_storage"       : wpmDataLayer.pixels.google.consent_mode.ad_storage,
								"analytics_storage": wpmDataLayer.pixels.google.consent_mode.analytics_storage,
								"wait_for_update"  : wpmDataLayer.pixels.google.consent_mode.wait_for_update,
							}

							if (wpmDataLayer?.pixels?.google?.consent_mode?.region) {
								google_consent_settings.region = wpmDataLayer.pixels.google.consent_mode.region
							}
							google_consent_settings = wpm.getVisitorConsentStatusAndUpdateGoogleConsentSettings(google_consent_settings)

							gtag("consent", "default", google_consent_settings)
							gtag("set", "ads_data_redaction", wpmDataLayer.pixels.google.consent_mode.ads_data_redaction)
							gtag("set", "url_passthrough", wpmDataLayer.pixels.google.consent_mode.url_passthrough)
						}

						// Google Linker
						// https://developers.google.com/gtagjs/devguide/linker
						if (wpmDataLayer?.pixels?.google?.linker?.settings) {
							gtag("set", "linker", wpmDataLayer.pixels.google.linker.settings)
						}

						gtag("js", new Date())

						// Google Ads loader
						if (!jQuery.isEmptyObject(wpmDataLayer?.pixels?.google?.ads?.conversionIds)) {  // Only run if the pixel has set up

							if (wpm.googleConfigConditionsMet("ads")) {  							// Only run if cookie consent has been given
								wpm.fireGtagGoogleAds()
							} else {
								wpm.logPreventedPixelLoading("google-ads", "ads")
							}
						}


						// Google Universal Analytics loader
						if (wpmDataLayer?.pixels?.google?.analytics?.universal?.property_id) {  		// Only run if the pixel has set up

							if (wpm.googleConfigConditionsMet("analytics")) {						// Only run if cookie consent has been given
								wpm.fireGtagGoogleAnalyticsUA()
							} else {
								wpm.logPreventedPixelLoading("google-universal-analytics", "analytics")
							}
						}

						// GA4 loader
						if (wpmDataLayer?.pixels?.google?.analytics?.ga4?.measurement_id) {  			// Only run if the pixel has set up

							if (wpm.googleConfigConditionsMet("analytics")) {						// Only run if cookie consent has been given
								wpm.fireGtagGoogleAnalyticsGA4()
							} else {
								wpm.logPreventedPixelLoading("ga4", "analytics")
							}
						}

						wpmDataLayer.pixels.google.state = "ready"
					} catch (e) {
						console.error(e)
					}
				})
		}
	}

	wpm.canGoogleLoad = function () {

		if (wpmDataLayer?.pixels?.google?.consent_mode?.active) {
			return true
		} else if ("category" === wpm.getConsentValues().mode) {
			return !!(wpm.getConsentValues().categories["ads"] || wpm.getConsentValues().categories["analytics"])
		} else if ("pixel" === wpm.getConsentValues().mode) {
			return wpm.getConsentValues().pixels.includes("google-ads") || wpm.getConsentValues().pixels.includes("google-analytics")
		} else {
			console.error("Couldn't find a valid load condition for Google mode in wpmConsentValues")
			return false
		}
	}

	wpm.gtagLoaded = function () {
		return new Promise(function (resolve, reject) {

			if (typeof wpmDataLayer?.pixels?.google?.state === "undefined") reject()

			let startTime = 0
			let timeout   = 5000
			let frequency = 200;

			(function wait() {
				if (wpmDataLayer?.pixels?.google?.state === "ready") return resolve()
				if (startTime >= timeout) return reject()
				startTime += frequency
				setTimeout(wait, frequency)
			})()
		})
	}


}(window.wpm = window.wpm || {}, jQuery))
