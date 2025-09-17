/**
 * TechEssentials Pro - Service Worker
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 * 
 * Progressive Web App Service Worker avec gestion du cache intelligent
 */

const CACHE_NAME = 'techessentials-pro-v2.0.0';
const STATIC_CACHE_NAME = 'techessentials-static-v2.0.0';
const DYNAMIC_CACHE_NAME = 'techessentials-dynamic-v2.0.0';
const API_CACHE_NAME = 'techessentials-api-v2.0.0';

// Ressources à mettre en cache immédiatement
const STATIC_ASSETS = [
    '/',
    '/assets/css/main.css',
    '/assets/js/main.js',
    '/assets/images/logo.svg',
    '/assets/images/hero-workspace.webp',
    '/assets/fonts/inter-var.woff2',
    '/manifest.json',
    '/offline.html'
];

// Ressources critiques à précharger
const CRITICAL_PAGES = [
    '/',
    '/reviews',
    '/blog',
    '/contact'
];

// API endpoints à mettre en cache
const API_ENDPOINTS = [
    '/api/reviews',
    '/api/blog',
    '/api/newsletter/stats'
];

// Durées de cache par type de ressource
const CACHE_STRATEGIES = {
    static: 30 * 24 * 60 * 60 * 1000, // 30 jours
    dynamic: 7 * 24 * 60 * 60 * 1000, // 7 jours
    api: 1 * 60 * 60 * 1000, // 1 heure
    images: 7 * 24 * 60 * 60 * 1000 // 7 jours
};

// ==========================================
// INSTALLATION DU SERVICE WORKER
// ==========================================

self.addEventListener('install', event => {
    console.log('[SW] Installing Service Worker v2.0.0');
    
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                // Précharger les pages critiques
                return caches.open(DYNAMIC_CACHE_NAME)
                    .then(cache => {
                        console.log('[SW] Preloading critical pages');
                        return Promise.allSettled(
                            CRITICAL_PAGES.map(url => 
                                fetch(url)
                                    .then(response => {
                                        if (response.ok) {
                                            return cache.put(url, response.clone());
                                        }
                                    })
                                    .catch(err => console.log(`[SW] Failed to preload ${url}:`, err))
                            )
                        );
                    });
            })
            .then(() => {
                console.log('[SW] Installation completed');
                // Force l'activation immédiate
                return self.skipWaiting();
            })
    );
});

// ==========================================
// ACTIVATION DU SERVICE WORKER
// ==========================================

self.addEventListener('activate', event => {
    console.log('[SW] Activating Service Worker v2.0.0');
    
    event.waitUntil(
        // Nettoyer les anciens caches
        caches.keys().then(cacheNames => {
            const deletePromises = cacheNames
                .filter(cacheName => 
                    cacheName.startsWith('techessentials-') &&
                    cacheName !== STATIC_CACHE_NAME &&
                    cacheName !== DYNAMIC_CACHE_NAME &&
                    cacheName !== API_CACHE_NAME
                )
                .map(cacheName => {
                    console.log('[SW] Deleting old cache:', cacheName);
                    return caches.delete(cacheName);
                });
            
            return Promise.all(deletePromises);
        })
        .then(() => {
            console.log('[SW] Activation completed');
            // Prendre le contrôle immédiatement
            return self.clients.claim();
        })
    );
});

// ==========================================
// INTERCEPTION DES REQUÊTES
// ==========================================

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Ignorer les requêtes non-HTTP
    if (!request.url.startsWith('http')) {
        return;
    }
    
    // Ignorer les requêtes vers d'autres domaines (sauf pour les CDN autorisés)
    if (url.origin !== location.origin && 
        !url.hostname.includes('cdnjs.cloudflare.com') &&
        !url.hostname.includes('fonts.googleapis.com') &&
        !url.hostname.includes('fonts.gstatic.com')) {
        return;
    }
    
    // Stratégie de cache selon le type de requête
    if (request.method === 'GET') {
        if (isStaticAsset(url.pathname)) {
            event.respondWith(handleStaticAssets(request));
        } else if (isAPIRequest(url.pathname)) {
            event.respondWith(handleAPIRequests(request));
        } else if (isImageRequest(url.pathname)) {
            event.respondWith(handleImageRequests(request));
        } else {
            event.respondWith(handlePageRequests(request));
        }
    } else if (request.method === 'POST') {
        // Gérer les requêtes POST (formulaires, API)
        event.respondWith(handlePostRequests(request));
    }
});

