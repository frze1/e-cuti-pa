<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'jabatan_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status_pegawai',
        'sisa_cuti_n',
        'sisa_cuti_n_1',
        'sisa_cuti_n_2',
    ];

    /**
     * Nama lengkap aparatur dari akun user
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->user?->name ?? '-';
    }

    /**
     * Override fill untuk memetakan input 'jabatan' ke 'jabatan_id' secara transparan jika ada pemanggilan lama,
     * serta mengabaikan kolom-kolom yang sudah dipindahkan ke arsitektur master jabatan.
     */
    public function fill(array $attributes)
    {
        if (isset($attributes['jabatan']) && !isset($attributes['jabatan_id'])) {
            $val = $attributes['jabatan'];
            if ($val instanceof Jabatan) {
                $attributes['jabatan_id'] = $val->id;
            } elseif (is_numeric($val)) {
                $attributes['jabatan_id'] = (int) $val;
            } elseif (is_string($val) && !empty($val)) {
                $jabatanRecord = Jabatan::firstOrCreate(['nama_jabatan' => $val]);
                $attributes['jabatan_id'] = $jabatanRecord->id;
            }
            unset($attributes['jabatan']);
        }

        if (isset($attributes['is_atasan']) && $attributes['is_atasan'] && isset($attributes['jabatan_id'])) {
            Jabatan::where('id', $attributes['jabatan_id'])->update([
                'is_struktural' => true
            ]);
        }

        if (isset($attributes['atasan_langsung_id']) && !empty($attributes['atasan_langsung_id'])) {
            $atasanPeg = self::find($attributes['atasan_langsung_id']);
            if ($atasanPeg && $atasanPeg->jabatan_id && isset($attributes['jabatan_id'])) {
                Jabatan::where('id', $attributes['jabatan_id'])->whereNull('parent_id')->update([
                    'parent_id' => $atasanPeg->jabatan_id
                ]);
            }
        }

        unset($attributes['pangkat_golongan'], $attributes['is_atasan'], $attributes['atasan_langsung_id'], $attributes['unit_kerja']);

        return parent::fill($attributes);
    }

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
    ];

    /**
     * Hitung durasi masa kerja secara otomatis dari tanggal_masuk.
     * Jika tanggal_keluar terisi (sudah keluar/pensiun/mutasi), hitung selisih mutlak
     * dari tanggal_masuk ke tanggal_keluar agar angka masa kerjanya berhenti (terkunci).
     */
    public function getMasaKerjaAttribute()
    {
        if ($this->tanggal_masuk) {
            $targetDate = $this->tanggal_keluar ? Carbon::parse($this->tanggal_keluar) : now();
            $diff = Carbon::parse($this->tanggal_masuk)->diff($targetDate);
            $years = $diff->y;
            $months = $diff->m;
            $parts = [];
            if ($years > 0) {
                $parts[] = "{$years} Tahun";
            }
            if ($months > 0 || $years === 0) {
                $parts[] = "{$months} Bulan";
            }
            return implode(' ', $parts);
        }
        return '-';
    }

    // Relasi ke Master Jabatan
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function getNamaJabatanAttribute(): string
    {
        return $this->jabatan?->nama_jabatan ?? '-';
    }

    // Relasi kebalikan ke akun User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper untuk mendapatkan aparatur atasan langsung berdasarkan hierarki jabatan (parent_id)
     */
    public function getAtasanPegawai()
    {
        if (!$this->jabatan || !$this->jabatan->parent_id) {
            return null;
        }
        return self::where('jabatan_id', $this->jabatan->parent_id)
            ->where('status_pegawai', 'Aktif')
            ->first();
    }

    /**
     * Accessor dinamis untuk $pegawai->atasan (kompatibilitas dengan seluruh kode alur cuti & views)
     */
    public function getAtasanAttribute()
    {
        return $this->getAtasanPegawai();
    }

    protected ?bool $memoizedIsPimpinanFinal = null;
    protected ?bool $memoizedIsAtasanLangsung = null;

    /**
     * Helper untuk cek apakah ini pimpinan final (jabatan pucuk hierarki tanpa atasan / parent_id = null)
     */
    public function getIsPimpinanFinalAttribute(): bool
    {
        if ($this->memoizedIsPimpinanFinal !== null) {
            return $this->memoizedIsPimpinanFinal;
        }

        // Pimpinan final adalah mereka yang memiliki jabatan, TAPI jabatan tersebut tidak memiliki atasan (parent_id = null).
        $isFinal = (bool) ($this->jabatan_id !== null && $this->jabatan && $this->jabatan->parent_id === null);

        return $this->memoizedIsPimpinanFinal = $isFinal;
    }

    /**
     * Helper untuk cek apakah pegawai ini menjabat atasan langsung (struktural non-ketua)
     */
    public function getIsAtasanLangsungAttribute(): bool
    {
        if ($this->memoizedIsAtasanLangsung !== null) {
            return $this->memoizedIsAtasanLangsung;
        }
        if ($this->is_pimpinan_final) {
            return $this->memoizedIsAtasanLangsung = false;
        }
        return $this->memoizedIsAtasanLangsung = (bool) (
            ($this->jabatan?->is_struktural ?? false)
            || $this->bawahan()->exists()
        );
    }

    /**
     * Accessor dinamis untuk cek apakah pegawai ini menjabat posisi struktural/atasan (baik atasan langsung maupun ketua)
     */
    public function getIsAtasanAttribute(): bool
    {
        return $this->is_pimpinan_final || $this->is_atasan_langsung;
    }

    /**
     * Query bawahan langsung berdasarkan pohon jabatan
     */
    public function bawahan()
    {
        $jabatanId = $this->jabatan_id;
        if (!$jabatanId) {
            return self::whereRaw('1 = 0');
        }
        return self::whereHas('jabatan', function ($q) use ($jabatanId) {
            $q->where('parent_id', $jabatanId);
        });
    }

    /**
     * Accessor bawahan untuk kemudahan koleksi
     */
    public function getBawahanAttribute()
    {
        return $this->bawahan()->get();
    }
}
