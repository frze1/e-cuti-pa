<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Manajemen Jabatan & Struktur Organisasi') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola pohon hierarki jabatan instansi, atasan langsung, serta kewenangan persetujuan cuti
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6" x-data="{
        modalBawahanOpen: false,
        selectedJabatan: '',
        listBawahan: [],
        bukaModalBawahan(namaJabatan, bawahan) {
            this.selectedJabatan = namaJabatan;
            this.listBawahan = bawahan || [];
            this.modalBawahanOpen = true;
        }
    }">
        <!-- Ringkasan Statistik Status Jabatan (Desktop & Tablet) -->
        <div class="hidden md:grid md:grid-cols-3 md:gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-none p-4 md:p-5 border border-slate-300 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 border border-dinas-green/20 dark:border-dinas-green/30 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-slate-800 dark:text-white leading-tight">{{ $countTotal }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-tight mt-0.5">Total Master Jabatan</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-none p-4 md:p-5 border border-slate-300 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-none bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400 leading-tight">{{ $countStruktural }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-tight mt-0.5">Jabatan Struktural</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-none p-4 md:p-5 border border-slate-300 dark:border-slate-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-none bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-bold text-slate-700 dark:text-slate-200 leading-tight">{{ $countNonStruktural }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-tight mt-0.5">Fungsional / Pelaksana</div>
                </div>
            </div>
        </div>

        <!-- Komponen Single Inline Summary Khusus Mobile -->
        <div class="block md:hidden mb-4">
            <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-md py-2 px-3 text-center text-xs text-slate-600 dark:text-slate-300 flex justify-center items-center flex-wrap gap-x-2 gap-y-1">
                <span>Total: <strong class="text-slate-800 dark:text-white">{{ $countTotal }}</strong></span>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <span>Struktural: <strong class="text-amber-600 dark:text-amber-400">{{ $countStruktural }}</strong></span>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <span>Fungsional: <strong class="text-slate-800 dark:text-white">{{ $countNonStruktural }}</strong></span>
            </div>
        </div>

        @php
            $hasActiveFilters = (request()->filled('struktural') && request('struktural') !== 'all') 
                || (request()->has('sort') && request('sort') !== 'struktural_desc' && request('sort') !== 'terbaru');
        @endphp

        <!-- Bar Pencarian & Filter Terpadu (Unified Search & Filter Bar) -->
        <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                
                <!-- Kotak Pencarian Tunggal Interaktif dengan Alpine.js Popover -->
                <div x-data="{ openFilter: false }" @click.outside="openFilter = false" class="relative flex-1 min-w-0">
                    <form id="jabatanFilterForm" method="GET" action="{{ route('jabatan.index') }}">
                        <div class="relative flex items-center">
                            <!-- Ikon Kaca Pembesar di Kiri -->
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>

                            <!-- Input Pencarian Tunggal -->
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari nama jabatan, atasan, atau aparatur pemegang..."
                                @focus="openFilter = true"
                                @click="openFilter = true"
                                @keydown.escape="openFilter = false"
                                autocomplete="off"
                                class="w-full pl-10 pr-24 py-2 md:py-2.5 text-xs md:text-sm rounded-none border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-1 focus:ring-dinas-green focus:border-dinas-green transition">

                            <!-- Elemen Kanan Dalam Input: Reset, Filter Toggles & Tombol Cari -->
                            <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                                @if(request()->filled('q') || $hasActiveFilters)
                                    <a href="{{ route('jabatan.index') }}" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition" title="Reset Semua Filter">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif

                                <!-- Ikon Filter / Toggles Popover -->
                                <button type="button" @click="openFilter = !openFilter"
                                    class="relative inline-flex items-center gap-1 px-1.5 py-1 text-xs font-medium text-slate-600 hover:text-dinas-green dark:text-slate-300 dark:hover:text-dinas-gold rounded transition"
                                    title="Buka / Tutup Filter Lanjutan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    @if($hasActiveFilters)
                                        <span class="w-1.5 h-1.5 rounded-full bg-dinas-green animate-pulse"></span>
                                    @endif
                                    <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Tombol Cari Utama -->
                                <button type="submit" class="py-1 px-3 bg-dinas-green hover:bg-dinas-greenhover text-white font-semibold text-xs rounded-none transition">
                                    Cari
                                </button>
                            </div>
                        </div>

                        <!-- Panel Filter Lanjutan (Accordion di Mobile, Popover Absolut di Desktop) -->
                        <div x-show="openFilter"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="static md:absolute md:left-0 md:right-0 md:top-full mt-3 md:mt-2 z-30 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 shadow-lg md:shadow-xl rounded-none p-3 sm:p-5"
                            style="display: none;">

                            <!-- Bagian 1: Urutkan Berdasarkan -->
                            <div class="mb-3">
                                <label class="block font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-2">
                                    Urutkan Berdasarkan
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 sm:gap-2">
                                    <label class="flex items-center gap-2 p-1.5 sm:p-2 border border-slate-200 dark:border-slate-700 rounded-none cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        <input type="radio" name="sort" value="bawahan_desc" {{ request('sort') === 'bawahan_desc' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Jumlah Bawahan (Terbanyak - Terdikit)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-1.5 sm:p-2 border border-slate-200 dark:border-slate-700 rounded-none cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        <input type="radio" name="sort" value="terbaru" {{ (request('sort') === 'terbaru' || !request()->has('sort')) ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Terbaru Ditambahkan</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-1.5 sm:p-2 border border-slate-200 dark:border-slate-700 rounded-none cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        <input type="radio" name="sort" value="nama_asc" {{ request('sort') === 'nama_asc' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Alfabetis Nama Jabatan (A-Z)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Bagian 2: Filter Berdasarkan Kewenangan Approval -->
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
                                <label class="block font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-2">
                                    Filter Berdasarkan
                                </label>
                                <div class="bg-slate-50/70 dark:bg-slate-900/50 p-2.5 sm:p-3 border border-slate-200 dark:border-slate-700/70">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                                        Kewenangan Approval
                                    </span>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 sm:gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                                            <input type="radio" name="struktural" value="all" {{ (!request()->filled('struktural') || request('struktural') === 'all') ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                            <span>Semua Tipe Jabatan</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                                            <input type="radio" name="struktural" value="1" {{ request('struktural') === '1' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                            <span>Struktural (Penyetuju)</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                                            <input type="radio" name="struktural" value="0" {{ request('struktural') === '0' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                            <span>Fungsional / Non-Atasan</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Toolbar Popover -->
                            <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 pt-3 mt-3">
                                <div>
                                    @if(request()->filled('q') || $hasActiveFilters)
                                        <a href="{{ route('jabatan.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 transition">
                                            ✕ Reset Filter
                                        </a>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openFilter = false" class="py-1.5 px-3 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold rounded-none transition">
                                        Tutup
                                    </button>
                                    <button type="submit" class="py-1.5 px-4 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none shadow-xs transition-colors">
                                        Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah Jabatan Baru -->
                <div class="shrink-0 flex justify-end">
                    <a href="{{ route('jabatan.create', request()->query()) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2 md:py-2.5 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none border border-transparent shadow-xs transition-colors">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Jabatan Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 1. Tampilan Desktop: Tabel HTML Murni (hidden md:block) -->
        <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto border-t border-slate-200 dark:border-slate-700">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-left border-collapse text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-300 dark:border-slate-700 bg-slate-50/75 dark:bg-slate-900/50 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="w-[24%] py-3 px-3 md:px-4 text-left">Nama Jabatan</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Kewenangan Approval</th>
                            <th class="w-[22%] py-3 px-3 md:px-4 text-left">Jabatan Atasan Langsung</th>
                            <th class="w-[20%] py-3 px-3 md:px-4 text-left">Aparatur Pemegang Saat Ini</th>
                            <th class="w-[8%] py-3 px-3 md:px-4 text-center">Bawahan</th>
                            <th class="w-[8%] py-3 px-3 md:px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-xs">
                        @forelse($jabatans as $jbt)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <!-- Nama Jabatan -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <div class="font-bold text-slate-800 dark:text-white">
                                        {{ $jbt->nama_jabatan }}
                                    </div>
                                </td>

                                <!-- Status Kewenangan Struktural -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    @if($jbt->is_struktural)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-none bg-amber-500"></span>
                                            Struktural (Penyetuju)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                            <span class="w-1.5 h-1.5 rounded-none bg-slate-400"></span>
                                            Fungsional / Non-Atasan
                                        </span>
                                    @endif
                                </td>

                                <!-- Jabatan Atasan Langsung -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    @if($jbt->parent)
                                        <div class="flex items-start gap-1 font-medium text-slate-700 dark:text-slate-200">
                                            <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                            <span class="break-words">{{ $jbt->parent->nama_jabatan }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">
                                            -- Pimpinan Tertinggi --
                                        </span>
                                    @endif
                                </td>

                                <!-- Aparatur Pemegang Saat Ini -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    @php
                                        $pemegangAktif = $jbt->pegawai_aktif;
                                    @endphp
                                    @if($pemegangAktif && $pemegangAktif->user)
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            {{ $pemegangAktif->user->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            NIP: {{ $pemegangAktif->nip ?: '-' }}
                                        </div>
                                    @else
                                        <span class="inline-block text-amber-600 dark:text-amber-400 italic text-[10px] bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded-none border border-amber-300 dark:border-amber-900/60">
                                            Belum Ada Pejabat Aktif
                                        </span>
                                    @endif
                                </td>

                                @php
                                    $bawahanData = $jbt->children->map(function ($c) {
                                        $peg = $c->pegawai_aktif;
                                        return [
                                            'nama_jabatan' => $c->nama_jabatan,
                                            'is_struktural' => (bool)$c->is_struktural,
                                            'nama_pejabat' => $peg?->user?->name ?? null,
                                            'nip' => $peg?->nip ?? null,
                                        ];
                                    })->values();
                                    $jmlBawahan = $jbt->children_count ?? $jbt->children->count();
                                @endphp
                                <!-- Jumlah Bawahan Langsung (Tombol Trigger Modal) -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    @if($jmlBawahan > 0)
                                        <button type="button"
                                            @click="bukaModalBawahan({{ json_encode($jbt->nama_jabatan) }}, {{ json_encode($bawahanData) }})"
                                            class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-none text-xs font-bold border border-dinas-green/20 dark:border-dinas-green/30 bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 cursor-pointer hover:bg-dinas-green hover:text-white dark:hover:bg-dinas-green dark:hover:text-white transition-all shadow-xs"
                                            title="Lihat {{ $jmlBawahan }} Jabatan Bawahan">
                                            {{ $jmlBawahan }}
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-none text-xs font-bold border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed"
                                            title="Tidak memiliki jabatan bawahan">
                                            0
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <a href="{{ route('jabatan.edit', array_merge(['jabatan' => $jbt->id], request()->query())) }}" 
                                           class="p-1 rounded-none border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-dinas-green hover:bg-dinas-green/10 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition" 
                                           title="Edit Struktur Jabatan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('jabatan.destroy', array_merge(['jabatan' => $jbt->id], request()->query())) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan \'{{ addslashes($jbt->nama_jabatan) }}\'? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            @foreach(request()->query() as $queryKey => $queryVal)
                                                @if(!empty($queryVal))
                                                    <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" 
                                                    class="p-1 rounded-none border border-rose-300 dark:border-rose-800 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" 
                                                    title="Hapus Jabatan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="font-semibold">Belum ada data jabatan yang ditemukan.</p>
                                    <p class="text-xs mt-1">Silakan tambahkan master jabatan baru ke dalam sistem.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Tampilan Mobile: Model "List of Cards" (block md:hidden) -->
        <div class="block md:hidden flex flex-col gap-4">
            @forelse($jabatans as $jbt)
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                    <!-- Header Card: Nama Jabatan dan Ikon Aksi (Edit, Hapus) -->
                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-base break-words">
                                {{ $jbt->nama_jabatan }}
                            </h3>
                        </div>

                        <!-- Ikon Aksi (Edit, Hapus) sejajar di sisi kanan -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('jabatan.edit', array_merge(['jabatan' => $jbt->id], request()->query())) }}" 
                               class="p-1.5 rounded-md border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-dinas-green hover:bg-dinas-green/10 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition" 
                               title="Edit Struktur Jabatan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>

                            <form action="{{ route('jabatan.destroy', array_merge(['jabatan' => $jbt->id], request()->query())) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan \'{{ addslashes($jbt->nama_jabatan) }}\'? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                @foreach(request()->query() as $queryKey => $queryVal)
                                    @if(!empty($queryVal))
                                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                                    @endif
                                @endforeach
                                <button type="submit" 
                                        class="p-1.5 rounded-md border border-rose-300 dark:border-rose-800 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" 
                                        title="Hapus Jabatan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Bagian Konten Card (Susunan Baris Flex Dua Kolom Gaya Web Dinas) -->
                    <div class="mt-1 divide-y divide-slate-100 dark:divide-slate-700/60">
                        <!-- Baris 1: Kewenangan (kiri) - Badge Struktural/Fungsional (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Kewenangan</span>
                            <div>
                                @if($jbt->is_struktural)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-none bg-amber-500"></span>
                                        Struktural (Penyetuju)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-none bg-slate-400"></span>
                                        Fungsional / Non-Atasan
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 2: Atasan Langsung (kiri) - Nama Jabatan Atasan (kanan, dengan ikon panah ke atas jika ada) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Atasan Langsung</span>
                            <div class="text-right">
                                @if($jbt->parent)
                                    <div class="flex items-center justify-end gap-1 font-medium text-slate-800 dark:text-slate-100">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                        </svg>
                                        <span class="break-words">{{ $jbt->parent->nama_jabatan }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic text-xs">
                                        -- Pimpinan Tertinggi --
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 3: Pejabat Saat Ini (kiri) - Nama Aparatur & NIP (kanan, buat teks rata kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Pejabat Saat Ini</span>
                            <div class="text-right">
                                @php
                                    $pemegangAktif = $jbt->pegawai_aktif;
                                @endphp
                                @if($pemegangAktif && $pemegangAktif->user)
                                    <div class="font-medium text-slate-800 dark:text-slate-100">
                                        {{ $pemegangAktif->user->name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        NIP: {{ $pemegangAktif->nip ?: '-' }}
                                    </div>
                                @else
                                    <span class="inline-block text-amber-600 dark:text-amber-400 italic text-[10px] bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded-none border border-amber-300 dark:border-amber-900/60">
                                        Belum Ada Pejabat Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        @php
                            $bawahanDataMobile = $jbt->children->map(function ($c) {
                                $peg = $c->pegawai_aktif;
                                return [
                                    'nama_jabatan' => $c->nama_jabatan,
                                    'is_struktural' => (bool)$c->is_struktural,
                                    'nama_pejabat' => $peg?->user?->name ?? null,
                                    'nip' => $peg?->nip ?? null,
                                ];
                            })->values();
                            $jmlBawahanMobile = $jbt->children_count ?? $jbt->children->count();
                        @endphp
                        <!-- Baris 4: Bawahan (kiri) - Angka/Tombol Jumlah Bawahan (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Bawahan</span>
                            <div>
                                @if($jmlBawahanMobile > 0)
                                    <button type="button"
                                        @click="bukaModalBawahan({{ json_encode($jbt->nama_jabatan) }}, {{ json_encode($bawahanDataMobile) }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border border-dinas-green/20 dark:border-dinas-green/30 bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 cursor-pointer hover:bg-dinas-green hover:text-white dark:hover:bg-dinas-green dark:hover:text-white transition-all shadow-xs"
                                        title="Lihat {{ $jmlBawahanMobile }} Jabatan Bawahan">
                                        <span>{{ $jmlBawahanMobile }} Bawahan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @else
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                        0 Bawahan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 rounded-lg p-8 text-center text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700">
                    <p class="font-semibold text-sm">Belum ada data jabatan yang ditemukan.</p>
                    <p class="text-xs mt-1">Silakan tambahkan master jabatan baru ke dalam sistem.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination (Bersama Desktop & Mobile) -->
        @if($jabatans->hasPages())
            <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-4">
                {{ $jabatans->links() }}
            </div>
        @endif

        <!-- Komponen Modal Pop-up Daftar Bawahan (Diteleport ke <body> agar overlay menutupi 100% viewport termasuk header & sidebar) -->
        <template x-teleport="body">
            <div x-show="modalBawahanOpen"
                 x-cloak
                 class="relative z-[999]"
                 aria-labelledby="modal-bawahan-title"
                 role="dialog"
                 aria-modal="true">

                <!-- 1. Pembungkus Latar Gelap (Backdrop) -->
                <div x-show="modalBawahanOpen"
                     @click="modalBawahanOpen = false"
                     x-transition:enter="transition-opacity ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 bg-black/50 transition-opacity"
                     style="display: none;"></div>

                <!-- 2. Pembungkus Konten Modal (Kotak Putih) -->
                <div x-show="modalBawahanOpen"
                     @click="modalBawahanOpen = false"
                     @keydown.escape.window="modalBawahanOpen = false"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
                     style="display: none;">

                    <div @click.stop
                         x-show="modalBawahanOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col my-8">
                        
                        <!-- Header Modal -->
                        <div class="bg-slate-50 dark:bg-slate-900/60 px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 id="modal-bawahan-title" class="text-sm md:text-base font-bold text-slate-800 dark:text-white truncate">
                                    Daftar Bawahan &mdash; <span x-text="selectedJabatan" class="text-dinas-green dark:text-emerald-400"></span>
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Daftar posisi jabatan yang berada langsung di bawah jabatan ini
                                </p>
                            </div>
                            <button type="button" 
                                    @click="modalBawahanOpen = false"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 dark:hover:text-slate-200 dark:hover:bg-slate-700 transition cursor-pointer shrink-0"
                                    aria-label="Tutup modal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Isi / Konten Modal (List View) -->
                        <div class="p-5 max-h-[60vh] overflow-y-auto">
                            <!-- State jika tidak ada bawahan -->
                            <template x-if="listBawahan.length === 0">
                                <div class="py-8 text-center text-slate-400 dark:text-slate-500">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada jabatan bawahan langsung.</p>
                                </div>
                            </template>

                            <!-- List Vertikal Bawahan -->
                            <ul class="flex flex-col gap-2.5" x-show="listBawahan.length > 0">
                                <template x-for="(bawahan, idx) in listBawahan" :key="idx">
                                    <li class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 shadow-xs flex flex-col gap-1.5 hover:border-dinas-green/30 transition">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="font-bold text-sm text-slate-800 dark:text-slate-100 break-words" x-text="bawahan.nama_jabatan"></span>
                                            <template x-if="bawahan.is_struktural">
                                                <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                    Struktural
                                                </span>
                                            </template>
                                            <template x-if="!bawahan.is_struktural">
                                                <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                                    Fungsional
                                                </span>
                                            </template>
                                        </div>
                                        <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100 dark:border-slate-700/60">
                                            <span class="text-slate-400 dark:text-slate-500">Pejabat Saat Ini:</span>
                                            <div class="text-right">
                                                <template x-if="bawahan.nama_pejabat">
                                                    <div>
                                                        <span class="font-medium text-slate-700 dark:text-slate-200" x-text="bawahan.nama_pejabat"></span>
                                                        <template x-if="bawahan.nip">
                                                            <span class="block text-[10px] text-slate-400 font-mono" x-text="'NIP: ' + bawahan.nip"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="!bawahan.nama_pejabat">
                                                    <span class="text-amber-600 dark:text-amber-400 italic text-[11px] bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-900/60">
                                                        [Belum Terisi]
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Footer Modal -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <button type="button" 
                                    @click="modalBawahanOpen = false"
                                    class="w-full sm:w-auto px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
