<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Pertimbangan Atasan Langsung') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Beri pertimbangan, persetujuan, atau catatan perbaikan permohonan cuti aparatur bawahan
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="flex justify-start">
            <a href="{{ route('cuti.persetujuan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Antrean Persetujuan</span>
            </a>
        </div>
            
            <!-- Panel Detail Pegawai & Pengajuan -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <h3 class="font-bold text-lg mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Detail Pengajuan Cuti</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Nama Pegawai</p>
                        <p class="font-semibold text-base">{{ $cuti->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Jabatan</p>
                        <p class="font-semibold text-base">{{ $cuti->user->jabatan }}</p>
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
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY') }} ({{ $cuti->durasi_hari }} Hari)</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Nomor Telepon / WhatsApp</p>
                        <p class="font-semibold">{{ $cuti->no_telp ?: '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500 dark:text-gray-400">Alamat Selama Menjalankan Cuti</p>
                        <p class="font-semibold whitespace-pre-line">{{ $cuti->alamat_selama_cuti ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Panel Form Keputusan -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('cuti.proses', $cuti->id) }}">
                    @csrf
                    
                    <h3 class="font-bold text-lg mb-4">Keputusan Pertimbangan</h3>
                    
                    <div class="mb-4">
                        <x-input-label :value="__('Pilih Keputusan')" />
                        <div class="mt-2 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="keputusan" value="DISETUJUI" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required>
                                <span class="ml-2">Disetujui</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="keputusan" value="PERUBAHAN" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required>
                                <span class="ml-2">Perubahan (Revisi)</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="keputusan" value="DITANGGUHKAN" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required>
                                <span class="ml-2">Ditangguhkan</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="keputusan" value="DITOLAK" class="text-dinas-green border-gray-300 focus:ring-dinas-green dark:bg-gray-900" required>
                                <span class="ml-2">Tidak Disetujui</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="catatan" :value="__('Catatan (Wajib diisi jika Perubahan / Ditangguhkan)')" />
                        <textarea id="catatan" name="catatan" class="block mt-1 w-full border-slate-300 dark:border-slate-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm" rows="3" placeholder="Masukkan alasan revisi atau penangguhan di sini..."></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                        <a href="{{ route('cuti.persetujuan') }}" class="w-full sm:w-auto text-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-none text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Kembali</a>
                        <x-primary-button class="w-full sm:w-auto justify-center rounded-none">
                            {{ __('Simpan Keputusan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

    </div>
</x-app-layout>
