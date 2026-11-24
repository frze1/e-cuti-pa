<div x-data="notificationCenter()" x-init="init()" class="relative">
    <!-- Bell Trigger Button -->
    <button type="button" 
        @click="toggleDropdown()" 
        class="relative p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 focus:outline-none transition group" 
        :title="notifEnabled ? 'Notifikasi Aktif (' + unreadCount + ' belum dibaca)' : 'Notifikasi Dinonaktifkan'"
        aria-label="Pusat Notifikasi">
        
        <!-- Bell Icon -->
        <svg class="w-5 h-5 transition-transform group-hover:scale-105" 
            :class="unreadCount > 0 ? 'text-dinas-green dark:text-emerald-400 animate-bounce' : (notifEnabled ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500 opacity-60')" 
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>

        <!-- Unread Badge Count -->
        <template x-if="unreadCount > 0 && notifEnabled">
            <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold shadow-xs">
                <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
            </span>
        </template>

        <!-- Muted Indicator (When Notification is OFF) -->
        <template x-if="!notifEnabled">
            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-slate-400 dark:bg-slate-500 border-2 border-white dark:border-slate-800" title="Notifikasi OFF"></span>
        </template>
    </button>

    <!-- Dropdown Popover Panel -->
    <div x-show="dropdownOpen" 
        @click.outside="dropdownOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed sm:absolute right-2 sm:right-0 top-14 sm:top-auto sm:mt-2 w-[calc(100vw-1rem)] sm:w-96 max-w-sm sm:max-w-none bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200/90 dark:border-slate-700 z-50 overflow-hidden"
        style="display: none;">

        <!-- Header Panel with Title & Global ON/OFF Switch -->
        <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-slate-50/90 dark:bg-slate-900/90 border-b border-gray-100 dark:border-slate-700/80 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-dinas-green/10 dark:bg-emerald-950/80 text-dinas-green dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                        <span>Notifikasi</span>
                        <template x-if="unreadCount > 0">
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-dinas-gold/20 text-amber-800 dark:bg-amber-950 dark:text-amber-300" x-text="unreadCount + ' Baru'"></span>
                        </template>
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Pemberitahuan sistem cuti</p>
                </div>
            </div>

            <!-- Global Setting ON / OFF Toggle -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                    @click="toggleNotifSetting()" 
                    :disabled="settingLoading"
                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold transition border"
                    :class="notifEnabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600'"
                    :title="notifEnabled ? 'Klik untuk mematikan notifikasi' : 'Klik untuk mengaktifkan notifikasi'">
                    <span class="w-2 h-2 rounded-full" :class="notifEnabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                    <span x-text="notifEnabled ? 'ON' : 'OFF'"></span>
                </button>

                <button type="button" @click="dropdownOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Toast Feedback -->
        <div x-show="toastMessage" 
            x-transition 
            class="px-4 py-2 text-xs text-center border-b font-medium"
            :class="toastType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' : 'bg-rose-50 text-rose-800 border-rose-100 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900'">
            <span x-text="toastMessage"></span>
        </div>

        <!-- Notice When Notification is OFF -->
        <template x-if="!notifEnabled">
            <div class="px-4 py-2 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200 dark:border-amber-900/60 text-[11px] text-amber-800 dark:text-amber-300 flex items-center justify-between">
                <span>Notifikasi sedang dinonaktifkan (OFF).</span>
                <button type="button" @click="toggleNotifSetting()" class="font-bold underline hover:text-amber-900 dark:hover:text-amber-100">
                    Aktifkan Kembali
                </button>
            </div>
        </template>

        <!-- Subheader: Filter & Actions -->
        <div class="px-4 py-2 bg-slate-50/50 dark:bg-slate-900/40 border-b border-gray-100 dark:border-slate-700/60 flex items-center justify-between text-xs">
            <div class="flex items-center gap-1">
                <button type="button" 
                    @click="activeFilter = 'all'" 
                    class="px-2 py-1 rounded-lg text-[11px] font-medium transition"
                    :class="activeFilter === 'all' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                    Semua (<span x-text="notifications.length"></span>)
                </button>
                <button type="button" 
                    @click="activeFilter = 'unread'" 
                    class="px-2 py-1 rounded-lg text-[11px] font-medium transition"
                    :class="activeFilter === 'unread' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'">
                    Belum Dibaca (<span x-text="unreadCount"></span>)
                </button>
            </div>

            <template x-if="unreadCount > 0">
                <button type="button" 
                    @click="markAllAsRead()" 
                    class="text-[11px] text-dinas-green dark:text-emerald-400 hover:text-dinas-greenhover hover:underline font-medium">
                    Tandai Semua Dibaca
                </button>
            </template>
        </div>

        <!-- Notification History List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-700/50">
            <!-- Loading State -->
            <div x-show="fetchLoading" class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">
                <svg class="animate-spin w-5 h-5 mx-auto mb-2 text-dinas-green dark:text-emerald-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Memuat riwayat notifikasi...</span>
            </div>

            <!-- List Items -->
            <template x-for="item in filteredNotifications" :key="item.id">
                <div @click="openNotification(item)" 
                    class="p-3.5 sm:p-4 flex items-start gap-3 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-700/50 group"
                    :class="!item.is_read ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : ''">
                    
                    <!-- Icon Type -->
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                        :class="{
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400': item.type === 'success',
                            'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300': item.type === 'warning',
                            'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400': item.type === 'danger',
                            'bg-dinas-green/10 text-dinas-green dark:bg-emerald-950 dark:text-emerald-400': item.type === 'info' || !item.type
                        }">
                        <template x-if="item.type === 'success'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="item.type === 'warning'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </template>
                        <template x-if="item.type === 'danger'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </template>
                        <template x-if="item.type === 'info' || !item.type">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </template>
                    </div>

                    <!-- Message text -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white truncate" :class="!item.is_read ? 'text-dinas-green dark:text-emerald-300' : ''" x-text="item.title"></h4>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 whitespace-nowrap" x-text="item.created_at_human"></span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-snug line-clamp-2" x-text="item.body"></p>
                    </div>

                    <!-- Unread dot or action -->
                    <div class="shrink-0 flex items-center gap-1.5 self-center">
                        <template x-if="!item.is_read">
                            <span class="w-2 h-2 rounded-full bg-dinas-gold"></span>
                        </template>
                        <button type="button" 
                            @click.stop="deleteNotification(item)" 
                            class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-rose-500 rounded transition" 
                            title="Hapus">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="!fetchLoading && filteredNotifications.length === 0" class="p-8 text-center">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <p class="font-bold text-xs text-slate-700 dark:text-slate-300">Belum Ada Notifikasi</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Semua pemberitahuan berkas cuti akan muncul di sini</p>
            </div>
        </div>

        <!-- Footer: Full History Link & Web Push Device Settings Accordion -->
        <div class="bg-slate-50/90 dark:bg-slate-900/90 border-t border-gray-100 dark:border-slate-700/80 p-2.5">
            <div class="flex items-center justify-between text-xs px-1">
                <a href="{{ route('notifications.index') }}" class="text-dinas-green dark:text-emerald-400 hover:text-dinas-greenhover hover:underline font-semibold flex items-center gap-1 text-[11px]">
                    <span>Buka Halaman Riwayat Lengkap</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>

                <button type="button" 
                    @click="showDeviceSettings = !showDeviceSettings" 
                    class="text-[11px] text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>Push HP/PC</span>
                    <svg class="w-3 h-3 transition-transform" :class="showDeviceSettings ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>

            <!-- Collapsible Device Push Settings Drawer -->
            <div x-show="showDeviceSettings" x-transition class="mt-2.5 pt-2.5 border-t border-gray-200/60 dark:border-slate-700/60 px-1 space-y-2">
                <template x-if="!supported">
                    <p class="text-[10px] text-amber-700 dark:text-amber-300">
                        Browser ini tidak mendukung Push API atau dibuka via HTTP non-aman.
                    </p>
                </template>

                <template x-if="supported && permission === 'denied'">
                    <p class="text-[10px] text-rose-600 dark:text-rose-400">
                        Izin notifikasi diblokir pada peramban ini. Buka ikon gembok di sebelah URL dan pilih "Izinkan".
                    </p>
                </template>

                <template x-if="supported && isSubscribed">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Push Web aktif di perangkat ini
                            </span>
                            <button type="button" @click="unsubscribe()" :disabled="pushLoading" class="text-rose-500 hover:underline text-[10px]">
                                Putuskan
                            </button>
                        </div>
                        <button type="button" 
                            @click="sendTest()" 
                            :disabled="pushLoading"
                            class="w-full py-1.5 bg-dinas-green hover:bg-dinas-greenhover disabled:opacity-50 text-white rounded-lg text-[11px] font-semibold transition flex items-center justify-center gap-1.5">
                            <span x-text="pushLoading ? 'Mengirim...' : 'Kirim Tes Push ke Layar Ini'"></span>
                        </button>
                    </div>
                </template>

                <template x-if="supported && !isSubscribed && permission !== 'denied'">
                    <div class="space-y-1.5">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">
                            Terima pop-up notifikasi instan di layar Windows / bar status HP saat berkas butuh persetujuan.
                        </p>
                        <button type="button" 
                            @click="subscribe()" 
                            :disabled="pushLoading"
                            class="w-full py-1.5 bg-dinas-green hover:bg-dinas-greenhover disabled:opacity-50 text-white rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                            <span x-text="pushLoading ? 'Mendaftarkan...' : 'Aktifkan Push di Perangkat Ini'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
