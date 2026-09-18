<?php

namespace App\Models;

use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'kategori',
        'target_role',
        'target_program',
        'target_rombel',
        'published_at',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'status_aktif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'target_program');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'target_rombel');
    }
}
