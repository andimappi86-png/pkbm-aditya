<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'option_id',
        'answer_text',
        'benar',
        'nilai',
        'catatan',
        'dinilai_oleh',
        'dinilai_pada',
        'media_path',
    ];

    protected $casts = [
        'benar' => 'boolean',
        'nilai' => 'decimal:2',
        'dinilai_pada' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}