function notificationCenter() {
    return {
        dropdownOpen: false,
        showDeviceSettings: false,
        activeFilter: 'all',
        notifications: [],
        unreadCount: 0,
        notifEnabled: true,
        fetchLoading: false,
        settingLoading: false,
        toastMessage: '',
        toastType: 'success',

        // Web Push status
        supported: false,
        permission: 'default',
        isSubscribed: false,
        pushLoading: false,

        async init() {
            // 1. Ambil dari cache sessionStorage terlebih dahulu untuk kecepatan navigasi instan
            const cachedNotif = sessionStorage.getItem('cached_notifications');
            const lastFetch = sessionStorage.getItem('last_notif_fetch_ts');
            const now = Date.now();
            let hasLoadedFromCache = false;

            if (cachedNotif && lastFetch && (now - parseInt(lastFetch, 10) < 30000)) {
                try {
                    const data = JSON.parse(cachedNotif);
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                    this.notifEnabled = data.notif_enabled ?? true;
                    hasLoadedFromCache = true;
                } catch (e) {
                    console.warn('Gagal membaca cache notifikasi:', e);
                }
            }

            if (!hasLoadedFromCache) {
                this.fetchNotifications(true);
            }

            // 2. Poll notifikasi setiap 45 detik saat tab browser sedang aktif
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    this.fetchNotifications(true);
                }
            }, 45000);

            // 3. Cek ketersediaan Web Push & status langganan
            this.supported = ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window);
            if (!this.supported) return;

            this.permission = Notification.permission;
            try {
                const reg = await navigator.serviceWorker.getRegistration('/sw.js');
                if (reg) {
                    const sub = await reg.pushManager.getSubscription();
                    if (sub && this.permission === 'granted') {
                        this.isSubscribed = true;

                        // Pasang endpoint ke form logout agar otomatis terlepas saat keluar
                        const logoutEndpointInput = document.getElementById('logout-push-endpoint');
                        if (logoutEndpointInput) {
                            logoutEndpointInput.value = sub.endpoint;
                        }

                        // Efisiensi request: sinkronkan ke server HANYA 1x per sesi per user,
                        // BUKAN di setiap klik link pindah halaman!
                        const syncKey = `push_synced_${window.authUser?.id || 'anon'}_${sub.endpoint}`;
                        if (sessionStorage.getItem(syncKey) !== 'true') {
                            await this.syncSubscription(sub);
                            sessionStorage.setItem(syncKey, 'true');
                        }
                    }
                }
            } catch (err) {
                console.warn('Error checking push subscription:', err);
            }
        },

        get filteredNotifications() {
            if (this.activeFilter === 'unread') {
                return this.notifications.filter(n => !n.is_read);
            }
            return this.notifications;
        },

        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen) {
                this.fetchNotifications();
            }
        },

        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            if (typeof window.showToast === 'function') {
                window.showToast({ message: msg, type: type });
            }
            setTimeout(() => {
                this.toastMessage = '';
            }, 4000);
        },

        updateCache() {
            try {
                sessionStorage.setItem('cached_notifications', JSON.stringify({
                    notifications: this.notifications,
                    unread_count: this.unreadCount,
                    notif_enabled: this.notifEnabled
                }));
                sessionStorage.setItem('last_notif_fetch_ts', Date.now().toString());
            } catch (e) {}
        },

        async fetchNotifications(isBackground = false) {
            if (!isBackground) this.fetchLoading = true;
            try {
                const res = await axios.get('/notifikasi', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.data.success) {
                    this.notifications = res.data.notifications || [];
                    this.unreadCount = res.data.unread_count || 0;
                    this.notifEnabled = res.data.notif_enabled ?? true;
                    this.updateCache();
                }
            } catch (err) {
                console.warn('Error fetching notifications:', err);
            } finally {
                if (!isBackground) this.fetchLoading = false;
            }
        },

        async toggleNotifSetting() {
            const targetState = !this.notifEnabled;

            // Validasi perizinan browser sebelum mengaktifkan (ON)
            if (targetState && ('Notification' in window) && Notification.permission !== 'granted') {
                try {
                    const perm = await Notification.requestPermission();
                    this.permission = perm;
                    if (perm !== 'granted') {
                        this.showToast('Gagal mengaktifkan. Anda harus memberikan izin notifikasi di popup browser terlebih dahulu.', 'error');
                        return;
                    }
                } catch (e) {
                    console.error('Permission request error:', e);
                    this.showToast('Gagal meminta izin notifikasi browser.', 'error');
                    return;
                }
            }

            this.settingLoading = true;
            try {
                const res = await axios.post('/notifikasi/toggle-setting', {
                    enabled: targetState
                });
                if (res.data.success) {
                    this.notifEnabled = res.data.notif_enabled;
                    this.updateCache();
                    if (targetState && this.permission === 'granted') {
                        await this.subscribe();
                    }
                    this.showToast(res.data.message || 'Pengaturan notifikasi berhasil diperbarui.', 'success');
                }
            } catch (err) {
                console.error('Error toggling notification setting:', err);
                this.showToast('Gagal mengubah pengaturan notifikasi.', 'error');
            } finally {
                this.settingLoading = false;
            }
        },

        async openNotification(item) {
            // Mark as read
            if (!item.is_read) {
                item.is_read = true;
                if (this.unreadCount > 0) this.unreadCount--;
                this.updateCache();
                try {
                    await axios.post(`/notifikasi/${item.id}/baca`);
                } catch (err) {
                    console.warn('Error marking as read:', err);
                }
            }

            // Redirect if action_url exists
            if (item.action_url && item.action_url !== '#') {
                window.location.href = item.action_url;
            }
        },

        async markAllAsRead() {
            try {
                const res = await axios.post('/notifikasi/baca-semua');
                if (res.data.success) {
                    this.unreadCount = 0;
                    this.notifications.forEach(n => n.is_read = true);
                    this.updateCache();
                    this.showToast('Semua notifikasi ditandai telah dibaca.', 'success');
                }
            } catch (err) {
                console.error('Error marking all as read:', err);
                this.showToast('Gagal menandai notifikasi.', 'error');
            }
        },

        async deleteNotification(item) {
            try {
                const res = await axios.delete(`/notifikasi/${item.id}`);
                if (res.data.success) {
                    this.notifications = this.notifications.filter(n => n.id !== item.id);
                    if (!item.is_read && this.unreadCount > 0) {
                        this.unreadCount--;
                    }
                    this.updateCache();
                }
            } catch (err) {
                console.error('Error deleting notification:', err);
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        },

        async syncSubscription(sub) {
            try {
                const subJson = sub.toJSON();
                await axios.post('/push/subscribe', {
                    endpoint: sub.endpoint,
                    keys: {
                        p256dh: subJson.keys.p256dh,
                        auth: subJson.keys.auth
                    },
                    content_encoding: (window.PushManager && PushManager.supportedContentEncodings) 
                        ? PushManager.supportedContentEncodings[0] 
                        : 'aes128gcm'
                });
            } catch (err) {
                console.warn('Sync push subscription error:', err);
            }
        },

        async subscribe() {
            if (!this.supported) return;
            this.pushLoading = true;
            try {
                const perm = await Notification.requestPermission();
                this.permission = perm;
                if (perm !== 'granted') {
                    this.showToast('Izin notifikasi tidak diberikan oleh peramban.', 'error');
                    return;
                }

                const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                await navigator.serviceWorker.ready;

                const keyRes = await axios.get('/push/public-key');
                const publicKey = keyRes.data.publicKey;
                if (!publicKey) throw new Error('VAPID Public Key tidak ditemukan.');

                const sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(publicKey)
                });

                const subJson = sub.toJSON();
                await axios.post('/push/subscribe', {
                    endpoint: sub.endpoint,
                    keys: {
                        p256dh: subJson.keys.p256dh,
                        auth: subJson.keys.auth
                    },
                    content_encoding: (window.PushManager && PushManager.supportedContentEncodings) 
                        ? PushManager.supportedContentEncodings[0] 
                        : 'aes128gcm'
                });

                this.isSubscribed = true;
                const syncKey = `push_synced_${window.authUser?.id || 'anon'}_${sub.endpoint}`;
                sessionStorage.setItem(syncKey, 'true');

                const logoutEndpointInput = document.getElementById('logout-push-endpoint');
                if (logoutEndpointInput) {
                    logoutEndpointInput.value = sub.endpoint;
                }
                this.showToast('Perangkat ini berhasil dihubungkan ke Web Push!', 'success');
            } catch (err) {
                console.error('Subscription error:', err);
                const errMsg = err.response?.data?.message || err.message || 'Gagal mengaktifkan Web Push.';
                this.showToast(errMsg, 'error');
            } finally {
                this.pushLoading = false;
            }
        },

        async unsubscribe() {
            this.pushLoading = true;
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    await axios.post('/push/unsubscribe', { endpoint: sub.endpoint });
                    await sub.unsubscribe();
                    const syncKey = `push_synced_${window.authUser?.id || 'anon'}_${sub.endpoint}`;
                    sessionStorage.removeItem(syncKey);
                }
                this.isSubscribed = false;
                const logoutEndpointInput = document.getElementById('logout-push-endpoint');
                if (logoutEndpointInput) {
                    logoutEndpointInput.value = '';
                }
                this.showToast('Notifikasi Web Push di perangkat ini dimatikan.', 'success');
            } catch (err) {
                console.error('Unsubscribe error:', err);
                this.showToast('Gagal mematikan: ' + err.message, 'error');
            } finally {
                this.pushLoading = false;
            }
        },

        async sendTest() {
            this.pushLoading = true;
            try {
                const res = await axios.post('/push/test');
                this.showToast(res.data.message || 'Notifikasi uji coba terkirim!', 'success');
                // Refresh list since test notification is also logged
                await this.fetchNotifications();
            } catch (err) {
                console.error('Test push error:', err);
                const errMsg = err.response?.data?.message || err.message || 'Gagal mengirim uji coba.';
                this.showToast(errMsg, 'error');
            } finally {
                this.pushLoading = false;
            }
        }
    };
}
</script>
