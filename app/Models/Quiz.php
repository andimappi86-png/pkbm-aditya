<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'penugasan_mengajar_id',
        'judul',
        'deskripsi',
        'durasi_menit',
        'status_aktif',
        'max_attempts',
        'buka_waktu',
        'tutup_waktu',
        'acak_soal',
        'tampilkan_jawaban',
        'tampilkan_nilai',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'max_attempts' => 'integer',
        'buka_waktu' => 'datetime',
        'tutup_waktu' => 'datetime',
        'acak_soal' => 'boolean',
        'tampilkan_jawaban' => 'string',
        'tampilkan_nilai' => 'string',
    ];

    /**
     * @return BelongsTo<PenugasanMengajar, $this>
     */
    public function penugasanMengajar(): BelongsTo
    {
        return $this->belongsTo(PenugasanMengajar::class, 'penugasan_mengajar_id');
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isOpen(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        if (!$this->status_aktif) {
            return false;
        }

        if ($this->buka_waktu && $now->lt($this->buka_waktu)) {
            return false;
        }

        if ($this->tutup_waktu && $now->gt($this->tutup_waktu)) {
            return false;
        }

        return true;
    }

    public function isUpcoming(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        return $this->status_aktif
            && $this->buka_waktu
            && $now->lt($this->buka_waktu);
    }

    public function isClosed(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        return !$this->status_aktif
            || ($this->tutup_waktu && $now->gt($this->tutup_waktu));
    }

    public function getStatusLabel(): string
    {
        if (!$this->status_aktif) {
            return 'Draf';
        }

        if ($this->isUpcoming()) {
            return 'Dijadwalkan';
        }

        if ($this->isOpen()) {
            return 'Terbit';
        }

        return 'Ditutup';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->getStatusLabel()) {
            'Draf' => 'bg-gray-100 text-gray-700',
            'Dijadwalkan' => 'bg-blue-100 text-blue-700',
            'Terbit' => 'bg-green-100 text-green-700',
            'Ditutup' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function scopeAvailableForStudent($query, Student $student)
    {
        return $query->where('status_aktif', true)
            ->whereHas('penugasanMengajar', function ($q) use ($student) {
                $q->where('classroom_id', $student->rombel_id)
                  ->where('status_aktif', true);
            })
            ->where(function ($q) {
                $q->whereNull('buka_waktu')
                  ->orWhere('buka_waktu', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('tutup_waktu')
                  ->orWhere('tutup_waktu', '>=', now());
            });
    }
}