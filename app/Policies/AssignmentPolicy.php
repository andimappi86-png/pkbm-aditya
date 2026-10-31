<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\PenugasanMengajar;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        if (! $user->can('assignments.view')) {
            return false;
        }

        return $this->canAccess($user, $assignment);
    }

    public function create(User $user): bool
    {
        return $user->can('assignments.manage');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if (! $user->can('assignments.manage')) {
            return false;
        }

        return $this->canManage($user, $assignment);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        if (! $user->can('assignments.manage')) {
            return false;
        }

        return $this->canManage($user, $assignment);
    }

    private function canAccess(User $user, Assignment $assignment): bool
    {
        /** @var PenugasanMengajar|null $penugasan */
        $penugasan = $assignment->penugasanMengajar;
        if (! $penugasan) {
            return false;
        }

        if ($user->hasRole('peserta-didik')) {
            return $user->student !== null
                && $user->student->rombel_id === $penugasan->classroom_id;
        }

        if ($user->hasRole('tutor')) {
            return $penugasan->tutor_id === $user->tutor?->id;
        }

        if ($user->hasRole('wali-kelas')) {
            return $penugasan->classroom !== null
                && $penugasan->classroom->wali_kelas_id === $user->id;
        }

        return true;
    }

    private function canManage(User $user, Assignment $assignment): bool
    {
        if ($user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return true;
        }

        /** @var PenugasanMengajar|null $penugasan */
        $penugasan = $assignment->penugasanMengajar;

        if ($user->hasRole('tutor')) {
            return $penugasan?->tutor_id === $user->tutor?->id;
        }

        return false;
    }
}
