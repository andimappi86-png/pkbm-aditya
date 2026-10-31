<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Carbon;

class QuizPolicy
{
    public function view(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.view')) {
            return false;
        }

        $penugasan = $quiz->penugasanMengajar;

        if ($user->hasRole('peserta-didik')) {
            return $user->student !== null
                && $user->student->rombel_id === $penugasan->classroom_id
                && $quiz->isOpen();
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

    public function viewDraft(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.view')) {
            return false;
        }

        if ($user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return true;
        }

        if ($user->hasRole('tutor')) {
            return $quiz->penugasanMengajar->tutor_id === $user->tutor?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('quizzes.manage');
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.manage')) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.manage')) {
            return false;
        }

        // Prevent deletion if attempts exist
        if ($quiz->attempts()->exists()) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    public function publish(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.manage')) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    public function archive(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.manage')) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    public function preview(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.view')) {
            return false;
        }

        if ($user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return true;
        }

        if ($user->hasRole('tutor')) {
            return $quiz->penugasanMengajar->tutor_id === $user->tutor?->id;
        }

        return false;
    }

    public function gradeEssay(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.manage')) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    public function viewRecap(User $user, Quiz $quiz): bool
    {
        if (! $user->can('quizzes.view')) {
            return false;
        }

        return $this->canManage($user, $quiz);
    }

    private function canManage(User $user, Quiz $quiz): bool
    {
        if ($user->hasAnyRole(['operator', 'kepala-pkbm'])) {
            return true;
        }

        if ($user->hasRole('tutor')) {
            return $quiz->penugasanMengajar->tutor_id === $user->tutor?->id;
        }

        return false;
    }
}