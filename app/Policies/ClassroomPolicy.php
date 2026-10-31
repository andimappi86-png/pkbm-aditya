<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function view(User $user, Classroom $classroom): bool
    {
        if (! $user->can('classrooms.view')) {
            return false;
        }

        if ($user->hasRole('wali-kelas')) {
            return $classroom->wali_kelas_id === $user->id;
        }

        if ($user->hasRole('tutor')) {
            return $classroom->penugasanMengajar()
                ->whereHas('tutor', fn ($query) => $query->where('user_id', $user->id))
                ->exists();
        }

        if ($user->hasRole('peserta-didik')) {
            return $user->student !== null && $user->student->rombel_id === $classroom->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('classrooms.create');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        if (! $user->can('classrooms.update') && ! $user->can('classrooms.manage')) {
            return false;
        }

        if ($user->hasRole('wali-kelas')) {
            return $classroom->wali_kelas_id === $user->id;
        }

        return $user->hasAnyRole(['operator', 'kepala-pkbm']);
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return ($user->can('classrooms.delete') || $user->can('classrooms.manage'))
            && $user->hasAnyRole(['operator', 'kepala-pkbm']);
    }
}
