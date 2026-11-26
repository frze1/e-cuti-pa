<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'notif_enabled',
    ];

    // Relasi One-to-One ke tabel pegawais
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }

    // Relasi ke data Pengajuan Cuti miliknya
    public function pengajuanCutis()
    {
        return $this->hasMany(PengajuanCuti::class);
    }

    // Helper accessors agar pemanggilan atribut kepegawaian langsung via user tetap kompatibel
    public function getJabatanAttribute()
    {
        return $this->pegawai?->jabatan?->nama_jabatan;
    }

    public function getPangkatGolonganAttribute()
    {
        return null;
    }

    public function getNipAttribute()
    {
        return $this->pegawai?->nip;
    }

    public function getUnitKerjaAttribute()
    {
        return 'Pengadilan Agama Sumber';
    }

    public function getMasaKerjaAttribute()
    {
        return $this->pegawai?->masa_kerja;
    }

    public function getUserAttribute()
    {
        return $this;
    }

    public function getTanggalMasukAttribute()
    {
        return $this->pegawai?->tanggal_masuk;
    }

    public function getJenisKelaminAttribute()
    {
        return $this->pegawai?->jenis_kelamin;
    }

    public function getStatusPegawaiAttribute()
    {
        return $this->pegawai?->status_pegawai ?? 'Aktif';
    }

    public function getSisaCutiNAttribute()
    {
        return $this->pegawai?->sisa_cuti_n ?? 0;
    }

    public function getSisaCutiN1Attribute()
    {
        return $this->pegawai?->sisa_cuti_n_1 ?? 0;
    }

    public function getSisaCutiN2Attribute()
    {
        return $this->pegawai?->sisa_cuti_n_2 ?? 0;
    }

    public function getAtasanAttribute()
    {
        return $this->pegawai?->atasan?->user;
    }

    public function getIsPimpinanFinalAttribute(): bool
    {
        return $this->pegawai?->is_pimpinan_final ?? false;
    }

    public function getIsAtasanLangsungAttribute(): bool
    {
        return $this->pegawai?->is_atasan_langsung ?? false;
    }

    public function getIsAtasanAttribute(): bool
    {
        return $this->pegawai?->is_atasan ?? false;
    }

    public function getBawahanAttribute()
    {
        return $this->pegawai ? $this->pegawai->bawahan : collect();
    }

    public function bawahan()
    {
        return $this->pegawai ? $this->pegawai->bawahan() : Pegawai::whereRaw('1 = 0');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notif_enabled' => 'boolean',
        ];
    }
}
