<!-- Mobile Backdrop Overlay -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs md:hidden"
     style="display: none;"></div>

<!-- Sidebar Component -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-dinas-green text-white flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0 shrink-0 h-full border-r border-emerald-800/60 shadow-xl md:shadow-none no-scrollbar">

    <!-- Bagian Atas: Header Sidebar -->
    <div class="h-16 px-5 border-b border-white/10 flex items-center justify-between shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center group">
            <img src="{{ asset('images/pa-logo.png') }}" alt="Logo PA" class="w-10 h-10 object-contain mr-3">
            <div>
                <h1 class="font-bold text-sm text-white tracking-wide leading-tight">Sistem Manajemen Cuti</h1>
                <p class="text-[10px] text-emerald-100/80 font-medium">Layanan Administrasi Pegawai</p>
            </div>
        </a>

        <!-- Mobile Close Button (X) -->
        <button type="button" 
                @click="sidebarOpen = false" 
                class="md:hidden p-1.5 rounded-none text-emerald-100 hover:text-white hover:bg-dinas-greenhover focus:outline-none transition"
                title="Tutup Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Bagian Tengah: Menu Navigasi (Konsep Laci / Accordion Terpadu untuk Semua Role) -->
    <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6 no-scrollbar">
        @php
            $initialDrawer = null;
            if (request()->routeIs('cuti.create', 'cuti.riwayat*', 'cuti.kalender*', 'cuti.edit*')) {
                $initialDrawer = 'cuti';
            } elseif (request()->routeIs('cuti.persetujuan*', 'cuti.tinjau*')) {
                $initialDrawer = 'atasan';
            } elseif (request()->routeIs('cuti.daftar_ketua*', 'cuti.tinjau_ketua*', 'cuti.ketua.riwayat*')) {
                $initialDrawer = 'ketua';
            } elseif (request()->routeIs('cuti.penomoran*', 'cuti.input_nomor*')) {
                $initialDrawer = 'penomoran';
            } elseif (request()->routeIs('pegawai.*', 'jabatan.*')) {
                $initialDrawer = 'master';
            } elseif (request()->routeIs('notifications.*', 'profile.*')) {
                $initialDrawer = 'pengaturan';
            }
        @endphp

        <div class="space-y-4" x-data="{ activeDrawer: {{ json_encode($initialDrawer) }} }">
            <p class="px-3 text-[10px] font-bold text-emerald-100/70 uppercase tracking-wider mb-2">
                {{ Auth::user()->role === 'admin' ? 'Menu Admin' : 'Menu Navigasi' }}
            </p>

            <div class="space-y-1.5">
                <!-- Dashboard (Menu Utama Tunggal) -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-none text-xs transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }}">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-dinas-gold' : 'text-emerald-200/80' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Laci 1: Manajemen / Pengajuan Cuti (Semua Role) -->
                @php
                    $cutiActive = request()->routeIs('cuti.create', 'cuti.riwayat*', 'cuti.kalender*', 'cuti.edit*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'cuti' ? null : 'cuti')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $cutiActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'cuti' && !{{ $cutiActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $cutiActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'cuti' || {{ $cutiActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ Auth::user()->role === 'admin' ? 'Manajemen Cuti' : 'Pengajuan Cuti' }}</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $cutiActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'cuti' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'cuti' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'cuti' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('cuti.create') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.create') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Ajukan Cuti Baru</span>
                                </a>
                                <a href="{{ route('cuti.riwayat') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.riwayat*') || request()->routeIs('cuti.edit*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Riwayat Pengajuan</span>
                                </a>
                                @if(Auth::user()->role === 'admin' || Auth::user()->is_pimpinan_final)
                                <a href="{{ route('cuti.kalender') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.kalender*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Kalender Cuti</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laci 2: Persetujuan Atasan Langsung (Jika Memiliki Hak Akses) -->
                @if(Auth::user()->is_atasan_langsung)
                @php
                    $atasanActive = request()->routeIs('cuti.persetujuan*', 'cuti.tinjau*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'atasan' ? null : 'atasan')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $atasanActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'atasan' && !{{ $atasanActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $atasanActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'atasan' || {{ $atasanActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Persetujuan Atasan</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $atasanActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'atasan' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'atasan' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'atasan' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('cuti.persetujuan') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.persetujuan') || request()->routeIs('cuti.tinjau*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Antrean Persetujuan</span>
                                </a>
                                <a href="{{ route('cuti.persetujuan.riwayat') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.persetujuan.riwayat*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Riwayat Persetujuan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Laci 3: Pengesahan Ketua PA (Jika Memiliki Hak Akses) -->
                @if(Auth::user()->is_pimpinan_final)
                @php
                    $ketuaActive = request()->routeIs('cuti.daftar_ketua*', 'cuti.tinjau_ketua*', 'cuti.ketua.riwayat*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'ketua' ? null : 'ketua')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $ketuaActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'ketua' && !{{ $ketuaActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $ketuaActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'ketua' || {{ $ketuaActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <span>Pengesahan Ketua</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $ketuaActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'ketua' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'ketua' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'ketua' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('cuti.daftar_ketua') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.daftar_ketua') || request()->routeIs('cuti.tinjau_ketua*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Antrean Pengesahan</span>
                                </a>
                                <a href="{{ route('cuti.ketua.riwayat') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.ketua.riwayat*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Riwayat Pengesahan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Laci 4: Penomoran Surat (Khusus Admin) -->
                @if(Auth::user()->role === 'admin')
                @php
                    $penomoranActive = request()->routeIs('cuti.penomoran*', 'cuti.input_nomor*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'penomoran' ? null : 'penomoran')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $penomoranActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'penomoran' && !{{ $penomoranActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $penomoranActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'penomoran' || {{ $penomoranActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                            <span>Penomoran Surat</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $penomoranActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'penomoran' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'penomoran' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'penomoran' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('cuti.penomoran') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.penomoran') || request()->routeIs('cuti.input_nomor*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Antrean Penomoran</span>
                                </a>
                                <a href="{{ route('cuti.penomoran.riwayat') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('cuti.penomoran.riwayat*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Riwayat Penomoran</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Laci 5: Master Data (Khusus Admin) -->
                @if(Auth::user()->role === 'admin')
                @php
                    $masterActive = request()->routeIs('pegawai.*', 'jabatan.*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'master' ? null : 'master')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $masterActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'master' && !{{ $masterActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $masterActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'master' || {{ $masterActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Master Data</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $masterActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'master' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'master' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'master' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('pegawai.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('pegawai.*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <span>Data Pegawai & Akun</span>
                                </a>
                                <a href="{{ route('jabatan.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('jabatan.*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span>Manajemen Jabatan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Laci 6: Pengaturan Akun & Notifikasi (Semua Role) -->
                @php
                    $pengaturanActive = request()->routeIs('notifications.*', 'profile.*');
                @endphp
                <div class="space-y-1">
                    <button type="button" 
                            @click="activeDrawer = (activeDrawer === 'pengaturan' ? null : 'pengaturan')" 
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-none text-xs transition duration-150 {{ $pengaturanActive ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-l-4 border-dinas-gold shadow-xs' : 'text-emerald-100 hover:text-white hover:bg-dinas-greenhover/70' }} group focus:outline-none"
                            :class="{ 'bg-dinas-greenhover/40 text-white': activeDrawer === 'pengaturan' && !{{ $pengaturanActive ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 transition-colors {{ $pengaturanActive ? 'text-dinas-gold' : 'text-emerald-200/80 group-hover:text-white' }}" 
                                 :class="{ 'text-dinas-gold': activeDrawer === 'pengaturan' || {{ $pengaturanActive ? 'true' : 'false' }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Pengaturan</span>
                        </div>
                        <svg class="w-4 h-4 shrink-0 transition-transform duration-300 ease-in-out {{ $pengaturanActive ? 'text-dinas-gold' : 'text-emerald-200/80' }}"
                             :class="{ 'rotate-180': activeDrawer === 'pengaturan' }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="accordion-content {{ $initialDrawer === 'pengaturan' ? 'is-open' : '' }}"
                         :class="{ 'is-open': activeDrawer === 'pengaturan' }">
                        <div class="accordion-inner">
                            <div class="space-y-1 pl-6 pr-1 pt-1 pb-1">
                                <a href="{{ route('notifications.index') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('notifications.*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span>Riwayat Notifikasi</span>
                                </a>
                                <a href="{{ route('profile.edit') }}" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 {{ request()->routeIs('profile.*') ? 'bg-dinas-greenhover text-dinas-gold font-semibold border-dinas-gold shadow-xs' : 'border-emerald-700/60 text-emerald-100/80 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Profil Pengguna</span>
                                </a>

                                <!-- Switch Mode Gelap (ON / OFF) -->
                                <button type="button" 
                                        x-data="{ isDark: document.documentElement.classList.contains('dark') }"
                                        @click="toggleTheme(); isDark = document.documentElement.classList.contains('dark')"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-none text-xs transition duration-150 border-l-2 border-emerald-700/60 text-emerald-100 hover:text-white hover:bg-dinas-greenhover/60 hover:border-emerald-300 cursor-pointer select-none text-left"
                                        role="switch"
                                        :aria-checked="isDark"
                                        title="Mode Gelap: Aktif / Nonaktif">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-emerald-200/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                        </svg>
                                        <span>Mode Gelap:</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] font-bold tracking-wider" 
                                              :class="isDark ? 'text-dinas-gold' : 'text-emerald-200'" 
                                              x-text="isDark ? 'ON' : 'OFF'"><span class="block dark:hidden">OFF</span><span class="hidden dark:block">ON</span></span>
                                        <span class="relative inline-flex h-4 w-7 shrink-0 rounded-full border border-transparent transition-colors duration-200 ease-in-out bg-dinas-greenhover dark:bg-emerald-950"
                                              :class="isDark ? 'bg-dinas-greenhover border border-dinas-gold/40' : 'bg-black/30'">
                                            <span class="pointer-events-none inline-block h-3 w-3 transform rounded-full bg-white shadow-xs transition duration-200 ease-in-out translate-x-0.5 dark:translate-x-3.5"
                                                  :class="isDark ? 'translate-x-3.5 bg-dinas-gold' : 'translate-x-0.5'"></span>
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Bawah: Profil & Logout -->
    <div class="p-3.5 border-t border-white/10 bg-dinas-greenhover/60 shrink-0">
        <div class="px-2 py-1.5 min-w-0">
            <p class="text-xs font-semibold text-white truncate leading-tight">
                {{ Auth::user()->name }}
            </p>
            <p class="text-[11px] text-emerald-100/80 truncate mt-0.5">
                {{ Auth::user()->pegawai?->jabatan ?? (Auth::user()->role === 'admin' ? 'Administrator' : 'Pegawai') }}
            </p>
        </div>

        <!-- Tombol Logout -->
        <form method="POST" action="{{ route('logout') }}" class="mt-2.5"
              id="logout-form"
              data-confirm="true"
              data-confirm-title="Konfirmasi Keluar"
              data-confirm-message="Apakah Anda yakin ingin keluar dari Sistem Manajemen Cuti?"
              data-confirm-btn="Ya, Keluar"
              data-confirm-cancel="Batal"
              data-confirm-type="warning">
            @csrf
            <input type="hidden" name="endpoint" id="logout-push-endpoint" value="">
            <button type="submit" 
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-red-300 hover:text-red-100 hover:bg-red-500/20 rounded-none transition duration-150">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Keluar (Log-out)</span>
            </button>
        </form>
    </div>
</aside>
