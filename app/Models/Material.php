<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'penugasan_mengajar_id',
        'judul',
        'deskripsi',
        'tipe',
        'file_path',
        'video_url',
        'isi_materi',
        'urutan',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function penugasanMengajar(): BelongsTo
    {
        return $this->belongsTo(PenugasanMengajar::class);
    }
}
