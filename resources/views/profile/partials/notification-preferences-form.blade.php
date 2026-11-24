<section x-data="notificationPreferenceManager({{ Auth::user()->notif_enabled ? 'true' : 'false' }})" x-init="init()">
    <!-- Banner Mismatch 1: DB tercatat Aktif (ON), tetapi Browser memblokir ('denied') -->
    <div x-show="isBlocked" x-cloak 
         class="mb-4 p-4 bg-amber-50 dark:bg-amber-950/60 border-l-4 border-amber-500 text-amber-900 dark:text-amber-200 text-xs rounded-none shadow-xs">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="flex-1 space-y-1">
                <p class="font-bold text-sm text-amber-900 dark:text-amber-100">
                    Peringatan: Notifikasi Diblokir Browser
                </p>
                <p class="leading-relaxed">
                    Pemberitahuan Aktif di sistem, namun diblokir oleh browser. Silakan izinkan (Allow) notifikasi di pengaturan URL browser Anda agar fitur ini berfungsi.
                </p>
                <p class="text-[11px] text-amber-700 dark:text-amber-300">
                    Petunjuk: Klik ikon gembok / setelan situs di sebelah kiri bilah URL browser Anda &gt; ubah izin <strong>Notifikasi</strong> menjadi <strong>Izinkan (Allow)</strong>, lalu muat ulang halaman ini.
                </p>
            </div>
        </div>
    </div>

    <!-- Banner Mismatch 2: DB tercatat Aktif (ON), tetapi Browser masih 'default' (belum diizinkan) -->
    <div x-show="isPendingPermission" x-cloak 
         class="mb-4 p-4 bg-amber-50 dark:bg-amber-950/60 border-l-4 border-amber-500 text-amber-900 dark:text-amber-200 text-xs rounded-none shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="space-y-0.5">
                <p class="font-bold text-sm text-amber-900 dark:text-amber-100">
                    Perizinan Notifikasi Browser Belum Aktif
                </p>
                <p class="leading-relaxed">
                    Pemberitahuan Aktif di sistem, namun izin browser belum aktif. Silakan izinkan notifikasi browser untuk menerima pembaruan berkas cuti secara real-time.
                </p>
            </div>
        </div>
        <button type="button" 
                @click="requestPermission()" 
                :disabled="isLoading"
                class="shrink-0 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-none transition shadow-xs flex items-center justify-center gap-1.5">
            <span x-text="isLoading ? 'Memproses...' : 'Izinkan Notifikasi'"></span>
        </button>
    </div>

    <!-- Error Alert: Jika pengguna membatalkan atau browser menolak izin saat tombol ON ditekan -->
    <div x-show="errorMessage" x-cloak 
         class="mb-4 p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs rounded-none shadow-xs flex items-start justify-between gap-2">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
    </div>

    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">
                {{ __('Pengaturan Notifikasi Sistem') }}
            </h2>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                {{ __('Kelola preferensi penerimaan pemberitahuan berkas cuti dan status alert akun Anda.') }}
            </p>
        </div>

        <!-- Badges dengan Sinkronisasi Status Database & Izin Browser -->
        <div>
            <!-- Nonaktif murni di Database -->
            <template x-if="!dbEnabled">
                <span class="px-2.5 py-1 rounded-none text-xs font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    STATUS: NONAKTIF (OFF)
                </span>
            </template>

            <!-- Di Database ON, tapi Browser diblokir ('denied') -->
            <template x-if="dbEnabled && browserPermission === 'denied'">
                <span class="px-2.5 py-1 rounded-none text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                    STATUS: DIBLOKIR BROWSER
                </span>
            </template>

            <!-- Di Database ON, tapi Browser belum diberi izin ('default') -->
            <template x-if="dbEnabled && browserPermission === 'default'">
                <span class="px-2.5 py-1 rounded-none text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                    STATUS: BUTUH IZIN BROWSER
                </span>
            </template>

            <!-- Di Database ON dan Browser sudah allow ('granted') -->
            <template x-if="dbEnabled && browserPermission === 'granted'">
                <span class="px-2.5 py-1 rounded-none text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    STATUS: AKTIF (ON)
                </span>
            </template>

            <!-- Fallback Blade sebelum Alpine terinisialisasi -->
            @if(Auth::user()->notif_enabled)
                <span x-show="browserPermission === null" class="px-2.5 py-1 rounded-none text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                    STATUS: AKTIF (ON)
                </span>
            @else
                <span x-show="browserPermission === null" class="px-2.5 py-1 rounded-none text-xs font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    STATUS: NONAKTIF (OFF)
                </span>
            @endif
        </div>
    </header>

    <div class="mt-4 p-4 rounded-none border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="font-semibold text-xs sm:text-sm text-gray-800 dark:text-gray-200"
                x-text="dbEnabled ? (browserPermission === 'denied' ? 'Pemberitahuan Sistem Terhalang Browser' : 'Pemberitahuan Sistem Sedang Aktif') : 'Pemberitahuan Sistem Dinonaktifkan'">
                {{ Auth::user()->notif_enabled ? 'Pemberitahuan Sistem Sedang Aktif' : 'Pemberitahuan Sistem Dinonaktifkan' }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
               x-text="dbEnabled ? (browserPermission === 'denied' ? 'Sistem disetel aktif, namun izin peramban diblokir sehingga pop-up pemberitahuan tidak dapat muncul di layar.' : 'Anda menerima peringatan otomatis saat ada permohonan baru, verifikasi atasan, dan pengesahan cuti.') : 'Pemberitahuan saat ini dimatikan. Anda tidak akan menerima pop-up notifikasi.'">
                {{ Auth::user()->notif_enabled ? 'Anda menerima peringatan otomatis saat ada permohonan baru, verifikasi atasan, dan pengesahan cuti.' : 'Pemberitahuan saat ini dimatikan. Anda tidak akan menerima pop-up notifikasi.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('notifications.toggle') }}" class="shrink-0" @submit="handleFormSubmit($event)">
            @csrf
            <input type="hidden" name="enabled" :value="dbEnabled ? '0' : '1'" value="{{ Auth::user()->notif_enabled ? '0' : '1' }}">
            <button type="submit" 
                    :disabled="isLoading"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-none text-xs font-bold transition shadow-xs"
                    :class="dbEnabled ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900' : 'bg-emerald-600 text-white hover:bg-emerald-500'">
                <template x-if="dbEnabled">
                    <span>Matikan Notifikasi (OFF)</span>
                </template>
                <template x-if="!dbEnabled">
                    <span x-text="isLoading ? 'Memvalidasi Izin...' : 'Aktifkan Notifikasi (ON)'"></span>
                </template>
                <span x-show="false">{{ Auth::user()->notif_enabled ? __('Matikan Notifikasi (OFF)') : __('Aktifkan Notifikasi (ON)') }}</span>
            </button>
        </form>
    </div>

    <div class="mt-3 flex items-center justify-between text-xs">
        <a href="{{ route('notifications.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium inline-flex items-center gap-1">
            <span>Buka Riwayat Notifikasi</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>
</section>

@once
<script>
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
</script>
@endonce