// ==========================================
// GESTION DES DIFFÉRENTS TYPES DE REQUÊTES
// ==========================================

/**
 * Cache-First Strategy pour les assets statiques
 */
async function handleStaticAssets(request) {
    try {
        const cache = await caches.open(STATIC_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse && !isExpired(cachedResponse, CACHE_STRATEGIES.static)) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            await cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
        
    } catch (error) {
        console.log('[SW] Static asset failed:', error);
        const cache = await caches.open(STATIC_CACHE_NAME);
        return await cache.match(request) || createErrorResponse();
    }
}

/**
 * Stale-While-Revalidate Strategy pour les pages
 */
async function handlePageRequests(request) {
    try {
        const cache = await caches.open(DYNAMIC_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        // Récupérer depuis le réseau en arrière-plan
        const networkPromise = fetch(request)
            .then(response => {
                if (response.ok) {
                    cache.put(request, response.clone());
                }
                return response;
            })
            .catch(() => null);
        
        // Retourner le cache s'il existe et n'est pas expiré
        if (cachedResponse && !isExpired(cachedResponse, CACHE_STRATEGIES.dynamic)) {
            networkPromise.catch(() => {}); // Silencieux pour le background update
            return cachedResponse;
        }
        
        // Sinon attendre la réponse réseau
        const networkResponse = await networkPromise;
        
        if (networkResponse) {
            return networkResponse;
        }
        
        // Fallback vers le cache même expiré
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Dernière option : page offline
        return await caches.match('/offline.html') || createErrorResponse();
        
    } catch (error) {
        console.log('[SW] Page request failed:', error);
        
        // Essayer de récupérer depuis le cache
        const cache = await caches.open(DYNAMIC_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Page offline ou erreur
        return await caches.match('/offline.html') || createErrorResponse();
    }
}

/**
 * Network-First Strategy pour les requêtes API avec fallback cache
 */
async function handleAPIRequests(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(API_CACHE_NAME);
            await cache.put(request, networkResponse.clone());
            return networkResponse;
        }
        
        throw new Error('Network response not ok');
        
    } catch (error) {
        console.log('[SW] API request failed, trying cache:', error);
        
        const cache = await caches.open(API_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse && !isExpired(cachedResponse, CACHE_STRATEGIES.api)) {
            return cachedResponse;
        }
        
        return createAPIErrorResponse();
    }
}

/**
 * Cache-First Strategy pour les images
 */
async function handleImageRequests(request) {
    try {
        const cache = await caches.open(DYNAMIC_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse && !isExpired(cachedResponse, CACHE_STRATEGIES.images)) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            await cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
        
    } catch (error) {
        console.log('[SW] Image request failed:', error);
        
        const cache = await caches.open(DYNAMIC_CACHE_NAME);
        const cachedResponse = await cache.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Retourner une image placeholder
        return createPlaceholderImage();
    }
}

/**
 * Gérer les requêtes POST (formulaires, API)
 */
async function handlePostRequests(request) {
    try {
        return await fetch(request);
    } catch (error) {
        console.log('[SW] POST request failed:', error);
        
        // Pour les requêtes POST, on peut implémenter un système de queue
        // pour les rejouer quand la connexion est rétablie
        if (isFormSubmission(request)) {
            await queueFormSubmission(request);
            return new Response(
                JSON.stringify({ 
                    success: false, 
                    error: 'Form submitted offline, will be processed when online',
                    queued: true
                }),
                {
                    status: 202,
                    headers: { 'Content-Type': 'application/json' }
                }
            );
        }
        
        return createAPIErrorResponse();
    }
}

// ==========================================
// FONCTIONS UTILITAIRES
// ==========================================

function isStaticAsset(pathname) {
    return pathname.match(/\.(css|js|woff2?|eot|ttf|otf|svg|ico)$/);
}

function isImageRequest(pathname) {
    return pathname.match(/\.(png|jpg|jpeg|gif|webp|svg)$/);
}

function isAPIRequest(pathname) {
    return pathname.startsWith('/api/') || API_ENDPOINTS.some(endpoint => pathname.startsWith(endpoint));
}

function isFormSubmission(request) {
    return request.method === 'POST' && 
           (request.headers.get('Content-Type')?.includes('form-data') ||
            request.headers.get('Content-Type')?.includes('form-urlencoded'));
}

function isExpired(response, maxAge) {
    const dateHeader = response.headers.get('date');
    if (!dateHeader) return false;
    
    const date = new Date(dateHeader);
    return (Date.now() - date.getTime()) > maxAge;
}

