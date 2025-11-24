// Service Worker para PWA
const CACHE_NAME = "lumisapp-v1";
const urlsToCache = [
  "/public/index.html",
  "/public/login.html",
  "/public/cadastro.html",
  "/public/css/styles.css",
  "/public/css/login.css",
  "/public/css/cadastro.css",
  "/public/js/app.js",
  "/public/js/login.js",
  "/public/js/cadastro.js",
  "/public/icons/icon-192.png",
  "/public/icons/icon-512.png",
];

// Instalação - cacheia arquivos
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("Cache aberto");
      return cache.addAll(urlsToCache);
    })
  );
});

// Ativação - limpa caches antigos
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log("Removendo cache antigo:", cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch - serve do cache quando offline
self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      // Cache hit - retorna resposta do cache
      if (response) {
        return response;
      }

      // Clona a requisição
      const fetchRequest = event.request.clone();

      return fetch(fetchRequest).then((response) => {
        // Verifica se é uma resposta válida
        if (!response || response.status !== 200 || response.type !== "basic") {
          return response;
        }

        // Clona a resposta
        const responseToCache = response.clone();

        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });

        return response;
      });
    })
  );
});
