importScripts("https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.11.0/firebase-messaging-compat.js");

firebase.initializeApp({
    apiKey: "AIzaSyD4ouDXqdVgAFI_NB6YOT8P5zv_pA86wsg",
    authDomain: "pesca-88b61.firebaseapp.com",
    projectId: "pesca-88b61",
    storageBucket: "pesca-88b61.firebasestorage.app",
    messagingSenderId: "375365901142",
    appId: "1:375365901142:web:9ef9c52e7bc4823cc254f7"
});

const messaging = firebase.messaging();

// Manejador para mensajes de FCM en segundo plano
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Mensaje FCM recibido en segundo plano:', payload);
    const notificationOptions = {
        body: payload.notification?.body || 'Cuerpo por defecto',
        icon: "/icon.png",
        data: payload.data || {}
    };
    console.log('[firebase-messaging-sw.js] Mostrando notificación:', notificationOptions);
    self.registration.showNotification(payload.notification?.title || 'Título por defecto', notificationOptions);
});

// Manejador genérico para eventos push (incluye mensajes de prueba de DevTools)
self.addEventListener('push', (event) => {
    console.log('[firebase-messaging-sw.js] Evento push recibido:', event);
    let data = {};
    try {
        data = event.data.json();
    } catch (e) {
        // Los mensajes de prueba de DevTools son texto plano
        data = { notification: { title: event.data.text(), body: 'Cuerpo por defecto' } };
    }
    const notificationOptions = {
        body: data.notification?.body || 'Cuerpo por defecto',
        icon: "./icono2.jpg",
        badge:"./icono2.jpg",
        data: data.data || {}
    };
    console.log('[firebase-messaging-sw.js] Mostrando notificación (push genérico):', notificationOptions);
    self.registration.showNotification(data.notification?.title || 'Título por defecto', notificationOptions);
});

self.addEventListener('notificationclick', (event) => {
    console.log('[firebase-messaging-sw.js] Notificación clicada:', event);
    event.notification.close();
    const url = event.notification.data?.click_action || 'http://localhost/proyectoPesca/';
    event.waitUntil(clients.openWindow(url));
});