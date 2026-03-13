// Service Worker per Logistia Autista PWA
const CACHE_NAME = 'logistia-autista-v2';  // era v1, metti v2

// Risorse da mettere in cache subito
const STATIC_CACHE = [
    '/autista/dashboard',
    '/css/app.css',
    '/js/fleet-tracker.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

// Installazione - mette in cache le risorse statiche
self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static files');
                return cache.addAll(STATIC_CACHE);
            })
            .catch(err => {
                console.log('[SW] Cache failed:', err);
            })
    );
    // Attiva subito senza aspettare
    self.skipWaiting();
});

// Attivazione - pulisce vecchie cache
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        })
    );
    // Prende controllo di tutte le pagine subito
    self.clients.claim();
});

// Fetch - strategia "Network First, Cache Fallback"
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Per le API di tracking, sempre network (no cache)
    if (url.pathname.includes('/api/tracking') ||
        url.pathname.includes('/tracking/') ||
        event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        // Prima prova network
        fetch(event.request)
            .then(response => {
                // Se OK, salva in cache e ritorna
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Se network fallisce, prova cache
                return caches.match(event.request)
                    .then(cachedResponse => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Se non c'è in cache, mostra pagina offline
                        if (event.request.destination === 'document') {
                            return caches.match('/autista/dashboard');
                        }
                        return new Response('Offline', { status: 503 });
                    });
            })
    );
});

// Background Sync per le posizioni GPS (se supportato)
self.addEventListener('sync', event => {
    if (event.tag === 'sync-positions') {
        console.log('[SW] Syncing GPS positions...');
        event.waitUntil(syncPendingPositions());
    }
});

// Funzione per sincronizzare posizioni in coda
async function syncPendingPositions() {
    try {
        const db = await openIndexedDB();
        const positions = await getPendingPositions(db);

        for (const pos of positions) {
            try {
                await fetch('/api/tracking/posizione', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(pos.data)
                });
                await deletePosition(db, pos.id);
            } catch (e) {
                console.log('[SW] Failed to sync position:', e);
            }
        }
    } catch (e) {
        console.log('[SW] Sync error:', e);
    }
}

// Helper IndexedDB (per salvare posizioni offline)
function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('logistia-tracking', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending-positions')) {
                db.createObjectStore('pending-positions', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getPendingPositions(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pending-positions', 'readonly');
        const store = tx.objectStore('pending-positions');
        const request = store.getAll();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function deletePosition(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('pending-positions', 'readwrite');
        const store = tx.objectStore('pending-positions');
        const request = store.delete(id);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

console.log('[SW] Service Worker loaded!');