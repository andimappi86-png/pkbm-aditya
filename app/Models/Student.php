<?php

namespace App\Models;

use App\Casts\EncryptedAttribute;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nik',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'no_hp',
        'email',
        'program_id',
        'tahun_ajaran_id',
        'rombel_id',
        'status',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'no_hp_wali',
        'alamat_wali',
        'foto',
        'nomor_akta_lahir',
        'nomor_kk',
        'urutan_anak',
        'titik_koordinat',
        'jenis_tempat_tinggal',
        'alat_transportasi',
        'jarak_rumah',
        'kebutuhan_khusus',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
        'kps_status',
        'nomor_kps',
        'pkh_status',
        'kip_status',
        'nama_kip',
        'kks_nomor',
        'pip_kelayakan',
        'pip_alasan',
        'bank_nama',
        'rekening_nomor',
        'rekening_nama',
        'nomor_peserta_un',
        'ijazah_nomor',
        'skhun_nomor',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'urutan_anak' => 'integer',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'lingkar_kepala' => 'decimal:2',
            'kps_status' => 'boolean',
            'pkh_status' => 'boolean',
            'kip_status' => 'boolean',
            'pip_kelayakan' => 'boolean',
            'nomor_kk' => EncryptedAttribute::class,
            'rekening_nomor' => EncryptedAttribute::class,
            'nik' => EncryptedAttribute::class,
            'alamat' => EncryptedAttribute::class,
            'no_hp' => EncryptedAttribute::class,
            'email' => EncryptedAttribute::class,
            'nama_ayah' => EncryptedAttribute::class,
            'nama_ibu' => EncryptedAttribute::class,
            'nama_wali' => EncryptedAttribute::class,
            'no_hp_wali' => EncryptedAttribute::class,
            'alamat_wali' => EncryptedAttribute::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    /**
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'rombel_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    /**
     * Parent details (ayah, ibu, wali)
     */
    public function parentDetails(): HasMany
    {
        return $this->hasMany(ParentDetail::class);
    }
}
