<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    /**
     * Hanya admin yang berhak mengelola data kepegawaian
     */
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Administrator Kepegawaian.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = Pegawai::with(['user', 'jabatan.parent']);

        // Filter status_pegawai: Default adalah 'Aktif'
        $statusFilter = $request->input('status', 'Aktif');
        if ($statusFilter !== 'semua' && $statusFilter !== 'all') {
            if ($statusFilter === 'tidak_aktif') {
                $query->where('status_pegawai', '!=', 'Aktif');
            } elseif (in_array($statusFilter, ['Aktif', 'Pensiun', 'Mutasi', 'Berhenti'])) {
                $query->where('status_pegawai', $statusFilter);
            }
        }

        // Filter Role / Hak Akses
        $roleFilter = $request->input('role');
        if (in_array($roleFilter, ['pegawai', 'admin'])) {
            $query->whereHas('user', function ($uq) use ($roleFilter) {
                $uq->where('role', $roleFilter);
            });
        }

        // Filter Jenis Kelamin (NIP digit ke-15: 1 = Laki-laki, 2 = Perempuan)
        $genderFilter = $request->input('gender');
        if ($genderFilter === 'L') {
            $query->whereRaw("SUBSTRING(nip, 15, 1) = '1'");
        } elseif ($genderFilter === 'P') {
            $query->whereRaw("SUBSTRING(nip, 15, 1) = '2'");
        }

        // Filter Jabatan Struktural / Non-Struktural
        $strukturalFilter = $request->input('struktural');
        if ($strukturalFilter === 'struktural') {
            $query->whereHas('jabatan', function ($jq) {
                $jq->where('is_struktural', true);
            });
        } elseif ($strukturalFilter === 'non_struktural') {
            $query->whereHas('jabatan', function ($jq) {
                $jq->where('is_struktural', false);
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nip', 'like', "%{$q}%")
                    ->orWhereHas('jabatan', function ($jq) use ($q) {
                        $jq->where('nama_jabatan', 'like', "%{$q}%");
                    })
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $sort = $request->input('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->orderBy('id', 'asc');
                break;
            case 'nama_asc':
                $query->orderBy(\App\Models\User::select('name')->whereColumn('users.id', 'pegawais.user_id'), 'asc');
                break;
            case 'nama_desc':
                $query->orderBy(\App\Models\User::select('name')->whereColumn('users.id', 'pegawais.user_id'), 'desc');
                break;
            case 'nip_asc':
                $query->orderBy('nip', 'asc');
                break;
            case 'nip_desc':
                $query->orderBy('nip', 'desc');
                break;
            case 'jabatan_asc':
                $query->orderBy(\App\Models\Jabatan::select('nama_jabatan')->whereColumn('jabatans.id', 'pegawais.jabatan_id'), 'asc');
                break;
            case 'cuti_desc':
                $query->orderBy('sisa_cuti_n', 'desc');
                break;
            case 'cuti_asc':
                $query->orderBy('sisa_cuti_n', 'asc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $pegawais = $query->paginate(10)->withQueryString();

        // Statistik / Count untuk tab filter status pegawai
        $countSemua = Pegawai::count();
        $countAktif = Pegawai::where('status_pegawai', 'Aktif')->count();
        $countPensiun = Pegawai::where('status_pegawai', 'Pensiun')->count();
        $countMutasi = Pegawai::where('status_pegawai', 'Mutasi')->count();
        $countBerhenti = Pegawai::where('status_pegawai', 'Berhenti')->count();

        return view('pegawai.index', compact(
            'pegawais',
            'statusFilter',
            'roleFilter',
            'genderFilter',
            'strukturalFilter',
            'sort',
            'countSemua',
            'countAktif',
            'countPensiun',
            'countMutasi',
            'countBerhenti'
        ));
    }

    public function create()
    {
        $this->checkAdmin();

        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('pegawai.create', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        // Backwards compatibility jika input masih berupa teks 'jabatan'
        if (!$request->has('jabatan_id') && $request->filled('jabatan')) {
            $jRec = Jabatan::firstOrCreate(['nama_jabatan' => $request->input('jabatan')]);
            $request->merge(['jabatan_id' => $jRec->id]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:pegawai,admin',
            'nip' => 'nullable|string|max:25|unique:pegawais,nip',
            'jabatan_id' => 'required|exists:jabatans,id',
            'tanggal_masuk' => 'nullable|date',
            'status_pegawai' => 'nullable|in:Aktif,Pensiun,Mutasi,Berhenti',
            'tanggal_keluar' => 'nullable|date',
            'sisa_cuti_n' => 'required|integer|min:0|max:100',
            'sisa_cuti_n_1' => 'nullable|integer|min:0|max:100',
            'sisa_cuti_n_2' => 'nullable|integer|min:0|max:100',
        ], [
            'name.required' => 'Nama lengkap pegawai wajib diisi.',
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.unique' => 'Alamat email tersebut sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'nip.unique' => 'NIP tersebut sudah digunakan pegawai lain.',
            'jabatan_id.required' => 'Jabatan pegawai wajib dipilih.',
            'jabatan_id.exists' => 'Pilihan jabatan tidak valid.',
            'tanggal_masuk.date' => 'Format tanggal masuk / TMT tidak valid.',
            'tanggal_keluar.date' => 'Format tanggal keluar tidak valid.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'password'),
                'role' => $validated['role'],
            ]);

            Pegawai::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'] ?? null,
                'jabatan_id' => $validated['jabatan_id'],
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status_pegawai' => $validated['status_pegawai'] ?? 'Aktif',
                'tanggal_keluar' => $validated['tanggal_keluar'] ?? null,
                'sisa_cuti_n' => $validated['sisa_cuti_n'] ?? 12,
                'sisa_cuti_n_1' => $validated['sisa_cuti_n_1'] ?? 0,
                'sisa_cuti_n_2' => $validated['sisa_cuti_n_2'] ?? 0,
            ]);
        });

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai dan akun pengguna berhasil ditambahkan ke sistem.');
    }

    public function show($id)
    {
        $this->checkAdmin();

        $pegawai = Pegawai::with(['user', 'jabatan.parent'])->findOrFail($id);

        return view('pegawai.show', compact('pegawai'));
    }

    public function edit($id)
    {
        $this->checkAdmin();

        $pegawai = Pegawai::with(['user', 'jabatan'])->findOrFail($id);
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();

        return view('pegawai.edit', compact('pegawai', 'jabatans'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $pegawai = Pegawai::with('user')->findOrFail($id);

        // Backwards compatibility jika input masih berupa teks 'jabatan'
        if (!$request->has('jabatan_id') && $request->filled('jabatan')) {
            $jRec = Jabatan::firstOrCreate(['nama_jabatan' => $request->input('jabatan')]);
            $request->merge(['jabatan_id' => $jRec->id]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $pegawai->user_id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:pegawai,admin',
            'nip' => 'nullable|string|max:25|unique:pegawais,nip,' . $pegawai->id,
            'jabatan_id' => 'required|exists:jabatans,id',
            'tanggal_masuk' => 'nullable|date',
            'status_pegawai' => 'nullable|in:Aktif,Pensiun,Mutasi,Berhenti',
            'tanggal_keluar' => 'nullable|date',
            'sisa_cuti_n' => 'required|integer|min:0|max:100',
            'sisa_cuti_n_1' => 'nullable|integer|min:0|max:100',
            'sisa_cuti_n_2' => 'nullable|integer|min:0|max:100',
        ], [
            'name.required' => 'Nama lengkap pegawai wajib diisi.',
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.unique' => 'Alamat email tersebut sudah terdaftar.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'nip.unique' => 'NIP tersebut sudah digunakan pegawai lain.',
            'jabatan_id.required' => 'Jabatan pegawai wajib dipilih.',
            'jabatan_id.exists' => 'Pilihan jabatan tidak valid.',
            'tanggal_masuk.date' => 'Format tanggal masuk / TMT tidak valid.',
            'tanggal_keluar.date' => 'Format tanggal keluar tidak valid.',
        ]);

        DB::transaction(function () use ($validated, $pegawai) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $pegawai->user->update($userData);

            $pegawai->update([
                'nip' => $validated['nip'] ?? null,
                'jabatan_id' => $validated['jabatan_id'],
                'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
                'status_pegawai' => $validated['status_pegawai'] ?? $pegawai->status_pegawai ?? 'Aktif',
                'tanggal_keluar' => $validated['tanggal_keluar'] ?? null,
                'sisa_cuti_n' => $validated['sisa_cuti_n'] ?? 12,
                'sisa_cuti_n_1' => $validated['sisa_cuti_n_1'] ?? 0,
                'sisa_cuti_n_2' => $validated['sisa_cuti_n_2'] ?? 0,
            ]);
        });

        $redirectParams = array_filter($request->only(['page', 'q', 'status']), fn($v) => !is_null($v) && $v !== '');

        return redirect()->route('pegawai.index', $redirectParams)->with('success', 'Data pegawai dan akun berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin();

        $pegawai = Pegawai::with('user')->findOrFail($id);

        $redirectParams = array_filter($request->only(['page', 'q', 'status']), fn($v) => !is_null($v) && $v !== '');

        if ($pegawai->user_id === Auth::id()) {
            return redirect()->route('pegawai.index', $redirectParams)->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($pegawai) {
            $user = $pegawai->user;
            $pegawai->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('pegawai.index', $redirectParams)->with('success', 'Data pegawai beserta akun login berhasil dihapus.');
    }
}
