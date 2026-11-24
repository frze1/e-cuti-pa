<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
        'parent_id',
        'is_struktural',
    ];

    protected $casts = [
        'is_struktural' => 'boolean',
    ];

    /**
     * Relasi ke Jabatan Atasan Langsung (Pohon Struktur Organisasi)
     */
    public function parent()
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi ke Jabatan-jabatan Bawahan Langsung
     */
    public function children()
    {
        return $this->hasMany(Jabatan::class, 'parent_id');
    }

    /**
     * Relasi One-to-Many ke data Pegawai yang menduduki jabatan ini
     */
    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id');
    }

    /**
     * Mengambil pegawai berstatus Aktif yang saat ini menduduki jabatan ini
     */
    public function getPegawaiAktifAttribute()
    {
        if ($this->relationLoaded('pegawais')) {
            return $this->pegawais->firstWhere('status_pegawai', 'Aktif') ?? $this->pegawais->first();
        }
        return $this->pegawais()->where('status_pegawai', 'Aktif')->first() ?? $this->pegawais()->first();
    }

    /**
     * Cek apakah jabatan ini merupakan posisi pimpinan final/tertinggi (pucuk hierarki tanpa atasan)
     */
    public function getIsPimpinanFinalAttribute(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Konversi otomatis ke string nama_jabatan untuk kemudahan tampilan di Blade
     */
    public function __toString(): string
    {
        return (string) ($this->nama_jabatan ?? '');
    }
}
