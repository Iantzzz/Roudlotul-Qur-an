/**
 * Service worker situs Roudlotul Qur'an.
 *
 * Strategi "network-first" untuk semua permintaan GET satu asal (same-origin):
 * jaringan selalu dicoba lebih dulu agar konten yang ditampilkan terbaru;
 * cache runtime hanya menjadi cadangan saat perangkat offline atau jaringan
 * gagal. Permintaan lintas asal (mis. Google Fonts) dan metode selain GET
 * diteruskan apa adanya supaya perilaku normal tidak berubah.
 */
const RUNTIME_CACHE = 'rq-runtime-v1';

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();

        await Promise.all(
            keys
                .filter((key) => key !== RUNTIME_CACHE)
                .map((key) => caches.delete(key))
        );
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    event.respondWith((async () => {
        try {
            const response = await fetch(request);

            if (response && response.ok) {
                const copy = response.clone();

                event.waitUntil(caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy)));
            }

            return response;
        } catch (error) {
            const cached = await caches.match(request);

            if (cached) {
                return cached;
            }

            throw error;
        }
    })());
});
