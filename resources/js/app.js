import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.notificationPreferenceManager = function(initialDbEnabled) {
    return {
        dbEnabled: initialDbEnabled,
        browserPermission: ('Notification' in window) ? Notification.permission : 'unsupported',
        isLoading: false,
        errorMessage: '',

        init() {
            if ('Notification' in window) {
                this.browserPermission = Notification.permission;
            } else {
                this.browserPermission = 'unsupported';
            }
        },

        get isBlocked() {
            return this.dbEnabled && this.browserPermission === 'denied';
        },

        get isPendingPermission() {
            return this.dbEnabled && this.browserPermission === 'default';
        },

        async requestPermission() {
            if (!('Notification' in window)) {
                this.errorMessage = 'Peramban (browser) Anda tidak mendukung Web Notification API.';
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';

            try {
                const perm = await Notification.requestPermission();
                this.browserPermission = perm;

                if (perm === 'granted') {
                    await this.tryRegisterWebPush();
                    if (typeof window.showToast === 'function') {
                        window.showToast({ message: 'Izin notifikasi browser berhasil diberikan!', type: 'success' });
                    }
                } else if (perm === 'denied') {
                    this.errorMessage = 'Notifikasi diblokir oleh browser. Silakan izinkan (Allow) notifikasi di pengaturan URL browser Anda agar fitur ini berfungsi.';
                }
            } catch (err) {
                console.error('Request permission error:', err);
                this.errorMessage = 'Gagal meminta izin notifikasi browser.';
            } finally {
                this.isLoading = false;
            }
        },

        async handleFormSubmit(event) {
            // Jika status saat ini adalah MATI (dbEnabled == false), maka target aksi adalah MENGAKTIFKAN (ON)
            if (!this.dbEnabled) {
                if (!('Notification' in window)) {
                    event.preventDefault();
                    this.errorMessage = 'Browser ini tidak mendukung fitur Web Notification.';
                    return;
                }

                // Cegat form submit jika izin belum granted
                if (this.browserPermission !== 'granted') {
                    event.preventDefault();
                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        const perm = await Notification.requestPermission();
                        this.browserPermission = perm;

                        if (perm === 'granted') {
                            // Izin diberikan oleh pengguna di popup browser!
                            await this.tryRegisterWebPush();
                            // Lanjutkan submit form ke database
                            event.target.submit();
                        } else {
                            // Izin ditolak ('denied') atau ditutup ('default')
                            this.errorMessage = 'Gagal mengaktifkan. Anda harus memberikan izin notifikasi di popup browser terlebih dahulu.';
                            if (typeof window.showToast === 'function') {
                                window.showToast({ 
                                    message: 'Gagal mengaktifkan. Anda harus memberikan izin notifikasi di popup browser terlebih dahulu.', 
                                    type: 'error' 
                                });
                            }
                        }
                    } catch (err) {
                        console.error('Permission request failed:', err);
                        this.errorMessage = 'Gagal meminta izin notifikasi dari browser.';
                    } finally {
                        this.isLoading = false;
                    }
                    return;
                }
            }

            // Jika mematikan notifikasi (dbEnabled == true), biarkan form submit secara natural
        },

        async tryRegisterWebPush() {
            if ('serviceWorker' in navigator && window.axios) {
                try {
                    const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                    await navigator.serviceWorker.ready;
                    const keyRes = await axios.get('/push/public-key');
                    const publicKey = keyRes.data?.publicKey;
                    if (publicKey && reg.pushManager) {
                        const urlBase64ToUint8Array = (base64String) => {
                            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                            const rawData = window.atob(base64);
                            const outputArray = new Uint8Array(rawData.length);
                            for (let i = 0; i < rawData.length; ++i) {
                                outputArray[i] = rawData.charCodeAt(i);
                            }
                            return outputArray;
                        };
                        const sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(publicKey)
                        });
                        const subJson = sub.toJSON();
                        await axios.post('/push/subscribe', {
                            endpoint: sub.endpoint,
                            keys: {
                                p256dh: subJson.keys?.p256dh,
                                auth: subJson.keys?.auth
                            },
                            content_encoding: (window.PushManager && PushManager.supportedContentEncodings) 
                                ? PushManager.supportedContentEncodings[0] 
                                : 'aes128gcm'
                        });
                    }
                } catch (e) {
                    console.warn('Auto register push note:', e);
                }
            }
        }
    };
};

Alpine.start();
