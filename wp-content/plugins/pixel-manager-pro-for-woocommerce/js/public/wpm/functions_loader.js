/**
 * Load all WPM functions
 *
 * Ignore event listeners. They need to be loaded after
 * we made sure that jQuery has been loaded.
 */

require("./functions")
require("./cookie_consent")

// #if process.env.TIER === 'premium'
require("./functions_premium")
// #endif
