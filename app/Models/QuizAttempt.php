<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_id', 'student_id', 'mulai_waktu', 'selesai_waktu', 'nilai', 'selesai', 'attempt_number'];

    protected $casts = [
        'mulai_waktu' => 'datetime',
        'selesai_waktu' => 'datetime',
        'selesai' => 'boolean',
        'attempt_number' => 'integer',
    ];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function isExpired(?Carbon $now = null): bool
    {
        $now = $now ?? now();

        $deadline = $this->mulai_waktu->copy()->addMinutes($this->quiz->durasi_menit);

        return $now->gt($deadline);
    }

    public function getRemainingSeconds(?Carbon $now = null): int
    {
        $now = $now ?? now();

        $deadline = $this->mulai_waktu->copy()->addMinutes($this->quiz->durasi_menit);
        $remaining = $now->diffInSeconds($deadline, false);

        return max(0, $remaining);
    }

    public function isAutoSubmitted(): bool
    {
        return $this->selesai && !$this->selesai_waktu;
    }

    /**
     * Alias accessor for legacy code compatibility.
     * Maps to mulai_waktu database column.
     */
    public function getMulaiPadaAttribute(): ?Carbon
    {
        return $this->mulai_waktu ?? $this->created_at;
    }

    /**
     * Alias accessor for legacy code compatibility.
     * Maps to selesai_waktu database column.
     */
    public function getWaktuHabisAttribute(): ?Carbon
    {
        $mulai = $this->mulai_pada;
        if (! $mulai) {
            return null;
        }

        return $mulai->copy()->addMinutes($this->quiz->durasi_menit ?? 0);
    }

    /**
     * Alias accessor for legacy code compatibility.
     * Maps to selesai_waktu database column.
     */
    public function getSelesaiPadaAttribute(): ?Carbon
    {
        return $this->selesai_waktu ?? $this->updated_at;
    }
}