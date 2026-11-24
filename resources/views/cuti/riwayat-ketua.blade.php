<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Riwayat Keputusan & Pengesahan (Ketua PA)') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar seluruh berkas permohonan cuti aparatur yang telah Anda beri keputusan final / pengesahan resmi.
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="flex justify-start">
            <a href="{{ route('cuti.daftar_ketua') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Antrean Keputusan</span>
            </a>
        </div>
        <!-- Tampilan Desktop: Tabel (hidden md:block) -->
        <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Nama Pemohon</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Jabatan</th>
                            <th class="w-[12%] py-3 px-3 md:px-4 text-left">Jenis Cuti</th>
                            <th class="w-[8%] py-3 px-3 md:px-4 text-center">Durasi</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Nomor Surat Dinas</th>
                            <th class="w-[13%] py-3 px-3 md:px-4 text-center">Status</th>
                            <th class="w-[13%] py-3 px-3 md:px-4 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($daftarPengajuan as $cuti)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="py-3 px-3 md:px-4 font-semibold text-slate-800 dark:text-white whitespace-normal break-words align-top text-left">
                                    {{ $cuti->user->name }}
                                </td>
                                <td class="py-3 px-3 md:px-4 text-slate-600 dark:text-slate-300 whitespace-normal break-words align-top text-left">
                                    {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <span class="inline-block px-2 py-0.5 rounded-none text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        {{ $cuti->jenis_cuti }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ $cuti->durasi_hari }} Hari
                                </td>
                                <td class="py-3 px-3 md:px-4 font-mono font-semibold text-dinas-green dark:text-emerald-400 whitespace-normal break-words align-top text-left">
                                    @if($cuti->nomor_surat)
                                        <span class="inline-block px-1.5 py-0.5 bg-dinas-green/10 dark:bg-dinas-green/20 border border-dinas-green/20 dark:border-dinas-green/30 rounded-none font-semibold text-xs text-dinas-green dark:text-emerald-400">
                                            {{ $cuti->nomor_surat }}
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-sans italic">Menunggu input admin</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    @php
                                        $badgeClass = match($cuti->status) {
                                            'SELESAI_DISETUJUI' => 'bg-dinas-green/10 text-dinas-green font-bold border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                                            'MENUNGGU_PENOMORAN' => 'bg-dinas-gold/20 text-yellow-800 font-bold border-yellow-300/60 dark:bg-yellow-950/60 dark:text-yellow-300 dark:border-yellow-800',
                                            'REVISI_KETUA' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 border-orange-300 dark:border-orange-800',
                                            'DITANGGUHKAN_KETUA' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/60 dark:text-yellow-300 border-yellow-300 dark:border-yellow-800',
                                            'DITOLAK' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700'
                                        };
                                        $statusLabel = match($cuti->status) {
                                            'SELESAI_DISETUJUI' => 'Disahkan & Beres',
                                            'MENUNGGU_PENOMORAN' => 'Disahkan (Proses Nomor)',
                                            'REVISI_KETUA' => 'Perubahan (Revisi)',
                                            'DITANGGUHKAN_KETUA' => 'Ditangguhkan',
                                            'DITOLAK' => 'Tidak Disetujui',
                                            default => $cuti->status
                                        };
                                    @endphp
                                    <span class="inline-block px-1.5 py-0.5 rounded-none text-[10px] font-bold border {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 md:px-4 text-xs text-slate-500 dark:text-slate-400 italic whitespace-normal break-words align-top text-left">
                                    {{ $cuti->catatan_revisi ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada riwayat keputusan cuti yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tampilan Mobile: Format Card List (block md:hidden) -->
        <div class="block md:hidden flex flex-col gap-3.5">
            @forelse ($daftarPengajuan as $cuti)
                @php
                    $mobileBadgeClass = match($cuti->status) {
                        'SELESAI_DISETUJUI' => 'bg-dinas-green/10 text-dinas-green border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                        'MENUNGGU_PENOMORAN' => 'bg-dinas-gold/20 text-yellow-800 font-bold border-yellow-300/60 dark:bg-yellow-950/60 dark:text-yellow-300 dark:border-yellow-800',
                        'REVISI_KETUA' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                        'DITANGGUHKAN_KETUA' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/60 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
                        'DITOLAK' => 'bg-red-50 text-red-700 dark:bg-rose-950/60 dark:text-rose-300 border-red-200 dark:border-rose-800',
                        default => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800'
                    };
                    $mobileStatusLabel = match($cuti->status) {
                        'SELESAI_DISETUJUI' => 'Disahkan & Beres',
                        'MENUNGGU_PENOMORAN' => 'Disahkan (Proses Nomor)',
                        'REVISI_KETUA' => 'Perubahan (Revisi)',
                        'DITANGGUHKAN_KETUA' => 'Ditangguhkan',
                        'DITOLAK' => 'Tidak Disetujui',
                        default => str_replace('_', ' ', $cuti->status)
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 flex flex-col gap-2.5">
                    <!-- Header Card: Nama Pemohon & Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-xs text-slate-800 dark:text-white font-bold block truncate">
                                {{ $cuti->user->name }}
                            </span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                NIP: {{ $cuti->user->pegawai?->nip ?? ($cuti->user->nip ?: '-') }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded border shrink-0 {{ $mobileBadgeClass }}">
                            {{ $mobileStatusLabel }}
                        </span>
                    </div>

                    <!-- Konten Card: Detail Data 2 Kolom -->
                    <div class="flex flex-col gap-1.5 text-[11px]">
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Jabatan</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium text-right">
                                {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Jenis Cuti</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                {{ $cuti->jenis_cuti }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Nomor Surat Dinas</span>
                            <span class="text-slate-800 dark:text-slate-200 font-mono font-semibold text-right">
                                @if($cuti->nomor_surat)
                                    <span class="text-dinas-green dark:text-emerald-400">{{ $cuti->nomor_surat }}</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-400 italic text-[10px] font-sans">Menunggu input admin</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between {{ $cuti->catatan_revisi ? 'border-b border-slate-100 dark:border-slate-700/60 pb-1.5' : '' }}">
                            <span class="text-slate-500 dark:text-slate-400">Durasi</span>
                            <span class="text-dinas-green dark:text-emerald-400 font-bold">
                                {{ $cuti->durasi_hari }} Hari
                            </span>
                        </div>
                        @if($cuti->catatan_revisi)
                            <div class="flex flex-col gap-0.5 pt-0.5">
                                <span class="text-slate-500 dark:text-slate-400 text-[10px]">Catatan:</span>
                                <span class="text-slate-700 dark:text-slate-300 italic text-[11px] bg-slate-50 dark:bg-slate-900/50 p-2 rounded">
                                    &ldquo;{{ $cuti->catatan_revisi }}&rdquo;
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 text-center text-slate-500 dark:text-slate-400 text-xs">
                    Belum ada riwayat keputusan cuti yang tercatat.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
