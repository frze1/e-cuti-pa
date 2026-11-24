// Service Worker for e-Cuti Pengadilan Agama Sumber Web Push Notifications

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(clients.claim());
});

// PWA Installability Fetch Handler (Syarat Chrome PWA Installation)
self.addEventListener('fetch', function (event) {
    // Listener fetch untuk memenuhi kriteria instalasi PWA Chrome
});

self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    let payload;
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'Notifikasi e-Cuti',
            body: event.data.text(),
            data: { url: '/dashboard' }
        };
    }

    const title = payload.title || 'e-Cuti PA Sumber';

    // Normalize icon path to prevent broken/unreachable localhost URL on mobile
    let iconUrl = payload.icon || '/favicon.ico';
    if (iconUrl.includes('127.0.0.1') || iconUrl.includes('localhost')) {
        iconUrl = '/favicon.ico';
    }

    const options = {
        body: payload.body || 'Terdapat pembaruan data pengajuan cuti aparatur.',
        icon: iconUrl,
        badge: iconUrl,
        data: payload.data || { url: '/dashboard' },
        tag: payload.tag || 'cuti-notification',
        renotify: true,
        requireInteraction: true,
        vibrate: [200, 100, 200]
    };

    if (payload.actions && Array.isArray(payload.actions)) {
        options.actions = payload.actions;
    }

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    return client.navigate(targetUrl).then(function (c) {
                        return c.focus();
                    });
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
