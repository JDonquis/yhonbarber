/*
 * Service Worker — MR Yhon Barber Studio
 *
 * Estrategia:
 *  - Navegación: network-first. Nunca cachea HTML autenticado; si no hay red, muestra /offline.html.
 *  - Estáticos (build, iconos, logo, fuentes): cache-first con revalidación en segundo plano.
 *  - Ignora peticiones que no sean GET y las de otros orígenes (salvo fuentes).
 *
 * Para publicar cambios: incrementa CACHE_VERSION.
 */
const CACHE_VERSION = 'yhonbarber-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/logo.jpeg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

const FONT_HOSTS = [
    'fonts.bunny.net',
    'fonts.googleapis.com',
    'fonts.gstatic.com',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_VERSION)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key)))
            )
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/logo.jpeg' ||
        url.pathname === '/manifest.webmanifest' ||
        /\.(css|js|mjs|png|jpe?g|svg|webp|gif|ico|woff2?|ttf|eot)$/.test(url.pathname)
    );
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Fuentes externas: cache-first.
    if (url.origin !== self.location.origin) {
        if (FONT_HOSTS.includes(url.host)) {
            event.respondWith(
                caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                    return response;
                }).catch(() => cached))
            );
        }
        return;
    }

    // Navegación: network-first con fallback offline (sin cachear HTML autenticado).
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Estáticos: cache-first con revalidación.
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const network = fetch(request)
                    .then((response) => {
                        if (response && response.status === 200 && response.type === 'basic') {
                            const copy = response.clone();
                            caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    })
                    .catch(() => cached);

                return cached || network;
            })
        );
    }
});
