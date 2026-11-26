<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Data Pegawai & Akun Pengguna') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola data identitas aparatur, jabatan, unit kerja, serta kredensial akun login
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">

        @php
            $hasActiveFilters = request()->filled('role') 
                || request()->filled('gender') 
                || request()->filled('struktural') 
                || (request()->has('status') && request('status') !== 'Aktif') 
                || (request()->has('sort') && request('sort') !== 'terbaru');
        @endphp

        <!-- Bar Pencarian & Filter Terpadu (Unified Search & Filter Bar) -->
        <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                
                <!-- Kotak Pencarian Tunggal Interaktif dengan Alpine.js Popover -->
                <div x-data="{ openFilter: false }" @click.outside="openFilter = false" class="relative flex-1 min-w-0">
                    <form id="pegawaiFilterForm" method="GET" action="{{ route('pegawai.index') }}">
                        <div class="relative flex items-center">
                            <!-- Ikon Kaca Pembesar di Kiri -->
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>

                            <!-- Input Pencarian Tunggal -->
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Cari nama, NIP, email, atau jabatan..."
                                @focus="openFilter = true"
                                @click="openFilter = true"
                                @keydown.escape="openFilter = false"
                                autocomplete="off"
                                class="w-full pl-10 pr-24 py-2 md:py-2.5 text-xs md:text-sm rounded-none border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-1 focus:ring-dinas-green focus:border-dinas-green transition">

                            <!-- Elemen Kanan Dalam Input: Ikon Filter Toggles & Tombol Cari -->
                            <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                                <!-- Reset Cepat jika ada query atau filter aktif -->
                                @if(request()->filled('q') || $hasActiveFilters)
                                    <a href="{{ route('pegawai.index') }}" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition" title="Reset Semua Filter">
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
                                        <span class="w-1.5 h-1.5 rounded-full bg-dinas-green dark:bg-dinas-gold animate-pulse"></span>
                                    @endif
                                    <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Tombol Cari Utama -->
                                <button type="submit" class="py-1 px-3 bg-dinas-green hover:bg-dinas-greenhover text-white font-semibold text-xs rounded-none transition-colors">
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
                                        <input type="radio" name="sort" value="terbaru" {{ request('sort', 'terbaru') === 'terbaru' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Terbaru Ditambahkan</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-1.5 sm:p-2 border border-slate-200 dark:border-slate-700 rounded-none cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        <input type="radio" name="sort" value="nip_asc" {{ request('sort') === 'nip_asc' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">NIP (Terkecil-Terbesar)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-1.5 sm:p-2 border border-slate-200 dark:border-slate-700 rounded-none cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                        <input type="radio" name="sort" value="cuti_asc" {{ request('sort') === 'cuti_asc' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Sisa Saldo Cuti (Terdikit)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Bagian 2: Filter Berdasarkan Kategori -->
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
                                <label class="block font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-2">
                                    Filter Berdasarkan
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
                                    <!-- A. Role / Hak Akses -->
                                    <div class="bg-slate-50/70 dark:bg-slate-900/50 p-2.5 sm:p-3 border border-slate-200 dark:border-slate-700/70">
                                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                            Role / Hak Akses
                                        </span>
                                        <div class="space-y-1">
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="role" value="" {{ !request()->filled('role') ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Semua Role</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="role" value="pegawai" {{ request('role') === 'pegawai' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Pegawai</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="role" value="admin" {{ request('role') === 'admin' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Admin</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- B. Jenis Kelamin -->
                                    <div class="bg-slate-50/70 dark:bg-slate-900/50 p-2.5 sm:p-3 border border-slate-200 dark:border-slate-700/70">
                                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                            Jenis Kelamin
                                        </span>
                                        <div class="space-y-1">
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="gender" value="" {{ !request()->filled('gender') ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Semua</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="gender" value="L" {{ request('gender') === 'L' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Laki-laki</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="gender" value="P" {{ request('gender') === 'P' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Perempuan</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- C. Jabatan Struktural / Non-Struktural -->
                                    <div class="bg-slate-50/70 dark:bg-slate-900/50 p-2.5 sm:p-3 border border-slate-200 dark:border-slate-700/70">
                                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                            Tipe Jabatan
                                        </span>
                                        <div class="space-y-1">
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="struktural" value="" {{ !request()->filled('struktural') ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Semua Jabatan</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="struktural" value="struktural" {{ request('struktural') === 'struktural' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Struktural</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="struktural" value="non_struktural" {{ request('struktural') === 'non_struktural' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Non-Struktural</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- D. Status Kepegawaian -->
                                    <div class="bg-slate-50/70 dark:bg-slate-900/50 p-2.5 sm:p-3 border border-slate-200 dark:border-slate-700/70">
                                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                                            Status Pegawai
                                        </span>
                                        <div class="space-y-1">
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="status" value="Aktif" {{ ($statusFilter ?? 'Aktif') === 'Aktif' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Aktif ({{ $countAktif }})</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="status" value="Pensiun" {{ ($statusFilter ?? '') === 'Pensiun' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Pensiun ({{ $countPensiun }})</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="status" value="Mutasi" {{ ($statusFilter ?? '') === 'Mutasi' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Mutasi ({{ $countMutasi }})</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="status" value="Berhenti" {{ ($statusFilter ?? '') === 'Berhenti' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Berhenti ({{ $countBerhenti }})</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 sm:gap-2 cursor-pointer text-[11px] sm:text-xs text-slate-700 dark:text-slate-300">
                                                <input type="radio" name="status" value="semua" {{ ($statusFilter ?? '') === 'semua' ? 'checked' : '' }} class="w-3.5 h-3.5 text-dinas-green focus:ring-dinas-green">
                                                <span>Semua ({{ $countSemua }})</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Toolbar Popover -->
                            <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 pt-3 mt-3">
                                <div>
                                    @if(request()->filled('q') || $hasActiveFilters)
                                        <a href="{{ route('pegawai.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 transition">
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

                <!-- Tombol Tambah Pegawai Baru -->
                <div class="shrink-0 flex justify-end">
                    <a href="{{ route('pegawai.create', request()->query()) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2 md:py-2.5 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none border border-transparent shadow-xs transition-colors">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Pegawai Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 1. Tampilan Desktop: Tabel HTML Murni (hidden md:block) -->
        <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto border-t border-slate-200 dark:border-slate-700">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-left text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                        <tr>
                            <th class="w-[22%] py-3 px-3 md:px-4 text-left">Aparatur / Akun</th>
                            <th class="w-[10%] py-3 px-3 md:px-4 text-center">Status</th>
                            <th class="w-[16%] py-3 px-3 md:px-4 text-left">NIP & Masa Kerja</th>
                            <th class="w-[22%] py-3 px-3 md:px-4 text-left">Jabatan</th>
                            <th class="w-[14%] py-3 px-3 md:px-4 text-left">Atasan Langsung</th>
                            <th class="w-[8%] py-3 px-3 md:px-4 text-center">Saldo (N)</th>
                            <th class="w-[8%] py-3 px-3 md:px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($pegawais as $p)
                            @php
                                $statusBadge = match($p->status_pegawai) {
                                    'Aktif' => 'bg-dinas-green/10 text-dinas-green dark:bg-dinas-green/20 dark:text-emerald-400 font-bold border border-dinas-green/20',
                                    'Pensiun' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                                    'Mutasi' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                                    'Berhenti' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition">
                                <!-- Profil & Akun -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white break-words">
                                            {{ $p->user->name ?? '-' }}
                                        </div>
                                        <div class="mt-0.5">
                                            @if($p->user?->role === 'admin')
                                                <span class="inline-block px-1.5 py-0.5 text-[9px] font-bold rounded-none bg-dinas-green/10 text-dinas-green dark:bg-dinas-green/20 dark:text-emerald-400 border border-dinas-green/20">
                                                    ADMIN
                                                </span>
                                            @else
                                                <span class="inline-block px-1.5 py-0.5 text-[9px] font-medium rounded-none bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                                    PEGAWAI
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 break-all mt-0.5">
                                            {{ $p->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Kepegawaian -->
                                <td class="py-3 px-3 md:px-4 text-xs whitespace-normal break-words align-top text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none font-bold text-[10px] border {{ $statusBadge }}">
                                        <span class="w-1.5 h-1.5 rounded-none {{ $p->status_pegawai === 'Aktif' ? 'bg-dinas-green' : ($p->status_pegawai === 'Pensiun' ? 'bg-blue-500' : ($p->status_pegawai === 'Mutasi' ? 'bg-amber-500' : 'bg-rose-500')) }}"></span>
                                        {{ $p->status_pegawai ?? 'Aktif' }}
                                    </span>
                                </td>

                                <!-- NIP & Masa Kerja -->
                                <td class="py-3 px-3 md:px-4 text-xs whitespace-normal break-words align-top text-left">
                                    <div class="font-mono text-slate-800 dark:text-slate-200 font-semibold break-all">
                                        {{ $p->nip ?: '-' }}
                                    </div>
                                    @if($p->jenis_kelamin)
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ $p->jenis_kelamin }}
                                        </div>
                                    @endif
                                    <div class="text-slate-500 dark:text-slate-400 mt-0.5 text-[11px]">
                                        MK: {{ $p->masa_kerja ?: '-' }}
                                        @if($p->tanggal_masuk)
                                            <span class="block text-[10px] text-slate-400 dark:text-slate-500">TMT Pegawai: {{ $p->tanggal_masuk->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Jabatan -->
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $p->jabatan?->nama_jabatan ?? '-' }}
                                    </div>
                                    @if($p->is_atasan_langsung)
                                        <div class="mt-1">
                                            <span class="inline-block px-1.5 py-0.5 text-[9px] font-bold rounded-none bg-dinas-gold/20 text-yellow-800 dark:text-yellow-400 border border-yellow-300/60 dark:border-yellow-700/60">
                                                STRUKTURAL
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Atasan Langsung (Berdasarkan Hierarki Jabatan) -->
                                <td class="py-3 px-3 md:px-4 text-xs whitespace-normal break-words align-top text-left">
                                    @if($p->jabatan?->parent)
                                        <div class="font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $p->atasan?->user->name ?? '(Belum Ada)' }}
                                        </div>
                                        <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                                            {{ $p->jabatan->parent->nama_jabatan }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">Pimpinan Utama / Tanpa Atasan</span>
                                    @endif
                                </td>

                                <!-- Kuota Cuti N -->
                                <td class="py-3 px-3 md:px-4 text-center whitespace-normal break-words align-top">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-none text-xs font-bold bg-dinas-green/10 text-dinas-green dark:text-emerald-400 border border-dinas-green/20">
                                        {{ $p->sisa_cuti_n }} Hari
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        N-1: {{ $p->sisa_cuti_n_1 }}<br>N-2: {{ $p->sisa_cuti_n_2 }}
                                    </div>
                                </td>

                                <!-- Aksi (Detail, Edit, Hapus) -->
                                <td class="py-3 px-3 md:px-4 text-center whitespace-normal break-words align-top">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Detail -->
                                        <a href="{{ route('pegawai.show', array_merge(['pegawai' => $p->id], request()->query())) }}" class="p-1 rounded-none border border-slate-300 dark:border-slate-600 text-slate-600 hover:text-dinas-green hover:bg-dinas-green/10 dark:text-slate-400 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition" title="Lihat Detail">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('pegawai.edit', array_merge(['pegawai' => $p->id], request()->query())) }}" class="p-1 rounded-none border border-slate-300 dark:border-slate-600 text-slate-600 hover:text-dinas-green hover:bg-dinas-green/10 dark:text-slate-400 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition" title="Edit Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        @if($p->user_id !== Auth::id())
                                            <form method="POST" action="{{ route('pegawai.destroy', array_merge(['pegawai' => $p->id], request()->query())) }}" class="inline"
                                                  data-confirm="true"
                                                  data-confirm-title="Hapus Data Pegawai?"
                                                  data-confirm-message="Apakah Anda yakin ingin menghapus data pegawai {{ $p->user->name ?? '' }} beserta akun loginnya? Tindakan ini tidak dapat dibatalkan."
                                                  data-confirm-btn="Ya, Hapus Pegawai"
                                                  data-confirm-cancel="Batal"
                                                  data-confirm-type="danger">
                                                @csrf
                                                @method('DELETE')
                                                @foreach(request()->query() as $queryKey => $queryVal)
                                                    @if(!empty($queryVal))
                                                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="p-1 rounded-none border border-rose-300 dark:border-rose-800 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Hapus Pegawai">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    Tidak ada data pegawai yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Tampilan Mobile: Model "List of Cards" (block md:hidden) -->
        <div class="block md:hidden flex flex-col gap-4">
            @forelse($pegawais as $p)
                @php
                    $statusBadge = match($p->status_pegawai) {
                        'Aktif' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
                        'Pensiun' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                        'Mutasi' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                        'Berhenti' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                    <!-- Header Card: Nama Pegawai & Ikon Aksi (Detail, Edit, Hapus) -->
                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white text-base truncate">
                                {{ $p->user->name ?? '-' }}
                            </h3>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                @if($p->user?->role === 'admin')
                                    <span class="inline-block px-1.5 py-0.2 text-[9px] font-bold rounded-none bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                        ADMIN
                                    </span>
                                @else
                                    <span class="inline-block px-1.5 py-0.2 text-[9px] font-medium rounded-none bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700">
                                        PEGAWAI
                                    </span>
                                @endif
                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {{ $p->user->email ?? '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Ikon-ikon Aksi (Detail, Edit, Hapus) sejajar di sebelah kanan -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Detail -->
                            <a href="{{ route('pegawai.show', array_merge(['pegawai' => $p->id], request()->query())) }}"
                                class="p-1.5 rounded-md border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-dinas-green hover:bg-dinas-green/10 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition"
                                title="Lihat Detail Pegawai">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>

                            <!-- Edit -->
                            <a href="{{ route('pegawai.edit', array_merge(['pegawai' => $p->id], request()->query())) }}"
                                class="p-1.5 rounded-md border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-dinas-green hover:bg-dinas-green/10 dark:hover:text-dinas-gold dark:hover:bg-dinas-green/20 transition"
                                title="Edit Data Pegawai">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>

                            <!-- Hapus -->
                            @if($p->user_id !== Auth::id())
                                <form method="POST" action="{{ route('pegawai.destroy', array_merge(['pegawai' => $p->id], request()->query())) }}" class="inline"
                                      data-confirm="true"
                                      data-confirm-title="Hapus Data Pegawai?"
                                      data-confirm-message="Apakah Anda yakin ingin menghapus data pegawai {{ $p->user->name ?? '' }} beserta akun loginnya? Tindakan ini tidak dapat dibatalkan."
                                      data-confirm-btn="Ya, Hapus Pegawai"
                                      data-confirm-cancel="Batal"
                                      data-confirm-type="danger">
                                    @csrf
                                    @method('DELETE')
                                    @foreach(request()->query() as $queryKey => $queryVal)
                                        @if(!empty($queryVal))
                                            <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                                        @endif
                                    @endforeach
                                    <button type="submit"
                                        class="p-1.5 rounded-md border border-rose-300 dark:border-rose-800 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                        title="Hapus Pegawai">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Bagian Konten Card (Susunan Baris Flex Dua Kolom Gaya Web Dinas) -->
                    <div class="mt-1 divide-y divide-slate-100 dark:divide-slate-700/60">
                        <!-- Baris 1: NIP (kiri) - Nilai NIP (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">NIP / Gender</span>
                            <div class="text-right">
                                <span class="text-slate-800 dark:text-slate-100 font-mono font-medium text-xs sm:text-sm">
                                    {{ $p->nip ?: '-' }}
                                </span>
                                @if($p->jenis_kelamin)
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $p->jenis_kelamin }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 2: Jabatan (kiri) - Nilai Jabatan & badge Struktural (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Jabatan</span>
                            <div class="text-right">
                                <span class="text-slate-800 dark:text-slate-100 font-medium">
                                    {{ $p->jabatan?->nama_jabatan ?? '-' }}
                                </span>
                                @if($p->is_atasan_langsung)
                                    <span class="inline-block ml-1 px-1.5 py-0.5 text-[9px] font-bold rounded-none bg-dinas-gold/20 text-yellow-800 dark:text-yellow-300 border border-yellow-300/60 dark:border-yellow-700/60">
                                        STRUKTURAL
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 3: Atasan (kiri) - Nama Atasan Langsung (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Atasan</span>
                            <div class="text-right">
                                @if($p->jabatan?->parent)
                                    <span class="text-slate-800 dark:text-slate-100 font-medium">
                                        {{ $p->atasan?->user->name ?? '(Belum Ada)' }}
                                    </span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $p->jabatan->parent->nama_jabatan }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic text-xs">
                                        Pimpinan Utama / Tanpa Atasan
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 4: Saldo Cuti (kiri) - Sisa Saldo (kanan, ditebalkan warna hijau dinas) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Saldo Cuti</span>
                            <div class="text-right">
                                <span class="font-bold text-dinas-green dark:text-emerald-400">
                                    {{ $p->sisa_cuti_n }} Hari
                                </span>
                                <span class="block text-[10px] text-slate-400">
                                    N-1: {{ $p->sisa_cuti_n_1 }}, N-2: {{ $p->sisa_cuti_n_2 }}
                                </span>
                            </div>
                        </div>

                        <!-- Baris 5: Status (kiri) - Badge Aktif/Nonaktif (kanan) -->
                        <div class="flex justify-between items-center py-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Status</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none font-bold text-[10px] border {{ $statusBadge }}">
                                <span class="w-1.5 h-1.5 rounded-none {{ $p->status_pegawai === 'Aktif' ? 'bg-emerald-500' : ($p->status_pegawai === 'Pensiun' ? 'bg-blue-500' : ($p->status_pegawai === 'Mutasi' ? 'bg-amber-500' : 'bg-rose-500')) }}"></span>
                                {{ $p->status_pegawai ?? 'Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 rounded-lg p-8 text-center text-slate-500 dark:text-slate-400 text-sm border border-slate-200 dark:border-slate-700">
                    Tidak ada data pegawai yang ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Pagination (Bersama Desktop & Mobile) -->
        @if($pegawais->hasPages())
            <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-4">
                {{ $pegawais->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
