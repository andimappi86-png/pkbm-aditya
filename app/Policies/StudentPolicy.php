<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return $user->can('students.view') && $this->canAccess($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->can('students.update') && $this->canAccess($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('students.delete') && $this->canAccess($user, $student);
    }

    /**
     * Kemampuan mengelola siswa pada satu rombel tertentu (penambahan/penempatan).
     *
     * Kepemilikan rombel diverifikasi eksplisit di sini, bukan sekadar
     * berdasarkan izin global sehingga wali kelas tidak dapat menempatkan
     * siswa ke rombel milik wali kelas lain.
     */
    public function manageClassroom(User $user, Classroom $classroom): bool
    {
        if (! $user->can('students.create') && ! $user->can('students.update')) {
            return false;
        }

        return $this->isScopedToOwnClassrooms($user)
            ? (int) $classroom->wali_kelas_id === (int) $user->id
            : true;
    }

    /**
     * True ketika pengguna adalah wali kelas tanpa peran administratif
     * (operator/kepala-pkbm): hanya boleh mengelola rombel miliknya sendiri.
     */
    public function isScopedToOwnClassrooms(User $user): bool
    {
        return $user->hasRole('wali-kelas')
            && ! $user->hasAnyRole(['operator', 'kepala-pkbm']);
    }

    private function canAccess(User $user, Student $student): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $student->user_id === $user->id;
        }

        if ($user->hasRole('wali-kelas')) {
            return $student->classroom !== null
                && $student->classroom->wali_kelas_id === $user->id;
        }

        if ($user->hasRole('tutor')) {
            return $student->classroom !== null
                && $student->classroom->penugasanMengajar()
                    ->where('tutor_id', $user->tutor()->value('id') ?? 0)
                    ->exists();
        }

        return true;
    }
}
