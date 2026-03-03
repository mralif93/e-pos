const CACHE_NAME = 'epos-v3-stable';

// Core assets that must be available for the POS to function offline
const PRECACHE_ASSETS = [
    '/pos',
    '/pos/checkout',
    '/manifest.json',
    '/assets/css/app.css', // Note: If using Vite, these names change. 
    '/assets/js/app.js',   // We handle dynamic vite names in the fetch listener.
    '/assets/fonts/inter.css',
    '/assets/fonts/figtree.css',
    '/assets/js/alpine.min.js',
    '/assets/js/sweetalert2.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Pre-caching core POS assets');
            return cache.addAll(PRECACHE_ASSETS).catch(err => {
                console.warn('Some assets failed to pre-cache, they will be cached on first use.', err);
            });
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log('Deleting old cache:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

/**
 * Strategy: 
 * 1. Static Assets (CSS, JS, Fonts): Cache First or Stale-While-Revalidate
 * 2. Navigation (HTML): Network First (fallback to cache)
 * 3. API Calls: Network Only (or custom offline queue handling elsewhere)
 * 4. Images: Cache First
 */
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET and non-http requests
    if (event.request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // --- 1. API Calls: Network Only ---
    if (url.pathname.startsWith('/api/')) {
        return; // Let the browser handle normally
    }

    // --- 2. Navigation (HTML Pages): Network First ---
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // --- 3. Static Assets & Images: Cache First / Stale-While-Revalidate ---
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
                // If it's a static asset (css/js/font), we use Stale-While-Revalidate
                // to update the cache in background while serving fast from cache
                if (url.pathname.includes('/assets/') || url.pathname.includes('/build/')) {
                    fetch(event.request).then(networkResponse => {
                        if (networkResponse.ok) {
                            caches.open(CACHE_NAME).then(cache => cache.put(event.request, networkResponse));
                        }
                    });
                }
                return cachedResponse;
            }

            return fetch(event.request).then(networkResponse => {
                // Cache successful responses for future use
                if (networkResponse.ok || networkResponse.type === 'opaque') {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                }
                return networkResponse;
            });
        })
    );
});
