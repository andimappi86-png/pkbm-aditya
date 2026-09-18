<?php

namespace App\Models;

use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    protected $fillable = [
        'nama_rombel',
        'kode_rombel',
        'program_id',
        'tahun_ajaran_id',
        'wali_kelas_id',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->homeroomTeacher();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'rombel_id');
    }

    public function penugasanMengajar(): HasMany
    {
        return $this->hasMany(PenugasanMengajar::class, 'classroom_id');
    }
}
