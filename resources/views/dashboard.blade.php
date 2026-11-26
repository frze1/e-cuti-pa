<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                    {{ __('Beranda') }}
                </h2>
                <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">Layanan Administrasi Pegawai</p>
            </div>
            <div class="text-[11px] sm:text-xs font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1 sm:gap-1.5 bg-slate-100 dark:bg-slate-800 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-dinas-green dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="truncate">
                    <span class="sm:hidden">{{ now()->isoFormat('D MMM YYYY') }}</span>
                    <span class="hidden sm:inline">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-3 sm:space-y-4 pb-6">

        <!-- Peringatan Sinkronisasi Izin Notifikasi Browser (Jika DB Aktif tapi Browser Memblokir / Belum Allow) -->
        @if($user->notif_enabled)
        <div x-data="{ 
                perm: ('Notification' in window) ? Notification.permission : 'unsupported',
                dismissed: false,
                isRequesting: false,
                async askPermission() {
                    if ('Notification' in window) {
                        this.isRequesting = true;
                        try {
                            this.perm = await Notification.requestPermission();
                        } catch(e) {
                            console.error(e);
                        } finally {
                            this.isRequesting = false;
                        }
                    }
                }
             }" 
             x-show="!dismissed && (perm === 'denied' || perm === 'default')" 
             :hidden="dismissed || (perm !== 'denied' && perm !== 'default')"
             x-cloak
             class="p-3 sm:p-3.5 rounded-none border text-xs shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3"
             :class="perm === 'denied' ? 'bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-blue-50 dark:bg-blue-950/60 border-blue-300 dark:border-blue-800 text-blue-900 dark:text-blue-200'">
            <div class="flex items-start sm:items-center gap-2.5">
                <svg class="w-5 h-5 shrink-0 mt-0.5 sm:mt-0" :class="perm === 'denied' ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <p class="font-bold text-xs sm:text-sm" x-text="perm === 'denied' ? 'Pemberitahuan Aktif di sistem, namun diblokir oleh browser.' : 'Pemberitahuan Aktif di sistem, namun izin notifikasi browser belum diberikan.'"></p>
                    <p class="mt-0.5 text-[11px] sm:text-xs text-slate-600 dark:text-slate-300" x-text="perm === 'denied' ? 'Silakan izinkan (Allow) notifikasi di pengaturan URL browser Anda agar fitur ini berfungsi.' : 'Klik tombol di samping atau izinkan pop-up notifikasi agar Anda menerima pemberitahuan otomatis.'"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                <template x-if="perm === 'default'">
                    <button type="button" @click="askPermission()" :disabled="isRequesting" class="px-3 py-1.5 bg-dinas-green hover:bg-dinas-greenhover text-white font-bold rounded-none text-xs transition-colors">
                        <span x-text="isRequesting ? 'Meminta...' : 'Izinkan Notifikasi'"></span>
                    </button>
                </template>
                <a href="{{ route('profile.edit') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-none text-xs transition">
                    Buka Pengaturan
                </a>
                <button type="button" @click="dismissed = true" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1" title="Tutup">
                    &times;
                </button>
            </div>
        </div>
        @endif
        
        <!-- Widget Profil & Hero Sapaan -->
        <div class="bg-dinas-green dark:bg-dinas-green overflow-hidden shadow-xs rounded-none border border-dinas-green hover:border-dinas-greenhover relative text-white">
            <!-- Aksen glow halus di pojok kanan -->
            <div class="absolute -right-12 -top-12 w-60 h-60 bg-dinas-gold/15 rounded-none blur-3xl pointer-events-none"></div>

            <div class="p-4 sm:p-6 relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <!-- Sisi Kiri: Profil Pegawai -->
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg sm:text-xl md:text-2xl font-extrabold text-white tracking-tight leading-tight truncate">
                            {{ $user->name }}
                        </h3>
                        <span class="px-2 py-0.5 rounded-none text-[10px] md:text-[11px] font-semibold bg-white/20 text-white border border-white/30 shrink-0">
                            Aktif
                        </span>
                    </div>
                    <p class="text-emerald-100 mt-0.5 text-xs sm:text-sm font-medium truncate">
                        {{ $user->pegawai?->jabatan?->nama_jabatan ?? $user->jabatan ?? 'Pegawai' }}
                    </p>
                    @if($user->pegawai?->nip)
                        <div class="mt-2 text-[11px] font-medium text-emerald-100/90 flex flex-col sm:flex-row gap-1.5 sm:gap-2 sm:items-center">
                            <span class="w-full sm:w-auto text-left bg-black/20 px-2.5 py-1 sm:py-0.5 rounded-none text-white border border-white/20 font-mono text-[11px]">
                                NIP. {{ $user->pegawai->nip }}
                            </span>
                            @if($user->pegawai?->masa_kerja)
                                <span class="w-full sm:w-auto text-left text-emerald-100 bg-black/15 px-2.5 py-1 sm:py-0.5 rounded-none border border-white/15 text-[11px]">
                                    Masa Kerja: {{ $user->pegawai->masa_kerja }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Sisi Kanan: Tombol Aksi Cepat Utama -->
                <div class="flex items-center gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/20">
                    <a href="{{ route('cuti.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 sm:py-2.5 rounded-none bg-dinas-gold hover:bg-yellow-400 text-slate-900 font-extrabold text-xs sm:text-sm shadow-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Ajukan Cuti Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bagian Notifikasi Antrean Tugas (Muncul otomatis jika ada antrean tindakan) -->
        @if($countAtasan > 0 || $countAdmin > 0 || $countKetua > 0)
        <div>
            <h4 class="text-[11px] md:text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Tugas & Antrean Verifikasi</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    @if($countAtasan > 0)
                    <a href="{{ route('cuti.persetujuan') }}" class="block bg-white dark:bg-slate-800 shadow-xs rounded-none p-3.5 md:p-6 border-l-4 border-amber-500 border-y border-r border-slate-300 dark:border-slate-700 hover:shadow-sm transition-all">
                        <h4 class="text-slate-500 dark:text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Persetujuan Cuti</h4>
                        <div class="flex items-baseline justify-between md:block">
                            <p class="text-2xl md:text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $countAtasan }} <span class="text-xs md:text-sm font-medium text-slate-500 dark:text-slate-400">Berkas</span></p>
                            <p class="text-xs md:text-sm text-amber-600 dark:text-amber-400 font-semibold md:mt-2 flex items-center gap-1">Menunggu tindakan Anda &rarr;</p>
                        </div>
                    </a>
                    @endif

                    @if($countAdmin > 0)
                    <a href="{{ route('cuti.penomoran') }}" class="block bg-white dark:bg-slate-800 shadow-xs rounded-none p-3.5 md:p-6 border-l-4 border-dinas-green border-y border-r border-slate-300 dark:border-slate-700 hover:shadow-sm transition-all">
                        <h4 class="text-slate-500 dark:text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Penomoran Surat</h4>
                        <div class="flex items-baseline justify-between md:block">
                            <p class="text-2xl md:text-3xl font-extrabold text-dinas-green dark:text-emerald-400">{{ $countAdmin }} <span class="text-xs md:text-sm font-medium text-slate-500 dark:text-slate-400">Berkas</span></p>
                            <p class="text-xs md:text-sm text-dinas-green dark:text-emerald-400 font-semibold md:mt-2 flex items-center gap-1">Menunggu input nomor &rarr;</p>
                        </div>
                    </a>
                    @endif

                    @if($countKetua > 0)
                    <a href="{{ route('cuti.daftar_ketua') }}" class="block bg-white dark:bg-slate-800 shadow-xs rounded-none p-3.5 md:p-6 border-l-4 border-dinas-gold border-y border-r border-slate-300 dark:border-slate-700 hover:shadow-sm transition-all">
                        <h4 class="text-slate-500 dark:text-slate-400 text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1">Keputusan Final</h4>
                        <div class="flex items-baseline justify-between md:block">
                            <p class="text-2xl md:text-3xl font-extrabold text-yellow-700 dark:text-yellow-400">{{ $countKetua }} <span class="text-xs md:text-sm font-medium text-slate-500 dark:text-slate-400">Berkas</span></p>
                            <p class="text-xs md:text-sm text-yellow-700 dark:text-yellow-400 font-semibold md:mt-2 flex items-center gap-1">Menunggu pengesahan &rarr;</p>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @if($user->role === 'admin')
                {{-- Widget Aparatur Sedang Cuti Hari Ini (Khusus Hanya Dapat Diakses & Dilihat Oleh Admin) --}}
                @if(isset($pegawaiCutiHariIni) && $pegawaiCutiHariIni->count() > 0)
                    <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
                        <!-- Header Card Cuti -->
                        <div class="p-4 sm:p-5 md:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                            <!-- Kiri: Judul & Badge -->
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                                    <!-- Teks untuk Mobile (Disembunyikan di layar medium ke atas) -->
                                    <span class="block md:hidden">Sedang Cuti</span>
                                    
                                    <!-- Teks untuk Desktop (Disembunyikan di layar kecil) -->
                                    <span class="hidden md:block">Aparatur Sedang Cuti Hari Ini</span>
                                </h3>
                                <span class="bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-none whitespace-nowrap">
                                    {{ $pegawaiCutiHariIni->count() }} Pegawai
                                </span>
                            </div>

                            <!-- Kanan: Tautan Kalender -->
                            <a href="{{ route('cuti.kalender') }}" class="text-xs text-dinas-green hover:text-dinas-greenhover dark:text-emerald-400 font-medium inline-flex items-center gap-1 transition">
                                <span>Lihat Kalender</span>
                                <span class="sr-only">Kalender Lengkap</span>
                                <span>&rarr;</span>
                            </a>
                        </div>

                        <div class="p-4 sm:p-5 md:p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                                @foreach($pegawaiCutiHariIni as $cutiHariIni)
                                    @php
                                        $jenisBadgeColor = match($cutiHariIni->jenis_cuti) {
                                            'Cuti Tahunan' => 'bg-dinas-green/10 text-dinas-green border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                                            'Cuti Sakit' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800',
                                            'Cuti Besar' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
                                            'Cuti Melahirkan' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                            'Cuti Karena Alasan Penting' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                        };
                                    @endphp
                                    <div class="p-3.5 rounded-none border border-slate-300 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40 hover:border-dinas-green/40 dark:hover:border-dinas-green/50 transition">
                                        <div class="min-w-0">
                                            <div class="flex items-center justify-between gap-1 mb-1">
                                                <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white truncate">
                                                    {{ $cutiHariIni->user?->name ?? 'Pegawai' }}
                                                </h4>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                                {{ $cutiHariIni->user?->jabatan ?? '-' }}
                                            </p>
                                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded-none text-[10px] font-semibold border {{ $jenisBadgeColor }}">
                                                    {{ $cutiHariIni->jenis_cuti }}
                                                </span>
                                                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                                    s.d. {{ \Carbon\Carbon::parse($cutiHariIni->tgl_selesai)->isoFormat('D MMMM YYYY') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Tampilan Efektif & Ramping: Sisi Kiri (Info & Judul) Sebelahan Sisi Kanan (Status Aktif & Tombol Kalender) -->
                    <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-4 sm:p-5 md:p-6 flex flex-wrap items-center justify-between gap-2.5">
                        <!-- Sisi Kiri: Judul & Badge -->
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Sedang Cuti</h3>
                            <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-none whitespace-nowrap">
                                0 Pegawai
                            </span>
                            <span class="hidden sm:inline text-xs text-slate-500 dark:text-slate-400">• Seluruh aparatur aktif bertugas</span>
                        </div>

                        <!-- Sisi Kanan: Status Hadir & Tombol Kalender -->
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold border border-emerald-200/80 dark:border-emerald-800/80">
                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span>Semua Bertugas</span>
                            </span>
                            <a href="{{ route('cuti.kalender') }}" class="text-xs text-dinas-green hover:text-dinas-greenhover dark:text-emerald-400 font-medium inline-flex items-center gap-1 transition">
                                <span>Lihat Kalender</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Widget Saldo Cuti Pegawai -->
            <div class="mt-2 md:mt-3">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-2 mb-2.5 md:mb-3.5">
                    <h3 class="text-sm md:text-base font-bold text-slate-800 dark:text-white flex items-center gap-1.5 md:gap-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-dinas-green dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Informasi Saldo Cuti Tahunan
                    </h3>
                    <span class="text-[10px] md:text-[11px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-200/70 dark:bg-slate-700/80 px-2 py-0.5 rounded-none">
                        Tahun {{ date('Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    <!-- Kartu N (Tahun Berjalan) -->
                    @php
                        $totalHakN = 12;
                        $persenN = min(100, max(0, round(($user->sisa_cuti_n / $totalHakN) * 100)));
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border-t-4 border-dinas-green p-4 sm:p-5 text-center border-x border-b border-slate-300 dark:border-slate-700 flex flex-col justify-between">
                        <div>
                            <p class="text-[10px] sm:text-[11px] font-bold text-dinas-green dark:text-emerald-400 uppercase tracking-wider mb-0.5">Tahun Berjalan (N - {{ date('Y') }})</p>
                            <p class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-white my-1 tracking-tight">{{ $user->sisa_cuti_n }}</p>
                            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Hari Kerja Tersedia</p>
                        </div>
                        <div class="mt-2.5 pt-2.5 border-t border-slate-200 dark:border-slate-700/80">
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-none h-1.5 overflow-hidden">
                                <div class="bg-dinas-green dark:bg-dinas-green h-1.5 rounded-none transition-all duration-500" style="width: {{ $persenN }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-400 dark:text-slate-400 mt-1 font-medium">
                                <span>Tersisa {{ $user->sisa_cuti_n }} dari {{ $totalHakN }} hari</span>
                                <span>{{ $persenN }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu N-1 -->
                    <div class="bg-gray-50/90 dark:bg-slate-800/60 rounded-none shadow-2xs border-t-4 border-slate-300 dark:border-slate-600 p-4 sm:p-5 text-center border-x border-b border-slate-300 dark:border-slate-700 flex flex-col justify-between">
                        <div>
                            <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Tahun N-1 ({{ date('Y') - 1 }})</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-500 dark:text-slate-200 my-1 sm:my-2">{{ $user->sisa_cuti_n_1 }}</p>
                            <p class="text-[11px] text-slate-400">Hari Sisa Tahun Lalu</p>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2.5 pt-2.5 border-t border-slate-300 dark:border-slate-700/80">
                            Digunakan setelah kuota N habis
                        </p>
                    </div>

                    <!-- Kartu N-2 -->
                    <div class="bg-gray-50/90 dark:bg-slate-800/60 rounded-none shadow-2xs border-t-4 border-slate-300 dark:border-slate-600 p-4 sm:p-5 text-center border-x border-b border-slate-300 dark:border-slate-700 opacity-80 flex flex-col justify-between">
                        <div>
                            <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Tahun N-2 ({{ date('Y') - 2 }})</p>
                            <p class="text-2xl sm:text-3xl font-bold text-slate-400 dark:text-slate-300 my-1 sm:my-2">{{ $user->sisa_cuti_n_2 }}</p>
                            <p class="text-[11px] text-slate-400">Hari Sisa Dua Tahun Lalu</p>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2.5 pt-2.5 border-t border-slate-300 dark:border-slate-700/80">
                            Digunakan setelah kuota N-1 habis
                        </p>
                    </div>
                </div>
            </div>

            <!-- Widget Aktivitas Pengajuan Terkini -->
            <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
                <div class="p-4 sm:p-5 md:p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-8 h-8 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4 text-dinas-green dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 7.5V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white truncate">Pengajuan Cuti Terkini</h3>
                            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400">Daftar permohonan cuti terbaru milik Anda</p>
                        </div>
                    </div>
                    <a href="{{ route('cuti.riwayat') }}" class="text-xs font-semibold text-dinas-green dark:text-emerald-400 hover:text-dinas-greenhover dark:hover:text-emerald-300 flex items-center gap-1 transition shrink-0">
                        <span>Semua Riwayat</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                @if(isset($riwayatTerbaru) && $riwayatTerbaru->count() > 0)
                    <div class="w-full overflow-x-auto border-t border-slate-200 dark:border-slate-700">
                        <table class="table-fixed w-full min-w-[750px] md:w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                            <thead class="bg-slate-100 dark:bg-slate-900 text-xs uppercase text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-300 dark:border-slate-700">
                                <tr>
                                    <th class="w-[18%] py-2.5 px-3 md:px-4 text-left">Tgl Pengajuan</th>
                                    <th class="w-[18%] py-2.5 px-3 md:px-4 text-left">Jenis Cuti</th>
                                    <th class="w-[20%] py-2.5 px-3 md:px-4 text-left">Rentang Waktu</th>
                                    <th class="w-[12%] py-2.5 px-3 md:px-4 text-center">Durasi</th>
                                    <th class="w-[16%] py-2.5 px-3 md:px-4 text-center">Status</th>
                                    <th class="w-[16%] py-2.5 px-3 md:px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($riwayatTerbaru as $cuti)
                                    @php
                                        $badgeClass = match($cuti->status) {
                                            'SELESAI_DISETUJUI' => 'bg-dinas-green/10 text-dinas-green font-bold border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                                            'DITOLAK' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                            'MENUNGGU_ATASAN' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                                            'MENUNGGU_PENOMORAN' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                            'MENUNGGU_KETUA' => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
                                            default => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition">
                                        <td class="py-3 px-3 md:px-4 font-medium text-slate-800 dark:text-slate-200 whitespace-normal break-words align-top text-left">
                                            {{ $cuti->created_at->isoFormat('D MMMM YYYY') }}
                                        </td>
                                        <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $cuti->jenis_cuti }}</span>
                                        </td>
                                        <td class="py-3 px-3 md:px-4 text-slate-600 dark:text-slate-300 text-xs whitespace-normal break-words align-top text-left">
                                            {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 px-3 md:px-4 text-center font-bold text-slate-800 dark:text-white whitespace-normal break-words align-top">
                                            {{ $cuti->durasi_hari }} Hari
                                        </td>
                                        <td class="py-3 px-3 md:px-4 text-center whitespace-normal break-words align-top">
                                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-none border {{ $badgeClass }}">
                                                {{ str_replace('_', ' ', $cuti->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 md:px-4 text-center whitespace-normal break-words align-top">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                {{-- Tombol Perbaiki Pengajuan (warna kuning/oranye) --}}
                                                @if(in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN']))
                                                    <a href="{{ route('cuti.edit', $cuti->id) }}" class="inline-flex items-center justify-center gap-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-1 px-2.5 rounded-none border border-transparent text-xs shadow-xs transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        Perbaiki Pengajuan
                                                    </a>
                                                @endif

                                                {{-- Tombol Tarik Permohonan --}}
                                                @if(in_array($cuti->status, ['Menunggu Atasan', 'MENUNGGU_ATASAN', 'Revisi Atasan', 'REVISI_ATASAN']))
                                                    <form action="{{ route('cuti.tarik', $cuti->id) }}" method="POST" class="inline-block"
                                                          data-confirm="true"
                                                          data-confirm-title="Tarik Permohonan Cuti?"
                                                          data-confirm-message="Apakah Anda yakin ingin menarik permohonan cuti ini? Kuota cuti Anda akan dikembalikan secara otomatis."
                                                          data-confirm-btn="Ya, Tarik Permohonan"
                                                          data-confirm-cancel="Batal"
                                                          data-confirm-type="danger">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex items-center justify-center gap-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2.5 rounded-none border border-transparent text-xs shadow-xs transition">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            Tarik Permohonan
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Tombol Unduh Word jika disetujui --}}
                                                @if(in_array($cuti->status, ['SELESAI_DISETUJUI', 'Disetujui Atasan']))
                                                    <div class="flex items-center justify-center gap-1">
                                                        <a href="{{ route('cuti.export-word', $cuti->id) }}" class="inline-flex items-center justify-center gap-1 border border-dinas-green text-dinas-green hover:bg-dinas-green hover:text-white font-semibold py-1 px-2.5 rounded-none text-xs shadow-xs transition" title="Unduh Dokumen Word Editable (.docx)">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                            <span>Word</span>
                                                        </a>
                                                    </div>
                                                @elseif(!in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN', 'Menunggu Atasan', 'MENUNGGU_ATASAN']))
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium italic">Terkunci</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Tampilan Kosong (Empty State) yang Ramah -->
                    <div class="py-6 sm:py-8 px-4 sm:px-6 text-center">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-slate-100 dark:bg-slate-700/60 rounded-none flex items-center justify-center mx-auto mb-2.5 sm:mb-3 text-slate-400 dark:text-slate-300">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 7.5V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h4 class="text-slate-800 dark:text-white font-bold text-sm">Belum Ada Pengajuan Cuti</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1 max-w-sm mx-auto">Anda belum memiliki riwayat pengajuan cuti saat ini.</p>
                        <a href="{{ route('cuti.create') }}" class="inline-flex items-center gap-1.5 mt-3 sm:mt-4 px-3.5 sm:px-4 py-2 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none shadow-xs transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajukan Cuti Sekarang
                        </a>
                    </div>
                @endif
            </div>

        </div>
</x-app-layout>
