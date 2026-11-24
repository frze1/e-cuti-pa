<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JabatanController extends Controller
{
    /**
     * Pastikan hanya Administrator Kepegawaian yang dapat mengelola jabatan
     */
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Fitur Manajemen Jabatan hanya dapat diakses oleh Administrator Kepegawaian.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = Jabatan::with(['parent', 'children.pegawais.user', 'pegawais.user'])->withCount('children');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_jabatan', 'like', "%{$q}%")
                    ->orWhereHas('parent', function ($pq) use ($q) {
                        $pq->where('nama_jabatan', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pegawais.user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('struktural') && $request->struktural !== 'all') {
            $query->where('is_struktural', $request->struktural === '1');
        }

        $sort = $request->input('sort', 'struktural_desc');
        switch ($sort) {
            case 'bawahan_desc':
                $query->orderBy('children_count', 'desc')->orderBy('nama_jabatan', 'asc');
                break;
            case 'fungsional_desc':
                $query->orderBy('is_struktural', 'asc')->orderBy('nama_jabatan', 'asc');
                break;
            case 'nama_asc':
                $query->orderBy('nama_jabatan', 'asc');
                break;
            case 'nama_desc':
                $query->orderBy('nama_jabatan', 'desc');
                break;
            case 'terbaru':
                $query->orderBy('id', 'desc');
                break;
            case 'terlama':
                $query->orderBy('id', 'asc');
                break;
            case 'struktural_desc':
            default:
                $query->orderBy('is_struktural', 'desc')
                    ->orderBy('nama_jabatan', 'asc');
                break;
        }

        $jabatans = $query->paginate(15)
            ->withQueryString();

        $countTotal = Jabatan::count();
        $countStruktural = Jabatan::where('is_struktural', true)->count();
        $countNonStruktural = Jabatan::where('is_struktural', false)->count();

        return view('jabatan.index', compact('jabatans', 'countTotal', 'countStruktural', 'countNonStruktural', 'sort'));
    }

    public function create()
    {
        $this->checkAdmin();

        $daftarAtasan = Jabatan::where('is_struktural', true)
            ->orderBy('nama_jabatan', 'asc')
            ->get();

        return view('jabatan.create', compact('daftarAtasan'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        if ($request->input('parent_id') === 'null' || $request->input('parent_id') === '') {
            $request->merge(['parent_id' => null]);
        }

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan',
            'parent_id' => [
                'nullable',
                'exists:jabatans,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = Jabatan::find($value);
                        if ($parent && !$parent->is_struktural) {
                            $fail('Jabatan atasan langsung (induk jabatan) harus merupakan jabatan struktural.');
                        }
                    }
                },
            ],
            'is_struktural' => 'nullable',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique' => 'Nama jabatan tersebut sudah terdaftar.',
            'parent_id.exists' => 'Pilihan jabatan atasan tidak valid.',
        ]);

        Jabatan::create([
            'nama_jabatan' => $validated['nama_jabatan'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_struktural' => $request->boolean('is_struktural'),
        ]);

        return redirect()->route('jabatan.index')->with('success', 'Data jabatan baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->checkAdmin();

        $jabatan = Jabatan::findOrFail($id);
        $daftarAtasan = Jabatan::where('id', '!=', $id)
            ->where(function ($q) use ($jabatan) {
                $q->where('is_struktural', true);
                if ($jabatan->parent_id) {
                    $q->orWhere('id', $jabatan->parent_id);
                }
            })
            ->orderBy('nama_jabatan', 'asc')
            ->get();

        return view('jabatan.edit', compact('jabatan', 'daftarAtasan'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $jabatan = Jabatan::findOrFail($id);

        if ($request->input('parent_id') === 'null' || $request->input('parent_id') === '') {
            $request->merge(['parent_id' => null]);
        }

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255|unique:jabatans,nama_jabatan,' . $id,
            'parent_id' => [
                'nullable',
                'exists:jabatans,id',
                function ($attribute, $value, $fail) use ($id, $jabatan) {
                    if ($value == $id) {
                        $fail('Jabatan tidak dapat menjadi atasan langsung untuk dirinya sendiri.');
                    }
                    if ($value && in_array((int)$value, $this->getDescendantIds($jabatan))) {
                        $fail('Tidak dapat memilih jabatan bawahan sebagai atasan (terjadi perputaran hierarki/circular reference).');
                    }
                    if ($value) {
                        $parent = Jabatan::find($value);
                        if ($parent && !$parent->is_struktural) {
                            $fail('Jabatan atasan langsung (induk jabatan) harus merupakan jabatan struktural.');
                        }
                    }
                },
            ],
            'is_struktural' => 'nullable',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique' => 'Nama jabatan tersebut sudah digunakan.',
            'parent_id.exists' => 'Pilihan jabatan atasan tidak valid.',
        ]);

        $jabatan->update([
            'nama_jabatan' => $validated['nama_jabatan'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_struktural' => $request->boolean('is_struktural'),
        ]);

        $redirectParams = array_filter($request->only(['page', 'q', 'struktural']), fn($v) => !is_null($v) && $v !== '');

        return redirect()->route('jabatan.index', $redirectParams)->with('success', 'Data jabatan dan struktur hierarki berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin();

        $jabatan = Jabatan::withCount(['pegawais', 'children'])->findOrFail($id);

        if ($jabatan->pegawais_count > 0) {
            return back()->with('error', "Jabatan '{$jabatan->nama_jabatan}' tidak dapat dihapus karena masih diduduki oleh {$jabatan->pegawais_count} aparatur.");
        }

        if ($jabatan->children_count > 0) {
            return back()->with('error', "Jabatan '{$jabatan->nama_jabatan}' tidak dapat dihapus karena masih menjadi atasan langsung bagi {$jabatan->children_count} jabatan lainnya.");
        }

        $jabatan->delete();

        $redirectParams = array_filter($request->only(['page', 'q', 'struktural']), fn($v) => !is_null($v) && $v !== '');

        return redirect()->route('jabatan.index', $redirectParams)->with('success', "Jabatan '{$jabatan->nama_jabatan}' berhasil dihapus dari sistem.");
    }

    /**
     * Helper rekursif untuk mengambil semua ID keturunan/bawahan jabatan
     */
    private function getDescendantIds(Jabatan $jabatan): array
    {
        $ids = [];
        foreach ($jabatan->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}

