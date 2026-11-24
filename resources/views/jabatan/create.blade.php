<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg sm:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Tambah Jabatan Baru') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Definisikan nama jabatan baru, hubungan atasan hierarki, dan kewenangan persetujuan cuti
            </p>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="mb-4 flex justify-start">
            <a href="{{ route('jabatan.index', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>

        <form action="{{ route('jabatan.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 border-t-4 border-t-dinas-green overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white">Identitas & Posisi Hierarki Jabatan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Atur rantai komando approval cuti antar posisi di lingkungan pengadilan</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4">
                        <!-- Nama Jabatan -->
                        <div>
                            <label for="nama_jabatan" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Jabatan Resmi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_jabatan" id="nama_jabatan" value="{{ old('nama_jabatan') }}" required
                                placeholder="Contoh: Panitera Muda Hukum, Kasubbag Umum, Pranata Komputer"
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('nama_jabatan') border-rose-500 @enderror">
                            @error('nama_jabatan')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Atasan Langsung (Parent Jabatan) -->
                        <div>
                            <label for="parent_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Atasan Langsung (Induk Jabatan)
                            </label>
                            <select name="parent_id" id="parent_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('parent_id') border-rose-500 @enderror">
                                <option value="">-- Pimpinan Tertinggi --</option>
                                @foreach($daftarAtasan as $atasan)
                                    <option value="{{ $atasan->id }}" {{ (string)old('parent_id') === (string)$atasan->id ? 'selected' : '' }}>
                                        {{ $atasan->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                                Setiap aparatur yang menduduki jabatan ini nantinya secara otomatis memiliki atasan langsung aparatur yang menduduki jabatan yang Anda pilih di atas. (Hanya menampilkan jabatan struktural / penyetuju cuti).
                            </p>
                        </div>

                        <!-- Checkbox Jabatan Struktural / Penyetuju Cuti -->
                        <div class="bg-dinas-green/5 dark:bg-dinas-green/10 border border-dinas-green/20 rounded-none p-3.5 sm:p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="is_struktural" id="is_struktural" value="1" {{ old('is_struktural') ? 'checked' : '' }}
                                    class="mt-0.5 rounded-none border-slate-300 dark:border-slate-600 text-dinas-green focus:ring-dinas-green dark:bg-slate-900">
                                <div class="min-w-0">
                                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-100">
                                        Jabatan Struktural / Penyetuju Cuti
                                    </span>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                        Centang jika jabatan ini memiliki hak dan wewenang untuk memberikan pertimbangan/persetujuan cuti bawahan langsungnya. Pemegang jabatan ini akan memiliki menu khusus persetujuan cuti di aplikasi.
                                    </span>
                                </div>
                            </label>
                            @error('is_struktural')
                                <p class="mt-1.5 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                        <a href="{{ route('jabatan.index', request()->query()) }}" class="px-4 py-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-none border border-slate-300 dark:border-slate-700 hover:bg-slate-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none shadow-xs transition-colors">
                            Simpan Jabatan
                        </button>
                    </div>
                </div>
            </form>
        </div>
</x-app-layout>

