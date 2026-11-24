<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Membolehkan semua kolom diisi massal kecuali ID

    protected $attributes = [
        'satuan_cuti' => 'hari',
    ];

    // Relasi ke pemohon (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Atasan Langsung yang menyetujui berkas ini (Snapshot Transaksi)
    public function atasan_penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_atasan_id');
    }

    // Relasi ke Ketua PA yang mengesahkan berkas ini (Snapshot Transaksi)
    public function ketua_pengesah()
    {
        return $this->belongsTo(User::class, 'disahkan_oleh_ketua_id');
    }

    // Accessor & Mutator agar catatan_atasan dan catatan_revisi sinkron
    public function getCatatanAtasanAttribute()
    {
        return $this->attributes['catatan_atasan'] ?? $this->attributes['catatan_revisi'] ?? null;
    }

    public function setCatatanAtasanAttribute($value)
    {
        $this->attributes['catatan_atasan'] = $value;
        $this->attributes['catatan_revisi'] = $value;
    }
}
