<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'teks', 'is_benar'];

    protected $casts = ['is_benar' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}