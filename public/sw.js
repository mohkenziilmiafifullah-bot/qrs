const CACHE_NAME = 'qr-sales-static-v1';
const PRECACHE = ['/manifest.json'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

// Cache-first for built static assets (JS/CSS/fonts), network-first for everything else
// (page data changes often — wallet balance, QR status — so we never serve those stale).
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    const isStaticAsset = url.pathname.startsWith('/build/');

    if (!isStaticAsset) return;

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;
            return fetch(event.request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                return response;
            });
        })
    );
});
