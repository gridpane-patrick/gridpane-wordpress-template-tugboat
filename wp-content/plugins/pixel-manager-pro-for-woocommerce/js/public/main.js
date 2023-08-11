/**
 *  Load all essential scripts first
 */

require("./wpm/functions_loader")

// Only load the event listeners after jQuery has been loaded for sure
wpm.jQueryExists().then(function () {

	require("./wpm/event_listeners")

	require("./google/loader")
	require("./facebook/loader")
	require("./hotjar/loader")

	/**
	 *  Load all premium scripts
	 */

	// #if process.env.TIER === 'premium'
	require("./microsoft-ads/loader")
	require("./pinterest/loader")
	require("./snapchat/loader")
	require("./tiktok/loader")
	require("./twitter/loader")
	// #endif


	/**
	 * Initiate WPM.
	 *
	 * It makes sure that the script flow gets executed correctly,
	 * no matter how JS "optimizers" shuffle the code.
	 */

	require("./wpm/init")
})

