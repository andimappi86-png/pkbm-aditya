<?php

namespace App\Models;

use Database\Factories\GradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;

    protected $fillable = [
        'penugasan_mengajar_id',
        'student_id',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nilai_tugas' => 'decimal:2',
            'nilai_uts' => 'decimal:2',
            'nilai_uas' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
        ];
    }

    public function penugasanMengajar(): BelongsTo
    {
        return $this->belongsTo(PenugasanMengajar::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
