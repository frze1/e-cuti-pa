<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-bold text-lg md:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Riwayat & Status Pengajuan Cuti') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Pantau progres verifikasi atasan, pengesahan pimpinan, dan penerbitan izin cuti Anda
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4 pb-6">
        <!-- Tampilan Desktop & Tablet: Format Tabel (hidden md:block) -->
        <div class="hidden md:block bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="table-fixed w-full min-w-[800px] md:w-full text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-300 dark:border-slate-700">
                            <th class="w-[15%] py-3 px-3 md:px-4 text-left">Tanggal Pengajuan</th>
                            <th class="w-[15%] py-3 px-3 md:px-4 text-left">Jenis Cuti</th>
                            <th class="w-[18%] py-3 px-3 md:px-4 text-left">Pelaksanaan</th>
                            <th class="w-[15%] py-3 px-3 md:px-4 text-center">Status</th>
                            <th class="w-[22%] py-3 px-3 md:px-4 text-left">Catatan Pimpinan</th>
                            <th class="w-[15%] py-3 px-3 md:px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($riwayatCuti as $cuti)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="py-3 px-3 md:px-4 text-slate-700 dark:text-slate-300 whitespace-normal break-words align-top text-left">
                                    {{ $cuti->created_at->isoFormat('D MMMM YYYY') }}
                                </td>
                                <td class="py-3 px-3 md:px-4 font-semibold text-slate-900 dark:text-slate-100 whitespace-normal break-words align-top text-left">
                                    {{ $cuti->jenis_cuti }}
                                </td>
                                <td class="py-3 px-3 md:px-4 text-slate-800 dark:text-slate-200 whitespace-normal break-words align-top text-left">
                                    <div>{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->format('d/m/Y') }}</div>
                                    <span class="text-slate-500 dark:text-slate-400 block text-[11px] mt-0.5">({{ $cuti->durasi_hari }} Hari Kerja)</span>
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    @php
                                        $badgeClass = match($cuti->status) {
                                            'SELESAI_DISETUJUI', 'Disetujui Atasan' => 'bg-dinas-green/10 text-dinas-green font-bold border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                                            'DITOLAK' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                                            'DITANGGUHKAN', 'DITANGGUHKAN_ATASAN', 'DITANGGUHKAN_KETUA', 'REVISI_ATASAN', 'Revisi Atasan', 'REVISI_KETUA', 'REVISI_AKHIR' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                                            'Dibatalkan Pegawai', 'DIBATALKAN_PEGAWAI' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                                            default => 'bg-dinas-gold/20 text-yellow-800 font-bold border-yellow-300/60 dark:bg-yellow-950/60 dark:text-yellow-300 dark:border-yellow-800',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-none text-[10px] md:text-[11px] font-semibold border {{ $badgeClass }}">
                                        {{ str_replace('_', ' ', $cuti->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 md:px-4 text-amber-600 dark:text-amber-400 italic whitespace-normal break-words align-top text-left text-xs">
                                    {{ $cuti->catatan_atasan ?? $cuti->catatan_revisi ?? '-' }}
                                </td>
                                <td class="py-3 px-3 md:px-4 whitespace-normal break-words align-top text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        {{-- Tombol Perbaiki Pengajuan --}}
                                        @if(in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN']))
                                            <a href="{{ route('cuti.edit', $cuti->id) }}" class="inline-flex items-center justify-center gap-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-1 px-2.5 rounded-none border border-transparent text-xs shadow-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Perbaiki Pengajuan
                                            </a>
                                        @endif

                                        @if(in_array($cuti->status, ['Menunggu Atasan', 'MENUNGGU_ATASAN', 'Revisi Atasan', 'REVISI_ATASAN']))
                                            <form action="{{ route('cuti.tarik', $cuti->id) }}" method="POST" class="inline-block"
                                                  data-confirm="true"
                                                  data-confirm-title="Tarik Permohonan Cuti?"
                                                  data-confirm-message="Apakah Anda yakin ingin menarik permohonan cuti ini? Kuota cuti Anda akan dikembalikan secara otomatis."
                                                  data-confirm-btn="Ya, Tarik Permohonan"
                                                  data-confirm-cancel="Batal"
                                                  data-confirm-type="danger">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center justify-center gap-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-1 px-2.5 rounded-none border border-transparent text-xs shadow-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Tarik Permohonan
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Tombol Unduh Word jika disetujui --}}
                                        @if(in_array($cuti->status, ['SELESAI_DISETUJUI', 'Disetujui Atasan']))
                                            <span class="max-w-48 text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                                                Surat cuti resmi telah diterbitkan. Salinan dapat diminta di Bagian Kepegawaian.
                                            </span>
                                        @elseif(!in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN', 'Menunggu Atasan', 'MENUNGGU_ATASAN']))
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">Terkunci</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    Anda belum pernah mengajukan cuti.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tampilan Mobile: Format Card List (block md:hidden) -->
        <div class="block md:hidden flex flex-col gap-3.5">
            @forelse ($riwayatCuti as $cuti)
                @php
                    $mobileBadgeClass = match($cuti->status) {
                        'SELESAI_DISETUJUI', 'Disetujui Atasan', 'DISETUJUI', 'Disetujui' => 'bg-dinas-green/10 text-dinas-green border-dinas-green/20 dark:bg-dinas-green/20 dark:text-emerald-400 dark:border-dinas-green/30',
                        'DITOLAK' => 'bg-red-50 text-red-700 dark:bg-rose-950/60 dark:text-rose-300 border-red-200 dark:border-rose-800',
                        'Dibatalkan Pegawai', 'DIBATALKAN_PEGAWAI' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600',
                        default => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                    };
                    $mobileStatusLabel = match($cuti->status) {
                        'SELESAI_DISETUJUI' => 'Disetujui',
                        'DITOLAK' => 'Ditolak',
                        'MENUNGGU_ATASAN' => 'Menunggu Atasan',
                        'MENUNGGU_PENOMORAN' => 'Menunggu Nomor',
                        'MENUNGGU_KETUA' => 'Menunggu Ketua',
                        'REVISI_ATASAN', 'Revisi Atasan' => 'Revisi Atasan',
                        'Dibatalkan Pegawai', 'DIBATALKAN_PEGAWAI' => 'Dibatalkan',
                        default => str_replace('_', ' ', $cuti->status)
                    };
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 flex flex-col gap-2.5">
                    <!-- Header Card: Jenis Cuti & Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-800 dark:text-white font-bold">
                            {{ $cuti->jenis_cuti }}
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $mobileBadgeClass }}">
                            {{ $mobileStatusLabel }}
                        </span>
                    </div>

                    <!-- Konten Card: Detail Data 2 Kolom -->
                    <div class="flex flex-col gap-1.5 text-[11px]">
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Tgl Pengajuan</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                {{ $cuti->created_at->isoFormat('D MMMM YYYY') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5">
                            <span class="text-slate-500 dark:text-slate-400">Rentang Waktu</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">
                                {{ \Carbon\Carbon::parse($cuti->tgl_mulai)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($cuti->tgl_selesai)->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 dark:border-slate-700/60 pb-1.5 last:border-0 last:pb-0">
                            <span class="text-slate-500 dark:text-slate-400">Durasi</span>
                            <span class="text-dinas-green dark:text-emerald-400 font-bold">
                                {{ $cuti->durasi_hari }} Hari Kerja
                            </span>
                        </div>
                        @if($cuti->catatan_atasan || $cuti->catatan_revisi)
                            <div class="flex justify-between pt-0.5 text-amber-600 dark:text-amber-400 italic">
                                <span class="text-slate-500 dark:text-slate-400 not-italic">Catatan</span>
                                <span class="text-right max-w-[65%]">{{ $cuti->catatan_atasan ?? $cuti->catatan_revisi }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Card: Tombol Aksi & Dokumen -->
                    @if(in_array($cuti->status, ['SELESAI_DISETUJUI', 'Disetujui Atasan']) || in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN', 'Menunggu Atasan', 'MENUNGGU_ATASAN']))
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                            @if(in_array($cuti->status, ['Revisi Atasan', 'REVISI_ATASAN']))
                                <a href="{{ route('cuti.edit', $cuti->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold py-1 px-2.5 rounded shadow-xs inline-flex items-center gap-1 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span>Perbaiki</span>
                                </a>
                            @endif

                            @if(in_array($cuti->status, ['Menunggu Atasan', 'MENUNGGU_ATASAN', 'Revisi Atasan', 'REVISI_ATASAN']))
                                <form action="{{ route('cuti.tarik', $cuti->id) }}" method="POST" class="inline-block"
                                      data-confirm="true"
                                      data-confirm-title="Tarik Permohonan Cuti?"
                                      data-confirm-message="Apakah Anda yakin ingin menarik permohonan cuti ini? Kuota cuti Anda akan dikembalikan secara otomatis."
                                      data-confirm-btn="Ya, Tarik Permohonan"
                                      data-confirm-cancel="Batal"
                                      data-confirm-type="danger">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold py-1 px-2.5 rounded shadow-xs inline-flex items-center gap-1 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span>Tarik</span>
                                    </button>
                                </form>
                            @endif

                            @if(in_array($cuti->status, ['SELESAI_DISETUJUI', 'Disetujui Atasan']))
                                <span class="text-right text-[10px] leading-relaxed text-slate-500 dark:text-slate-400">
                                    Surat cuti resmi telah diterbitkan. Salinan dapat diminta di Bagian Kepegawaian.
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 text-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    Anda belum pernah mengajukan cuti.
                </div>
            @endforelse
        </div>
    </div>
    </div>
</x-app-layout>
