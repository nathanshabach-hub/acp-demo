/**
 * ACP Portal – Enhanced Service Worker
 * - Caches static assets and visited pages
 * - Serves cached pages when offline
 * - Triggers sync queue processing via Background Sync
 */

var STATIC_CACHE = 'acp-static-v2';
var PAGES_CACHE  = 'acp-pages-v1';

var STATIC_ASSETS = [
    '/',
    '/css/front/bootstrap.min.css',
    '/css/front/style_front.css',
    '/css/front/responsive.css',
    '/css/front/forms.css',
    '/js/front/jquery.min.js',
    '/js/front/validate.js',
    '/js/offline/db.js',
    '/js/offline/sync.js',
    '/js/offline/ui.js',
    '/img/front/main-logo.png',
    '/img/favicon.ico',
    '/img/pwa/icon-192.png',
    '/manifest.json'
];

// Pages to aggressively cache when visited
var PAGE_PATTERNS = [
    /^\/users\//,
    /^\/conventionregistrations\//,
    /^\/eventsubmissions\//,
    /^\/groups\//
];

// ---- Install: cache static assets ----
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function(cache) {
            return cache.addAll(STATIC_ASSETS).catch(function() {
                return Promise.resolve();
            });
        })
    );
    self.skipWaiting();
});

// ---- Activate: clean old caches ----
self.addEventListener('activate', function(event) {
    var keep = [STATIC_CACHE, PAGES_CACHE];
    event.waitUntil(
        caches.keys().then(function(names) {
            return Promise.all(
                names.filter(function(n) { return keep.indexOf(n) === -1; })
                     .map(function(n) { return caches.delete(n); })
            );
        }).then(function() { return self.clients.claim(); })
    );
});

// ---- Fetch: network-first for pages, cache-first for static ----
self.addEventListener('fetch', function(event) {
    var request = event.request;
    var url = new URL(request.url);

    // Only handle same-origin
    if (url.origin !== self.location.origin) return;

    // Skip non-GET (POST interception is handled by the page JS)
    if (request.method !== 'GET') return;

    // Static assets: cache-first
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(request).then(function(cached) {
                return cached || fetch(request).then(function(response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(STATIC_CACHE).then(function(c) { c.put(request, clone); });
                    }
                    return response;
                });
            }).catch(function() {
                return caches.match(request);
            })
        );
        return;
    }

    // Pages: network-first, cache for offline
    if (isPageRequest(request, url)) {
        event.respondWith(
            fetch(request).then(function(response) {
                if (response.ok && !url.pathname.match(/\/login/)) {
                    var clone = response.clone();
                    caches.open(PAGES_CACHE).then(function(c) { c.put(request, clone); });
                }
                return response;
            }).catch(function() {
                return caches.match(request).then(function(cached) {
                    if (cached) return cached;
                    return new Response(
                        '<html><body style="font-family:Arial;text-align:center;padding:60px;">' +
                        '<h2>You are offline</h2>' +
                        '<p>This page has not been cached yet. Please reconnect to the internet.</p>' +
                        '<p>Any data you submitted offline will sync automatically when you reconnect.</p>' +
                        '</body></html>',
                        { headers: { 'Content-Type': 'text/html' } }
                    );
                });
            })
        );
        return;
    }

    // Everything else: network-first with cache fallback
    event.respondWith(
        fetch(request).then(function(response) {
            if (response.ok) {
                var clone = response.clone();
                caches.open(PAGES_CACHE).then(function(c) { c.put(request, clone); });
            }
            return response;
        }).catch(function() {
            return caches.match(request);
        })
    );
});

// ---- Background Sync ----
self.addEventListener('sync', function(event) {
    if (event.tag === 'acp-sync-queue') {
        event.waitUntil(
            self.clients.matchAll().then(function(clients) {
                clients.forEach(function(client) {
                    client.postMessage({ type: 'PROCESS_SYNC_QUEUE' });
                });
            })
        );
    }
});

// ---- Messages from the page ----
self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'QUEUE_SYNC') {
        if (self.registration.sync) {
            self.registration.sync.register('acp-sync-queue');
        }
    }
});

// ---- Helpers ----
function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)(\?.*)?$/.test(pathname);
}

function isPageRequest(request, url) {
    if (request.headers.get('Accept') && request.headers.get('Accept').indexOf('text/html') !== -1) {
        return true;
    }
    for (var i = 0; i < PAGE_PATTERNS.length; i++) {
        if (PAGE_PATTERNS[i].test(url.pathname)) return true;
    }
    return false;
}
