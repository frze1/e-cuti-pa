<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Riwayat Penomoran & Arsip Surat Cuti (Admin)') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Buku register dan arsip digital berkas cuti yang telah selesai diproses, disahkan Ketua, dan diterbitkan nomor surat dinas resminya.
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="flex justify-start">
            <a href="{{ route('cuti.penomoran') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Antrean Penomoran</span>
            </a>
        </div>
        <!-- Tampilan Desktop: Tabel (hidden md:block) -->
        <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                            <th class="w-[20%] py-3 px-3 md:px-4 text-left">Nomor Surat Dinas</th>
                            <th class="w-[20%] py-3 px-3 md:px-4 text-left">Nama Pemohon & NIP</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Jabatan</th>
                            <th class="w-[12%] py-3 px-3 md:px-4 text-left">Jenis Cuti</th>
                            <th class="w-[15%] py-3 px-3 md:px-4 text-left">Durasi & Periode</th>
                            <th class="w-[15%] py-3 px-3 md:px-4 text-center">Unduh Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($daftarPengajuan as $cuti)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="py-3 px-3 md:px-4 font-mono font-semibold text-dinas-green hover:text-dinas-greenhover dark:text-emerald-400 dark:hover:text-emerald-300 whitespace-normal break-words align-top text-left">
                                    <div class="flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-dinas-green dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span>{{ $cuti->nomor_surat }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <div class="font-semibold text-slate-800 dark:text-white">{{ $cuti->user->name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">NIP: {{ $cuti->user->pegawai?->nip ?? ($cuti->user->nip ?: '-') }}</div>
                                </td>
                                <td class="py-3 px-3 md:px-4 text-slate-600 dark:text-slate-300 whitespace-normal break-words align-top text-left">
                                    {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <span class="inline-block px-2 py-0.5 rounded-none text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        {{ $cuti->jenis_cuti }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-600 dark:text-slate-300">
                                    <div class="font-semibold text-slate-800 dark:text-white">{{ $cuti->durasi_hari }} Hari</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMM') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMM YYYY') }}</div>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    <div class="flex items-center justify-center">
                                        {{-- Tombol Unduh Word (.docx Resmi) --}}
                                        <a href="{{ route('cuti.export-word', $cuti->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-none text-xs font-semibold border border-dinas-green text-dinas-green hover:bg-dinas-green hover:text-white shadow-xs transition" title="Cetak Formulir Word Resmi (.docx)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            <span>Word (.docx)</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada berkas surat cuti yang selesai dinomori dan diarsipkan.
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
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 flex flex-col gap-2.5">
                    <!-- Header Card: Nomor Surat Dinas & Badge Jenis Cuti -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 text-dinas-green dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-xs font-mono font-bold text-dinas-green dark:text-emerald-400 truncate">
                                {{ $cuti->nomor_surat }}
                            </span>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shrink-0">
                            {{ $cuti->jenis_cuti }}
                        </span>
                    </div>

                    <!-- Konten Card: Detail Data 2 Kolom -->
                    <div class="flex flex-col gap-1.5 text-[11px]">
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Pemohon</span>
                            <div class="text-right">
                                <span class="text-slate-800 dark:text-slate-200 font-semibold block">{{ $cuti->user->name }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">NIP: {{ $cuti->user->pegawai?->nip ?? ($cuti->user->nip ?: '-') }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Jabatan</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium text-right">
                                {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Periode Cuti</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium text-right">
                                <span class="text-dinas-green dark:text-emerald-400 font-bold">{{ $cuti->durasi_hari }} Hari</span> &bull; {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMM') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMM YYYY') }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer Card: Tombol Unduh Dokumen (Word) -->
                    <div class="flex items-center justify-end pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <a href="{{ route('cuti.export-word', $cuti->id) }}" class="inline-flex items-center gap-1 border border-dinas-green text-dinas-green hover:bg-dinas-green/5 text-[10px] py-1 px-2.5 rounded font-bold transition" title="Cetak Word (.docx)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Word</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 text-center text-slate-500 dark:text-slate-400 text-xs">
                    Belum ada berkas surat cuti yang selesai dinomori dan diarsipkan.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
