<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Formulir Pengajuan Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Isi rincian permohonan cuti, durasi tanggal pelaksanaan, dan alasan pengajuan
            </p>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="mb-4 flex justify-start">
            <a href="{{ route('cuti.riwayat') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Riwayat</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Card Inputan Cuti (Normal - 2 Kolom) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <!-- Form Pengajuan -->
                <form method="POST" action="{{ route('cuti.store') }}" class="space-y-4"
                    x-data="{
                        jenisCuti: '{{ old('jenis_cuti', 'Cuti Tahunan') }}',
                        satuanCuti: '{{ old('satuan_cuti', 'hari') }}',
                        updateSatuan() {
                            if (this.jenisCuti === 'Cuti Melahirkan' || this.jenisCuti === 'Cuti Besar') {
                                this.satuanCuti = 'bulan';
                            } else if (this.jenisCuti === 'Cuti Tahunan') {
                                this.satuanCuti = 'hari';
                            }
                        }
                    }">
                    @csrf

                    <!-- Jenis Cuti -->
                    <div>
                        <x-input-label for="jenis_cuti" :value="__('Jenis Cuti')" />
                        @php
                            $isLakiLaki = (Auth::user()?->jenis_kelamin === 'Laki-laki' || Auth::user()?->pegawai?->jenis_kelamin === 'Laki-laki');
                        @endphp
                        <select id="jenis_cuti" name="jenis_cuti" x-model="jenisCuti" @change="updateSatuan()" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" required>
                            <option value="Cuti Tahunan">Cuti Tahunan</option>
                            <option value="Cuti Besar">Cuti Besar</option>
                            <option value="Cuti Sakit">Cuti Sakit</option>
                            <option value="Cuti Melahirkan" @if($isLakiLaki) disabled class="text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800" @endif>
                                Cuti Melahirkan @if($isLakiLaki) @endif
                            </option>
                            <option value="Cuti Karena Alasan Penting">Cuti Karena Alasan Penting</option>
                            <option value="Cuti di Luar Tanggungan Negara">Cuti di Luar Tanggungan Negara</option>
                        </select>
                        <x-input-error :messages="$errors->get('jenis_cuti')" class="mt-1" />
                    </div>

                    <!-- Alasan Cuti -->
                    <div>
                        <x-input-label for="alasan" :value="__('Alasan Cuti')" />
                        <textarea id="alasan" name="alasan" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" rows="3" placeholder="Contoh: Menjenguk orang tua sakit" required>{{ old('alasan') }}</textarea>
                        <x-input-error :messages="$errors->get('alasan')" class="mt-1" />
                    </div>

                    <!-- Tanggal & Durasi -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <x-input-label for="tgl_mulai" :value="__('Tanggal Mulai')" />
                            <x-text-input id="tgl_mulai" class="block mt-1 w-full" type="date" name="tgl_mulai" :value="old('tgl_mulai')" required />
                            <x-input-error :messages="$errors->get('tgl_mulai')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tgl_selesai" :value="__('Tanggal Selesai')" />
                            <x-text-input id="tgl_selesai" class="block mt-1 w-full" type="date" name="tgl_selesai" :value="old('tgl_selesai')" required />
                            <x-input-error :messages="$errors->get('tgl_selesai')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="durasi_hari" :value="__('Lama Cuti')" />
                            <div class="flex gap-2 mt-1">
                                <x-text-input id="durasi_hari" class="block w-full min-w-0" type="number" name="durasi_hari" :value="old('durasi_hari')" min="1" placeholder="Lama" required />
                                <select id="satuan_cuti" name="satuan_cuti" x-model="satuanCuti" :disabled="jenisCuti === 'Cuti Tahunan'" class="w-28 shrink-0 border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-2.5" required>
                                    <option value="hari">Hari</option>
                                    <option value="bulan">Bulan</option>
                                    <option value="tahun">Tahun</option>
                                </select>
                            </div>
                            <template x-if="jenisCuti === 'Cuti Tahunan'">
                                <input type="hidden" name="satuan_cuti" value="hari">
                            </template>
                            <x-input-error :messages="$errors->get('durasi_hari')" class="mt-1" />
                            <x-input-error :messages="$errors->get('satuan_cuti')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Alamat & Telepon -->
                    <div>
                        <x-input-label for="alamat_selama_cuti" :value="__('Alamat Selama Menjalankan Cuti')" />
                        <textarea id="alamat_selama_cuti" name="alamat_selama_cuti" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" rows="2" placeholder="Masukkan alamat lengkap..." required>{{ old('alamat_selama_cuti') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat_selama_cuti')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="no_telp" :value="__('Nomor Telepon / WhatsApp')" />
                        <x-text-input id="no_telp" class="block mt-1 w-full" type="text" name="no_telp" :value="old('no_telp')" placeholder="08xxxxxxxxxx" required />
                        <x-input-error :messages="$errors->get('no_telp')" class="mt-1" />
                    </div>

                    <!-- Tombol Aksi Simpan & Ajukan Cuti (Di Bawah Kanan Card Form) -->
                    <div class="flex items-center justify-end pt-4 border-t border-slate-100 dark:border-slate-700/60">
                        <x-primary-button class="w-full sm:w-auto justify-center">
                            {{ __('Simpan & Ajukan Cuti') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Card Kecil Informasi Saldo Cuti (Di Samping Kanan Atas - 1 Kolom) -->
            <div class="lg:col-span-1 p-4 sm:p-5 bg-blue-50 dark:bg-blue-950/60 rounded-none border border-blue-200 dark:border-blue-800 shadow-xs">
                <h3 class="font-bold text-xs sm:text-sm text-blue-800 dark:text-blue-200 mb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Informasi Kuota Cuti Tahunan:</span>
                </h3>
                <ul class="list-disc list-inside text-xs sm:text-sm text-blue-700 dark:text-blue-300 space-y-1.5">
                    <li>Tahun Berjalan (N): <strong>{{ $user->sisa_cuti_n }} hari</strong></li>
                    <li>Tahun N-1: <strong>{{ $user->sisa_cuti_n_1 }} hari</strong></li>
                    <li>Tahun N-2: <strong>{{ $user->sisa_cuti_n_2 }} hari</strong></li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
