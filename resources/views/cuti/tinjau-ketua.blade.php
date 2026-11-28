<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Keputusan Final') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Beri keputusan final dan pengesahan resmi atas permohonan izin cuti aparatur
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="flex justify-start">
            <a href="{{ route('cuti.daftar_ketua') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-sm hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Antrean Keputusan</span>
            </a>
        </div>
            
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-sm border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4 border-b border-gray-200 dark:border-gray-700 pb-4">
                <h3 class="font-bold text-lg">Detail Pengajuan Cuti</h3>
                @if($cuti->nomor_surat)
                    <span class="bg-slate-100 text-slate-800 text-xs font-semibold px-2.5 py-1 rounded-sm dark:bg-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                        Nomor: {{ $cuti->nomor_surat }}
                    </span>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Nama Pegawai</p>
                    <p class="font-semibold text-base">{{ $cuti->user->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Jabatan</p>
                    <p class="font-semibold text-base">{{ $cuti->user?->pegawai?->jabatan?->nama_jabatan ?? $cuti->user->jabatan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Jenis Cuti</p>
                    <p class="font-semibold">{{ $cuti->jenis_cuti }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Alasan Cuti</p>
                    <p class="font-semibold">{{ $cuti->alasan }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Tanggal Pelaksanaan</p>
                    <p class="font-semibold">
                        @if(\Carbon\Carbon::parse($cuti->tgl_mulai)->isSameDay(\Carbon\Carbon::parse($cuti->tgl_selesai)))
                            {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }} (1 Hari)
                        @else
                            {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY') }} ({{ $cuti->durasi_hari }} Hari)
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Nomor Telepon / WhatsApp</p>
                    <p class="font-semibold">{{ $cuti->no_telp ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Alamat Selama Menjalankan Cuti</p>
                    <p class="font-semibold whitespace-pre-line">{{ $cuti->alamat_selama_cuti ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Pertimbangan Atasan Langsung</p>
                    <div class="mt-0.5">
                        @if($cuti->atasan_penyetuju)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Disetujui oleh {{ $cuti->atasan_penyetuju->name }} ({{ $cuti->atasan_penyetuju->pegawai?->jabatan?->nama_jabatan ?? $cuti->atasan_penyetuju->jabatan ?? 'Atasan Langsung' }})
                            </span>
                        @elseif(!$cuti->user->pegawai?->getAtasanPegawai() || $cuti->user->pegawai?->getAtasanPegawai()?->is_pimpinan_final)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Langsung di bawah Ketua PA (Tanpa Atasan Perantara)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800">
                                Menunggu / {{ $cuti->user->pegawai?->getAtasanPegawai()?->nama_lengkap ?? $cuti->user->atasan->name ?? 'Atasan Langsung' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Form Keputusan -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
            <form method="POST" action="{{ route('cuti.proses_ketua', $cuti->id) }}">
                @csrf
                
                <h3 class="font-bold text-lg mb-4">Keputusan Pertimbangan</h3>
                
                <div class="mb-4">
                    <x-input-label :value="__('Pilih Keputusan')" />
                    <div class="mt-2 space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="keputusan" value="DISETUJUI" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required {{ old('keputusan') === 'DISETUJUI' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Disetujui</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center">
                            <input type="radio" name="keputusan" value="PERUBAHAN" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required {{ old('keputusan') === 'PERUBAHAN' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Perubahan (Revisi)</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center">
                            <input type="radio" name="keputusan" value="DITANGGUHKAN" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required {{ old('keputusan') === 'DITANGGUHKAN' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Ditangguhkan</span>
                        </label>
                        <br>
                        <label class="inline-flex items-center">
                            <input type="radio" name="keputusan" value="DITOLAK" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required {{ old('keputusan') === 'DITOLAK' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tidak Disetujui</span>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <x-input-label for="catatan" :value="__('Catatan (Wajib diisi jika Perubahan / Ditangguhkan)')" />
                    <textarea id="catatan" name="catatan" class="block mt-1 w-full border-slate-300 dark:border-slate-700 dark:bg-gray-900 dark:text-gray-300 focus:border-dinas-green focus:ring-dinas-green rounded-none shadow-xs text-sm" rows="3" placeholder="Masukkan alasan revisi atau penangguhan di sini...">{{ old('catatan') }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-dinas-green hover:bg-dinas-greenhover text-white font-semibold text-xs rounded-none shadow-xs transition-colors">
                        Sahkan Keputusan & Eksekusi Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
