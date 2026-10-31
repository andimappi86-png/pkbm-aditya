<?php

namespace App\Models;

use Database\Factories\LetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Letter extends Model
{
    /** @use HasFactory<LetterFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'jenis_surat',
        'nomor_surat',
        'tanggal',
        'tujuan',
        'isi',
        'penandatangan_id',
        'penandatangan_nama',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan_id');
    }

    public static function jenisOptions(): array
    {
        return [
            'keterangan_peserta' => 'Surat Keterangan Peserta Didik',
            'aktif_belajar' => 'Surat Keterangan Aktif Belajar',
            'pindah' => 'Surat Pindah',
            'kelakuan' => 'Surat Keterangan Kelakuan Baik',
            'lainnya' => 'Surat Lainnya',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::jenisOptions()[$this->jenis_surat] ?? $this->jenis_surat;
    }
}
