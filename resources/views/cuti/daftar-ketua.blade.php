<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Keputusan Final Pengajuan Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Daftar pengajuan cuti aparatur yang menunggu keputusan final dan pengesahan Ketua PA
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                            <th class="w-[24%] py-3 px-3 md:px-4 text-left">Pemohon</th>
                            <th class="w-[24%] py-3 px-3 md:px-4 text-left">Disetujui Oleh (Atasan)</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Jenis Cuti</th>
                            <th class="w-[20%] py-3 px-3 md:px-4 text-left">Durasi & Periode</th>
                            <th class="w-[14%] py-3 px-3 md:px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($daftarPengajuan as $cuti)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left font-semibold text-slate-900 dark:text-white">
                                    {{ $cuti->user->name }}
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-normal">
                                        {{ $cuti->user->pegawai?->jabatan?->nama_jabatan ?? ($cuti->user->jabatan ?: '-') }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-700 dark:text-slate-300">
                                    @if($cuti->atasan_penyetuju)
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $cuti->atasan_penyetuju->name }}</span>
                                    @elseif(!$cuti->user->pegawai?->getAtasanPegawai() || $cuti->user->pegawai?->getAtasanPegawai()?->is_pimpinan_final)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-none text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                            Langsung ke Ketua
                                        </span>
                                    @else
                                        {{ $cuti->user->pegawai?->atasan?->user?->name ?? '-' }}
                                    @endif
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left">
                                    <span class="inline-block px-2 py-0.5 rounded-none text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        {{ $cuti->jenis_cuti }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-left text-slate-700 dark:text-slate-300">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $cuti->durasi_hari }} Hari</div>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->isoFormat('D MMM') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->isoFormat('D MMM YYYY') }}</span>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    <a href="{{ route('cuti.tinjau_ketua', $cuti->id) }}" class="inline-block px-3 py-1.5 text-xs font-semibold rounded-none text-white bg-purple-600 hover:bg-purple-700 shadow-xs transition">
                                        Beri Keputusan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada berkas cuti yang menunggu keputusan / pengesahan Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
