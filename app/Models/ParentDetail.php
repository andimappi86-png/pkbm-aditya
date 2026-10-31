<?php

namespace App\Models;

use App\Casts\EncryptedAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'relationship_type', // ayah, ibu, wali
        'nik',
        'name',
        'birth_year',
        'education',
        'occupation',
        'monthly_income',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'nik' => EncryptedAttribute::class,
            'phone' => EncryptedAttribute::class,
            'monthly_income' => 'decimal:2',
            'birth_year' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
