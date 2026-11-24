<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('pegawai.index', request()->query()) }}" class="hover:text-dinas-green dark:hover:text-dinas-gold transition">Data Pegawai</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Edit: {{ $pegawai->user->name }}</span>
            </div>
            <h2 class="font-bold text-lg sm:text-xl text-slate-800 dark:text-white leading-tight truncate">
                {{ __('Perbarui Data Pegawai & Akun') }}
            </h2>
            <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Modifikasi informasi jabatan, atasan langsung, kuota cuti, atau ubah kredensial akun
            </p>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto pb-6">
        <!-- Tombol Aksi Kembali (Area Konten) -->
        <div class="mb-4 flex justify-start">
            <a href="{{ route('pegawai.index', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </div>

        <form method="POST" action="{{ route('pegawai.update', array_merge(['pegawai' => $pegawai->id], request()->query())) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @foreach(request()->query() as $queryKey => $queryVal)
                    @if(!empty($queryVal))
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                    @endif
                @endforeach

                <!-- Section 1: Kredensial Akun Pengguna -->
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 border-t-4 border-t-dinas-green overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                                1
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white">Informasi Akun Login</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Kredensial akses aplikasi Sistem Manajemen Cuti</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">ID User #{{ $pegawai->user_id }}</span>
                    </div>

                    <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email Dinas -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Alamat Email Dinas <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $pegawai->user->email) }}" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('email') border-rose-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Kata sandi awal akun ini akan otomatis diatur menjadi <i>password</i>.
                            </p>
                        </div>

                        <!-- Role Akun -->
                        <div>
                            <label for="role" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Peran Akun Sistem (Role) <span class="text-rose-500">*</span>
                            </label>
                            <select name="role" id="role" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('role') border-rose-500 @enderror">
                                <option value="pegawai" {{ old('role', $pegawai->user->role) === 'pegawai' ? 'selected' : '' }}>Pegawai (Pengguna Standar)</option>
                                <option value="admin" {{ old('role', $pegawai->user->role) === 'admin' ? 'selected' : '' }}>Admin (Administrator Kepegawaian)</option>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                Role menentukan hak akses teknis aplikasi. Jabatan kedinasan dipilih pada bagian Data Kepegawaian di bawah.
                            </p>
                            @error('role')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Kepegawaian & Hierarki -->
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 border-t-4 border-t-dinas-green relative z-20">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                                2
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white">Identitas & Jabatan Kepegawaian</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Data profil aparatur sipil negara dan penempatan hierarki kerja</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">ID Pegawai #{{ $pegawai->id }}</span>
                    </div>

                    <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nama Lengkap & Gelar -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $pegawai->user->name) }}" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('name') border-rose-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- NIP -->
                        <div>
                            <label for="nip" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nomor Induk Pegawai (NIP)
                            </label>
                            <input type="text" name="nip" id="nip" value="{{ old('nip', $pegawai->nip) }}"
                                placeholder="Contoh: 198503152010011002"
                                class="w-full px-3.5 py-2.5 text-xs font-mono rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('nip') border-rose-500 @enderror">
                            @error('nip')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Masuk / TMT -->
                        <div>
                            <label for="tanggal_masuk" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tanggal Masuk / TMT
                            </label>
                            <input type="date" name="tanggal_masuk" id="tanggal_masuk" 
                                value="{{ old('tanggal_masuk', $pegawai->tanggal_masuk ? $pegawai->tanggal_masuk->format('Y-m-d') : '') }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('tanggal_masuk') border-rose-500 @enderror">
                            @error('tanggal_masuk')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                            @if($pegawai->masa_kerja && $pegawai->masa_kerja !== '-')
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                    Masa kerja terhitung: <span class="font-semibold text-dinas-green dark:text-emerald-400">{{ $pegawai->masa_kerja }}</span>
                                    @if($pegawai->tanggal_keluar)
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold">(Terkunci s.d. {{ $pegawai->tanggal_keluar->isoFormat('D MMMM YYYY') }})</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <!-- Status Pegawai -->
                        <div>
                            <label for="status_pegawai" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Status Kepegawaian <span class="text-rose-500">*</span>
                            </label>
                            <select name="status_pegawai" id="status_pegawai" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('status_pegawai') border-rose-500 @enderror">
                                <option value="Aktif" {{ old('status_pegawai', $pegawai->status_pegawai ?? 'Aktif') === 'Aktif' ? 'selected' : '' }}>Aktif (Bertugas)</option>
                                <option value="Pensiun" {{ old('status_pegawai', $pegawai->status_pegawai) === 'Pensiun' ? 'selected' : '' }}>Pensiun</option>
                                <option value="Mutasi" {{ old('status_pegawai', $pegawai->status_pegawai) === 'Mutasi' ? 'selected' : '' }}>Mutasi Keluar</option>
                                <option value="Berhenti" {{ old('status_pegawai', $pegawai->status_pegawai) === 'Berhenti' ? 'selected' : '' }}>Berhenti / Resign</option>
                            </select>
                            @error('status_pegawai')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Keluar -->
                        <div>
                            <label for="tanggal_keluar" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Tanggal Keluar / SK Pemberhentian
                            </label>
                            <input type="date" name="tanggal_keluar" id="tanggal_keluar" 
                                value="{{ old('tanggal_keluar', $pegawai->tanggal_keluar ? $pegawai->tanggal_keluar->format('Y-m-d') : '') }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('tanggal_keluar') border-rose-500 @enderror">
                            @error('tanggal_keluar')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                Diisi jika aparatur sudah pensiun/mutasi/berhenti agar perhitungan masa kerja otomatis terkunci (*freeze*).
                            </p>
                        </div>

                        <!-- Jabatan -->
                        <div class="md:col-span-2">
                            <label for="jabatan_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Jabatan Kedinasan <span class="text-rose-500">*</span>
                            </label>
                            <select name="jabatan_id" id="jabatan_id" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-none border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-dinas-green focus:border-dinas-green @error('jabatan_id') border-rose-500 @enderror">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatans as $jbt)
                                    <option value="{{ $jbt->id }}" {{ (string)old('jabatan_id', $pegawai->jabatan_id) === (string)$jbt->id ? 'selected' : '' }}>{{ $jbt->nama_jabatan }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                Hierarki atasan langsung otomatis terhubung sesuai pohon struktur jabatan yang dipilih.
                            </p>
                            @error('jabatan_id')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Saldo Cuti Tahunan -->
                <div class="bg-white dark:bg-slate-800 rounded-none shadow-xs border border-slate-300 dark:border-slate-700 border-t-4 border-t-dinas-green overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-slate-50/60 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-none bg-dinas-green/10 dark:bg-dinas-green/20 text-dinas-green dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white">Alokasi Kuota Cuti Tahunan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Saldo hak cuti tahunan berjalan dan sisa akumulasi cuti tahun-tahun sebelumnya</p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Saldo Cuti N -->
                        <div>
                            <label for="sisa_cuti_n" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Cuti Tahun N (Berjalan) <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-none shadow-2xs">
                                <input type="number" name="sisa_cuti_n" id="sisa_cuti_n" value="{{ old('sisa_cuti_n', $pegawai->sisa_cuti_n) }}" required min="0" max="100"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-none border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:z-10 focus:ring-2 focus:ring-dinas-green focus:border-dinas-green @error('sisa_cuti_n') border-rose-500 @enderror">
                                <span class="inline-flex items-center px-3 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-l-0 border-slate-300 dark:border-slate-600 rounded-none select-none shrink-0">
                                    Hari
                                </span>
                            </div>
                            @error('sisa_cuti_n')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Saldo Cuti N-1 -->
                        <div>
                            <label for="sisa_cuti_n_1" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Cuti Tahun N-1 (Sebelumnya)
                            </label>
                            <div class="flex rounded-none shadow-2xs">
                                <input type="number" name="sisa_cuti_n_1" id="sisa_cuti_n_1" value="{{ old('sisa_cuti_n_1', $pegawai->sisa_cuti_n_1) }}" min="0" max="100"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-none border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:z-10 focus:ring-2 focus:ring-dinas-green focus:border-dinas-green @error('sisa_cuti_n_1') border-rose-500 @enderror">
                                <span class="inline-flex items-center px-3 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-l-0 border-slate-300 dark:border-slate-600 rounded-none select-none shrink-0">
                                    Hari
                                </span>
                            </div>
                            @error('sisa_cuti_n_1')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Saldo Cuti N-2 -->
                        <div>
                            <label for="sisa_cuti_n_2" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Saldo Cuti Tahun N-2
                            </label>
                            <div class="flex rounded-none shadow-2xs">
                                <input type="number" name="sisa_cuti_n_2" id="sisa_cuti_n_2" value="{{ old('sisa_cuti_n_2', $pegawai->sisa_cuti_n_2) }}" min="0" max="100"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-none border border-slate-300 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:z-10 focus:ring-2 focus:ring-dinas-green focus:border-dinas-green @error('sisa_cuti_n_2') border-rose-500 @enderror">
                                <span class="inline-flex items-center px-3 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-l-0 border-slate-300 dark:border-slate-600 rounded-none select-none shrink-0">
                                    Hari
                                </span>
                            </div>
                            @error('sisa_cuti_n_2')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2">
                    <a href="{{ route('pegawai.index', request()->query()) }}" class="w-full sm:w-auto text-center px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-none transition">
                        Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto text-center px-6 py-2.5 bg-dinas-green hover:bg-dinas-greenhover text-white text-xs font-bold rounded-none shadow-xs transition-colors">
                        Perbarui Data Pegawai
                    </button>
                </div>

            </form>
        </div>
</x-app-layout>
