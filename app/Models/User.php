<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke profil pengguna
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Helper inisial nama untuk avatar fallback
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }

        return strtoupper($initials ?: 'U');
    }

    /**
     * Get avatar URL with fallback to UI Avatars
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path('storage/'.$this->avatar))) {
            return asset('storage/'.$this->avatar);
        }

        // Fallback to UI Avatars
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&size=128&background=5DB8B3&color=ffffff&bold=true";
    }

    /**
     * Get primary role name
     */
    public function primaryRole(): string
    {
        if ($this->hasRole('operator')) return 'operator';
        if ($this->hasRole('kepala-pkbm')) return 'kepala-pkbm';
        if ($this->hasRole('ketua-yayasan')) return 'ketua-yayasan';
        if ($this->hasRole('wali-kelas')) return 'wali-kelas';
        if ($this->hasRole('tutor')) return 'tutor';
        if ($this->hasRole('peserta-didik')) return 'peserta-didik';
        if ($this->hasRole('calon-peserta')) return 'calon-peserta';

        return $this->roles->first()?->name ?? 'user';
    }

    /**
     * Get dashboard route based on role
     */
    public function dashboardRoute(): string
    {
        return match ($this->primaryRole()) {
            'operator' => 'operator.dashboard',
            'ketua-yayasan' => 'dashboard.yayasan',
            'kepala-pkbm' => 'dashboard.kepala',
            'wali-kelas' => 'dashboard.walikelas',
            'tutor' => 'dashboard.tutor',
            'peserta-didik' => 'dashboard.siswa',
            'calon-peserta' => 'ppdb.index',
            default => 'dashboard',
        };
    }
}
