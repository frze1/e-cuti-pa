<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Perbaiki Pengajuan Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Sesuaikan rincian pengajuan cuti berdasarkan catatan evaluasi atasan langsung
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
                
                <!-- Card Inputan Perbaikan (Normal - 2 Kolom) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-xs rounded-none border border-slate-300 dark:border-slate-700 p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Alert Box Catatan Revisi dari Atasan -->
                    <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-700 dark:bg-amber-950/70 dark:border-amber-400 dark:text-amber-200 p-4 mb-4 rounded-none shadow-xs border border-amber-200/60 dark:border-amber-800/60 border-l-amber-500 dark:border-l-amber-400" role="alert">
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 rounded-none bg-amber-200/70 dark:bg-amber-900/70 text-amber-800 dark:text-amber-300 shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-amber-900 dark:text-amber-100">Catatan Revisi dari Atasan Langsung:</p>
                                <p class="text-sm mt-1 whitespace-pre-line text-amber-800 dark:text-amber-200 font-medium">{{ $cuti->catatan_atasan ?: ($cuti->catatan_revisi ?: 'Mohon periksa kembali dan sesuaikan rincian permohonan cuti Anda.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Perbaikan Pengajuan -->
                    <form method="POST" action="{{ route('cuti.update', $cuti->id) }}" class="space-y-4"
                        x-data="{
                            jenisCuti: '{{ old('jenis_cuti', $cuti->jenis_cuti) }}',
                            satuanCuti: '{{ old('satuan_cuti', $cuti->satuan_cuti ?? 'hari') }}',
                            updateSatuan() {
                                if (this.jenisCuti === 'Cuti Melahirkan' || this.jenisCuti === 'Cuti Besar') {
                                    this.satuanCuti = 'bulan';
                                } else if (this.jenisCuti === 'Cuti Tahunan') {
                                    this.satuanCuti = 'hari';
                                }
                            }
                        }">
                        @csrf
                        @method('PUT')

                        <!-- Jenis Cuti -->
                        <div>
                            <x-input-label for="jenis_cuti" :value="__('Jenis Cuti')" />
                            @php
                                $isLakiLaki = (Auth::user()?->jenis_kelamin === 'Laki-laki' || Auth::user()?->pegawai?->jenis_kelamin === 'Laki-laki');
                                $daftarJenis = [
                                    'Cuti Tahunan',
                                    'Cuti Besar',
                                    'Cuti Sakit',
                                    'Cuti Melahirkan',
                                    'Cuti Karena Alasan Penting',
                                    'Cuti di Luar Tanggungan Negara'
                                ];
                            @endphp
                            <select id="jenis_cuti" name="jenis_cuti" x-model="jenisCuti" @change="updateSatuan()" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" required>
                                @foreach($daftarJenis as $jenis)
                                    @php
                                        $isDisabled = ($jenis === 'Cuti Melahirkan' && $isLakiLaki);
                                    @endphp
                                    <option value="{{ $jenis }}" @if($isDisabled) disabled class="text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800" @endif>
                                        {{ $jenis }} @if($isDisabled) (Hanya Pegawai Perempuan) @endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jenis_cuti')" class="mt-1" />
                        </div>

                        <!-- Alasan Cuti -->
                        <div>
                            <x-input-label for="alasan" :value="__('Alasan Cuti')" />
                            <textarea id="alasan" name="alasan" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" rows="3" placeholder="Contoh: Menjenguk orang tua sakit" required>{{ old('alasan', $cuti->alasan) }}</textarea>
                            <x-input-error :messages="$errors->get('alasan')" class="mt-1" />
                        </div>

                        <!-- Tanggal & Durasi -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                            <div>
                                <x-input-label for="tgl_mulai" :value="__('Tanggal Mulai')" />
                                <x-text-input id="tgl_mulai" class="block mt-1 w-full" type="date" name="tgl_mulai" value="{{ old('tgl_mulai', \Carbon\Carbon::parse($cuti->tgl_mulai)->format('Y-m-d')) }}" required />
                                <x-input-error :messages="$errors->get('tgl_mulai')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="tgl_selesai" :value="__('Tanggal Selesai')" />
                                <x-text-input id="tgl_selesai" class="block mt-1 w-full" type="date" name="tgl_selesai" value="{{ old('tgl_selesai', \Carbon\Carbon::parse($cuti->tgl_selesai)->format('Y-m-d')) }}" required />
                                <x-input-error :messages="$errors->get('tgl_selesai')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="durasi_hari" :value="__('Lama Cuti')" />
                                <div class="flex gap-2 mt-1">
                                    <x-text-input id="durasi_hari" class="block w-full min-w-0" type="number" name="durasi_hari" min="1" value="{{ old('durasi_hari', $cuti->durasi_hari) }}" placeholder="Lama" required />
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
                            <textarea id="alamat_selama_cuti" name="alamat_selama_cuti" class="block mt-1 w-full border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-none shadow-xs text-sm py-2 px-3" rows="2" placeholder="Masukkan alamat lengkap..." required>{{ old('alamat_selama_cuti', $cuti->alamat_selama_cuti) }}</textarea>
                            <x-input-error :messages="$errors->get('alamat_selama_cuti')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="no_telp" :value="__('Nomor Telepon / WhatsApp')" />
                            <x-text-input id="no_telp" class="block mt-1 w-full" type="text" name="no_telp" value="{{ old('no_telp', $cuti->no_telp) }}" placeholder="08xxxxxxxxxx" required />
                            <x-input-error :messages="$errors->get('no_telp')" class="mt-1" />
                        </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('cuti.riwayat') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-none border border-slate-300 dark:border-slate-600 text-xs uppercase tracking-widest transition">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-semibold rounded-none border border-transparent text-xs uppercase tracking-widest shadow-xs transition">
                                {{ __('Simpan Pembaruan & Ajukan Ulang') }}
                            </button>
                        </div>
                    </form>

                </div>

                <!-- Card Kecil Informasi Saldo Cuti (Di Samping Kanan - 1 Kolom) -->
                <div class="lg:col-span-1 p-4 bg-blue-50 dark:bg-blue-950/60 rounded-none border border-blue-200 dark:border-blue-800 shadow-xs">
                    <h3 class="font-bold text-xs sm:text-sm text-blue-800 dark:text-blue-200 mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Informasi Kuota Cuti Tahunan:</span>
                    </h3>
                    <ul class="list-disc list-inside text-xs sm:text-sm text-blue-700 dark:text-blue-300 space-y-1">
                        <li>Tahun Berjalan (N): <strong>{{ $user->sisa_cuti_n }} hari</strong></li>
                        <li>Tahun N-1: <strong>{{ $user->sisa_cuti_n_1 }} hari</strong></li>
                        <li>Tahun N-2: <strong>{{ $user->sisa_cuti_n_2 }} hari</strong></li>
                    </ul>
                </div>

        </div>
    </div>
</x-app-layout>