function createErrorResponse() {
    return new Response(
        '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>You are offline</h1><p>Please check your internet connection.</p></body></html>',
        {
            status: 503,
            headers: { 'Content-Type': 'text/html' }
        }
    );
}

function createAPIErrorResponse() {
    return new Response(
        JSON.stringify({ 
            success: false, 
            error: 'Network unavailable', 
            offline: true 
        }),
        {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        }
    );
}

function createPlaceholderImage() {
    // SVG placeholder simple
    const svg = `<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
        <rect width="300" height="200" fill="#f3f4f6"/>
        <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af">Image unavailable</text>
    </svg>`;
    
    return new Response(svg, {
        headers: { 
            'Content-Type': 'image/svg+xml',
            'Cache-Control': 'no-cache'
        }
    });
}

// ==========================================
// SYSTÈME DE QUEUE POUR LES REQUÊTES OFFLINE
// ==========================================

async function queueFormSubmission(request) {
    try {
        const formData = await request.clone().formData();
        const queueData = {
            url: request.url,
            method: request.method,
            headers: Object.fromEntries(request.headers.entries()),
            body: Object.fromEntries(formData.entries()),
            timestamp: Date.now()
        };
        
        // Stocker dans IndexedDB ou localStorage
        const queue = JSON.parse(localStorage.getItem('sw-queue') || '[]');
        queue.push(queueData);
        localStorage.setItem('sw-queue', JSON.stringify(queue));
        
        console.log('[SW] Form submission queued for later');
        
    } catch (error) {
        console.error('[SW] Failed to queue form submission:', error);
    }
}

// Traiter la queue quand la connexion est rétablie
self.addEventListener('online', event => {
    event.waitUntil(processQueue());
});

async function processQueue() {
    try {
        const queue = JSON.parse(localStorage.getItem('sw-queue') || '[]');
        if (queue.length === 0) return;
        
        console.log('[SW] Processing queued requests:', queue.length);
        
        const processedQueue = [];
        
        for (const item of queue) {
            try {
                const formData = new FormData();
                for (const [key, value] of Object.entries(item.body)) {
                    formData.append(key, value);
                }
                
                const response = await fetch(item.url, {
                    method: item.method,
                    body: formData
                });
                
                if (response.ok) {
                    console.log('[SW] Successfully processed queued request to', item.url);
                } else {
                    processedQueue.push(item); // Garder en cas d'échec
                }
                
            } catch (error) {
                console.error('[SW] Failed to process queued request:', error);
                processedQueue.push(item); // Garder pour réessayer
            }
        }
        
        localStorage.setItem('sw-queue', JSON.stringify(processedQueue));
        
    } catch (error) {
        console.error('[SW] Failed to process queue:', error);
    }
}

// ==========================================
// NETTOYAGE PÉRIODIQUE DU CACHE
// ==========================================

self.addEventListener('message', event => {
    if (event.data && event.data.type === 'CACHE_CLEANUP') {
        event.waitUntil(cleanupCache());
    } else if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

async function cleanupCache() {
    const cacheNames = [DYNAMIC_CACHE_NAME, API_CACHE_NAME];
    
    for (const cacheName of cacheNames) {
        try {
            const cache = await caches.open(cacheName);
            const requests = await cache.keys();
            
            for (const request of requests) {
                const response = await cache.match(request);
                if (response && isExpired(response, CACHE_STRATEGIES.dynamic)) {
                    await cache.delete(request);
                }
            }
            
        } catch (error) {
            console.error('[SW] Cache cleanup failed:', error);
        }
    }
    
    console.log('[SW] Cache cleanup completed');
}

// ==========================================
// GESTION DES NOTIFICATIONS PUSH
// ==========================================

self.addEventListener('push', event => {
    if (!event.data) return;
    
    const options = {
        body: event.data.text(),
        icon: '/assets/images/icons/icon-192x192.png',
        badge: '/assets/images/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        tag: 'techessentials-notification',
        renotify: true,
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: 'View',
                icon: '/assets/images/icons/action-view.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/assets/images/icons/action-dismiss.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('TechEssentials Pro', options)
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// ==========================================
// SYNCHRONISATION EN ARRIÈRE-PLAN
// ==========================================

self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(processQueue());
    }
});

// ==========================================
// GESTION DES ERREURS
// ==========================================

self.addEventListener('error', event => {
    console.error('[SW] Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', event => {
    console.error('[SW] Unhandled promise rejection:', event.reason);
});

console.log('[SW] Service Worker v2.0.0 loaded successfully');