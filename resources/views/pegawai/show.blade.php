<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('pegawai.index', request()->query()) }}" class="hover:text-dinas-green dark:hover:text-dinas-gold">Data Pegawai</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Detail: {{ $pegawai->user->name ?? '-' }}</span>
            </div>
            <h2 class="font-bold text-lg sm:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Detail Data Pegawai & Akun') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Informasi lengkap identitas aparatur, jabatan, dan hak akses aplikasi
            </p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-6 space-y-4">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="flex items-center justify-between">
            <a href="{{ route('pegawai.index', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>

            <a href="{{ route('pegawai.edit', array_merge(['pegawai' => $pegawai->id], request()->query())) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold text-white bg-dinas-green hover:bg-dinas-greenhover rounded-none shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Pegawai</span>
            </a>
        </div>

        <!-- Kartu Identitas Utama -->
        <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="px-5 py-4 bg-slate-50/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-800 dark:text-white">{{ $pegawai->user->name ?? '-' }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">NIP: {{ $pegawai->nip ?: '-' }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-none border {{ $pegawai->status_pegawai === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700' }}">
                    <span class="w-2 h-2 rounded-none {{ $pegawai->status_pegawai === 'Aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $pegawai->status_pegawai ?? 'Aktif' }}
                </span>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <!-- Jabatan -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Jabatan</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pegawai->jabatan?->nama_jabatan ?? '-' }}
                    </span>
                </div>

                <!-- Jenis Kelamin -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Jenis Kelamin</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pegawai->jenis_kelamin ?: '-' }}
                    </span>
                </div>

                <!-- Email Akun -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Alamat Email Dinas</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pegawai->user->email ?? '-' }}
                    </span>
                </div>

                <!-- Peran (Role) -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Peran Akun Sistem</span>
                    <span class="font-semibold uppercase text-slate-800 dark:text-slate-100">
                        {{ $pegawai->user->role ?? 'pegawai' }}
                    </span>
                </div>

                <!-- TMT Pegawai -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">TMT Pegawai</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pegawai->tanggal_masuk ? $pegawai->tanggal_masuk->isoFormat('D MMMM YYYY') : '-' }}
                    </span>
                </div>

                <!-- Masa Kerja -->
                <div class="p-3 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60">
                    <span class="text-xs text-slate-500 dark:text-slate-400 block mb-0.5">Masa Kerja</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pegawai->masa_kerja ?: '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Kartu Saldo Cuti -->
        <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 p-5">
            <h4 class="font-bold text-sm text-slate-800 dark:text-white mb-3">Rincian Kuota Cuti Tahunan</h4>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-3 bg-dinas-green/10 dark:bg-dinas-green/20 border border-dinas-green/20">
                    <span class="text-xs text-dinas-green dark:text-emerald-300 font-medium block">Tahun N (Berjalan)</span>
                    <span class="text-xl font-bold text-dinas-green dark:text-emerald-200">{{ $pegawai->sisa_cuti_n }}</span>
                    <span class="text-[10px] text-dinas-green/80 dark:text-emerald-400 block">hari</span>
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium block">Tahun N-1</span>
                    <span class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $pegawai->sisa_cuti_n_1 }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">hari</span>
                </div>
                <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium block">Tahun N-2</span>
                    <span class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $pegawai->sisa_cuti_n_2 }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block">hari</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

