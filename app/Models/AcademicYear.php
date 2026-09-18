<?php

namespace App\Models;

use Database\Factories\AcademicYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    /** @use HasFactory<AcademicYearFactory> */
    use HasFactory;

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status_aktif' => 'boolean',
        ];
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran_id');
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'tahun_ajaran_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'tahun_ajaran_id');
    }
}
