<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Antrean Penomoran Surat Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar berkas cuti yang telah disahkan Ketua PA dan siap diterbitkan nomor surat dinas resminya
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        @if($daftarPengajuan->count() > 0)
            <!-- Kontainer Tabel Utama (Tampilan Desktop: hidden md:block) -->
            <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="table-fixed w-full min-w-[800px] md:w-full text-xs md:text-sm text-slate-600 dark:text-slate-300">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                                <th class="w-[24%] py-3 px-3 md:px-4 text-left">Pemohon</th>
                                <th class="w-[22%] py-3 px-3 md:px-4 text-left">Pertimbangan Atasan</th>
                                <th class="w-[22%] py-3 px-3 md:px-4 text-left">Pengesahan Ketua PA</th>
                                <th class="w-[18%] py-3 px-3 md:px-4 text-left">Tanggal ACC</th>
                                <th class="w-[14%] py-3 px-3 md:px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($daftarPengajuan as $cuti)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left font-semibold text-slate-900 dark:text-white">
                                        {{ $cuti->user->name }}
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-normal">
                                            {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-600 dark:text-slate-300">
                                        {{ $cuti->atasan_penyetuju->name ?? ($cuti->user->pegawai?->atasan?->user?->name ?? '-') }}
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left font-medium text-emerald-600 dark:text-emerald-400">
                                        &check; {{ $cuti->ketua_pengesah->name ?? 'Ketua PA' }}
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-600 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($cuti->updated_at)->isoFormat('D MMMM YYYY, HH:mm') }}
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                        <a href="{{ route('cuti.input_nomor', $cuti->id) }}" class="inline-block px-3 py-1.5 text-xs font-bold rounded-none text-slate-900 bg-dinas-gold hover:bg-yellow-500 shadow-xs transition-colors">
                                            Input Nomor
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kontainer Card List (Tampilan Mobile: block md:hidden) -->
            <div class="block md:hidden flex flex-col gap-3.5">
                @foreach ($daftarPengajuan as $cuti)
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 border-l-4 border-l-dinas-green flex flex-col gap-2.5">
                        <!-- Header Card: Nama Pemohon & Jenis Cuti -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">
                                    {{ $cuti->user->name }}
                                </h4>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">
                                    NIP: {{ $cuti->user->pegawai?->nip ?? ($cuti->user->nip ?: '-') }} &bull; {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                </p>
                            </div>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shrink-0">
                                {{ $cuti->jenis_cuti }}
                            </span>
                        </div>

                        <!-- Konten Card: Detail Pertimbangan, Pengesahan & Periode -->
                        <div class="flex flex-col gap-1.5 text-[11px]">
                            <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Pertimbangan Atasan</span>
                                <span class="text-slate-800 dark:text-slate-200 font-medium text-right">
                                    {{ $cuti->atasan_penyetuju->name ?? ($cuti->user->pegawai?->atasan?->user?->name ?? '-') }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Pengesahan Ketua PA</span>
                                <span class="text-dinas-green dark:text-emerald-400 font-semibold text-right">
                                    &check; {{ $cuti->ketua_pengesah->name ?? 'Ketua PA' }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Periode & Durasi</span>
                                <span class="text-slate-800 dark:text-slate-200 font-medium text-right">
                                    <span class="text-dinas-green dark:text-emerald-400 font-bold">{{ $cuti->durasi_hari }} Hari</span> &bull; {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMM') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMM YYYY') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Tanggal ACC</span>
                                <span class="text-slate-700 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($cuti->updated_at)->isoFormat('D MMM YYYY, HH:mm') }}
                                </span>
                            </div>
                        </div>

                        <!-- Aksi Footer: Tombol Lebar Penuh (w-full) Emas Dinas -->
                        <a href="{{ route('cuti.input_nomor', $cuti->id) }}" class="mt-2 block w-full text-center bg-dinas-gold text-slate-900 text-xs font-bold py-2.5 rounded-lg hover:bg-yellow-500 shadow-xs transition">
                            Terbitkan Nomor Surat
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State (Desktop & Mobile) -->
            <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Antrean Kosong!</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Saat ini tidak ada berkas yang perlu diproses.</p>
            </div>
        @endif
    </div>
</x-app-layout>