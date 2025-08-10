self.addEventListener('install', function(event) {
  self.skipWaiting();
});
 
self.addEventListener('fetch', function(event) {
  // Service worker básico, sem cache offline
}); 