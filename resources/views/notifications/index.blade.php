<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Riwayat Notifikasi') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar lengkap pemberitahuan proses dan status permohonan cuti Anda
            </p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4 pb-6">
        @if($unreadCount > 0 || $notifications->count() > 0)
            <!-- Quick Actions (Area Konten) -->
            <div class="flex items-center justify-end gap-2 flex-wrap">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.readAll') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-dinas-green/10 hover:bg-dinas-green/20 dark:bg-dinas-green/20 dark:hover:bg-dinas-green/30 text-dinas-green dark:text-emerald-300 rounded-none text-xs font-semibold transition border border-dinas-green/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Tandai Semua Dibaca</span>
                        </button>
                    </form>
                @endif

                @if($notifications->count() > 0)
                    <form method="POST" action="{{ route('notifications.clear') }}"
                          data-confirm="true"
                          data-confirm-title="Bersihkan Semua Riwayat Notifikasi?"
                          data-confirm-message="Apakah Anda yakin ingin menghapus seluruh riwayat notifikasi Anda? Tindakan ini tidak dapat dibatalkan."
                          data-confirm-btn="Ya, Bersihkan Riwayat"
                          data-confirm-cancel="Batal"
                          data-confirm-type="danger">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/40 dark:hover:text-rose-400 text-slate-600 dark:text-slate-400 rounded-none text-xs font-medium transition border border-slate-300 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Bersihkan Riwayat</span>
                        </button>
                    </form>
                @endif
            </div>
        @endif
            
            <!-- Card Setting ON/OFF Notifikasi & Validasi Perizinan Browser -->
            <div x-data="notificationPreferenceManager({{ $notifEnabled ? 'true' : 'false' }})" x-init="init()" class="space-y-3">
                <!-- Banner Mismatch 1: DB tercatat Aktif (ON), tetapi Browser memblokir ('denied') -->
                <div x-show="isBlocked" x-cloak 
                     class="p-4 bg-amber-50 dark:bg-amber-950/60 border-l-4 border-amber-500 text-amber-900 dark:text-amber-200 text-xs rounded-none shadow-xs">
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
                     class="p-4 bg-amber-50 dark:bg-amber-950/60 border-l-4 border-amber-500 text-amber-900 dark:text-amber-200 text-xs rounded-none shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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
                     class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs rounded-none shadow-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
                </div>

                <!-- Card Setting Box -->
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-none flex items-center justify-center shrink-0"
                             :class="dbEnabled ? (browserPermission === 'denied' ? 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400') : 'bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white flex items-center gap-2">
                                <span>Pengaturan Notifikasi Akun</span>
                                <!-- Status Badges -->
                                <template x-if="!dbEnabled">
                                    <span class="px-2 py-0.5 rounded-none text-[10px] font-bold bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        NONAKTIF (OFF)
                                    </span>
                                </template>
                                <template x-if="dbEnabled && browserPermission === 'denied'">
                                    <span class="px-2 py-0.5 rounded-none text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                        STATUS: DIBLOKIR BROWSER
                                    </span>
                                </template>
                                <template x-if="dbEnabled && browserPermission === 'default'">
                                    <span class="px-2 py-0.5 rounded-none text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                        STATUS: BUTUH IZIN BROWSER
                                    </span>
                                </template>
                                <template x-if="dbEnabled && browserPermission === 'granted'">
                                    <span class="px-2 py-0.5 rounded-none text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        STATUS: AKTIF (ON)
                                    </span>
                                </template>
                                <!-- Fallback text before Alpine -->
                                <span x-show="browserPermission === null" class="px-2 py-0.5 rounded-none text-[10px] font-bold {{ $notifEnabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $notifEnabled ? 'AKTIF (ON)' : 'NONAKTIF (OFF)' }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"
                               x-text="dbEnabled ? (browserPermission === 'denied' ? 'Sistem disetel aktif, namun perizinan browser Anda diblokir sehingga pemberitahuan cuti tidak dapat masuk ke layar.' : 'Anda akan menerima pemberitahuan in-app dan push alert saat ada pengajuan cuti baru, verifikasi atasan, atau pengesahan surat.') : 'Notifikasi saat ini dimatikan. Anda tidak akan menerima pemberitahuan alert pengajuan baru.'">
                                {{ $notifEnabled ? 'Anda akan menerima pemberitahuan in-app dan push alert saat ada pengajuan cuti baru, verifikasi atasan, atau pengesahan surat.' : 'Notifikasi saat ini dimatikan. Anda tidak akan menerima pemberitahuan alert pengajuan baru.' }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('notifications.toggle') }}" class="shrink-0" @submit="handleFormSubmit($event)">
                        @csrf
                        <input type="hidden" name="enabled" :value="dbEnabled ? '0' : '1'" value="{{ $notifEnabled ? '0' : '1' }}">
                        <button type="submit" 
                                :disabled="isLoading"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-none text-xs font-bold transition shadow-xs"
                                :class="dbEnabled ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-900' : 'bg-emerald-600 text-white hover:bg-emerald-500'">
                            <template x-if="dbEnabled">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    <span>Matikan Notifikasi (Turn OFF)</span>
                                </span>
                            </template>
                            <template x-if="!dbEnabled">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-text="isLoading ? 'Memvalidasi Izin...' : 'Nyalakan Notifikasi (Turn ON)'"></span>
                                </span>
                            </template>
                            <span x-show="false">{{ $notifEnabled ? __('Matikan Notifikasi (Turn OFF)') : __('Nyalakan Notifikasi (Turn ON)') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Notifikasi -->
            <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden divide-y divide-gray-100 dark:divide-slate-700/60">
                @forelse($notifications as $notif)
                    @php
                        $data = $notif->data ?? [];
                        $type = $data['type'] ?? 'info';
                        $isRead = !is_null($notif->read_at);
                        $actionUrl = $data['action_url'] ?? '#';
                    @endphp
                    <div class="p-4 sm:p-5 flex items-start gap-3 sm:gap-4 transition hover:bg-slate-50/70 dark:hover:bg-slate-700/40 {{ !$isRead ? 'bg-dinas-green/5 dark:bg-dinas-green/10' : '' }}">
                        <!-- Type Icon -->
                        <div class="w-9 h-9 rounded-none flex items-center justify-center shrink-0 mt-0.5
                            @if($type === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400
                            @elseif($type === 'warning') bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300
                            @elseif($type === 'danger') bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400
                            @else bg-dinas-green/10 text-dinas-green dark:bg-emerald-950 dark:text-emerald-400 @endif">
                            @if($type === 'success')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($type === 'warning')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            @elseif($type === 'danger')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white truncate">
                                    {{ $data['title'] ?? 'Pemberitahuan Sistem' }}
                                </h4>
                                @if(!$isRead)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-none text-[9px] font-bold bg-dinas-gold/20 text-yellow-800 dark:bg-yellow-950/60 dark:text-yellow-300 border border-yellow-300/60">
                                        BARU
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-2">
                                {{ $data['body'] ?? '' }}
                            </p>
                            <div class="flex items-center gap-3 text-[11px] text-slate-400 dark:text-slate-500">
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span>{{ $notif->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            @if($actionUrl && $actionUrl !== '#')
                                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-dinas-green hover:bg-dinas-greenhover text-white rounded-none text-xs font-semibold shadow-xs transition-colors">
                                        Lihat
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}"
                                  data-confirm="true"
                                  data-confirm-title="Hapus Notifikasi?"
                                  data-confirm-message="Apakah Anda yakin ingin menghapus notifikasi ini dari daftar riwayat?"
                                  data-confirm-btn="Ya, Hapus"
                                  data-confirm-cancel="Batal"
                                  data-confirm-type="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-none hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Hapus Notifikasi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-12 h-12 rounded-none bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 mb-1">Belum Ada Riwayat Notifikasi</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm mx-auto">
                            Saat ada pengajuan cuti, pertimbangan atasan langsung, atau pengesahan dari Ketua PA, riwayat notifikasi akan otomatis tercatat di sini.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>

        </div>
</x-app-layout>
