<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ppdb extends Model
{
    protected $fillable = [
        'user_id',
        'nomor_pendaftaran',
        'program_id',
        'tahun_ajaran_id',
        'nik',
        'nisn',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'no_hp',
        'email',
        'nama_ayah',
        'pekerjaan_ayah',
        'no_hp_ayah',
        'nama_ibu',
        'pekerjaan_ibu',
        'no_hp_ibu',
        'nama_wali',
        'pekerjaan_wali',
        'no_hp_wali',
        'alamat_wali',
        'dok_ktp_ayah',
        'dok_ktp_ibu',
        'dok_kk',
        'dok_akta_kelahiran',
        'dok_ijasah',
        'dok_skhun',
        'dok_foto',
        'dok_ktp_wali',
        'status',
        'catatan_verifikasi',
        'verified_by',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_DIVERIFIKASI = 'diverifikasi';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_DIVERIFIKASI => 'Diverifikasi',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DIVERIFIKASI => 'bg-blue-100 text-blue-800',
            self::STATUS_DITERIMA => 'bg-green-100 text-green-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }
}
