<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Input Nomor Surat Dinas') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Terbitkan nomor surat keputusan cuti resmi sesuai buku register persuratan instansi
            </p>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="mb-4 flex justify-start">
            <a href="{{ route('cuti.penomoran') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Antrean Penomoran</span>
            </a>
        </div>
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6">
                
                <div class="mb-6 p-3 sm:p-4 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 rounded-none">
                    <p class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-200">
                        Berkas cuti a.n. <strong>{{ $cuti->user->name }}</strong> telah <strong>Disetujui</strong> oleh Atasan Langsung dan telah <strong>Disahkan</strong> oleh Ketua Pengadilan Agama. Silakan input nomor surat dinas resmi untuk menerbitkan dokumen final.
                    </p>
                </div>

                <form method="POST" action="{{ route('cuti.simpan_nomor', $cuti->id) }}">
                    @csrf
                    
                    <div class="mb-4">
                        <x-input-label for="nomor_surat" :value="__('Nomor Surat Dinas')" />
                        <x-text-input id="nomor_surat" name="nomor_surat" type="text" class="mt-1 block w-full font-mono text-sm rounded-none border-slate-300" value="{{ $rekomendasiNomor }}" required />
                        <p class="text-xs text-slate-500 mt-1">Ubah simbol "..." dengan nomor urut buku register yang sesuai.</p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 mt-6">
                        <a href="{{ route('cuti.penomoran') }}" class="w-full sm:w-auto text-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-none text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</a>
                        <x-primary-button class="w-full sm:w-auto justify-center bg-dinas-green hover:bg-dinas-greenhover rounded-none transition-colors">
                            {{ __('Simpan & Terbitkan Surat Cuti') }}
                        </x-primary-button>
                    </div>
                </form>

        </div>
    </div>
</x-app-layout>