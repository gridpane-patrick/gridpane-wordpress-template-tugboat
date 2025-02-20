importScripts('https://storage.googleapis.com/workbox-cdn/releases/3.4.1/workbox-sw.js');

workbox.skipWaiting();
workbox.clientsClaim();

// Provide an URL to enable a custom offline page
const OFFLINE_PAGE = "/offline.html";

//Pre-cache the AMP Runtime
self.addEventListener('install', event => {
    const urls = [
        'https://cdn.ampproject.org/v0.js',
        'https://cdn.ampproject.org/v0/amp-bind-0.1.js',
        'https://cdn.ampproject.org/v0/amp-sidebar-0.1.js',
        'https://cdn.ampproject.org/v0/amp-list-0.1.js',
        'https://cdn.ampproject.org/v0/amp-carousel-0.2.js',
        'https://cdn.ampproject.org/v0/amp-mustache-0.2.js',
        'https://cdn.ampproject.org/v0/amp-form-0.1.js',
        'https://cdn.ampproject.org/v0/amp-selector-0.1.js',
        'https://cdn.ampproject.org/v0/amp-lightbox-gallery-0.1.js',
        'https://cdn.ampproject.org/v0/amp-accordion-0.1.js',
        'https://cdn.ampproject.org/v0/amp-animation-0.1.js',
        'https://cdn.ampproject.org/v0/amp-position-observer-0.1.js',
        // Add AMP extensions used on your pages
        // Add fonts, icons, logos used on your pages
    ];
    if (OFFLINE_PAGE) {
        urls.push(OFFLINE_PAGE);
    }
    event.waitUntil(
        caches.open(workbox.core.cacheNames.runtime).then(cache => cache.addAll(urls))
    );
});

// Enable navigation preload . This is only necessary if navigation routes are not cached,
// see: https://developers.google.com/web/tools/workbox/modules/workbox-navigation-preload
workbox.navigationPreload.enable();

// Fallback to an offline page for navigation requests if there is no
// network connection
let navigationStrategy;
if (OFFLINE_PAGE) {
    const networkFirstWithOfflinePage = async (args) => {
        const response = await workbox.strategies.networkFirst().handle(args);
        if ( response) {
            return response;
        }
        return caches.match(OFFLINE_PAGE);
    }
    navigationStrategy = networkFirstWithOfflinePage;
} else {
    navigationStrategy = workbox.strategies.networkFirst();
}
const navigationRoute = new workbox.routing.NavigationRoute(navigationStrategy, {
    // Optionally, provide a white/blacklist of RegExps to determine
    // which paths will match this route.
    // whitelist: [],
    // blacklist: [],
});
workbox.routing.registerRoute(navigationRoute);

// By default Use a network first strategy to ensure the latest content is used
workbox.routing.setDefaultHandler(workbox.strategies.networkFirst());

// Serve the AMP Runtime from cache and check for an updated version in the background
workbox.routing.registerRoute(
    /https:\/\/cdn\.ampproject\.org\/.*/,
    workbox.strategies.staleWhileRevalidate()
);

// Cache Images
workbox.routing.registerRoute(
    /\.(?:png|gif|jpg|jpeg|svg)$/,
    workbox.strategies.cacheFirst({
        cacheName: 'images',
        plugins: [
            new workbox.expiration.Plugin({
                maxEntries: 60,
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
            }),
        ],
    }),
);

// Google Font Caching 
// see https://developers.google.com/web/tools/workbox/guides/common-recipes#google_fonts
workbox.routing.registerRoute(
    new RegExp('https://fonts.(?:googleapis|gstatic).com/(.*)'),
    workbox.strategies.cacheFirst({
        cacheName: 'googleapis',
        plugins: [
            new workbox.cacheableResponse.Plugin({
                statuses: [0, 200]
            }),
            new workbox.expiration.Plugin({
                maxEntries: 30,
            }),
        ],
    }),
);