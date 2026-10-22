<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        if (! $user->can('assignments.view')) {
            return false;
        }

        return $this->canAccess($user, $submission);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('peserta-didik') && $user->can('assignments.submit');
    }

    public function update(User $user, Submission $submission): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $user->can('assignments.submit')
                && $submission->student_id === $user->student?->id
                && $submission->status !== 'graded';
        }

        if (! $user->can('assignments.manage')) {
            return false;
        }

        return $this->canManage($user, $submission);
    }

    public function delete(User $user, Submission $submission): bool
    {
        if (! $user->can('assignments.manage')) {
            return false;
        }

        return $this->canManage($user, $submission);
    }

    private function canAccess(User $user, Submission $submission): bool
    {
        if ($user->hasRole('peserta-didik')) {
            return $submission->student_id === $user->student?->id;
        }

        if ($user->hasRole('wali-kelas')) {
            return $submission->assignment?->penugasanMengajar?->classroom?->wali_kelas_id === $user->id;
        }

        return $this->canManage($user, $submission);
    }

    private function canManage(User $user, Submission $submission): bool
    {
        if ($user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return true;
        }

        if ($user->hasRole('tutor')) {
            return $submission->assignment?->penugasanMengajar?->tutor_id === $user->tutor?->id;
        }

        return false;
    }
}
