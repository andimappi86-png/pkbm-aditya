<?php

namespace App\Models;

use App\Casts\EncryptedAttribute;
use Database\Factories\TutorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tutor extends Model
{
    /** @use HasFactory<TutorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_induk',
        'nik',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'no_hp',
        'email',
        'pendidikan_terakhir',
        'bidang_keahlian',
        'status_tutor',
        'tanggal_mulai_mengajar',
        'foto',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai_mengajar' => 'date',
            'tanggal_lahir' => 'date',
            'nik' => EncryptedAttribute::class,
            'alamat' => EncryptedAttribute::class,
            'no_hp' => EncryptedAttribute::class,
            'email' => EncryptedAttribute::class,
            'bidang_keahlian' => EncryptedAttribute::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tutor_subject');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'tutor_classroom');
    }
}
