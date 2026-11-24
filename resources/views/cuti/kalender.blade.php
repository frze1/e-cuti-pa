<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Kalender Cuti Aparatur') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Jadwal cuti dan pemantauan aparatur Pengadilan Agama Sumber
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6" x-data="{
        modalOpen: false,
        activeEvent: null,
        showDetail(props) {
            this.activeEvent = props;
            this.modalOpen = true;
        }
    }">

        <!-- Action Bar (Area Konten) -->
        <div class="flex justify-end">
            <a href="{{ route('cuti.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-none bg-dinas-green hover:bg-dinas-greenhover text-white font-bold text-xs shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Ajukan Cuti</span>
            </a>
        </div>

            <!-- Bagian Aparatur yang Sedang Cuti Hari Ini -->
            @if(isset($pegawaiCutiHariIni) && $pegawaiCutiHariIni->count() > 0)
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
                    <div class="p-3.5 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                                <!-- Teks untuk Mobile (Disembunyikan di layar medium ke atas) -->
                                <span class="block md:hidden">Sedang Cuti</span>
                                
                                <!-- Teks untuk Desktop (Disembunyikan di layar kecil) -->
                                <span class="hidden md:block">Aparatur Sedang Cuti Hari Ini</span>
                            </h3>
                            <span class="px-2 py-0.5 rounded-none text-[10px] sm:text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 whitespace-nowrap">
                                {{ $pegawaiCutiHariIni->count() }} Pegawai
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block">
                            {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                        </p>
                    </div>

                    <div class="p-3.5 sm:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($pegawaiCutiHariIni as $cutiHariIni)
                                @php
                                    $isItemPemohon = (Auth::id() === $cutiHariIni->user_id);
                                    $isItemAdmin = (Auth::user()->role === 'admin');
                                    $isItemKetua = str_contains(strtolower(Auth::user()->jabatan ?? ''), 'ketua');
                                    $isItemAtasan = Auth::user()->pegawai && ($cutiHariIni->user?->pegawai?->atasan?->id === Auth::user()->pegawai->id);

                                    $canViewThisItem = $isItemPemohon || $isItemAdmin || $isItemKetua || $isItemAtasan;

                                    $jenisBadgeColor = match($cutiHariIni->jenis_cuti) {
                                        'Cuti Tahunan' => 'bg-dinas-green/10 text-dinas-green border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                                        'Cuti Sakit' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800',
                                        'Cuti Besar' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
                                        'Cuti Melahirkan' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                                        'Cuti Karena Alasan Penting' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                    };
                                @endphp
                                <div class="p-3.5 rounded-none border border-slate-300 dark:border-slate-700/80 bg-slate-50/60 dark:bg-slate-900/40 hover:border-dinas-green/40 dark:hover:border-dinas-green/50 transition">
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white truncate">
                                            {{ $cutiHariIni->user?->name ?? 'Pegawai' }}
                                        </h4>
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
                <!-- Tampilan Efektif & Ramping Saat Kosong -->
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-3.5 sm:px-5 sm:py-3.5 flex flex-wrap items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                            <!-- Teks untuk Mobile (Disembunyikan di layar medium ke atas) -->
                            <span class="block md:hidden">Sedang Cuti</span>
                            
                            <!-- Teks untuk Desktop (Disembunyikan di layar kecil) -->
                            <span class="hidden md:block">Aparatur Sedang Cuti Hari Ini</span>
                        </h3>
                        <span class="px-2 py-0.5 rounded-none text-[10px] sm:text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                            0 Pegawai
                        </span>
                        <span class="hidden sm:inline text-xs text-slate-500 dark:text-slate-400">• Seluruh aparatur aktif bertugas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-semibold border border-emerald-200 dark:border-emerald-800/80">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Semua Bertugas</span>
                        </span>
                    </div>
                </div>
            @endif

            <!-- Petunjuk Warna / Legend (Kiri Label, Kanan Kategori Cuti Sebelahan) -->
            <div class="bg-white dark:bg-slate-800 rounded-none p-3 sm:px-5 sm:py-3.5 shadow-xs border border-slate-300 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider shrink-0">Kategori Jenis Cuti:</span>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 border border-dinas-green/20 dark:border-dinas-green/30 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-dinas-green"></span>
                        <span>Cuti Tahunan</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-orange-50 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800/60 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                        <span>Cuti Sakit</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                        <span>Cuti Besar</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        <span>Cuti Melahirkan</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                        <span>Alasan Penting</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-none bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                        <span>Cuti Lainnya</span>
                    </div>
                </div>
            </div>

            <!-- Kalender Utama (FullCalendar.js Container) -->
            <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-3 sm:p-5 overflow-hidden">
                <div id="calendar-loading" class="py-12 text-center text-slate-500 dark:text-slate-400">
                    <svg class="animate-spin h-7 w-7 mx-auto text-dinas-green dark:text-emerald-400 mb-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="text-xs font-medium">Memuat jadwal kalender cuti...</p>
                </div>
                <div id="calendar" class="min-h-[580px]"></div>
            </div>

        </div>

        <!-- Modal Detail Cuti (Compact & Privacy-Enforced) -->
        <div x-show="modalOpen" 
             x-cloak
             @click.self="modalOpen = false" 
             @keydown.escape.window="modalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs"
             style="display: none;">
            
            <!-- Box Dialog dengan Batas Lebar Maksimal 420px (Compact) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 overflow-hidden mx-auto transition-all"
                 style="width: 100%; max-width: 420px;"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="scale-95 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="scale-100 opacity-100"
                 x-transition:leave-end="scale-95 opacity-0">
                
                <!-- Modal Header -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700/80 flex items-center justify-between gap-2.5 bg-slate-50/80 dark:bg-slate-900/50">
                    <div class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white truncate" x-text="activeEvent?.nama"></h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="activeEvent?.jabatan"></p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="p-1 rounded-none text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Compact) -->
                <div class="p-3.5 sm:p-4 space-y-2.5 text-xs">
                    
                    <!-- Rentang Waktu & Durasi (Sebelahan Kiri Kanan) -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-700/80 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Periode Cuti</span>
                            <div class="flex items-center gap-1.5 mt-0.5 text-xs font-semibold text-slate-800 dark:text-white truncate">
                                <svg class="w-3.5 h-3.5 text-dinas-green shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span x-text="activeEvent?.tgl_mulai_formatted"></span>
                                <span class="text-slate-400">&mdash;</span>
                                <span x-text="activeEvent?.tgl_selesai_formatted"></span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Durasi</span>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 border border-dinas-green/20 dark:border-dinas-green/30 font-bold text-xs" x-text="(activeEvent?.durasi_hari || '-') + ' Hari'"></span>
                        </div>
                    </div>

                    <!-- Jenis Cuti & Nomor Surat (Sebelahan Kiri Kanan) -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-700/80">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jenis Cuti</span>
                            <span class="font-bold text-slate-800 dark:text-white mt-0.5 block truncate" x-text="activeEvent?.jenis_cuti"></span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-700/80">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Surat Dinas</span>
                            <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 mt-0.5 block truncate" x-text="activeEvent?.nomor_surat || '-'"></span>
                        </div>
                    </div>

                    <!-- BLOK ALASAN CUTI: Tampil jelas jika user berwenang (Pemohon, Atasan Langsung, Admin, Ketua) -->
                    <template x-if="activeEvent?.can_view_alasan">
                        <div class="p-2.5 rounded-xl bg-dinas-green/10 dark:bg-dinas-green/20 border border-dinas-green/20 dark:border-dinas-green/30 text-slate-800 dark:text-slate-200">
                            <span class="text-[10px] font-bold text-dinas-green dark:text-emerald-400 uppercase tracking-wider block mb-1">Alasan Cuti</span>
                            <p class="text-xs leading-relaxed" x-text="activeEvent?.alasan || '-'"></p>
                        </div>
                    </template>

                    <!-- BLOK PRIVASI: Tampil jika user bukan pemohon/atasan/admin -->
                    <template x-if="!activeEvent?.can_view_alasan">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/90 dark:border-slate-700/80 flex items-start gap-2.5 text-slate-600 dark:text-slate-400">
                            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <div class="min-w-0 flex-1 text-[11px] leading-relaxed">
                                <span class="font-semibold block text-slate-700 dark:text-slate-300">Alasan Dirahasiakan</span>
                                Keterangan alasan cuti bersifat pribadi dan dirahasiakan.
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Modal Footer -->
                <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-900/60 border-t border-gray-100 dark:border-slate-700/80 flex justify-end">
                    <button type="button" @click="modalOpen = false" class="px-3.5 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold text-xs rounded-lg transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    <!-- FullCalendar CDN Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/id.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const loadingEl = document.getElementById('calendar-loading');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'id',
                initialView: 'dayGridMonth',
                eventDisplay: 'block',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    list: 'Daftar'
                },
                events: '{{ route('cuti.kalender.events') }}',
                loading: function(isLoading) {
                    if (loadingEl) {
                        loadingEl.style.display = isLoading ? 'block' : 'none';
                    }
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    // Panggil fungsi Alpine.js untuk menampilkan modal
                    const rootAlpine = Alpine.$data(calendarEl.closest('[x-data]'));
                    if (rootAlpine && typeof rootAlpine.showDetail === 'function') {
                        rootAlpine.showDetail(info.event.extendedProps);
                    }
                },
                eventDidMount: function(info) {
                    info.el.style.cursor = 'pointer';
                    info.el.title = info.event.title;
                    info.el.classList.add('text-[10px]');
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl) {
                        titleEl.classList.add('truncate', 'overflow-hidden', 'block');
                    }
                },
                height: 'auto',
                aspectRatio: 1.6,
                dayMaxEvents: 3,
                moreLinkText: function(num) {
                    return '+' + num + ' lainnya';
                }
            });

            calendar.render();
            if (loadingEl) loadingEl.style.display = 'none';
        });
    </script>

    <!-- Custom Styling untuk FullCalendar Dark Mode & Mobile Responsiveness -->
    <style>
        .fc {
            font-family: inherit;
        }
        .fc .fc-toolbar-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
        }
        .fc .fc-button {
            border-radius: 0.375rem;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: none !important;
            transition: all 0.15s ease;
        }

        /* 1. Tombol Navigasi Prev/Next (< >) */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            background-color: #0A773A !important;
            border-color: #0A773A !important;
            color: #ffffff !important;
        }
        .fc .fc-prev-button:hover,
        .fc .fc-next-button:hover {
            background-color: #085C2D !important;
            border-color: #085C2D !important;
            color: #ffffff !important;
        }
        .fc .fc-prev-button:disabled,
        .fc .fc-next-button:disabled {
            background-color: #94a3b8 !important;
            border-color: #94a3b8 !important;
            opacity: 0.6;
        }

        /* 2. Tombol "Hari Ini" (Gaya Outline Hijau Dinas) */
        .fc .fc-today-button {
            background-color: transparent !important;
            border: 1px solid #0A773A !important;
            color: #0A773A !important;
            font-weight: 700 !important;
        }
        .fc .fc-today-button:hover:not(:disabled) {
            background-color: rgba(10, 119, 58, 0.08) !important;
            color: #085C2D !important;
            border-color: #085C2D !important;
        }
        .fc .fc-today-button:disabled {
            background-color: transparent !important;
            border-color: #cbd5e1 !important;
            color: #94a3b8 !important;
            opacity: 0.5;
        }
        .dark .fc .fc-today-button {
            border-color: #10b981 !important;
            color: #34d399 !important;
        }
        .dark .fc .fc-today-button:hover:not(:disabled) {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #6ee7b7 !important;
            border-color: #34d399 !important;
        }

        /* 3. Toggle View ("Bulan | Daftar") */
        .fc .fc-button-group {
            display: inline-flex;
            gap: 1px;
            background-color: #e2e8f0;
            padding: 2px;
            border-radius: 0.375rem;
        }
        .dark .fc .fc-button-group {
            background-color: #334155;
        }
        .fc .fc-button-group > .fc-button {
            background-color: transparent !important;
            border: none !important;
            color: #64748b !important;
            border-radius: 0.25rem !important;
            padding: 0.3rem 0.65rem !important;
            font-weight: 600;
        }
        .dark .fc .fc-button-group > .fc-button {
            color: #94a3b8 !important;
        }
        .fc .fc-button-group > .fc-button:hover:not(.fc-button-active) {
            background-color: rgba(255, 255, 255, 0.6) !important;
            color: #1e293b !important;
        }
        .dark .fc .fc-button-group > .fc-button:hover:not(.fc-button-active) {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        /* State Aktif Toggle View */
        .fc .fc-button-group > .fc-button.fc-button-active {
            background-color: #0A773A !important;
            border-color: #0A773A !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
        }

        /* 4. Event Anti-Overflow & Truncate */
        .fc .fc-event {
            border-radius: 3px !important;
            padding: 1px 3px !important;
            font-size: 10px !important;
            font-weight: 600;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.1s ease;
        }
        .fc .fc-event:hover {
            transform: scale(1.005);
            filter: brightness(1.05);
        }
        .fc .fc-event-main {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            font-size: 10px !important;
            line-height: 1.25 !important;
        }
        .fc .fc-event-title {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            display: block !important;
            font-size: 10px !important;
            font-weight: 600 !important;
        }
        .fc .fc-daygrid-event {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }
        .fc .fc-daygrid-day-number {
            font-size: 11px !important;
            padding: 2px 4px !important;
        }

        /* 5. Nama Hari (SEN, SEL, RAB...) */
        .fc-theme-standard th {
            padding: 4px 1px !important;
            font-size: 10px !important;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #64748b !important;
        }
        .fc .fc-col-header-cell-cushion {
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #64748b !important;
            padding: 2px 1px !important;
            text-decoration: none !important;
        }

        /* Highlight Hari Ini */
        .fc .fc-day-today {
            background-color: rgba(10, 119, 58, 0.06) !important;
        }
        .fc .fc-day-today .fc-daygrid-day-number {
            color: #0A773A;
            font-weight: 700;
        }
        .fc .fc-more-link {
            color: #0A773A;
            font-weight: 600;
            font-size: 10px !important;
        }

        /* Dark Mode Overrides */
        .dark .fc-theme-standard th {
            background-color: #0f172a;
            color: #94a3b8 !important;
            border-color: #334155;
        }
        .dark .fc .fc-col-header-cell-cushion {
            color: #94a3b8 !important;
        }
        .dark .fc-theme-standard td {
            border-color: #334155;
        }
        .dark .fc-theme-standard .fc-scrollgrid {
            border-color: #334155;
        }
        .dark .fc .fc-toolbar-title {
            color: #f8fafc;
        }
        .dark .fc .fc-daygrid-day-number {
            color: #94a3b8;
            font-weight: 500;
        }
        .dark .fc .fc-day-today {
            background-color: rgba(10, 119, 58, 0.16) !important;
        }
        .dark .fc .fc-day-today .fc-daygrid-day-number {
            color: #34d399;
            font-weight: 700;
        }
        .dark .fc .fc-more-link {
            color: #34d399;
            font-weight: 600;
        }
        .dark .fc-list-day-cushion {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        .dark .fc-list-event:hover td {
            background-color: #334155 !important;
        }
        .dark .fc-list-event-title a {
            color: #f1f5f9 !important;
        }

        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            .fc .fc-toolbar-title {
                text-align: center;
                font-size: 1rem;
            }
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
            }
        }
    </style>
</x-app-layout>
