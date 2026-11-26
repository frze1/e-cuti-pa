<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Persetujuan Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar pengajuan cuti aparatur bawahan yang memerlukan pertimbangan dan persetujuan Anda
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
                                <th class="w-[26%] py-3 px-3 md:px-4 text-left">Nama Pegawai</th>
                                <th class="w-[20%] py-3 px-3 md:px-4 text-left">Jenis Cuti</th>
                                <th class="w-[26%] py-3 px-3 md:px-4 text-left">Tanggal Pelaksanaan</th>
                                <th class="w-[12%] py-3 px-3 md:px-4 text-center">Durasi</th>
                                <th class="w-[16%] py-3 px-3 md:px-4 text-center">Aksi</th>
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
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                        <span class="inline-block px-2 py-0.5 rounded-none text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                            {{ $cuti->jenis_cuti }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-700 dark:text-slate-300">
                                        {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMMM YYYY') }}
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center font-bold text-slate-800 dark:text-slate-200">
                                        {{ $cuti->durasi_hari }} Hari
                                    </td>
                                    <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                        <a href="{{ route('cuti.tinjau', $cuti->id) }}" class="inline-block px-3 py-1.5 text-xs font-semibold rounded-none text-white bg-dinas-green hover:bg-dinas-greenhover shadow-xs transition">
                                            Tinjau Berkas
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
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 border-l-4 border-l-amber-500 flex flex-col gap-2.5">
                        <!-- Header Card: Nama Pemohon & Jenis Cuti -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">
                                    {{ $cuti->user->name }}
                                </h4>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                                    {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                </p>
                            </div>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 shrink-0">
                                {{ $cuti->jenis_cuti }}
                            </span>
                        </div>

                        <!-- Konten Card: Detail Tanggal & Durasi -->
                        <div class="flex flex-col gap-1.5 text-[11px]">
                            <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Tgl Pengajuan</span>
                                <span class="text-slate-800 dark:text-slate-200 font-medium">
                                    {{ $cuti->created_at->isoFormat('D MMMM YYYY') }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Periode Cuti</span>
                                <span class="text-slate-800 dark:text-slate-200 font-medium">
                                    {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMM') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMM YYYY') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Durasi</span>
                                <span class="text-dinas-green dark:text-emerald-400 font-bold">
                                    {{ $cuti->durasi_hari }} Hari
                                </span>
                            </div>
                        </div>

                        <!-- Aksi Footer: Tombol Lebar Penuh (w-full) -->
                        <a href="{{ route('cuti.tinjau', $cuti->id) }}" class="mt-2 block w-full text-center bg-dinas-green text-white text-xs font-semibold py-2.5 rounded-lg hover:bg-dinas-greenhover shadow-xs transition">
                            Tinjau Berkas
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