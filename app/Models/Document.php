<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'ppdb_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'status_verifikasi',
        'catatan',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Ppdb, $this>
     */
    public function ppdb(): BelongsTo
    {
        return $this->belongsTo(Ppdb::class);
    }

    public static function jenisOptions(): array
    {
        return [
            'ktp_ayah' => 'KTP Ayah',
            'ktp_ibu' => 'KTP Ibu',
            'kk' => 'Kartu Keluarga',
            'akta_kelahiran' => 'Akta Kelahiran',
            'ijasah' => 'Ijazah',
            'skhun' => 'SKHUN',
            'foto' => 'Pas Foto',
            'ktp_wali' => 'KTP Wali',
            'rapor' => 'Rapor',
            'skl' => 'Surat Keterangan Lulus',
            'lainnya' => 'Lainnya',
        ];
    }

    public function getJenisLabelAttribute(): string
    {
        return self::jenisOptions()[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_verifikasi) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'diverifikasi' => 'bg-green-100 text-green-800',
            'ditolak' => 'bg-red-100 text-red-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status_verifikasi);
    }

    /**
     * Get the storage disk where the file currently resides.
     * Prefers private 'local' disk, falls back to legacy 'public' disk.
     */
    public function getStorageDisk(): string
    {
        if (Storage::disk('local')->exists($this->file_path)) {
            return 'local';
        }

        if (Storage::disk('public')->exists($this->file_path)) {
            return 'public';
        }

        return 'local';
    }

    /**
     * Check if the document file exists on either private or public disk.
     */
    public function fileExists(): bool
    {
        return Storage::disk('local')->exists($this->file_path)
            || Storage::disk('public')->exists($this->file_path);
    }
}
