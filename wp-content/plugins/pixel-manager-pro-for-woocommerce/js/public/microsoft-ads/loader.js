/**
 * Microsoft Ads loader
 */

// #if process.env.TIER === 'premium'
require("./functions_premium")
require("./event_listeners_premium")
// #endif

